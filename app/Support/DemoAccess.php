<?php
namespace App\Support;

use Illuminate\Support\Facades\Auth;
use App\Models\Modules;

class DemoAccess
{
    public static function profile()
    {
        $user = Auth::user();
        if (!$user) return null;
        $account = config('demo.accounts')[$user->email] ?? null;
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
            $legacy = ['emergency/requests'=>'emergency#patientForm', 'emergency/approvals'=>'emergency#queue', 'emergency/beds'=>'emergency#hospitals', 'emergency/reservations'=>'emergency#events', 'emergency/history'=>'emergency#events'];
            if (in_array($m['url'], ['emergency/inventory', 'emergency/blood', 'emergency/settings'])) {
                return $profile['role'] === 'hospital' && (in_array($m['url'], $assigned) || in_array('emergency/beds', $assigned) || in_array('emergency#hospitals', $assigned));
            }
            return in_array($profile['role'],$m['roles']) && (in_array($m['url'],$assigned) || in_array($legacy[$m['url']] ?? null,$assigned));
        });
    }

    public static function canPage($page)
    {
        $url = $page === 'dashboard' ? 'emergency' : 'emergency/'.$page;
        foreach (self::menu() as $module) if ($module['url'] === $url) return true;
        return false;
    }

    public static function operator() { $p=self::profile(); return $p && $p['role'] === 'hospital'; }
}
