<?php
// Isolated SQL tests. All fixtures live in SQLite memory, never in the local
// application's MySQL database. There are no production demo seed records.
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);
Illuminate\Support\Facades\DB::purge('sqlite');
Illuminate\Support\Facades\RateLimiter::swap(new Illuminate\Cache\RateLimiter(Illuminate\Support\Facades\Cache::store('array')));
$db=Illuminate\Support\Facades\DB::connection();
$db->unprepared('CREATE TABLE hospital_mast (id INTEGER PRIMARY KEY, hospital_name TEXT, status INTEGER, accepting_requests INTEGER, latitude REAL, longitude REAL, contact_no TEXT, address TEXT, created_at TEXT, updated_at TEXT);
CREATE TABLE bedding_type (id INTEGER PRIMARY KEY, name TEXT, status INTEGER);
CREATE TABLE blood_type (id INTEGER PRIMARY KEY, blood_type TEXT, status INTEGER);
CREATE TABLE blodd_inventory_management (id INTEGER PRIMARY KEY AUTOINCREMENT, hospital_id INTEGER, blood_id INTEGER, unit INTEGER, status INTEGER, created_by INTEGER, updated_by INTEGER, created_at TEXT, updated_at TEXT);
CREATE TABLE care_beds (id TEXT PRIMARY KEY, hospital_id INTEGER, bed_type_id INTEGER, name TEXT, status TEXT, request_id TEXT UNIQUE);
CREATE TABLE care_requests (id TEXT PRIMARY KEY, owner_id INTEGER, patient TEXT, location TEXT, latitude REAL, longitude REAL, care TEXT, bed_type_id INTEGER, severity TEXT, verified INTEGER, status TEXT, hospital_id INTEGER, bed_id TEXT, created_at INTEGER, expires_at INTEGER);
CREATE TABLE care_events (id INTEGER PRIMARY KEY AUTOINCREMENT, request_id TEXT, hospital_id INTEGER, message TEXT, at INTEGER);
CREATE TABLE care_workflow_lock (id INTEGER PRIMARY KEY);');
$db->table('care_workflow_lock')->insert(['id'=>1]);
$db->table('hospital_mast')->insert([
    ['id'=>1,'hospital_name'=>'Test hospital one','status'=>1,'accepting_requests'=>1,'latitude'=>28.6,'longitude'=>77.2,'contact_no'=>'','address'=>'Test address'],
    ['id'=>2,'hospital_name'=>'Test hospital two','status'=>1,'accepting_requests'=>1,'latitude'=>28.7,'longitude'=>77.3,'contact_no'=>'','address'=>'Other address'],
]);
$db->table('bedding_type')->insert([['id'=>1,'name'=>'General','status'=>1],['id'=>2,'name'=>'ICU','status'=>1]]);
$db->table('blood_type')->insert([['id'=>1,'blood_type'=>'A+','status'=>1],['id'=>2,'blood_type'=>'O-','status'=>1]]);
$service=new App\Services\EmergencyBedService;
$checks=0;
function check($condition,$message) { global $checks; if (!$condition) throw new RuntimeException($message); $checks++; }
function fails($callback) { try { $callback(); } catch (RuntimeException $e) { return true; } return false; }
function record($service,$id) { return collect($service->state()['requests'])->firstWhere('id',$id); }
function hospital($service,$id) { return collect($service->state()['hospitals'])->firstWhere('id',$id); }
check(count($service->state()['hospitals'])===2,'Only database hospitals are returned');
check(count(hospital($service,'1')['beds'])===0,'No beds are generated automatically');
$service->inventoryCrud('1','inventory','create',null,['care'=>'ICU','name'=>'Review bed']);
$bed=hospital($service,'1')['beds'][0];
check(App\Models\CareBed::count()===1,'Create writes a SQL bed record');
check(fails(fn()=>$service->inventoryCrud('2','inventory','update',$bed['id'],['care'=>'ICU','name'=>'Wrong hospital'])),'Cross-hospital edit rejected');
$service->inventoryCrud('1','inventory','update',$bed['id'],['care'=>'General','name'=>'Edited bed']);
check(App\Models\CareBed::find($bed['id'])->name==='Edited bed','Update persists in SQL');
$service->inventoryCrud('1','inventory','update',$bed['id'],['care'=>'ICU','name'=>'ICU bed']);
$input=['patient'=>'Test patient','location'=>'Test location','care'=>'ICU','severity'=>'Critical','latitude'=>28.61,'longitude'=>77.21];
$first=$service->create($input,'test-client',42);
check(record($service,$first)['priority']===1,'Unverified Critical has standard priority');
check(record($service,$first)['matches'][0]['id']==='1','Matching uses real database beds');
check(record($service,$first)['matches'][0]['distance']!==null,'Coordinates calculate distance');
check(fails(fn()=>$service->requestBed($first,'1',43)),'Patient ownership enforced');
$service->requestBed($first,'1',42);
check(App\Models\CareBed::find($bed['id'])->status==='Temporary','Pending request holds a SQL bed');
check(fails(fn()=>$service->action($first,'accept','2')),'Other hospital cannot accept');
check(fails(fn()=>$service->action($first,'accept','1')),'Triage required before acceptance');
check(fails(fn()=>$service->inventoryCrud('1','inventory','delete',$bed['id'],[])),'Temporary bed cannot be deleted');
$service->verify($first,'Urgent','1');
check(record($service,$first)['severity']==='Urgent','Hospital severity change persists');
$service->verify($first,'Critical','1');$service->action($first,'accept','1');
check(App\Models\CareBed::find($bed['id'])->status==='Reserved','Acceptance reserves SQL bed');
check(fails(fn()=>$service->manageHospital('1','discharge',['bed_id'=>$bed['id']])),'Reserved bed cannot be discharged');
$second=$service->create(array_merge($input,['patient'=>'Second patient']),'other-client',43);
$service->verify($second,'Critical','1');
check(fails(fn()=>$service->requestBed($second,'1',43)),'Confirmed reservation cannot be displaced');
$service->action($first,'arrive','1');
check(App\Models\CareBed::find($bed['id'])->status==='Occupied','Arrival updates SQL occupancy');
check(fails(fn()=>$service->inventoryCrud('1','inventory','update',$bed['id'],['name'=>'Invalid','care'=>'General'])),'Occupied bed cannot be edited');
$service->manageHospital('1','discharge',['bed_id'=>$bed['id']]);
check(record($service,$first)['status']==='Discharged','Discharge updates SQL request');
check(App\Models\CareBed::find($bed['id'])->status==='Available','Discharge releases SQL bed');
$service->requestBed($second,'1',43);
App\Models\CareRequest::where('id',$second)->update(['expires_at'=>time()-1]);
check(record($service,$second)['status']==='Expired','Expiry persists in SQL');
check(App\Models\CareBed::find($bed['id'])->status==='Available','Expiry frees SQL bed');
$stable=$service->create(array_merge($input,['patient'=>'Stable','severity'=>'Stable']),'stable',44);$service->requestBed($stable,'1',44);
$critical=$service->create(array_merge($input,['patient'=>'Critical']),'critical',45);$service->verify($critical,'Critical','1');$service->requestBed($critical,'1',45);
check(record($service,$stable)['status']==='Released','Verified critical can reclaim lower-priority hold');
check(record($service,$critical)['status']==='Pending','Reclaimed SQL bed belongs to critical request');
check(fails(fn()=>$service->changeRequest($critical,45,'cancel',[])),'Allocated request cannot be cancelled');
$service->action($critical,'reject','1');
$service->changeRequest($critical,45,'update',array_merge($input,['patient'=>'Updated request']));
check(record($service,$critical)['verified']===false,'Request edit resets verification');
check(fails(fn()=>$service->changeRequest($critical,46,'cancel',[])),'Another patient cannot cancel');
$service->changeRequest($critical,45,'cancel',[]);
check(record($service,$critical)['status']==='Cancelled','Request DELETE cancels while preserving history');
$service->inventoryCrud('1','blood','create',null,['blood_type'=>'A+','units'=>12]);
check(App\Models\BloodInv::where('hospital_id',1)->value('unit')===12,'Reuses existing blood inventory table');
check(fails(fn()=>$service->inventoryCrud('1','blood','create',null,['blood_type'=>'A+','units'=>5])),'Duplicate group rejected');
$service->inventoryCrud('1','blood','update','A+',['blood_type'=>'O-','units'=>5]);
check(hospital($service,'1')['blood']['O-']===5,'Blood group and units updated');
check(fails(fn()=>$service->inventoryCrud('2','blood','delete','O-',[])),'Cross-hospital blood delete rejected');
$service->inventoryCrud('1','blood','delete','O-',[]);
check(!isset(hospital($service,'1')['blood']['O-']),'Deleted stock removed from active inventory');
$service->manageHospital('1','settings',['status'=>'Offline','contact'=>'9876543210','address'=>'Updated address','latitude'=>28.6,'longitude'=>77.2]);
check(!App\Models\Hospital::find(1)->accepting_requests,'Settings reuse hospital master');
check(App\Models\Hospital::find(1)->contact_no==='9876543210','Phone numbers are stored as strings');
check(!record($service,$stable)['matches'][0]['compatible'],'Offline hospital is not eligible');
check(App\Models\CareEvent::count()>0,'Decision history stored in SQL');
$db->table('bedding_type')->where('id',2)->update(['name'=>'Renamed care']);
check(record($service,$stable)['care']==='Renamed care','Type rename preserves request relationship');
check(collect(hospital($service,'1')['beds'])->firstWhere('id',$bed['id'])['care']==='Renamed care','Type rename preserves bed relationship');
echo "PASS: $checks database workflow checks (isolated SQLite)\n";
