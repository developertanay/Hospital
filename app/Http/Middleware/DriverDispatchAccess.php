<?php
namespace App\Http\Middleware;
use Closure;
use App\Support\RoleAccess;
class DriverDispatchAccess
{
    public function handle($request,Closure $next)
    {
        $profile=RoleAccess::profile();
        abort_unless($profile && $profile['role']==='driver' && RoleAccess::canPage('dispatch'),403,'Driver dispatch access required.');
        return $next($request);
    }
}
