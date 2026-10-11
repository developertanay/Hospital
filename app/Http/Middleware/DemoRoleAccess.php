<?php
namespace App\Http\Middleware;
use Closure;
use App\Support\RoleAccess;

class DemoRoleAccess
{
    public function handle($request, Closure $next)
    {
        if (auth()->check() && !auth()->user()->care_enabled) { auth()->logout(); $request->session()->invalidate(); return redirect('/login'); }
        $first=explode('/',$request->path())[0];
        // Emergency controllers check the requested page and record ownership.
        // Other module URLs use the same database assignments as the sidebar.
        if ($first !== 'emergency') {
            $path = trim($request->path(), '/');
            $query = \App\Models\Modules::query();
            if (auth()->check()) $query->where('college_id', auth()->user()->college_id);
            $modules = $query->get();
            $matching = $modules->filter(function ($module) use ($path) {
                $url = trim((string) $module->url, '/');
                return $url && $url !== '#' && ($path === $url || strpos($path, $url . '/') === 0);
            })->sortByDesc(function ($module) { return strlen($module->url); });
            $module = $matching->first();
            if ($module) {
                if (!auth()->check()) return redirect('/login');
                abort_unless(isset(RoleAccess::menu()[$module->url]), 403, 'This module is not assigned to your role.');
            }
        }
        return $next($request);
    }
}
