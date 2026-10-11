<?php
namespace App\Http\Controllers;
use App\Support\RoleAccess;
class DriverDispatchController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request,$next) {
            $profile=RoleAccess::profile();
            abort_unless($profile && $profile['role']==='driver' && RoleAccess::canPage('dispatch'),403,'Driver dispatch access required.');
            return $next($request);
        });
    }
    public function index() { return view('drivers.dispatch'); }
}
