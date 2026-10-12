<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\Models\CareRequest;
use App\Models\User;
use App\Services\EmergencyBedService;
$checks=0;
function checkDetail($condition,$message) { global $checks; if (!$condition) throw new RuntimeException($message); $checks++; }
DB::beginTransaction();
try {
    $owner=User::where('email','patient.test@careroute.test')->firstOrFail();
    $service=new EmergencyBedService;
    $hospital=DB::table('hospital_mast')->where('status',1)->first();
    $input=['patient'=>'Details '.bin2hex(random_bytes(5)),'location'=>'GPS 28.6,77.2','care'=>$service->careTypes()[0],'severity'=>'Stable','problem_type'=>'Internal','description'=>'Appendix pain <script>test</script>','latitude'=>28.6,'longitude'=>77.2,'location_captured_at'=>time(),'preferred_hospital_id'=>$hospital->id];
    $id=$service->create($input,'details-smoke',$owner->id);
    $record=CareRequest::findOrFail($id);
    checkDetail($record->assistance_preference==='Ambulance','Internal defaults to ambulance');
    checkDetail($record->description===$input['description'],'Description saved intact for escaped display');
    checkDetail(abs((float)$record->latitude-28.6)<0.00001 && $record->location_captured_at==$input['location_captured_at'],'GPS and capture time persisted');
    $input['problem_type']='External'; $input['assistance_preference']='Hospital';
    $service->changeRequest($id,$owner->id,'update',$input);
    checkDetail(CareRequest::find($id)->problem_type==='External','Problem type editable');
    $matches=collect($service->state()['requests'])->firstWhere('id',$id)['matches'];
    checkDetail(collect($matches)->firstWhere('id',(string)$hospital->id)['preferred']===true,'Preferred hospital marked in recommendations');
    checkDetail(count($matches)>1,'Other hospitals remain selectable');
    $input['assistance_preference']='Ambulance';
    $service->changeRequest($id,$owner->id,'update',$input);
    checkDetail(CareRequest::find($id)->assistance_preference==='Ambulance','External preference can be overridden');
    checkDetail(CareRequest::find($id)->preferred_hospital_id===null,'Ambulance preference clears old preferred hospital');
    foreach ([['problem_type'=>'Other'],['assistance_preference'=>'Other'],['description'=>str_repeat('x',2001)],['preferred_hospital_id'=>-1]] as $invalid) checkDetail(Validator::make($invalid,CareRequest::detailRules())->fails(),'Invalid detail rejected');
    checkDetail(Validator::make([],CareRequest::detailRules())->passes(),'Existing clients remain compatible');
    echo "$checks Request Care checks passed; changes rolled back.\n";
} finally { DB::rollBack(); }
