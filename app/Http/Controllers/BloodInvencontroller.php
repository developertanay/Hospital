<?php
namespace App\Http\Controllers;
use App\Models\BloodInv;
use App\Models\bloodtype;
use App\Models\Hospital;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Validation\Rule;
class BloodInvencontroller extends Controller
{
    public function index() { $data=BloodInv::where('status','!=',9)->get(); $final=bloodtype::pluck('blood_type','id'); $hospitals=Hospital::pluck('hospital_name','id'); $current_menu='BloodInven'; return view('BloodInven.index',compact('data','final','hospitals','current_menu')); }
    public function create() { return $this->form(new BloodInv); }
    public function edit($id) { return $this->form(BloodInv::findOrFail(Crypt::decryptString($id))); }
    private function form($record) { $types=bloodtype::where('status',1)->get(); $hospitals=Hospital::where('status',1)->get(); return view('BloodInven.form',compact('record','types','hospitals')); }
    public function store(Request $request) { return $this->save($request,new BloodInv); }
    public function update(Request $request,$id) { return $this->save($request,BloodInv::findOrFail(Crypt::decryptString($id))); }
    private function save(Request $request,BloodInv $record)
    {
        $data=$request->validate(['blood_id'=>['required',Rule::exists('blood_type','id')->where('status',1)],'unit'=>'required|integer|min:0|max:10000','hospital_id'=>['nullable',Rule::exists('hospital_mast','id')->where('status',1)],'status'=>'required|in:1,2']);
        $saved=DB::transaction(function () use ($record,$data) {
            DB::table('care_workflow_lock')->where('id',1)->lockForUpdate()->first();
            if (!empty($data['hospital_id']) && (int)$data['status']===1 && BloodInv::where('hospital_id',$data['hospital_id'])->where('blood_id',$data['blood_id'])->where('status',1)->when($record->exists,fn($q)=>$q->where('id','!=',$record->id))->exists()) return false;
            $record->fill($data);
            if (!$record->exists) $record->created_by=auth()->id();
            $record->updated_by=auth()->id(); $record->save(); return true;
        });
        if (!$saved) return back()->withErrors(['stock'=>'This hospital already has active stock for that group. Edit that record instead.'])->withInput();
        return redirect()->route('BloodInven.index')->with('message','Blood stock saved.');
    }
    public function destroy($id)
    {
        DB::transaction(function () use ($id) { DB::table('care_workflow_lock')->where('id',1)->lockForUpdate()->first(); $record=BloodInv::findOrFail(Crypt::decryptString($id)); $record->status=9; $record->save(); });
        return redirect()->route('BloodInven.index')->with('message','Blood inventory deleted.');
    }
}
