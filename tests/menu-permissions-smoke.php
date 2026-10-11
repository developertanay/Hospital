<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Support\RoleAccess;
use App\Http\Controllers\ModuleAssigningController;
use Illuminate\Http\Request;

$checks = 0;
function checkMenu($condition, $message) {
    global $checks;
    if (!$condition) throw new RuntimeException($message);
    $checks++;
}
DB::beginTransaction();
try {
    $user = App\Models\User::where('care_role', 'admin')->firstOrFail();
    Auth::setUser($user);
    $roleId = DB::table('role_mast')->insertGetId(['name'=>'Menu verification','college_id'=>$user->college_id,'status'=>1,'sequence'=>999]);
    $moduleId = DB::table('modules')->insertGetId(['college_id'=>$user->college_id,'name'=>'Dynamic verification','url'=>'menu-verification','icon'=>'bx bx-check','status'=>1,'sequence'=>999,'care_roles'=>null]);
    $controller = new ModuleAssigningController;
    $controller->store(Request::create('/ModuleAssigning','POST',['role_id'=>$roleId,'module_ids'=>[$moduleId]]));
    $viewer = clone $user;
    $viewer->role_id = $roleId;
    $viewer->care_role = null;
    Auth::setUser($viewer);
    checkMenu(isset(RoleAccess::menu()['menu-verification']), 'Assigned module without fixed care role must appear');
    checkMenu(RoleAccess::menu()['menu-verification']['icon']==='bx bx-check', 'Icon comes from database');
    DB::table('module_assigning')->where('role_id',$roleId)->update(['view_flag'=>0]);
    checkMenu(!isset(RoleAccess::menu()['menu-verification']), 'View permission disables menu');
    DB::table('module_assigning')->where('role_id',$roleId)->update(['view_flag'=>1]);
    DB::table('modules')->where('id',$moduleId)->update(['status'=>0]);
    checkMenu(!isset(RoleAccess::menu()['menu-verification']), 'Inactive module is hidden');
    DB::table('modules')->where('id',$moduleId)->update(['status'=>1]);
    Auth::setUser($user);
    $controller->store(Request::create('/ModuleAssigning','POST',['role_id'=>$roleId]));
    Auth::setUser($viewer);
    checkMenu(RoleAccess::menu()===[], 'Empty selection removes all assignments');
    Auth::setUser($user);
    try {
        $controller->store(Request::create('/ModuleAssigning','POST',['role_id'=>$roleId,'module_ids'=>[2147483647]]));
        throw new RuntimeException('Invalid module accepted');
    } catch (Illuminate\Validation\ValidationException $exception) {
        checkMenu(true, 'Invalid module rejected');
    }
    echo "PASS: $checks dynamic menu checks; all changes rolled back\n";
} finally {
    DB::rollBack();
    Auth::setUser($user);
}
