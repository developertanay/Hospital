<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
require __DIR__.'/support/mobile-sql-fixtures.php'; mobileSqlFixtures();
require __DIR__.'/support/sos-photo-fixtures.php'; $cleanup=sosPhotoFixtureDisk();

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Models\AmbulanceTrip;
use App\Models\User;
use App\Services\AmbulanceDispatchStore;
use App\Services\SosReporterVoiceService;

DB::unprepared('CREATE TABLE patient_emergency_profiles (user_id INTEGER PRIMARY KEY,name TEXT,mobile TEXT,age INTEGER,emergency_name TEXT,emergency_phone TEXT,created_at TEXT,updated_at TEXT);
CREATE TABLE ambulance_trips (id TEXT PRIMARY KEY,owner_id INTEGER,driver_id TEXT,status TEXT,payload TEXT,created_at TEXT,updated_at TEXT);
CREATE TABLE ambulance_dispatch_lock (id INTEGER PRIMARY KEY);');
DB::table('ambulance_dispatch_lock')->insert(['id'=>1]);
$module=DB::table('modules')->insertGetId(['name'=>'Driver Dispatch','url'=>'emergency/dispatch','college_id'=>1,'care_group'=>'Care coordination','status'=>1,'sequence'=>1]);
DB::table('module_assigning')->insert(['modules_id'=>$module,'role_id'=>4,'college_id'=>1,'status'=>1,'view_flag'=>1]);
$kernel=$app->make(Illuminate\Contracts\Http\Kernel::class); $users=[]; $tokens=[]; $checks=0;
function voiceCheck($condition,$message) { global $checks; if (!$condition) throw new RuntimeException($message); $checks++; }
function voiceHttp($role,$path,$body=null,$expected=200,$web=false,$range=null) {
    global $kernel,$users,$tokens;
    Auth::shouldUse('web'); Auth::forgetGuards(); if ($web) Auth::setUser($users[$role]);
    $headers=['HTTP_ACCEPT'=>'application/json','CONTENT_TYPE'=>'application/json','HTTP_AUTHORIZATION'=>'Bearer '.($tokens[$role]??'invalid')];
    if ($range) $headers['HTTP_RANGE']=$range;
    $request=Illuminate\Http\Request::create($web?$path:'/api/mobile/'.$path,$body===null?'GET':'POST',[],[],[],$headers,$body===null?null:json_encode($body));
    $response=$kernel->handle($request); $kernel->terminate($request,$response);
    voiceCheck($response->getStatusCode()===$expected,$role.' '.$path.' expected '.$expected.' got '.$response->getStatusCode().' '.substr($response->getContent(),0,180));
    return $response;
}
function voiceApi($role,$path,$body=null,$expected=200) { return json_decode(voiceHttp($role,$path,$body,$expected)->getContent(),true); }
function voiceFileCount() { return count(Storage::disk('local')->allFiles()); }
function changedAtom($bytes,$type,$relative,$replacement) {
    $position=strpos($bytes,$type); if ($position===false) throw new RuntimeException('Test atom missing: '.$type);
    return substr_replace($bytes,$replacement,$position+4+$relative,strlen($replacement));
}

try {
    foreach (['patient'=>'patient@careroute.test','other'=>'other@careroute.test','driver'=>'driver.1@careroute.test','driver2'=>'driver.2@careroute.test','driver3'=>'driver.3@careroute.test','hospital'=>'hospital.c@careroute.test','admin'=>'admin@careroute.test'] as $role=>$email) {
        $users[$role]=User::where('email',$email)->firstOrFail();
        $tokens[$role]=$users[$role]->createToken('sos-voice-fixture',['careroute-mobile'])->plainTextToken;
    }
    voiceApi('patient','sos/action',['action'=>'profile','name'=>'Reporter','mobile'=>'9000000011','age'=>31,'emergency_name'=>'Contact','emergency_phone'=>'9000000012']);
    foreach (['driver','driver2'] as $role) voiceApi($role,'sos/action',['action'=>'duty','on_duty'=>true,'lat'=>28.6139,'lng'=>77.209,'mode'=>'demo']);
    $base=['action'=>'sos','lat'=>28.6139,'lng'=>77.209,'accuracy'=>5,'captured_at'=>time(),'mode'=>'demo','reporter_photo_base64'=>sosPhotoFixtureBase64()];
    $recording=sosVoiceFixtureInput(); $bytes=base64_decode($recording['reporter_voice_base64']); $valid=$base+$recording;
    voiceApi('patient','sos/action',$base,422);
    voiceApi('patient','sos/action',$base+['reporter_voice_base64'=>$recording['reporter_voice_base64']],422);
    voiceApi('patient','sos/action',array_replace($valid,['reporter_voice_base64'=>'not base64']),422);
    voiceApi('patient','sos/action',array_replace($valid,['reporter_voice_base64'=>base64_encode('not an audio recording')]),422);
    voiceApi('patient','sos/action',array_replace($valid,['reporter_voice_base64'=>base64_encode(str_repeat('x',2097153))]),422);
    foreach ([0,8999,11001,999999,'10000.5'] as $duration) voiceApi('patient','sos/action',array_replace($valid,['reporter_voice_duration_ms'=>$duration]),422);
    $badRecordings=[
        substr($bytes,0,-1),
        changedAtom($bytes,'hdlr',8,'vide'),
        substr_replace($bytes,'avc1',strpos($bytes,'mp4a'),4),
        changedAtom($bytes,'mdhd',12,pack('N',1)),
        changedAtom($bytes,'mdhd',16,pack('N',44100)),
        changedAtom($bytes,'stts',4,pack('N',0)),
        changedAtom($bytes,'stsz',8,pack('N',0)),
        changedAtom($bytes,'stsc',8,pack('N',2)),
    ];
    foreach ($badRecordings as $bad) voiceApi('patient','sos/action',array_replace($valid,['reporter_voice_base64'=>base64_encode($bad)]),422);
    voiceCheck(AmbulanceTrip::count()===0 && voiceFileCount()===0,'Rejected voice uploads create neither trips nor photo/voice files');
    $id=voiceApi('patient','sos/action',$valid,201)['id']; $trip=AmbulanceTrip::find($id)->payload; $voice=$trip['reporter_voice']; $path=$voice['path'];
    voiceCheck(strpos($path,'sos-reporter-voices/')===0 && Storage::disk('local')->get($path)===$bytes,'Actual AAC recording remains associated with its SQL SOS on the private disk');
    voiceCheck($voice['duration_ms']>=9000 && $voice['duration_ms']<=11000 && $voice['duration_ms']!==0,'Duration comes from the recording sample timing');
    $state=voiceApi('patient','sos/state')['calls'][0];
    voiceCheck($state['has_reporter_voice'] && $state['reporter_voice_duration_ms']===$voice['duration_ms'] && strpos($state['reporter_voice_url'],'/api/mobile/sos/'.$id.'/reporter-voice')!==false,'Authenticated state gives the private audio URL and server duration');
    voiceCheck(!isset($state['reporter_voice']) && strpos(json_encode($state),$path)===false && strpos(json_encode($state),$recording['reporter_voice_base64'])===false,'State never exposes storage paths or voice upload bytes');
    $response=voiceHttp('patient','sos/'.$id.'/reporter-voice');
    voiceCheck($response->headers->get('Content-Type')==='audio/mp4' && $response->headers->get('X-Audio-Duration-Ms')===(string)$voice['duration_ms'] && strpos($response->headers->get('Cache-Control'),'no-store')!==false && $response->headers->get('X-Content-Type-Options')==='nosniff','Playback headers identify private audio and its actual duration');
    ob_start(); $response->sendContent(); $download=ob_get_clean(); voiceCheck($download===$bytes,'Authorized GET streams the stored recording');
    $partial=voiceHttp('driver','sos/'.$id.'/reporter-voice',null,206,false,'bytes=0-31');
    ob_start(); $partial->sendContent(); $fragment=ob_get_clean(); voiceCheck($fragment===substr($bytes,0,32),'Authorized audio playback supports byte ranges');
    voiceHttp('driver2','sos/'.$id.'/reporter-voice'); voiceHttp('admin','sos/'.$id.'/reporter-voice');
    voiceHttp('other','sos/'.$id.'/reporter-voice',null,403); voiceHttp('driver3','sos/'.$id.'/reporter-voice',null,403);
    voiceHttp('hospital','sos/'.$id.'/reporter-voice',null,403); voiceHttp('anonymous','sos/'.$id.'/reporter-voice',null,401);
    voiceHttp('patient','sos/missing/reporter-voice',null,404);
    voiceHttp('driver','/emergency/dispatch/'.$id.'/reporter-voice',null,200,true);
    voiceHttp('other','/emergency/dispatch/'.$id.'/reporter-voice',null,403,true);
    $before=voiceFileCount(); $repeat=voiceApi('patient','sos/action',$valid,201);
    voiceCheck($repeat['id']===$id && voiceFileCount()===$before && AmbulanceTrip::find($id)->payload['reporter_voice']['path']===$path,'Retry preserves the active SOS original media without orphan files');
    voiceApi('driver','sos/action',['action'=>'accept','id'=>$id]);
    voiceHttp('driver2','sos/'.$id.'/reporter-voice',null,403); voiceHttp('driver','sos/'.$id.'/reporter-voice');
    voiceApi('patient','sos/action',['action'=>'location','id'=>$id,'lat'=>28.6139,'lng'=>77.209,'accuracy'=>5,'captured_at'=>time(),'mode'=>'demo']);
    foreach (['arrive','pickup','first_aid','complete'] as $action) voiceApi('driver','sos/action',['action'=>$action,'id'=>$id]);
    voiceHttp('patient','sos/'.$id.'/reporter-voice'); voiceHttp('driver','sos/'.$id.'/reporter-voice');
    voiceCheck(Storage::disk('local')->exists($path),'Completed SOS retains its server recording');
    $legacy=$trip; $legacy['id']=bin2hex(random_bytes(6)); $legacy['status']='Cancelled'; $legacy['offers']=[]; $legacy['created_at']=time()-1000; unset($legacy['reporter_voice']);
    AmbulanceTrip::create(['id'=>$legacy['id'],'owner_id'=>$legacy['owner_id'],'driver_id'=>null,'status'=>'Cancelled','payload'=>$legacy]);
    voiceHttp('patient','sos/'.$legacy['id'].'/reporter-voice',null,404);
    $old=collect(voiceApi('patient','sos/state')['calls'])->firstWhere('id',$legacy['id']);
    voiceCheck($old['has_reporter_voice']===false && $old['reporter_voice_url']===null && $old['reporter_voice_duration_ms']===null,'Legacy SOS and ordinary bookings remain readable without voice');
    $before=voiceFileCount(); $beforeTrips=AmbulanceTrip::count();
    $app->instance(AmbulanceDispatchStore::class,new class extends AmbulanceDispatchStore {
        public function transaction($callback) { return parent::transaction(function (&$state) use ($callback) { $callback($state); throw new RuntimeException('Simulated dispatch persistence failure.'); }); }
    });
    voiceApi('patient','sos/action',$valid,409); $app->instance(AmbulanceDispatchStore::class,new AmbulanceDispatchStore);
    voiceCheck(voiceFileCount()===$before && AmbulanceTrip::count()===$beforeTrips,'SQL failure removes both freshly stored photo and voice');
    $app->instance(SosReporterVoiceService::class,new class extends SosReporterVoiceService { public function store($recording) { throw new RuntimeException('Simulated voice disk failure.'); } });
    voiceApi('patient','sos/action',$valid,409); $app->instance(SosReporterVoiceService::class,new SosReporterVoiceService);
    voiceCheck(voiceFileCount()===$before && AmbulanceTrip::count()===$beforeTrips,'Voice storage failure also removes the new photo and creates no trip');
    voiceApi('patient','sos/action',array_replace($valid,['captured_at'=>time()-121]),409);
    voiceCheck(voiceFileCount()===$before,'Stale location causes no new media files');
    echo "PASS: $checks automatic SOS voice checks (isolated SQLite and private files)\n";
} finally { $cleanup(); }
