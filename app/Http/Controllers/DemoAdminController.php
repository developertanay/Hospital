<?php
namespace App\Http\Controllers;
use App\Support\RoleAccess;
use Illuminate\Support\Facades\DB;
class DemoAdminController extends Controller
{
    public function index($page='modules')
    {
        $p=RoleAccess::profile();
        abort_unless($p && $p['role']==='admin',403,'Admin access required.');
        if ($page==='users') return redirect()->route('Accounts.index');
        $accounts=DB::table('users')->whereNotNull('care_role')->select('id','name','email','role_id','care_role','hospital_id')->get();
        $assignments=DB::table('module_assigning')->join('role_mast','role_mast.id','=','module_assigning.role_id')
            ->where('module_assigning.college_id',$p['college_id'])->where('module_assigning.status',1)
            ->where('module_assigning.view_flag',1)->select('module_assigning.modules_id','role_mast.name')->get();
        $modules=DB::table('modules')->where('college_id',$p['college_id'])->where('status',1)->get();
        return view('demo.admin',compact('page','accounts','assignments','modules'));
    }
}
