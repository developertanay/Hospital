<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\AmbulanceCategory;
use App\Models\HospitalDepartment;

$checks = 0;
function assertMaster($condition, $message) { global $checks; if (!$condition) throw new RuntimeException($message); $checks++; }
Auth::setUser(App\Models\User::where('care_role','admin')->firstOrFail());
view()->share('errors', new Illuminate\Support\ViewErrorBag);
DB::beginTransaction();
try {
    $suffix = bin2hex(random_bytes(5));
    $department = new App\Http\Controllers\DepartmentController;
    $ambulance = new App\Http\Controllers\AmbulanceCategoryController;
    $mapping = new App\Http\Controllers\HospitalDepartmentController;
    $department->store(Request::create('/Department','POST',['department_name'=>'Test '.$suffix,'status'=>1]));
    $row = Department::where('department_name','Test '.$suffix)->firstOrFail();
    assertMaster($row->created_by===Auth::id(),'Department creation records author');
    $department->update(Request::create('/Department','PUT',['department_name'=>'Edited '.$suffix,'status'=>2]),$row->id);
    assertMaster($row->fresh()->status===2,'Department update works');
    $department->update(Request::create('/Department','PUT',['department_name'=>'Edited '.$suffix,'status'=>1]),$row->id);
    $ambulance->store(Request::create('/AmbulanceCategory','POST',['name'=>'Test '.$suffix,'status'=>1]));
    $category = AmbulanceCategory::where('name','Test '.$suffix)->firstOrFail();
    $ambulance->update(Request::create('/AmbulanceCategory','PUT',['name'=>'Edited '.$suffix,'status'=>2]),$category->id);
    assertMaster($category->fresh()->status===2,'Ambulance update works');
    $hospital = App\Models\Hospital::where('status',1)->firstOrFail();
    $data = ['hospital_id'=>$hospital->id,'department_id'=>$row->id,'status'=>1];
    $mapping->store(Request::create('/HospitalDepartment','POST',$data));
    $link = HospitalDepartment::where('department_id',$row->id)->firstOrFail();
    assertMaster($link->hospital->id===$hospital->id,'Mapping relationships work');
    try { $mapping->store(Request::create('/HospitalDepartment','POST',$data)); throw new RuntimeException('Duplicate accepted'); }
    catch (Illuminate\Validation\ValidationException $e) { assertMaster(true,'Duplicate mapping rejected'); }
    $department->destroy($row->id);
    assertMaster($row->fresh()->status===1,'Mapped department deletion blocked');
    foreach ([$department,$ambulance,$mapping] as $controller) {
        assertMaster(strlen($controller->index()->render())>0,'Index renders');
        assertMaster(strlen($controller->create()->render())>0,'Create renders');
    }
    $mapping->destroy($link->id);
    $mapping->store(Request::create('/HospitalDepartment','POST',$data));
    assertMaster($link->fresh()->status===1,'Deleted mapping can be restored');
    $mapping->destroy($link->id);
    $department->destroy($row->id);
    $ambulance->destroy($category->id);
    assertMaster($row->fresh()->status===9 && $category->fresh()->status===9,'Soft deletion works');
    $before = [Department::count(),AmbulanceCategory::count(),HospitalDepartment::count(),App\Models\Hospital::count()];
    (new Database\Seeders\HospitalMasterSampleSeeder)->run();
    assertMaster($before===[Department::count(),AmbulanceCategory::count(),HospitalDepartment::count(),App\Models\Hospital::count()],'Sample seeder does not duplicate existing data');
    foreach (['Department','AmbulanceCategory','HospitalDepartment'] as $resource) {
        assertMaster(isset(App\Support\RoleAccess::menu()[$resource]),'Admin can open '.$resource.' from menu');
        $route = $app['router']->getRoutes()->getByName($resource.'.store');
        assertMaster($route && $route->getController(),'Resource controller resolves');
    }
    echo "PASS: $checks hospital master checks; test changes rolled back\n";
} finally { DB::rollBack(); }
