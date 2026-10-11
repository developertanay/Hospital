<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Models\Hospital;
use App\Support\RoleAccess;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AccountController extends Controller
{
    public function __construct() { $this->middleware(function ($request, $next) { abort_unless(RoleAccess::profile() && RoleAccess::profile()['role']==='admin',403); return $next($request); }); }
    public function index() { $accounts=User::with('hospital')->get(); return view('accounts.index',compact('accounts')); }
    public function create() { $account=new User; return $this->form($account); }
    public function edit($id) { return $this->form(User::findOrFail($id)); }
    private function form($account) { $hospitals=Hospital::where('status',1)->get(); return view('accounts.form',compact('account','hospitals')); }
    public function store(Request $request) { return $this->save($request,new User); }
    public function update(Request $request,$id) { return $this->save($request,User::findOrFail($id)); }
    private function save(Request $request,User $account)
    {
        $data=$request->validate(['name'=>'required|string|max:255','email'=>['required','string','max:255',Rule::unique('users')->ignore($account->id)],'password'=>($account->exists?'nullable':'required').'|string|min:8|confirmed','care_role'=>'required|in:admin,hospital,patient','hospital_id'=>['nullable','required_if:care_role,hospital',Rule::exists('hospital_mast','id')->where('status',1)],'care_enabled'=>'required|boolean']);
        if ($account->id===auth()->id() && ($data['care_role']!=='admin' || !$data['care_enabled'])) return back()->withErrors(['account'=>'You cannot remove your own administrator access.'])->withInput();
        $roleName=['admin'=>'Admin','hospital'=>'Hospital Operator','patient'=>'Patient Requester'][$data['care_role']];
        $role=DB::table('role_mast')->where('name',$roleName)->first();
        if (!$role) $roleId=DB::table('role_mast')->insertGetId(['name'=>$roleName,'college_id'=>1,'status'=>1,'sequence'=>10]);
        else $roleId=$role->id;
        $account->name=$data['name']; $account->email=$data['email']; $account->care_role=$data['care_role']; $account->care_enabled=$data['care_enabled'];
        $account->hospital_id=$data['care_role']==='hospital' ? $data['hospital_id'] : null; $account->role_id=$roleId; $account->college_id=$account->college_id ?? 1;
        if (!empty($data['password'])) $account->password=Hash::make($data['password']);
        DB::transaction(function () use ($account) {
            $account->save();
        });
        return redirect()->route('Accounts.index')->with('message','Account saved successfully.');
    }
    public function destroy($id) { $account=User::findOrFail($id); abort_if($account->id===auth()->id(),422,'You cannot disable your own account.'); $account->care_enabled=false; $account->save(); return redirect()->route('Accounts.index')->with('message','Account disabled. Existing records are preserved.'); }
}
