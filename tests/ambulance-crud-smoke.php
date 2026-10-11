<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use App\Models\Ambulance;
use App\Models\Hospital;
use App\Models\AmbulanceCategory;
use App\Http\Controllers\AmbulanceController;

$checks = 0;
$tempFiles = [];
function verifyAmbulance($condition,$message) { global $checks; if (!$condition) throw new RuntimeException($message); $checks++; }
function ambulanceRequest($data,$files=[]) { $request = Request::create('/Ambulance','POST',$data); $request->files->add($files); return $request; }
function testPhoto() {
    global $tempFiles;
    $path = tempnam(sys_get_temp_dir(),'ambulance-test-');
    $tempFiles[] = $path;
    file_put_contents($path,base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+aD1sAAAAASUVORK5CYII='));
    return new UploadedFile($path,'test.png','image/png',null,true);
}
function invalidAmbulance($controller,$data,$field,$files=[]) {
    try { $controller->store(ambulanceRequest($data,$files)); throw new RuntimeException('Invalid ambulance accepted: '.$field); }
    catch (Illuminate\Validation\ValidationException $exception) { verifyAmbulance(isset($exception->errors()[$field]),'Validation rejects '.$field); }
}
Auth::setUser(App\Models\User::where('care_role','admin')->firstOrFail());
view()->share('errors',new Illuminate\Support\ViewErrorBag);
Storage::fake('local');
DB::beginTransaction();
try {
    $controller = new AmbulanceController;
    $suffix = strtoupper(bin2hex(random_bytes(5)));
    $hospital = Hospital::where('status',1)->firstOrFail();
    $category = AmbulanceCategory::where('status',1)->firstOrFail();
    $data = ['vehicle_number'=>'TEST'.$suffix,'ambulance_category_id'=>$category->id,'operation_type'=>'hospital',
        'hospital_id'=>$hospital->id,'driver_name'=>'Test Driver','driver_contact'=>'+91 9876543210',
        'doctor_name'=>'Test Doctor','doctor_contact'=>'9876543211','operator_name'=>'Test Operator','operator_contact'=>'9876543212','status'=>1];
    verifyAmbulance(isset(App\Support\RoleAccess::menu()['Ambulance']),'Admin menu includes ambulance details');
    $controller->store(ambulanceRequest($data,['photo'=>testPhoto(),'doctor_photo'=>testPhoto(),'driver_photo'=>testPhoto()]));
    $row = Ambulance::where('vehicle_number',$data['vehicle_number'])->firstOrFail();
    verifyAmbulance($row->hospital_id===$hospital->id && $row->operation_type==='hospital','Hospital association saved');
    verifyAmbulance($row->category->id===$category->id && $row->created_by===Auth::id(),'Category and author saved');
    foreach (['photo','doctor_photo','driver_photo'] as $kind) {
        verifyAmbulance(Storage::disk('local')->exists($row->{$kind.'_path'}),'Photo saved: '.$kind);
        verifyAmbulance($controller->photo($row->id,$kind) instanceof Symfony\Component\HttpFoundation\BinaryFileResponse,'Photo served: '.$kind);
    }
    invalidAmbulance($controller,$data,'vehicle_number');
    $next = array_merge($data,['vehicle_number'=>'NEXT'.$suffix]);
    invalidAmbulance($controller,array_merge($next,['hospital_id'=>null]),'hospital_id');
    invalidAmbulance($controller,array_merge($next,['ambulance_category_id'=>2147483647]),'ambulance_category_id');
    invalidAmbulance($controller,array_merge($next,['driver_contact'=>'not-a-phone']),'driver_contact');
    invalidAmbulance($controller,array_merge($next,['doctor_contact'=>null]),'doctor_contact');
    invalidAmbulance($controller,array_merge($next,['operation_type'=>'other']),'operation_type');
    invalidAmbulance($controller,$next,'photo',['photo'=>UploadedFile::fake()->create('bad.txt',10,'text/plain')]);
    invalidAmbulance($controller,$next,'photo',['photo'=>UploadedFile::fake()->image('large.jpg')->size(2049)]);
    $oldPhoto = $row->photo_path;
    $private = array_merge($data,['operation_type'=>'private','hospital_id'=>2147483647,'status'=>2]);
    $controller->update(ambulanceRequest($private),$row->id);
    $row->refresh();
    verifyAmbulance($row->hospital_id===null && $row->operation_type==='private','Private operation clears hospital even if submitted');
    verifyAmbulance($row->photo_path===$oldPhoto && $row->status===2,'Edit preserves photos and updates status');
    $controller->update(ambulanceRequest($data,['photo'=>testPhoto()]),$row->id);
    $row->refresh();
    verifyAmbulance($row->hospital_id===$hospital->id,'Private ambulance can switch to hospital association');
    verifyAmbulance($row->photo_path!==$oldPhoto && !Storage::disk('local')->exists($oldPhoto),'Replacement removes old vehicle photo');
    $controller->update(ambulanceRequest(array_merge($data,['remove_photo'=>1])),$row->id);
    verifyAmbulance($row->fresh()->photo_path===null,'Photo removal works');
    verifyAmbulance(strpos($controller->index()->render(),'Test Driver')!==false,'Index shows driver details');
    verifyAmbulance(strpos($controller->create()->render(),'multipart/form-data')!==false,'Create form supports uploads');
    verifyAmbulance(strpos($controller->edit($row->id)->render(),'Test Doctor')!==false,'Edit form loads saved details');
    $normalized = array_merge($next,['vehicle_number'=>' test '.$suffix.' x ','operation_type'=>'private','doctor_name'=>null,'doctor_contact'=>null]);
    $controller->store(ambulanceRequest($normalized));
    verifyAmbulance(Ambulance::where('vehicle_number','TEST'.$suffix.'X')->exists(),'Vehicle number normalized');
    $controller->destroy($row->id);
    verifyAmbulance($row->fresh()->status===9,'Delete preserves record with deleted status');
    try { $controller->edit($row->id); throw new RuntimeException('Deleted record editable'); }
    catch (Illuminate\Database\Eloquent\ModelNotFoundException $e) { verifyAmbulance(true,'Deleted record cannot be edited'); }
    $routes = $app['router']->getRoutes();
    verifyAmbulance((bool)$routes->getByName('Ambulance.store')->getController(),'Resource route resolves');
    verifyAmbulance((bool)$routes->getByName('Ambulance.photo')->getController(),'Photo route resolves');
    $admin = Auth::user();
    $unassigned = clone $admin;
    $unassigned->role_id = -1;
    Auth::setUser($unassigned);
    foreach ($controller->getMiddleware() as $middleware) {
        if ($middleware['middleware'] instanceof Closure) {
            try {
                $middleware['middleware'](Request::create('/Ambulance'),function () { return 'allowed'; });
                throw new RuntimeException('Unassigned role accepted');
            } catch (Symfony\Component\HttpKernel\Exception\HttpException $e) {
                verifyAmbulance($e->getStatusCode()===403,'Unassigned role cannot access ambulance pages or photos');
            }
        }
    }
    Auth::setUser($admin);
    echo "PASS: $checks ambulance checks; all database changes rolled back\n";
} finally {
    DB::rollBack();
    Storage::disk('local')->deleteDirectory('ambulance-photos');
    foreach ($tempFiles as $path) if (is_file($path)) unlink($path);
}
