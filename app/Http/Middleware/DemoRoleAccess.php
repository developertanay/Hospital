<?php
namespace App\Http\Middleware;
use Closure;
use App\Support\DemoAccess;

class DemoRoleAccess
{
    public function handle($request, Closure $next)
    {
        $first=explode('/',$request->path())[0];
        $masters=['Hospital','Bedtype','BloodType','BedInven','BloodInven','Module','ModuleMaster','ModuleAssigning','NewUser'];
        if (in_array($first,$masters) && !auth()->check()) return redirect('/login');
        $p=DemoAccess::profile();
        if ($p && in_array($first,$masters) && $p['role']!=='admin') abort(403,'This module is assigned to the admin role.');
        if ($p && $p['role']!=='admin' && !in_array($first,['','emergency','login','logout','dashboard','home'])) abort(403,'This module is not assigned to your demo role.');
        return $next($request);
    }
}
