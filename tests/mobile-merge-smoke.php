<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
require __DIR__.'/support/mobile-sql-fixtures.php';mobileSqlFixtures();
$kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);$checks=0;
function mobileCheck($role,$path,$input=null,$expected=200) {
    global $kernel,$tokens,$checks;
    Illuminate\Support\Facades\Auth::forgetGuards();
    $request=Illuminate\Http\Request::create('/api/mobile/'.$path,$input===null?'GET':'POST',[],[],[],['HTTP_ACCEPT'=>'application/json','CONTENT_TYPE'=>'application/json','HTTP_AUTHORIZATION'=>'Bearer '.($tokens[$role]??'invalid')],$input===null?null:json_encode($input));
    $response=$kernel->handle($request);$kernel->terminate($request,$response);
    if($response->getStatusCode()!==$expected)throw new RuntimeException("$role $path expected $expected got ".$response->getStatusCode().' '.substr($response->getContent(),0,250));
    $checks++;return json_decode($response->getContent(),true);
}
$tokens=[];
mobileCheck('none','state',null,401);
foreach(['patient'=>'patient@careroute.test','hospital'=>'hospital.c@careroute.test','admin'=>'admin@careroute.test','driver'=>'driver.1@careroute.test','other'=>'other@careroute.test'] as $role=>$email) {
    $result=mobileCheck('none','login',['email'=>$email,'password'=>'FixturePassword@2026']);
    $tokens[$role]=$result['token'];
}
$registered=mobileCheck('none','register',['name'=>'New SQL patient','email'=>'new@fixture.test','password'=>'FixturePassword@2026','password_confirmation'=>'FixturePassword@2026'],201);
if($registered['profile']['role']!=='patient')throw new RuntimeException('Signup granted an elevated role');
$tokens['new']=$registered['token'];mobileCheck('new','state');
mobileCheck('admin','action',['action'=>'create'],403);
mobileCheck('driver','action',['action'=>'create'],403);
$id=mobileCheck('patient','action',['action'=>'create','patient'=>'SQL Mobile patient','location'=>'Accident site','latitude'=>28.6139,'longitude'=>77.2090,'severity'=>'Critical','care'=>'ICU'])['id'];
$state=mobileCheck('patient','state');
if($state['requests'][0]['id']!==$id || !$state['requests'][0]['matches'] || $state['requests'][0]['matches'][0]['id']!=='2')throw new RuntimeException('Mobile matching does not use SQL IDs/coordinates');
if(count(mobileCheck('other','state')['requests']))throw new RuntimeException('Patient privacy failed');
mobileCheck('other','action',['action'=>'request','id'=>$id,'hospital_id'=>3],403);
mobileCheck('patient','action',['action'=>'request','id'=>$id,'hospital_id'=>'C'],422);
mobileCheck('patient','action',['action'=>'request','id'=>$id,'hospital_id'=>3]);
mobileCheck('hospital','action',['action'=>'verify','id'=>$id,'severity'=>'Critical']);
mobileCheck('hospital','action',['action'=>'accept','id'=>$id]);
if(mobileCheck('patient','state')['requests'][0]['status']!=='Reserved')throw new RuntimeException('SQL reservation not visible on phone');
mobileCheck('hospital','action',['action'=>'arrive','id'=>$id]);
if(count(mobileCheck('admin','state')['requests']))throw new RuntimeException('Admin gained clinical request access');
$module=Illuminate\Support\Facades\DB::table('modules')->where('url','emergency/requests')->value('id');
Illuminate\Support\Facades\DB::table('module_assigning')->where('role_id',2)->where('modules_id',$module)->update(['status'=>0]);
$limited=mobileCheck('patient','state');
if(in_array('create',$limited['capabilities'],true) || isset($limited['requests'][0]['matches']))throw new RuntimeException('Removed module permission bypassed on mobile');
mobileCheck('patient','action',['action'=>'create'],403);
Illuminate\Support\Facades\DB::table('users')->where('id',101)->update(['care_enabled'=>0]);
mobileCheck('patient','state',null,401);mobileCheck('none','login',['email'=>'patient@careroute.test','password'=>'FixturePassword@2026'],401);
mobileCheck('new','logout',[]);mobileCheck('new','state',null,401);
echo "PASS: $checks mobile SQL merge checks (isolated SQLite)\n";
