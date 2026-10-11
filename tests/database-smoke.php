<?php
// Exercise the installed MySQL schema inside one outer transaction. Every test
// record and change is rolled back, including nested workflow transactions.
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\Hospital;
use App\Models\User;
use App\Models\CareBed;
use App\Models\CareRequest;
use App\Support\RoleAccess;
use App\Services\EmergencyBedService;
Illuminate\Support\Facades\RateLimiter::swap(new Illuminate\Cache\RateLimiter(Illuminate\Support\Facades\Cache::store('array')));
$checks=0;
function verify($condition,$message) { global $checks; if (!$condition) throw new RuntimeException($message); $checks++; }
foreach (DB::select('SHOW TABLE STATUS') as $table) {
    if (in_array($table->Name,['hospital_mast','users','blodd_inventory_management','care_beds','care_requests','care_events','care_workflow_lock']) && $table->Engine!=='InnoDB') throw new RuntimeException('Transactional tables required for rollback tests.');
}
$counts=[Hospital::count(),User::count(),CareBed::count(),CareRequest::count()];
DB::beginTransaction();
try {
    $hospital=Hospital::create(['hospital_name'=>'Transaction verification hospital','short_name'=>'VERIFY','registration_no'=>'VERIFY','type'=>'Test','host'=>'verify','logo'=>'','state'=>0,'district'=>0,'city'=>0,'address'=>'Verification address','pin_code'=>0,'contact_no'=>'9876543210','emergency_no'=>'','email_id'=>'','website'=>'','geo_location'=>'','status'=>1,'created_by'=>0,'updated_by'=>0,'accepting_requests'=>true,'latitude'=>28.6,'longitude'=>77.2]);
    $roleIds=[];
    foreach (['admin'=>'Admin','hospital'=>'Hospital Operator','patient'=>'Patient Requester'] as $role=>$name) {
        $roleIds[$role]=DB::table('role_mast')->where('name',$name)->value('id');
        verify((bool)$roleIds[$role],'Required role exists: '.$role);
    }
    $users=[];
    foreach ($roleIds as $role=>$roleId) $users[$role]=User::create(['name'=>'Verification '.$role,'email'=>bin2hex(random_bytes(8)).'@verification.invalid','password'=>Illuminate\Support\Facades\Hash::make(bin2hex(random_bytes(12))),'care_role'=>$role,'care_enabled'=>true,'hospital_id'=>$role==='hospital' ? $hospital->id : null,'role_id'=>$roleId,'college_id'=>1]);
    Auth::setUser($users['admin']);
    verify(!RoleAccess::canPage('requests') && !RoleAccess::canPage('approvals'),'Admin cannot perform care workflows');
    Auth::setUser($users['hospital']);
    verify(RoleAccess::profile()['hospital']===(string)$hospital->id,'Hospital profile comes from SQL assignment');
    verify(RoleAccess::canPage('inventory') && !RoleAccess::canPage('requests'),'Hospital page access is role scoped');
    $service=new EmergencyBedService;
    $care=$service->careTypes()[0] ?? null;
    $group=$service->bloodTypes()[0] ?? null;
    verify((bool)$care && (bool)$group,'Existing bed and blood types are reused');
    $service->inventoryCrud((string)$hospital->id,'inventory','create',null,['care'=>$care,'name'=>'Verification bed']);
    $bed=CareBed::where('hospital_id',$hospital->id)->first();
    verify((bool)$bed,'Bed persisted in actual MySQL schema');
    $service->inventoryCrud((string)$hospital->id,'blood','create',null,['blood_type'=>$group,'units'=>2]);
    verify(App\Models\BloodInv::where('hospital_id',$hospital->id)->value('unit')===2,'Blood saved in old table with hospital ownership');
    Auth::setUser($users['patient']);
    verify(RoleAccess::canPage('requests') && !RoleAccess::canPage('inventory'),'Patient cannot access hospital inventory');
    $id=$service->create(['patient'=>'Verification patient','location'=>'Verification location','care'=>$care,'severity'=>'Critical','latitude'=>28.61,'longitude'=>77.21],'verification',$users['patient']->id);
    $service->requestBed($id,(string)$hospital->id,$users['patient']->id);
    Auth::setUser($users['hospital']);
    $service->verify($id,'Critical',(string)$hospital->id);$service->action($id,'accept',(string)$hospital->id);$service->action($id,'arrive',(string)$hospital->id);
    verify(CareRequest::find($id)->status==='Occupied','Full workflow persists against MySQL');
    $service->manageHospital((string)$hospital->id,'discharge',['bed_id'=>$bed->id]);
    verify(CareRequest::find($id)->status==='Discharged' && CareBed::find($bed->id)->status==='Available','Discharge updates request and bed atomically');
    $service->inventoryCrud((string)$hospital->id,'inventory','delete',$bed->id,[]);
    verify(!CareBed::find($bed->id),'Available bed deletion persists');
    app('view')->share('errors',new Illuminate\Support\ViewErrorBag);
    $inventory=app(App\Http\Controllers\HospitalInventoryController::class);
    $createView=$inventory->create(Illuminate\Http\Request::create('/emergency/inventory/create'));
    verify(strpos($createView->render(),'Verification')!==false,'Inventory Add form renders for assigned SQL hospital');
    $bloodView=$inventory->index(Illuminate\Http\Request::create('/emergency/blood'));
    verify(strpos($bloodView->render(),$group)!==false,'Blood list reads saved SQL stock');
    $stock=App\Models\BloodInv::where('hospital_id',$hospital->id)->where('status',1)->first();
    $stockInput=Illuminate\Http\Request::create('/emergency/blood/'.$stock->id,'PUT',['blood_type'=>$group,'units'=>7]);
    $inventory->update($stockInput,$stock->id);
    verify(App\Models\BloodInv::find($stock->id)->unit===7,'Blood resource update preserves record ID');
    $inventory->destroy($stockInput,$stock->id);
    verify(App\Models\BloodInv::find($stock->id)->status===9,'Blood resource DELETE archives its SQL record');
    $settings=app(App\Http\Controllers\HospitalSettingsController::class);
    verify(strpos($settings->edit($hospital->id)->render(),'9876543210')!==false,'Settings Edit form renders SQL contact');
    Auth::setUser($users['patient']);
    $requests=app(App\Http\Controllers\EmergencyRequestController::class);
    verify(strpos($requests->create()->render(),$care)!==false,'Request Add form reads SQL care types');
    $input=Illuminate\Http\Request::create('/emergency/requests','POST',['patient'=>'Resource verification','location'=>'Resource location','care'=>$care,'severity'=>'Stable'],[],[],['HTTP_ACCEPT'=>'application/json']);
    $response=$requests->store($input);
    $createdId=$response->getData()->id;
    verify((bool)CareRequest::find($createdId),'Resource store creates SQL request');
    $input=Illuminate\Http\Request::create('/emergency/requests/'.$createdId,'PUT',['patient'=>'Resource verification edited','location'=>'Resource location','care'=>$care,'severity'=>'Urgent']);
    $requests->update($input,$createdId);
    verify(CareRequest::find($createdId)->patient==='Resource verification edited','Resource update persists SQL request');
    $requests->destroy($input,$createdId);
    verify(CareRequest::find($createdId)->status==='Cancelled','Resource delete cancels own request');
    Auth::setUser($users['admin']);
    $accounts=app(App\Http\Controllers\AccountController::class);
    verify(strpos($accounts->edit($users['hospital']->id)->render(),$hospital->hospital_name)!==false,'Account Edit form lists real hospitals');
} finally { Auth::forgetGuards(); DB::rollBack(); }
verify($counts===[Hospital::count(),User::count(),CareBed::count(),CareRequest::count()],'Outer rollback preserves existing database');
echo "PASS: $checks installed-database checks; all test changes rolled back\n";
