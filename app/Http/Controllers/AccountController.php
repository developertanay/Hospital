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
    private function form($account) { $hospitals=Hospital::where('status',1)->get(); $ambulances=\App\Models\Ambulance::where('status',1)->get(); return view('accounts.form',compact('account','hospitals','ambulances')); }
    public function store(Request $request) { return $this->save($request,new User); }
    public function update(Request $request,$id) { return $this->save($request,User::findOrFail($id)); }
    private function save(Request $request,User $account)
    {
        $data=$request->validate(['name'=>'required|string|max:255','email'=>['required','string','max:255',Rule::unique('users')->ignore($account->id)],'password'=>($account->exists?'nullable':'required').'|string|min:8|confirmed','care_role'=>'required|in:admin,hospital,patient,driver','hospital_id'=>['nullable','required_if:care_role,hospital',Rule::exists('hospital_mast','id')->where('status',1)],'ambulance_id'=>['nullable','required_if:care_role,driver',Rule::exists('ambulance_details','id')->where('status',1),Rule::unique('ambulance_drivers','ambulance_id')->ignore($account->id,'user_id')],'care_enabled'=>'required|boolean']);
        if ($account->id===auth()->id() && ($data['care_role']!=='admin' || !$data['care_enabled'])) return back()->withErrors(['account'=>'You cannot remove your own administrator access.'])->withInput();
        $driver=$account->ambulanceDriver;
        if ($driver && \App\Models\AmbulanceTrip::where('driver_id',$driver->id)->whereNotIn('status',['Completed','Cancelled','Expired'])->exists()
            && ($data['care_role']!=='driver' || (string)($data['ambulance_id'] ?? '')!==(string)$driver->ambulance_id)) return back()->withErrors(['ambulance_id'=>'Finish the active trip before changing this driver assignment.'])->withInput();
        $roleName=['admin'=>'Admin','hospital'=>'Hospital Operator','patient'=>'Patient Requester','driver'=>'Ambulance Driver'][$data['care_role']];
        $role=DB::table('role_mast')->where('name',$roleName)->first();
        if (!$role) $roleId=DB::table('role_mast')->insertGetId(['name'=>$roleName,'college_id'=>1,'status'=>1,'sequence'=>10]);
        else $roleId=$role->id;
        if ($account->exists && $account->care_role===$data['care_role']) $roleId=$account->role_id;
        $account->name=$data['name']; $account->email=$data['email']; $account->care_role=$data['care_role']; $account->care_enabled=$data['care_enabled'];
        $account->hospital_id=$data['care_role']==='hospital' ? $data['hospital_id'] : null; $account->role_id=$roleId; $account->college_id=$account->college_id ?? 1;
        if (!empty($data['password'])) $account->password=Hash::make($data['password']);
        DB::transaction(function () use ($account,$data,$driver) {
            $account->save();
            if ($account->care_role==='driver') {
                \App\Models\AmbulanceDriver::updateOrCreate(['user_id'=>$account->id],['id'=>$driver ? $driver->id : 'driver-'.$account->id,'ambulance_id'=>$data['ambulance_id']]);
            } elseif ($driver) {
                $driver->on_duty=false; $driver->ambulance_id=null; $driver->save();
            }
        });
        return redirect()->route('Accounts.index')->with('message','Account saved successfully.');
    }
    public function destroy($id) { $account=User::findOrFail($id); abort_if($account->id===auth()->id(),422,'You cannot disable your own account.'); $account->care_enabled=false; $account->save(); return redirect()->route('Accounts.index')->with('message','Account disabled. Existing records are preserved.'); }
}
