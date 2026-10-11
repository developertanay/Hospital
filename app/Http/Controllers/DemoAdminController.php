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
        $assignments=DB::table('module_assigning')->where('college_id',1)->where('status',1)->get();
        $modules=DB::table('modules')->whereNotNull('care_roles')->where('status',1)->get();
        return view('demo.admin',compact('page','accounts','assignments','modules'));
    }
}
