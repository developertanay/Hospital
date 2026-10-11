<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Services\AmbulanceService;
use App\Services\EmergencyBedService;
use App\Support\DemoAccess;
use Illuminate\Http\Request;
use RuntimeException;
class AmbulanceController extends Controller {
    public function state(Request $request,AmbulanceService $service,EmergencyBedService $beds) {
        $p=DemoAccess::profile();
        if (!$p || !in_array($p['role'],['patient','driver','admin'],true)) return response()->json(['message'=>'Patient, driver or admin account required.'],403);
        if ($p['role']==='driver') $service->heartbeat($p);
        $state=$service->state($p);
        $bedState=$beds->state();
        foreach ($state['calls'] as &$call) if ($call['bed_request_id']) {
            $record=collect($bedState['requests'])->firstWhere('id',$call['bed_request_id']);
            $call['bed_status']=$record['status']??'Unavailable';
            $call['bed_care']=$record['care']??null;
        } unset($call);
        if (in_array($p['role'],['driver','admin'],true)) $state['hospitals']=array_map(function ($h) {
            $counts=['General'=>0,'ICU'=>0,'Ventilator'=>0]; foreach ($h['beds'] as $b) if ($b['status']==='Available') $counts[$b['care']]++;
            return ['id'=>$h['id'],'name'=>$h['name'],'distance'=>$h['distance'],'status'=>$h['status'],'available'=>$counts];
        },$bedState['hospitals']);
        return response()->json($state);
    }
    public function action(Request $request,AmbulanceService $service,EmergencyBedService $beds) {
        $p=DemoAccess::profile(); if (!$p) return response()->json(['message'=>'Sign in first.'],401);
        $action=$request->validate(['action'=>'required|in:profile,sos,location,duty,accept,decline,cancel,arrive,pickup,first_aid,transport,handoff,complete'])['action'];
        $allowed=in_array($action,['profile','sos','location','cancel'],true)?['patient']:['driver'];
        if (!in_array($p['role'],$allowed,true)) return response()->json(['message'=>'This role cannot perform that SOS action.'],403);
        try {
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
                $input=$request->validate(['on_duty'=>'required|boolean','lat'=>'required_if:on_duty,true|numeric|between:-90,90','lng'=>'required_with:lat|numeric|between:-180,180','mode'=>'required_with:lat|in:gps,demo']);
                return response()->json($service->duty($p,$input));
            }
            $request->validate(['id'=>'required|string|max:32']);
            $input=$action==='transport'?$request->validate(['care'=>'required|in:General,ICU,Ventilator','hospital_id'=>'required|in:A,B,C,D,E']):[];
            return response()->json($service->action($p,$request->id,$action,$input,$beds));
        } catch (RuntimeException $e) { return response()->json(['message'=>$e->getMessage()],409); }
    }
}
