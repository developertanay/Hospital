<?php
namespace App\Http\Controllers;
use App\Models\Hospital;
use App\Support\RoleAccess;
use App\Services\EmergencyBedService;
use Illuminate\Http\Request;
use RuntimeException;
class HospitalSettingsController extends Controller
{
    private function hospital()
    {
        $profile=RoleAccess::profile();
        abort_unless($profile && $profile['role']==='hospital' && RoleAccess::canPage('settings'),403);
        return Hospital::where('status',1)->findOrFail($profile['hospital']);
    }
    public function index() { $hospital=$this->hospital(); $editing=false; return view('demo.hospital-settings',compact('hospital','editing')); }
    public function edit($id) { $hospital=$this->hospital(); abort_unless((string)$hospital->id===(string)$id,404); $editing=true; return view('demo.hospital-settings',compact('hospital','editing')); }
    public function update(Request $request,$id)
    {
        $hospital=$this->hospital(); abort_unless((string)$hospital->id===(string)$id,404);
        $data=$request->validate(['status'=>'required|in:Open,Offline','contact'=>'nullable|string|max:40','address'=>'nullable|string|max:600','latitude'=>'nullable|numeric|between:-90,90|required_with:longitude','longitude'=>'nullable|numeric|between:-180,180|required_with:latitude']);
        try { app(EmergencyBedService::class)->manageHospital((string)$hospital->id,'settings',$data); }
        catch (RuntimeException $e) { return back()->withErrors(['hospital'=>$e->getMessage()])->withInput(); }
        return redirect()->route('hospital.settings.index')->with('message','Hospital settings updated.');
    }
}
