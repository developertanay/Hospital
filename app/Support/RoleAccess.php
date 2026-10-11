<?php
namespace App\Support;

use App\Models\Modules;
use Illuminate\Support\Facades\Auth;

class RoleAccess
{
    public static function profile()
    {
        $user = Auth::user();
        if (!$user || !$user->care_enabled) return null;
        if (!in_array($user->care_role, ['admin', 'hospital', 'patient'])) return null;

        $hospital = $user->hospital;
        $hospitalId = $hospital && (int) $hospital->status === 1 ? (string) $hospital->id : null;

        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->care_role,
            'role_id' => $user->role_id,
            'college_id' => $user->college_id,
            'hospital' => $hospitalId,
            'hospital_name' => $hospital->hospital_name ?? null,
        ];
    }

    public static function menu()
    {
        $profile = self::profile();
        if (!$profile) return [];

        $modules = Modules::join('module_assigning', 'modules.id', '=', 'module_assigning.modules_id')
            ->where('module_assigning.role_id', $profile['role_id'])
            ->where('module_assigning.college_id', $profile['college_id'])
            ->where('module_assigning.status', 1)
            ->where('modules.status', 1)
            ->whereNotNull('modules.care_roles')
            ->select('modules.*')
            ->orderBy('modules.sequence')
            ->get();

        // Clinical duties remain separate from administration, even if a
        // legacy assignment accidentally grants the wrong module to a role.
        $clinicalPages = [
            'emergency/requests' => ['patient'],
            'emergency/reservations' => ['patient'],
            'emergency/approvals' => ['hospital'],
            'emergency/beds' => ['hospital'],
            'emergency/inventory' => ['hospital'],
            'emergency/blood' => ['hospital'],
            'emergency/settings' => ['hospital'],
            'emergency/history' => ['hospital', 'patient'],
        ];

        $menu = [];
        foreach ($modules as $module) {
            if ($profile['role'] === 'hospital' && !$profile['hospital'] && $module->url !== 'emergency') continue;
            if (isset($clinicalPages[$module->url]) && !in_array($profile['role'], $clinicalPages[$module->url])) continue;
            if (!in_array($profile['role'], json_decode($module->care_roles, true) ?? [])) continue;
            $menu[$module->url] = ['name' => $module->name, 'url' => $module->url, 'group' => $module->care_group ?? 'Modules'];
        }
        return $menu;
    }

    public static function canPage($page)
    {
        $url = $page === 'dashboard' ? 'emergency' : 'emergency/' . $page;
        foreach (self::menu() as $module) {
            if ($module['url'] === $url) return true;
        }
        return false;
    }

    public static function operator()
    {
        $profile = self::profile();
        return $profile && $profile['role'] === 'hospital' && $profile['hospital'];
    }
}
