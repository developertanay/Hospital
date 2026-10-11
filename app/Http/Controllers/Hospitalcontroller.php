<?php
namespace App\Http\Controllers;
use App\Models\Hospital;
use App\Models\CareBed;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
class Hospitalcontroller extends Controller
{
    public function index() { $data=Hospital::where('status','!=',9)->get(); $current_menu='Hospital'; return view('Hospital.index',compact('data','current_menu')); }
    public function create() { return $this->form(); }
    public function edit($id) { return $this->form(Hospital::findOrFail(Crypt::decryptString($id)),$id); }
    private function form($data=null,$encid=null)
    {
        $state=DB::table('state_mast')->where('status','!=',9)->pluck('state_name','id');
        $city=DB::table('city_mast')->where('status','!=',9)->pluck('city_name','id');
        $current_menu='Hospital'; return view($data ? 'Hospital.edit':'Hospital.create',compact('data','encid','state','city','current_menu'));
    }
    public function store(Request $request) { return $this->save($request,new Hospital); }
    public function update(Request $request,$id) { return $this->save($request,Hospital::findOrFail(Crypt::decryptString($id))); }
    private function save(Request $request,Hospital $hospital)
    {
        $request->validate(['name'=>'required|string|max:500','short_name'=>'nullable|string|max:20','Registration'=>'nullable|string|max:100','type'=>'nullable|string|max:20','host'=>'nullable|string|max:11','contact_no'=>'nullable|string|max:40','alternate_no'=>'nullable|string|max:40','email'=>'nullable|email|max:50','address'=>'nullable|string|max:600','url'=>'nullable|string|max:100','geo_location'=>'nullable|string|max:100','image'=>'nullable|image|max:2048','latitude'=>'nullable|numeric|between:-90,90|required_with:longitude','longitude'=>'nullable|numeric|between:-180,180|required_with:latitude','status'=>'required|in:0,1']);
        $fields=['hospital_name'=>'name','short_name'=>'short_name','registration_no'=>'Registration','type'=>'type','host'=>'host','contact_no'=>'contact_no','emergency_no'=>'alternate_no','email_id'=>'email','address'=>'address','website'=>'url','geo_location'=>'geo_location'];
        foreach ($fields as $column=>$field) $hospital->$column=$request->input($field) ?? '';
        $hospital->state=(int)$request->input('current_state',0); $hospital->district=(int)$request->input('district',0); $hospital->city=(int)$request->input('city',0); $hospital->pin_code=(int)$request->input('pincode',0);
        $hospital->status=(int)$request->status; $hospital->latitude=$request->latitude; $hospital->longitude=$request->longitude;
        if (!$hospital->exists) { $hospital->created_by=auth()->id(); $hospital->logo=''; }
        $hospital->updated_by=auth()->id();
        if ($request->hasFile('image')) $hospital->logo='storage/'.$request->file('image')->store('hospital-logos','public');
        $saved=DB::transaction(function () use ($hospital) {
            DB::table('care_workflow_lock')->where('id',1)->lockForUpdate()->first();
            if ($hospital->exists && $hospital->status!==1 && CareBed::where('hospital_id',$hospital->id)->where('status','!=','Available')->exists()) return false;
            $hospital->save(); return true;
        });
        if (!$saved) return back()->withErrors(['hospital'=>'This hospital has protected allocations. Keep it active; use Offline to stop new requests.'])->withInput();
        return redirect()->route('Hospital.index')->with('message','Hospital saved successfully.');
    }
    public function destroy($id)
    {
        $hospital=Hospital::findOrFail(Crypt::decryptString($id));
        $removed=DB::transaction(function () use ($hospital) {
            DB::table('care_workflow_lock')->where('id',1)->lockForUpdate()->first();
            if (CareBed::where('hospital_id',$hospital->id)->where('status','!=','Available')->exists()) return false;
            $hospital->status=9; $hospital->save(); return true;
        });
        return redirect()->route('Hospital.index')->with($removed ? 'message':'error',$removed ? 'Hospital deleted.':'Protected allocations must be completed before deleting the hospital.');
    }
}
