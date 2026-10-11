<?php
namespace App\Http\Controllers;
use App\Support\DemoAccess;
use Illuminate\Support\Facades\DB;
class DemoAdminController extends Controller
{
    public function index($page='modules')
    {
        $p=DemoAccess::profile();
        abort_unless($p && $p['role']==='admin',403,'Admin access required.');
        $accounts=DB::table('users')->whereIn('email',array_keys(config('demo.accounts')))->select('id','name','email','role_id')->get();
        $assignments=DB::table('module_assigning')->where('college_id',1)->where('status',1)->get();
        $modules=DB::table('modules')->where('college_id',1)->get();
        return view('demo.admin',compact('page','accounts','assignments','modules'));
    }
}
