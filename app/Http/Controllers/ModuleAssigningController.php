<?php
namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Modules;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class ModuleAssigningController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $role = Role::find(auth()->user()->role_id);
            abort_unless($role && in_array($role->name, ['Admin', 'Super Admin']), 403, 'Administrator access required to assign modules.');
            return $next($request);
        });
    }

    public function index()
    {
        $college_mast = Hospital::pluckActiveCodeAndName(auth()->user()->college_id);
        $role_mast = $this->roles()->pluck('name', 'id');
        $current_menu = 'ModuleAssigning';
        return view('ModuleAssigning.index', compact('college_mast', 'role_mast', 'current_menu'));
    }

    private function roles()
    {
        return Role::where('status', 1)->where(function ($query) {
            $query->where('college_id', auth()->user()->college_id)->orWhereNull('college_id');
        });
    }

    private function validateRole(Request $request)
    {
        $request->validate(['role_id' => ['required', 'integer', Rule::in($this->roles()->pluck('id')->all())]]);
    }

    public function create(Request $request)
    {
        $this->validateRole($request);
        $role = $this->roles()->findOrFail($request->role_id);
        $modules = Modules::where('college_id', auth()->user()->college_id)->where('status', 1)->orderBy('sequence')->get();
        $assigned = DB::table('module_assigning')->where('college_id', auth()->user()->college_id)
            ->where('role_id', $role->id)->where('status', 1)->where('view_flag', 1)->pluck('modules_id')->all();
        return view('ModuleAssigning.create', compact('role', 'modules', 'assigned'));
    }

    public function store(Request $request)
    {
        $this->validateRole($request);
        $collegeId = auth()->user()->college_id;
        $request->validate(['module_ids' => 'nullable|array', 'module_ids.*' => ['integer', 'distinct',
            Rule::exists('modules', 'id')->where('college_id', $collegeId)->where('status', 1)]]);
        DB::transaction(function () use ($request, $collegeId) {
            // Lock the role so two administrators cannot partially replace its assignments.
            Role::where('id', $request->role_id)->lockForUpdate()->firstOrFail();
            DB::table('module_assigning')->where('college_id', $collegeId)->where('role_id', $request->role_id)->delete();
            foreach ($request->input('module_ids', []) as $moduleId) {
                DB::table('module_assigning')->insert([
                    'college_id' => $collegeId, 'role_id' => $request->role_id, 'modules_id' => $moduleId,
                    'status' => 1, 'view_flag' => 1, 'create_flag' => 1, 'edit_flag' => 1,
                    'upload_flag' => 1, 'download_flag' => 1,
                    'created_at' => now(), 'created_by' => auth()->id(),
                ]);
            }
        });
        return redirect('ModuleAssigning')->with('message', 'Role modules saved successfully.');
    }
}
