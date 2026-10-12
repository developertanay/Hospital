<?php
/** Isolated SQLite fixtures. Never mutate the installed MySQL hospital data. */
function mobileSqlFixtures() {
    set_exception_handler(function ($error) { fwrite(STDERR,$error->getMessage().PHP_EOL); exit(1); });
    config(['database.default'=>'sqlite','database.connections.sqlite.database'=>':memory:']);
    Illuminate\Support\Facades\DB::purge('sqlite');
    $existing=Illuminate\Support\Facades\RateLimiter::getFacadeRoot();
    $limiter=new Illuminate\Cache\RateLimiter(Illuminate\Support\Facades\Cache::store('array'));
    foreach (['api','mobile-login','mobile-register'] as $name) if ($definition=$existing->limiter($name)) $limiter->for($name,$definition);
    Illuminate\Support\Facades\RateLimiter::swap($limiter);
    $db=Illuminate\Support\Facades\DB::connection();
    $db->unprepared('CREATE TABLE hospital_mast (id INTEGER PRIMARY KEY,hospital_name TEXT,status INTEGER,accepting_requests INTEGER,latitude REAL,longitude REAL,contact_no TEXT,address TEXT,created_at TEXT,updated_at TEXT);
CREATE TABLE bedding_type (id INTEGER PRIMARY KEY,name TEXT,status INTEGER);
CREATE TABLE department_mast (id INTEGER PRIMARY KEY,department_name TEXT,status INTEGER);
CREATE TABLE hospital_department_mapping (id INTEGER PRIMARY KEY,hospital_id INTEGER,department_id INTEGER,status INTEGER);
CREATE TABLE blood_type (id INTEGER PRIMARY KEY,blood_type TEXT,status INTEGER);
CREATE TABLE blodd_inventory_management (id INTEGER PRIMARY KEY AUTOINCREMENT,hospital_id INTEGER,blood_id INTEGER,unit INTEGER,status INTEGER,created_by INTEGER,updated_by INTEGER,created_at TEXT,updated_at TEXT);
CREATE TABLE care_beds (id TEXT PRIMARY KEY,hospital_id INTEGER,bed_type_id INTEGER,name TEXT,status TEXT,request_id TEXT UNIQUE);
CREATE TABLE care_requests (id TEXT PRIMARY KEY,owner_id INTEGER,patient TEXT,location TEXT,latitude REAL,longitude REAL,care TEXT,bed_type_id INTEGER,severity TEXT,verified INTEGER,status TEXT,hospital_id INTEGER,bed_id TEXT,created_at INTEGER,expires_at INTEGER);
CREATE TABLE care_events (id INTEGER PRIMARY KEY AUTOINCREMENT,request_id TEXT,hospital_id INTEGER,message TEXT,at INTEGER);
CREATE TABLE care_workflow_lock (id INTEGER PRIMARY KEY);
CREATE TABLE users (id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT,email TEXT UNIQUE,password TEXT,care_role TEXT,care_enabled INTEGER,hospital_id INTEGER,role_id INTEGER,college_id INTEGER,company_id INTEGER,register_type TEXT,created_at TEXT,updated_at TEXT);
CREATE TABLE role_mast (id INTEGER PRIMARY KEY,name TEXT,college_id INTEGER,status INTEGER,sequence INTEGER);
CREATE TABLE personal_access_tokens (id INTEGER PRIMARY KEY AUTOINCREMENT,tokenable_type TEXT,tokenable_id INTEGER,name TEXT,token TEXT UNIQUE,abilities TEXT,last_used_at TEXT,created_at TEXT,updated_at TEXT);
CREATE TABLE modules (id INTEGER PRIMARY KEY AUTOINCREMENT,name TEXT,url TEXT,parent_id INTEGER,college_id INTEGER,care_group TEXT,care_roles TEXT,icon TEXT,status INTEGER,sequence INTEGER);
CREATE TABLE module_assigning (id INTEGER PRIMARY KEY AUTOINCREMENT,modules_id INTEGER,role_id INTEGER,college_id INTEGER,status INTEGER,view_flag INTEGER);');
    $db->table('care_workflow_lock')->insert(['id'=>1]);
    $db->table('bedding_type')->insert([['id'=>1,'name'=>'General','status'=>1],['id'=>2,'name'=>'ICU','status'=>1],['id'=>3,'name'=>'Ventilator','status'=>1]]);
    foreach ([1,2,3,4,5] as $id) $db->table('hospital_mast')->insert(['id'=>$id,'hospital_name'=>'SQL test hospital '.$id,'status'=>1,'accepting_requests'=>$id!==5,'latitude'=>28.6139+$id*.01,'longitude'=>77.2090,'contact_no'=>'','address'=>'Fixture']);
    foreach ([2=>2,3=>1,4=>3] as $hospital=>$capacity) for($i=1;$i<=$capacity;$i++) $db->table('care_beds')->insert(['id'=>"$hospital-ICU-$i",'hospital_id'=>$hospital,'bed_type_id'=>2,'name'=>'Fixture ICU','status'=>'Available','request_id'=>null]);
    foreach ([1=>'Admin',2=>'Patient Requester',3=>'Hospital Operator',4=>'Ambulance Driver'] as $id=>$name) $db->table('role_mast')->insert(['id'=>$id,'name'=>$name,'college_id'=>1,'status'=>1,'sequence'=>1]);
    $accounts=[[1,'admin@careroute.test','admin',1,null],[101,'patient@careroute.test','patient',2,null],[102,'other@careroute.test','patient',2,null],[201,'driver.1@careroute.test','driver',4,null],[202,'driver.2@careroute.test','driver',4,null],[203,'driver.3@careroute.test','driver',4,null],[301,'hospital.c@careroute.test','hospital',3,3]];
    $password=Illuminate\Support\Facades\Hash::make('FixturePassword@2026');
    foreach($accounts as [$id,$email,$role,$roleId,$hospital]) $db->table('users')->insert(['id'=>$id,'name'=>'Fixture '.$role,'email'=>$email,'password'=>$password,'care_role'=>$role,'care_enabled'=>1,'role_id'=>$roleId,'college_id'=>1,'hospital_id'=>$hospital]);
    $db->unprepared('CREATE TABLE ambulance_details (id INTEGER PRIMARY KEY,vehicle_number TEXT,driver_name TEXT,driver_contact TEXT,hospital_id INTEGER,status INTEGER);
        CREATE TABLE ambulance_drivers (id TEXT PRIMARY KEY,user_id INTEGER,ambulance_id INTEGER,latitude REAL,longitude REAL,on_duty INTEGER,seen_at INTEGER,location_at INTEGER,location_mode TEXT,follow_pickup INTEGER,created_at TEXT,updated_at TEXT);');
    foreach ([1=>201,2=>202,3=>203] as $number=>$userId) {
        $db->table('ambulance_details')->insert(['id'=>$number,'vehicle_number'=>'FIXTURE-'.$number,'driver_name'=>'Fixture driver '.$number,'driver_contact'=>'+91900000010'.$number,'status'=>1]);
        $db->table('ambulance_drivers')->insert(['id'=>'D'.$number,'user_id'=>$userId,'ambulance_id'=>$number,'on_duty'=>0,'seen_at'=>0,'location_at'=>0,'location_mode'=>'gps','follow_pickup'=>0]);
    }
    foreach(config('workflow.modules') as $m) {
        $id=$db->table('modules')->insertGetId(['name'=>$m['name'],'url'=>$m['url'],'college_id'=>1,'care_group'=>$m['group'],'status'=>1,'sequence'=>1]);
        foreach(['admin'=>1,'patient'=>2,'hospital'=>3] as $role=>$roleId) if(in_array($role,$m['roles'],true))$db->table('module_assigning')->insert(['modules_id'=>$id,'role_id'=>$roleId,'college_id'=>1,'status'=>1,'view_flag'=>1]);
    }
}
