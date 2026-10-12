<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
require __DIR__.'/support/sql-test-account.php';
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\CareRequest;
use App\Models\AmbulanceDriver;
use App\Models\PatientEmergencyProfile;
use App\Services\EmergencyBedService;
use App\Services\AmbulanceService;
$checks=0;
function flowCheck($condition,$message){global $checks;if(!$condition)throw new RuntimeException($message);$checks++;}
function flowReject($callback,$message){try{$callback();}catch(RuntimeException $e){flowCheck(true,$message);return;}throw new RuntimeException($message);}
function flowApi($user,$body,$expected=200,$path='sos/action'){
    global $app;
    $token=$user->createToken('care-flow-test',['careroute-mobile'])->plainTextToken;
    \Illuminate\Support\Facades\Auth::shouldUse('web');\Illuminate\Support\Facades\Auth::forgetGuards();
    $kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);
    $request=Illuminate\Http\Request::create('/api/mobile/'.$path,$body===null?'GET':'POST',[],[],[],['HTTP_ACCEPT'=>'application/json','CONTENT_TYPE'=>'application/json','HTTP_AUTHORIZATION'=>'Bearer '.$token],$body===null?null:json_encode($body));
    $response=$kernel->handle($request);$kernel->terminate($request,$response);
    flowCheck($response->getStatusCode()===$expected,'Booking API expected '.$expected.' got '.$response->getStatusCode().' '.substr($response->getContent(),0,180));
    return json_decode($response->getContent(),true);
}
DB::beginTransaction();
try{
    $template=User::where('email','patient.test@careroute.test')->firstOrFail();
    $patients=[];
    foreach([1,2] as $n){$user=$template->replicate();$user->email='flow-'.bin2hex(random_bytes(5)).'@test.invalid';$user->save();$patients[]=$user;PatientEmergencyProfile::create(['user_id'=>$user->id,'name'=>'Flow patient '.$n,'mobile'=>'9000000001','age'=>30,'emergency_name'=>'Test contact','emergency_phone'=>'9000000002']);}
    $hospital=DB::table('hospital_mast')->where('registration_no','APP-TEST-HOSPITAL')->first();
    $department=DB::table('department_mast')->where('department_name','General Medicine')->first();
    $otherService=\App\Models\Department::create(['department_name'=>'Flow unmatched '.bin2hex(random_bytes(4)),'status'=>1]);
    $beds=new EmergencyBedService;$ambulances=new AmbulanceService;
    $care=DB::table('bedding_type')->where('id',DB::table('care_beds')->where('hospital_id',$hospital->id)->value('bed_type_id'))->value('name');
    $requests=[];
    foreach($patients as $n=>$user){$id=$beds->create(['patient'=>'Flow '.$user->id,'location'=>'GPS test','latitude'=>28.6139,'longitude'=>77.209,'care'=>$care,'severity'=>'Stable','problem_type'=>$n===0?'Internal':'External','department_id'=>$department->id,'description'=>'Test care details'],'flow',$user->id);$requests[]=CareRequest::findOrFail($id);}
    $state=$beds->state();$matches=collect($state['requests'])->firstWhere('id',$requests[0]->id)['matches'];
    $hospitalUser=User::where('email','hospital.test@careroute.test')->firstOrFail();
    flowCheck(collect(flowApi($hospitalUser,null,200,'state')['requests'])->firstWhere('id',$requests[0]->id)===null,'Unbooked patient form is not broadcast to hospitals');
    flowCheck(collect($matches)->firstWhere('id',(string)$hospital->id)['compatible'],'Mapped service and bed produce compatible hospital');
    CareRequest::where('id',$requests[0]->id)->update(['department_id'=>$otherService->id]);
    flowReject(fn()=>$beds->requestBed($requests[0]->id,(string)$hospital->id,$patients[0]->id),'Wrong service cannot be booked through crafted request');
    CareRequest::where('id',$requests[0]->id)->update(['department_id'=>$department->id]);
    $drivers=[];
    foreach([1,2] as $n){$user=isolatedSqlAccount('driver'.$n.'.test@careroute.test');$driver=AmbulanceDriver::where('user_id',$user->id)->firstOrFail();$driver->update(['on_duty'=>true,'seen_at'=>time(),'location_at'=>time(),'location_mode'=>'gps','follow_pickup'=>false,'latitude'=>28.6139+$n*.001,'longitude'=>77.209]);$drivers[]=['id'=>$user->id,'role'=>'driver','driver'=>$driver->id];}
    $location=['lat'=>28.6139,'lng'=>77.209,'accuracy'=>5,'captured_at'=>time(),'mode'=>'gps'];
    $requests[0]->update(['latitude'=>null,'longitude'=>null]);
    flowApi($patients[0],['action'=>'nearby','request_id'=>$requests[0]->id],409);
    flowApi($patients[0],['action'=>'nearby','request_id'=>$requests[0]->id]+$location);
    flowCheck($requests[0]->fresh()->latitude!==null,'Missing patient GPS can be fixed from ambulance selection');
    $nearby=$ambulances->nearby($location)['ambulances'];
    flowCheck(in_array($drivers[0]['driver'],array_column($nearby,'driver_id'),true),'Idle GPS ambulance visible before booking');
    flowCheck($nearby[0]['distance_km']<=$nearby[count($nearby)-1]['distance_km'],'Idle list ordered by nearest');
    $p=['id'=>$patients[0]->id,'role'=>'patient'];$p2=['id'=>$patients[1]->id,'role'=>'patient'];
    flowApi($patients[1],['action'=>'nearby','request_id'=>$requests[0]->id],404);
    flowCheck(count(flowApi($patients[0],['action'=>'nearby','request_id'=>$requests[0]->id])['ambulances'])>0,'Patient API returns nearest idle vehicles');
    $booking=flowApi($patients[0],['action'=>'book','request_id'=>$requests[0]->id,'driver_id'=>$drivers[0]['driver']]+$location,201);
    flowCheck(!in_array($drivers[0]['driver'],array_column($ambulances->nearby($location)['ambulances'],'driver_id'),true),'Pending booking removes selected ambulance from idle list');
    flowReject(fn()=>$ambulances->book($p2,$requests[1],$drivers[0]['driver'],$location),'Second patient cannot double book ambulance');
    flowApi($patients[1],['action'=>'book','request_id'=>$requests[1]->id,'driver_id'=>$drivers[0]['driver']]+$location,409);
    flowCheck(collect($ambulances->state($drivers[0])['calls'])->firstWhere('id',$booking['id'])!==null,'Selected driver sees booking');
    flowCheck(collect($ambulances->state($drivers[1])['calls'])->firstWhere('id',$booking['id'])===null,'Other driver does not receive selected booking');
    $trip=collect($ambulances->state($p)['calls'])->firstWhere('id',$booking['id']);
    flowCheck(isset($trip['driver']['phone'],$trip['driver']['vehicle'],$trip['eta_minutes']),'Patient gets driver details and ETA immediately after booking');
    flowCheck($trip['description']==='Test care details' && $trip['care_request_id']===$requests[0]->id,'Booking preserves form description and request association');
    $ambulances->action($drivers[0],$booking['id'],'accept');
    flowCheck(collect($ambulances->state($p)['calls'])->firstWhere('id',$booking['id'])['status']==='Accepted','Driver accepts selected booking');
    $moved=$location;$moved['lat']+=.0001;$ambulances->location($p,$booking['id'],$moved);
    flowCheck(abs((float)$requests[0]->fresh()->latitude-$moved['lat'])<.000001,'Live pickup also updates linked care GPS');
    $beds->requestBed($requests[0]->id,(string)$hospital->id,$patients[0]->id);
    flowCheck($requests[0]->fresh()->status==='Pending' && $requests[0]->fresh()->hospital_id===$hospital->id,'Patient can also book hospital bed on same care request');
    flowCheck(collect(flowApi($hospitalUser,null,200,'state')['requests'])->firstWhere('id',$requests[0]->id)!==null,'Booked bed appears to its hospital owner');
    $beds->verify($requests[0]->id,'Stable',(string)$hospital->id);
    $beds->action($requests[0]->id,'accept',(string)$hospital->id);
    flowCheck($requests[0]->fresh()->status==='Reserved','Respective hospital accepts bed request');
    $ambulances->action($drivers[0],$booking['id'],'arrive');$ambulances->action($drivers[0],$booking['id'],'pickup');
    $ambulances->action($drivers[0],$booking['id'],'transport',['care'=>$care,'hospital_id'=>(string)$hospital->id],$beds);
    flowCheck(collect($ambulances->state($p)['calls'])->firstWhere('id',$booking['id'])['bed_request_id']===$requests[0]->id,'Driver reuses patient booking instead of duplicating hospital request');
    $beds->action($requests[0]->id,'arrive',(string)$hospital->id);$ambulances->action($drivers[0],$booking['id'],'handoff',[],$beds);
    flowCheck(in_array($drivers[0]['driver'],array_column($ambulances->nearby($location)['ambulances'],'driver_id'),true),'Completed handoff releases driver');
    $again=$ambulances->book($p2,$requests[1],$drivers[1]['driver'],$location);$ambulances->action($drivers[1],$again['id'],'decline');
    flowCheck(collect($ambulances->state($p2)['calls'])->firstWhere('id',$again['id'])['status']==='Cancelled','Decline tells patient to choose another ambulance');
    $driver=AmbulanceDriver::find($drivers[1]['driver']);$driver->update(['location_at'=>time()-121]);
    flowCheck(!in_array($driver->id,array_column($ambulances->nearby($location)['ambulances'],'driver_id'),true),'Stale GPS ambulance hidden');
    echo "$checks care booking checks passed; all changes rolled back.\n";
}finally{DB::rollBack();}
