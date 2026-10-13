<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
require __DIR__.'/support/mobile-sql-fixtures.php'; mobileSqlFixtures();
require __DIR__.'/support/sos-photo-fixtures.php'; $cleanupPhotos=sosPhotoFixtureDisk();

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\AmbulanceTrip;
use App\Models\User;
use App\Services\AmbulanceDispatchStore;
use App\Services\SosReporterPhotoService;

// Exercise SQL persistence with disposable SQLite data, including failures after a file write.
DB::unprepared('CREATE TABLE patient_emergency_profiles (user_id INTEGER PRIMARY KEY,name TEXT,mobile TEXT,age INTEGER,emergency_name TEXT,emergency_phone TEXT,created_at TEXT,updated_at TEXT);
CREATE TABLE ambulance_trips (id TEXT PRIMARY KEY,owner_id INTEGER,driver_id TEXT,status TEXT,payload TEXT,created_at TEXT,updated_at TEXT);
CREATE TABLE ambulance_dispatch_lock (id INTEGER PRIMARY KEY);
CREATE TABLE patient_dependents (id INTEGER PRIMARY KEY AUTOINCREMENT,owner_id INTEGER,name TEXT,relationship TEXT,age INTEGER,mobile TEXT,status INTEGER,created_at TEXT,updated_at TEXT);');
DB::table('ambulance_dispatch_lock')->insert(['id'=>1]);
$dispatchModule=DB::table('modules')->insertGetId(['name'=>'Driver Dispatch','url'=>'emergency/dispatch','college_id'=>1,'care_group'=>'Care coordination','status'=>1,'sequence'=>1]);
DB::table('module_assigning')->insert(['modules_id'=>$dispatchModule,'role_id'=>4,'college_id'=>1,'status'=>1,'view_flag'=>1]);
$kernel=$app->make(Illuminate\Contracts\Http\Kernel::class); $tokens=[]; $users=[]; $checks=0;
function photoCheck($condition,$message) { global $checks; if (!$condition) throw new RuntimeException($message); $checks++; }
function photoHttp($role,$path,$body=null,$expected=200,$web=false) {
    global $kernel,$tokens,$users;
    Auth::shouldUse('web'); Auth::forgetGuards();
    if ($web) Auth::setUser($users[$role]);
    $request=Illuminate\Http\Request::create($web?$path:'/api/mobile/'.$path,$body===null?'GET':'POST',[],[],[],[
        'HTTP_ACCEPT'=>'application/json','CONTENT_TYPE'=>'application/json','HTTP_AUTHORIZATION'=>'Bearer '.($tokens[$role]??'invalid'),
    ],$body===null?null:json_encode($body));
    $response=$kernel->handle($request); $kernel->terminate($request,$response);
    photoCheck($response->getStatusCode()===$expected,$role.' '.$path.' expected '.$expected.' got '.$response->getStatusCode().' '.substr($response->getContent(),0,200));
    return $response;
}
function photoApi($role,$path,$body=null,$expected=200) { return json_decode(photoHttp($role,$path,$body,$expected)->getContent(),true); }
function photoCount() { return count(Storage::disk('local')->allFiles('sos-reporter-photos')); }

try {
    foreach (['patient'=>'patient@careroute.test','other'=>'other@careroute.test','driver'=>'driver.1@careroute.test','driver2'=>'driver.2@careroute.test','driver3'=>'driver.3@careroute.test','hospital'=>'hospital.c@careroute.test','admin'=>'admin@careroute.test'] as $role=>$email) {
        $users[$role]=User::where('email',$email)->firstOrFail();
        $tokens[$role]=$users[$role]->createToken('sos-photo-fixture',['careroute-mobile'])->plainTextToken;
    }
    $image=sosPhotoFixtureBase64();
    $fix=['action'=>'sos','lat'=>28.6139,'lng'=>77.209,'accuracy'=>5,'captured_at'=>time(),'mode'=>'demo'];
    photoApi('patient','sos/action',['action'=>'profile','name'=>'Reporter','mobile'=>'9000000011','age'=>31,'emergency_name'=>'Contact','emergency_phone'=>'9000000012']);
    foreach (['driver','driver2'] as $role) photoApi($role,'sos/action',['action'=>'duty','on_duty'=>true,'lat'=>28.6139,'lng'=>77.209,'mode'=>'demo']);
    photoApi('patient','sos/action',$fix,422);
    photoApi('patient','sos/action',$fix+['reporter_photo_base64'=>'not base64!'],422);
    photoApi('patient','sos/action',$fix+['reporter_photo_base64'=>base64_encode('this is not an image')],422);
    photoApi('patient','sos/action',$fix+['reporter_photo_base64'=>base64_encode(str_repeat('x',2097153))],422);
    $wide=imagecreatetruecolor(4097,1);ob_start();imagepng($wide);$wideBytes=ob_get_clean();imagedestroy($wide);
    photoApi('patient','sos/action',$fix+['reporter_photo_base64'=>base64_encode($wideBytes)],422);
    $pixels=imagecreatetruecolor(3000,2001);ob_start();imagepng($pixels);$pixelBytes=ob_get_clean();imagedestroy($pixels);
    photoApi('patient','sos/action',$fix+['reporter_photo_base64'=>base64_encode($pixelBytes)],422);
    photoCheck(AmbulanceTrip::count()===0 && photoCount()===0,'Rejected captures create no trip or private file');
    $valid=$fix+['reporter_photo_base64'=>$image]+sosVoiceFixtureInput();
    $id=photoApi('patient','sos/action',$valid,201)['id'];
    $trip=AmbulanceTrip::findOrFail($id)->payload; $path=$trip['reporter_photo']['path'];
    photoCheck(strpos($path,'sos-reporter-photos/')===0 && Storage::disk('local')->exists($path),'Photo is associated with the SQL trip and stored on its private disk');
    photoCheck(getimagesizefromstring(Storage::disk('local')->get($path))['mime']==='image/jpeg','Stored photo is a genuine canonical JPEG');
    $state=photoApi('patient','sos/state')['calls'][0];
    photoCheck($state['has_reporter_photo'] && strpos($state['reporter_photo_url'],'/api/mobile/sos/'.$id.'/reporter-photo')!==false,'Patient receives an authenticated photo URL');
    photoCheck(!isset($state['reporter_photo']) && strpos(json_encode($state),$path)===false,'State never discloses the private path or upload bytes');
    $response=photoHttp('patient','sos/'.$id.'/reporter-photo');
    photoCheck($response->headers->get('Content-Type')==='image/jpeg' && strpos($response->headers->get('Cache-Control'),'no-store')!==false && $response->headers->get('X-Content-Type-Options')==='nosniff','Photo responses have private image headers');
    ob_start();$response->sendContent();$download=ob_get_clean();
    photoCheck($download===Storage::disk('local')->get($path),'Authenticated response streams the stored photo');
    photoHttp('driver','sos/'.$id.'/reporter-photo');
    photoHttp('driver2','sos/'.$id.'/reporter-photo');
    photoHttp('admin','sos/'.$id.'/reporter-photo');
    photoHttp('other','sos/'.$id.'/reporter-photo',null,403);
    photoHttp('driver3','sos/'.$id.'/reporter-photo',null,403);
    photoHttp('hospital','sos/'.$id.'/reporter-photo',null,403);
    photoHttp('anonymous','sos/'.$id.'/reporter-photo',null,401);
    photoHttp('patient','sos/nonexistent/reporter-photo',null,404);
    $driverState=photoApi('driver','sos/state')['calls'][0];
    photoCheck($driverState['has_reporter_photo'] && !isset($driverState['reporter_photo']) && !isset($driverState['reporter']),'Driver sees the sender photo without unassigned contact details');
    $repeat=photoApi('patient','sos/action',$valid,201);
    photoCheck($repeat['id']===$id && photoCount()===1 && AmbulanceTrip::find($id)->payload['reporter_photo']['path']===$path,'Existing active SOS keeps its original photo and creates no orphan');
    photoHttp('driver','/emergency/dispatch/'.$id.'/reporter-photo',null,200,true);
    photoHttp('other','/emergency/dispatch/'.$id.'/reporter-photo',null,403,true);
    photoApi('driver','sos/action',['action'=>'accept','id'=>$id]);
    photoHttp('driver2','sos/'.$id.'/reporter-photo',null,403);
    photoHttp('driver','sos/'.$id.'/reporter-photo');
    photoApi('patient','sos/action',['action'=>'location','id'=>$id,'lat'=>28.6139,'lng'=>77.209,'accuracy'=>5,'captured_at'=>time(),'mode'=>'demo']);
    foreach (['arrive','pickup','first_aid','complete'] as $action) photoApi('driver','sos/action',['action'=>$action,'id'=>$id]);
    photoHttp('driver','sos/'.$id.'/reporter-photo');
    photoHttp('patient','sos/'.$id.'/reporter-photo');
    photoCheck(Storage::disk('local')->exists($path),'Finished SOS retains its server photo');
    $legacy=$trip; $legacy['id']=bin2hex(random_bytes(6));$legacy['status']='Cancelled';$legacy['offers']=[];$legacy['created_at']=time()-1000;unset($legacy['reporter_photo']);
    AmbulanceTrip::create(['id'=>$legacy['id'],'owner_id'=>$legacy['owner_id'],'driver_id'=>null,'status'=>'Cancelled','payload'=>$legacy]);
    photoHttp('patient','sos/'.$legacy['id'].'/reporter-photo',null,404);
    $legacyState=collect(photoApi('patient','sos/state')['calls'])->firstWhere('id',$legacy['id']);
    photoCheck($legacyState['has_reporter_photo']===false && $legacyState['reporter_photo_url']===null,'Legacy trips remain readable without a photo');
    $before=photoCount();$beforeTrips=AmbulanceTrip::count();
    $app->instance(AmbulanceDispatchStore::class,new class extends AmbulanceDispatchStore {
        public function transaction($callback) {
            return parent::transaction(function (&$state) use ($callback) { $callback($state); throw new RuntimeException('Simulated SQL persistence failure.'); });
        }
    });
    photoApi('patient','sos/action',$valid,409);
    $app->instance(AmbulanceDispatchStore::class,new AmbulanceDispatchStore);
    photoCheck(photoCount()===$before && AmbulanceTrip::count()===$beforeTrips,'Persistence failure rolls back the trip and deletes its new private photo');
    photoApi('patient','sos/action',array_replace($valid,['captured_at'=>time()-121]),409);
    photoCheck(photoCount()===$before,'Stale GPS leaves no orphan upload');
    // Family and bystander requests still photograph the reporter, independently of recipient details.
    $dependent=App\Models\PatientDependent::create(['owner_id'=>$users['patient']->id,'name'=>'Father','relationship'=>'Father','age'=>64,'mobile'=>'9000000013','status'=>1]);
    $family=['reported_for'=>'Family','dependent_id'=>$dependent->id,'reporter_name'=>'Reporter','reporter_phone'=>'9000000011','reporter_with_patient'=>true,'description'=>'Father needs care'];
    photoApi('patient','sos/action',$fix+$family,422);
    $familyId=photoApi('patient','sos/action',$valid+$family,201)['id'];
    $familyTrip=AmbulanceTrip::find($familyId)->payload;
    photoCheck($familyTrip['patient']['name']==='Father' && $familyTrip['reporter']['name']==='Reporter' && isset($familyTrip['reporter_photo']),'Family SOS retains reporter photo separately from the patient');
    $other=['reported_for'=>'Other','reporter_name'=>'Bystander','reporter_phone'=>'9000000014','reporter_with_patient'=>true,'description'=>'Unknown person needs help'];
    photoApi('other','sos/action',$fix+$other,422);
    $otherId=photoApi('other','sos/action',$valid+$other,201)['id'];
    photoCheck(isset(AmbulanceTrip::find($otherId)->payload['reporter_photo']),'Bystander SOS also requires and stores its reporter photo');
    // Camera portrait EXIF orientation is applied before metadata is removed.
    $jpeg=base64_decode($image);$exif="Exif\0\0".'II'.pack('vV',42,8).pack('v',1).pack('vvVv',0x112,3,1,6)."\0\0".pack('V',0);
    $oriented=substr($jpeg,0,2)."\xff\xe1".pack('n',strlen($exif)+2).$exif.substr($jpeg,2);
    $processed=app(SosReporterPhotoService::class)->validate(Illuminate\Http\Request::create('/','POST',['reporter_photo_base64'=>base64_encode($oriented)]));
    photoCheck($processed['width']===48 && $processed['height']===64,'Front-camera portrait orientation survives safe reencoding');
    foreach (['imagepng','imagewebp'] as $encode) {
        $source=imagecreatetruecolor(20,10);ob_start();$encode($source);$bytes=ob_get_clean();imagedestroy($source);
        $processed=app(SosReporterPhotoService::class)->validate(Illuminate\Http\Request::create('/','POST',['reporter_photo_base64'=>base64_encode($bytes)]));
        photoCheck(getimagesizefromstring($processed['bytes'])['mime']==='image/jpeg','Valid '.$encode.' camera photo is canonicalized safely');
    }
    echo "PASS: $checks mandatory SOS photo checks (isolated SQLite and private files)\n";
} finally { $cleanupPhotos(); }
