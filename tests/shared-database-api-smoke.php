<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\AmbulanceTrip;
use App\Models\PatientEmergencyProfile;
use App\Models\AmbulanceDriver;
use App\Models\CareRequest;

Illuminate\Support\Facades\RateLimiter::swap(new Illuminate\Cache\RateLimiter(Illuminate\Support\Facades\Cache::store('array')));
foreach (['api','mobile-login','mobile-register'] as $name) Illuminate\Support\Facades\RateLimiter::for($name,fn($request)=>Illuminate\Cache\RateLimiting\Limit::perMinute(200)->by($request->ip()));
$kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);
$tokens=[]; $checks=0;
function sharedCheck($condition,$message) { global $checks; if (!$condition) throw new RuntimeException($message); $checks++; }
function sharedApi($role,$path,$body=null,$expected=200) {
    global $kernel,$tokens,$checks;
    Auth::shouldUse('web');
    Auth::forgetGuards();
    $request=Illuminate\Http\Request::create('/api/mobile/'.$path,$body===null?'GET':'POST',[],[],[],
        ['HTTP_ACCEPT'=>'application/json','CONTENT_TYPE'=>'application/json','HTTP_AUTHORIZATION'=>'Bearer '.($tokens[$role] ?? '')],$body===null?null:json_encode($body));
    $response=$kernel->handle($request); $kernel->terminate($request,$response);
    sharedCheck($response->getStatusCode()===$expected,$role.' '.$path.' expected '.$expected.' got '.$response->getStatusCode().' '.substr($response->getContent(),0,200));
    return json_decode($response->getContent(),true);
}
DB::beginTransaction();
try {
    $emails=['patient'=>'patient.test@careroute.test','hospital'=>'hospital.test@careroute.test','driver'=>'driver1.test@careroute.test','driver2'=>'driver2.test@careroute.test','admin'=>'admin.test@careroute.test'];
    $health=sharedApi('anonymous','health');
    sharedCheck($health['mock_data']===false && $health['storage']==='mysql','Health confirms database-backed service');
    sharedApi('anonymous','state',null,401);
    $users=[];
    foreach ($emails as $role=>$email) {
        $users[$role]=User::where('email',$email)->firstOrFail();
        sharedCheck(Auth::guard('web')->attempt(['email'=>$email,'password'=>'TestCare@2026']),'Same SQL credentials authenticate on web for '.$role);
        Auth::guard('web')->logout();
        $login=sharedApi('anonymous','login',['email'=>$email,'password'=>'TestCare@2026']);
        $tokens[$role]=$login['token'];
        sharedCheck($login['profile']['id']===$users[$role]->id,'Mobile identity matches web identity for '.$role);
        sharedApi($role,'state');
    }
    sharedApi('anonymous','login',['email'=>$emails['patient'],'password'=>'IncorrectTestPassword'],401);
    sharedApi('hospital','sos/state',null,403);
    $users['patient']->care_enabled=false; $users['patient']->save();
    sharedApi('patient','state',null,401);
    $users['patient']->care_enabled=true; $users['patient']->save();
    sharedApi('patient','sos/action',['action'=>'profile','name'=>'Shared SQL Patient','mobile'=>'+19999999991','age'=>30,'emergency_name'=>'Test Contact','emergency_phone'=>'+19999999992']);
    sharedCheck(PatientEmergencyProfile::find($users['patient']->id)->name==='Shared SQL Patient','Emergency profile stored in MySQL');
    foreach (['driver','driver2'] as $role) sharedApi($role,'sos/action',['action'=>'duty','on_duty'=>true,'lat'=>28.6139,'lng'=>77.2090,'mode'=>'demo','follow_pickup'=>true]);
    $tripId=sharedApi('patient','sos/action',['action'=>'sos','lat'=>28.6139,'lng'=>77.2090,'accuracy'=>5,'captured_at'=>time(),'mode'=>'demo'],201)['id'];
    sharedCheck(AmbulanceTrip::find($tripId)->status==='Searching','Trip stored in MySQL');
    $offers=sharedApi('driver','sos/state');
    $offered=collect($offers['calls'])->firstWhere('id',$tripId);
    sharedCheck($offered && !isset($offered['patient']['mobile']),'Driver sees offer without unassigned patient contact');
    sharedApi('driver','sos/action',['action'=>'accept','id'=>$tripId]);
    sharedApi('driver2','sos/action',['action'=>'accept','id'=>$tripId],409);
    sharedCheck(AmbulanceTrip::find($tripId)->driver_id===AmbulanceDriver::where('user_id',$users['driver']->id)->value('id'),'First acceptance is assigned in SQL');
    sharedApi('driver2','sos/action',['action'=>'pickup','id'=>$tripId],409);
    sharedApi('driver','sos/action',['action'=>'arrive','id'=>$tripId]);
    sharedApi('driver','sos/action',['action'=>'pickup','id'=>$tripId]);
    $care=App\Models\CareBed::with('type')->where('hospital_id',$users['hospital']->hospital_id)->firstOrFail()->type->name;
    sharedApi('driver','sos/action',['action'=>'transport','id'=>$tripId,'care'=>$care,'hospital_id'=>$users['hospital']->hospital_id]);
    $trip=AmbulanceTrip::find($tripId)->payload;
    $requestId=$trip['bed_request_id'];
    sharedCheck(CareRequest::find($requestId)->owner_id===$users['patient']->id,'Ambulance transfer creates same SQL patient request');
    sharedApi('hospital','action',['action'=>'verify','id'=>$requestId,'severity'=>'Critical']);
    sharedApi('hospital','action',['action'=>'accept','id'=>$requestId]);
    sharedApi('hospital','action',['action'=>'arrive','id'=>$requestId]);
    sharedApi('driver','sos/action',['action'=>'handoff','id'=>$tripId]);
    sharedCheck(AmbulanceTrip::find($tripId)->status==='Completed' && CareRequest::find($requestId)->status==='Occupied','Patient-driver-hospital flow shares MySQL state');
    sharedApi('driver','logout',[]);
    sharedCheck(!AmbulanceDriver::where('user_id',$users['driver']->id)->first()->on_duty,'Driver logout removes availability');
    sharedApi('driver','state',null,401);
    echo "PASS: $checks installed SQL web/mobile checks; all test writes rolled back\n";
} finally { DB::rollBack(); }
