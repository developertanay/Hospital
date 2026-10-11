<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\CareBed;
use App\Models\bedtype;
use App\Http\Controllers\HospitalInventoryController;
$checks=0;
function bedCheck($condition,$message) { global $checks; if (!$condition) throw new RuntimeException($message); $checks++; }
view()->share('errors',new Illuminate\Support\ViewErrorBag);
DB::beginTransaction();
try {
    $user=App\Models\User::where('email','hospital.test@careroute.test')->firstOrFail();
    Auth::setUser($user);
    $type=bedtype::create(['name'=>'Custom-'.bin2hex(random_bytes(4)),'status'=>1,'created_by'=>$user->id,'updated_by'=>$user->id]);
    $controller=new HospitalInventoryController;
    $form=$controller->create(Request::create('/emergency/inventory/create'));
    bedCheck(strpos($form->render(),$type->name)!==false,'Form includes active SQL bed type');
    $name='Dynamic verification '.bin2hex(random_bytes(4));
    $response=$controller->store(Request::create('/emergency/inventory','POST',['care'=>$type->name,'name'=>$name]));
    $bed=CareBed::where('hospital_id',$user->hospital_id)->where('name',$name)->first();
    bedCheck((bool)$bed,'Resource store accepts a custom SQL bed type');
    bedCheck((int)$bed->bed_type_id===(int)$type->id && $bed->status==='Available','New bed has correct type and available status');
    $controller->update(Request::create('/emergency/inventory/'.$bed->id,'PUT',['care'=>$type->name,'name'=>$name.' edited']),$bed->id);
    bedCheck($bed->fresh()->name===$name.' edited','Custom-type bed can be edited');
    $type->status=2; $type->save();
    try {
        $controller->store(Request::create('/emergency/inventory','POST',['care'=>$type->name,'name'=>'Inactive verification']));
        throw new RuntimeException('Inactive bed type accepted');
    } catch (Illuminate\Validation\ValidationException $e) { bedCheck(isset($e->errors()['care']),'Inactive bed types rejected'); }
    $controller->destroy(Request::create('/emergency/inventory/'.$bed->id,'DELETE'),$bed->id);
    bedCheck(!CareBed::find($bed->id),'Available custom-type bed can be deleted');
    echo "PASS: $checks dynamic bed CRUD checks; all changes rolled back\n";
} finally { Auth::forgetGuards(); DB::rollBack(); }
