<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
require __DIR__.'/support/mobile-sql-fixtures.php'; mobileSqlFixtures();
$path=storage_path('app/sos-test-api-'.bin2hex(random_bytes(4)).'.json');
$app->instance(App\Services\AmbulanceService::class,new App\Services\AmbulanceService($path));
$app->instance(App\Services\EmergencyBedService::class,new App\Services\EmergencyBedService);
$kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);$tokens=[];$issued=[];$count=0;
function sosHttp($role,$path,$data=null,$expected=200) {
    global $kernel,$issued,$count;
    Illuminate\Support\Facades\Auth::forgetGuards();
    $request=Illuminate\Http\Request::create('/api/mobile/'.$path,$data===null?'GET':'POST',[],[],[],['HTTP_ACCEPT'=>'application/json','CONTENT_TYPE'=>'application/json','HTTP_AUTHORIZATION'=>'Bearer '.($issued[$role]??'invalid')],$data===null?null:json_encode($data));
    $response=$kernel->handle($request); $kernel->terminate($request,$response);
    if($response->getStatusCode()!==$expected)throw new RuntimeException("$role $path: expected $expected got ".$response->getStatusCode().' '.substr($response->getContent(),0,250));
    $count++;return json_decode($response->getContent(),true);
}
try {
    foreach(['patient'=>'patient@careroute.test','driver'=>'driver.1@careroute.test','driver2'=>'driver.2@careroute.test','hospital'=>'hospital.c@careroute.test','admin'=>'admin@careroute.test']as $key=>$email) {
        $user=App\Models\User::where('email',$email)->firstOrFail();$t=$user->createToken('sos-test',['careroute-mobile']);$tokens[]=$t->accessToken;$issued[$key]=$t->plainTextToken;
    }
    sosHttp('unknown','sos/state',null,401);sosHttp('hospital','sos/state',null,403);
    sosHttp('patient','sos/action',['action'=>'driver_contact','phone'=>'+919000000001'],403);
    sosHttp('driver','sos/action',['action'=>'driver_contact','phone'=>'123'],422);
    sosHttp('driver','sos/action',['action'=>'driver_contact','phone'=>'+919000000099']);
    sosHttp('patient','sos/action',['action'=>'duty','on_duty'=>true],403);
    sosHttp('driver','action',['action'=>'create'],403);
    sosHttp('driver','sos/action',['action'=>'profile'],403);
    sosHttp('patient','sos/action',['action'=>'profile','name'=>'API SOS Patient','mobile'=>'123','age'=>30,'emergency_name'=>'Contact','emergency_phone'=>'+919000000002'],422);
    sosHttp('patient','sos/action',['action'=>'profile','name'=>'API SOS Patient','mobile'=>'+919000000001','age'=>30,'emergency_name'=>'Contact','emergency_phone'=>'+919000000002']);
    sosHttp('driver','sos/action',['action'=>'duty','on_duty'=>true,'lat'=>28.6139,'lng'=>77.2090,'mode'=>'demo']);
    sosHttp('driver2','sos/action',['action'=>'duty','on_duty'=>true,'lat'=>28.6239,'lng'=>77.2190,'mode'=>'demo']);
    $fix=['action'=>'sos','lat'=>28.6139,'lng'=>77.2090,'accuracy'=>5,'captured_at'=>time(),'mode'=>'demo'];
    sosHttp('patient','sos/action',array_merge($fix,['accuracy'=>500]),422);
    $id=sosHttp('patient','sos/action',$fix,201)['id'];
    $offer=sosHttp('driver','sos/state');if(isset($offer['calls'][0]['patient']['mobile']))throw new RuntimeException('Contacts leaked before acceptance');
    $secondOffer=sosHttp('driver2','sos/state'); if(count($secondOffer['calls'])!==1)throw new RuntimeException('Broadcast missing for second driver');
    sosHttp('driver','sos/action',['action'=>'accept','id'=>$id]);
    sosHttp('driver2','sos/action',['action'=>'accept','id'=>$id],409);
    $state=sosHttp('patient','sos/state');if($state['calls'][0]['driver']['phone']!=='+919000000099')throw new RuntimeException('Updated driver contact not shared');if($state['calls'][0]['status']!=='Accepted')throw new RuntimeException('Patient state not updated');
    sosHttp('driver','sos/action',['action'=>'arrive','id'=>$id]);sosHttp('driver','sos/action',['action'=>'pickup','id'=>$id]);
    sosHttp('driver','sos/action',['action'=>'first_aid','id'=>$id]);
    sosHttp('driver','sos/action',['action'=>'transport','id'=>$id,'care'=>'ICU','hospital_id'=>'3']);
    $linked=sosHttp('patient','sos/state')['calls'][0]['bed_request_id'];
    sosHttp('driver','sos/action',['action'=>'handoff','id'=>$id],409);
    sosHttp('hospital','action',['action'=>'verify','id'=>$linked,'severity'=>'Critical']);sosHttp('hospital','action',['action'=>'accept','id'=>$linked]);sosHttp('hospital','action',['action'=>'arrive','id'=>$linked]);
    sosHttp('driver','sos/action',['action'=>'handoff','id'=>$id]);
    if(sosHttp('patient','sos/state')['calls'][0]['status']!=='Completed')throw new RuntimeException('Handoff failed');
    echo "PASS: $count authenticated SOS API checks (isolated stores)\n";
}finally{foreach($tokens as $t)$t->delete();foreach([$path,$path.'.lock',$path.'.tmp',$path.'.beds',$path.'.beds.lock',$path.'.beds.tmp']as$f)if(file_exists($f))unlink($f);}
