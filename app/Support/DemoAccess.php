<?php
namespace App\Support;

use Illuminate\Support\Facades\Auth;
use App\Models\Modules;

class DemoAccess
{
    public static function profile()
    {
        $user = Auth::user();
        return self::forUser($user);
    }

    public static function forUser($user)
    {
        if (!$user) return null;
        $account = config('demo.accounts')[$user->email] ?? null;
        if (!$account && \App\Models\Role::where('id',$user->role_id)->where('name','Patient Requester')->exists()) {
            $account=['name'=>$user->name,'role'=>'patient','hospital'=>null];
        }
        return $account ? $account + ['id'=>$user->id,'email'=>$user->email,'role_id'=>$user->role_id,'college_id'=>$user->college_id] : null;
    }

    public static function menu()
    {
        $profile = self::profile();
        if (!$profile) return [];
        $assigned = Modules::join('module_assigning','modules.id','=','module_assigning.modules_id')
            ->where('module_assigning.role_id',$profile['role_id'])->where('module_assigning.college_id',$profile['college_id'])
            ->where('module_assigning.status',1)->where('modules.status',1)->pluck('modules.url')->all();
        return array_filter(config('demo.modules'), function ($m) use ($assigned,$profile) {
            return in_array($profile['role'],$m['roles']) && in_array($m['url'],$assigned);
        });
    }

    public static function operator() { $p=self::profile(); return $p && in_array($p['role'],['admin','hospital']); }
}
