<?php
namespace App\Http\Controllers;
use App\Models\CareRequest;
use App\Services\EmergencyBedService;
use App\Support\RoleAccess;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use RuntimeException;

class EmergencyRequestController extends Controller
{
    public function __construct() { $this->middleware(function ($request,$next) { abort_unless(RoleAccess::canPage('requests'),403); return $next($request); }); }
    public function index() { return app(EmergencyController::class)->index('requests'); }
    public function create() { return $this->form(new CareRequest); }
    public function edit($id) { return $this->form(CareRequest::where('owner_id',auth()->id())->findOrFail($id)); }
    private function form($record) { if ($record->exists) $record->care=$record->type->name ?? $record->care; $careTypes=app(EmergencyBedService::class)->careTypes(); $hospitals=\App\Models\Hospital::where('status',1)->orderBy('hospital_name')->get(); return view('requests.form',compact('record','careTypes','hospitals')); }
    private function validateInput(Request $request)
    {
        return $request->validate(['patient'=>'required|string|max:80','location'=>'required|string|max:120','severity'=>'required|in:Critical,Urgent,Stable','care'=>['required',Rule::in(app(EmergencyBedService::class)->careTypes())],'latitude'=>'nullable|numeric|between:-90,90|required_with:longitude','longitude'=>'nullable|numeric|between:-180,180|required_with:latitude'] + \App\Models\CareRequest::detailRules());
    }
    public function store(Request $request)
    {
        try { $id=app(EmergencyBedService::class)->create($this->validateInput($request),hash('sha256',$request->ip()),auth()->id()); }
        catch (RuntimeException $e) { return $request->expectsJson() ? response()->json(['message'=>$e->getMessage()],409) : back()->withErrors(['request'=>$e->getMessage()])->withInput(); }
        if ($request->expectsJson()) return response()->json(['id'=>$id,'message'=>'Request created. Hospital verification is required for elevated priority.']);
        return redirect()->route('care.requests.index')->with('message','Request created.');
    }
    public function update(Request $request,$id) { return $this->change($request,$id,'update',$this->validateInput($request)); }
    public function destroy(Request $request,$id) { return $this->change($request,$id,'cancel',[]); }
    private function change(Request $request,$id,$action,$data)
    {
        try { app(EmergencyBedService::class)->changeRequest($id,auth()->id(),$action,$data); }
        catch (RuntimeException $e) { return back()->withErrors(['request'=>$e->getMessage()])->withInput(); }
        return redirect()->route('care.requests.index')->with('message','Request '.$action.'d successfully.');
    }
}
