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
        $user = Auth::user();
        if (!$user || !$user->care_enabled) return [];

        $modules = Modules::join('module_assigning', 'modules.id', '=', 'module_assigning.modules_id')
            ->where('module_assigning.role_id', $user->role_id)
            ->where('module_assigning.college_id', $user->college_id)
            ->where('module_assigning.status', 1)
            ->where('module_assigning.view_flag', 1)
            ->where('modules.college_id', $user->college_id)
            ->where('modules.status', 1)
            ->select('modules.*')
            ->orderBy('modules.sequence')
            ->get();

        $menu = [];
        foreach ($modules as $module) {
            if (!$module->url || $module->url === '#') continue;
            $parent = $modules->firstWhere('id', $module->parent_id);
            $menu[$module->url] = ['name' => $module->name, 'url' => $module->url,
                'icon' => $module->icon ?: 'bx bx-grid-alt',
                'group' => $parent ? $parent->name : ($module->care_group ?: 'Modules')];
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
