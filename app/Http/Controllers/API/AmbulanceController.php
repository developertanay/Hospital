<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Services\AmbulanceService;
use App\Services\EmergencyBedService;
use App\Support\RoleAccess;
use Illuminate\Http\Request;
use RuntimeException;
class AmbulanceController extends Controller {
    public function state(Request $request,AmbulanceService $service,EmergencyBedService $beds) {
        $p=RoleAccess::profile();
        if (!$p || !in_array($p['role'],['patient','driver','admin'],true)) return response()->json(['message'=>'Patient, driver or admin account required.'],403);
        if ($p['role']==='driver') $service->heartbeat($p);
        $state=$service->state($p);
        $bedState=$beds->state();
        foreach ($state['calls'] as &$call) if ($call['bed_request_id'] || !empty($call['care_request_id'])) {
            $record=collect($bedState['requests'])->firstWhere('id',$call['bed_request_id'] ?: $call['care_request_id']);
            $call['bed_status']=$record['status']??'Unavailable';
            $call['bed_care']=$record['care']??null;
            $call['department_id']=$record['department_id']??null;
        } unset($call);
        if (in_array($p['role'],['driver','admin'],true)) $state['hospitals']=array_map(function ($h) {
            $counts=array_fill_keys(app(EmergencyBedService::class)->careTypes(),0); foreach ($h['beds'] as $b) if ($b['status']==='Available') $counts[$b['care']]=($counts[$b['care']]??0)+1;
            return ['id'=>$h['id'],'name'=>$h['name'],'distance'=>$h['distance'],'latitude'=>$h['latitude'],'longitude'=>$h['longitude'],'status'=>$h['status'],'available'=>$counts,'departments'=>$h['departments']];
        },$bedState['hospitals']);
        $state['care_types']=$beds->careTypes();
        foreach ($state['calls'] as &$call) {
            $call['hospital_options']=$state['hospitals']??[];
            foreach ($call['hospital_options'] as &$h) if (isset($h['latitude'],$h['longitude'])) $h['distance']=round(AmbulanceService::distance($call['location']['lat'],$call['location']['lng'],$h['latitude'],$h['longitude']),1);
            unset($h);
            if(!empty($call['department_id'])) $call['hospital_options']=array_values(array_filter($call['hospital_options'],fn($h)=>in_array((int)$call['department_id'],array_column($h['departments'],'id'),true)));
        } unset($call);
        return response()->json($state)->header('Cache-Control','private, no-store');
    }
    public function action(Request $request,AmbulanceService $service,EmergencyBedService $beds) {
        $p=RoleAccess::profile(); if (!$p) return response()->json(['message'=>'Sign in first.'],401);
        $action=$request->validate(['action'=>'required|in:profile,driver_contact,sos,location,duty,accept,decline,cancel,arrive,pickup,first_aid,transport,handoff,complete,nearby,book'])['action'];
        $allowed=in_array($action,['profile','sos','location','cancel','nearby','book'],true)?['patient']:['driver'];
        if (!in_array($p['role'],$allowed,true)) return response()->json(['message'=>'This role cannot perform that SOS action.'],403);
        try {
            if (in_array($action,['nearby','book'],true)) {
                $request->validate(['request_id'=>'required|string|max:40']);
                $care=\App\Models\CareRequest::where('owner_id',$p['id'])->findOrFail($request->request_id);
                if($action==='nearby') {
                    if($request->has('lat')) {
                        $fix=$request->validate(['lat'=>'required|numeric|between:-90,90','lng'=>'required|numeric|between:-180,180','accuracy'=>'required|numeric|between:0,200','captured_at'=>'required|integer','mode'=>'required|in:gps']);
                        if(abs(time()-$fix['captured_at'])>120) throw new RuntimeException('Get a fresh GPS location and retry.');
                        $beds->updatePatientLocation($care->id,$p['id'],$fix);
                        $care->refresh();
                    }
                    if($care->latitude===null || $care->longitude===null) throw new RuntimeException('Share live GPS on the care form to find nearby ambulances.');
                    return response()->json($service->nearby(['lat'=>(float)$care->latitude,'lng'=>(float)$care->longitude]));
                }
                $input=$request->validate(['driver_id'=>'required|string|max:40','lat'=>'required|numeric|between:-90,90','lng'=>'required|numeric|between:-180,180','accuracy'=>'required|numeric|between:0,200','captured_at'=>'required|integer','mode'=>'required|in:gps']);
                return response()->json($service->book($p,$care,$input['driver_id'],$input),201);
            }
            if ($action==='driver_contact') {
                $input=$request->validate(['phone'=>'required|regex:/^\+?[0-9]{8,15}$/']);
                return response()->json($service->contact($p,$input['phone']));
            }
            if ($action==='profile') {
                $input=$request->validate(['name'=>'required|string|max:50','mobile'=>'required|regex:/^\+?[0-9]{8,15}$/','age'=>'required|integer|min:1|max:120','emergency_name'=>'required|string|max:60','emergency_phone'=>'required|regex:/^\+?[0-9]{8,15}$/']);
                return response()->json($service->profile($p,$input));
            }
            if (in_array($action,['sos','location'],true)) {
                $input=$request->validate(['lat'=>'required|numeric|between:-90,90','lng'=>'required|numeric|between:-180,180','accuracy'=>'required|numeric|between:0,200','captured_at'=>'required|integer','mode'=>'required|in:gps,demo']);
                if ($action==='location') { $request->validate(['id'=>'required|string|max:32']); return response()->json($service->location($p,$request->id,$input)); }
                return response()->json($service->create($p,$input),201);
            }
            if ($action==='duty') {
                $input=$request->validate(['follow_pickup'=>'nullable|boolean','on_duty'=>'required|boolean','lat'=>'required_if:on_duty,true|numeric|between:-90,90','lng'=>'required_with:lat|numeric|between:-180,180','mode'=>'required_with:lat|in:gps,demo']);
                return response()->json($service->duty($p,$input));
            }
            $request->validate(['id'=>'required|string|max:32']);
            $input=$action==='transport'?$request->validate(['care'=>['required',\Illuminate\Validation\Rule::in($beds->careTypes())],'hospital_id'=>'required|integer|exists:hospital_mast,id']):[];
            if ($action==='transport') $input['hospital_id']=(string)$input['hospital_id'];
            return response()->json($service->action($p,$request->id,$action,$input,$beds));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) { return response()->json(['message'=>'Care request not found.'],404); }
        catch (RuntimeException $e) { return response()->json(['message'=>$e->getMessage()],409); }
    }
}
