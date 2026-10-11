<?php
namespace App\Http\Controllers;

use App\Services\EmergencyBedService;
use Illuminate\Http\Request;
use RuntimeException;
use App\Support\DemoAccess;

class EmergencyController extends Controller
{
    public function index() { return DemoAccess::profile() ? view('emergency') : redirect(auth()->check()?'/dashboard':'/login'); }

    public function state(Request $request, EmergencyBedService $service)
    {
        $p=DemoAccess::profile();
        if (!$p) return response()->json(['message'=>'Sign in with a demo account.'],401);
        $state=$service->state();
        if ($p['role']==='patient') {
            $state['requests']=array_values(array_filter($state['requests'],fn($r)=>($r['owner_id'] ?? null)===$p['id']));
        }
        if ($p['role']==='hospital') {
            $state['hospitals']=array_values(array_filter($state['hospitals'],fn($h)=>$h['id']===$p['hospital']));
            $state['requests']=array_values(array_filter($state['requests'],fn($r)=>$r['status']==='New' || $r['hospital_id']===$p['hospital']));
        }
        if ($p['role']!=='admin') {
            $visibleIds=array_column($state['requests'],'id');
            $state['events']=array_values(array_filter($state['events'],fn($e)=>in_array($e['request_id'] ?? null,$visibleIds,true)));
        }
        return response()->json($state + ['operator'=>DemoAccess::operator(),'profile'=>$p]);
    }

    public function mutate(Request $request, EmergencyBedService $service)
    {
        $data = $request->validate(['action'=>'required|in:create,login,logout,verify,request,accept,reject,arrive,reset']);
        $action = $data['action'];
        $profile=DemoAccess::profile();
        if (!$profile) return response()->json(['message'=>'Sign in with a demo account.'],401);
        if (in_array($action,['login','logout'])) return response()->json(['message'=>'Use the account login/logout controls. Operator PIN access has been replaced by role logins.'],403);
        try {
            if ($action === 'create') {
                if ($profile['role']==='hospital') return response()->json(['message'=>'Use the Patient login to submit emergencies.'],403);
                $input = $request->validate(['patient'=>'required|string|max:80','location'=>'required|string|max:120','severity'=>'required|in:Critical,Urgent,Stable','care'=>'required|in:General,ICU,Ventilator']);
                $id = $service->create($input, hash('sha256',$request->ip()),$profile['id']);
                return response()->json(['id'=>$id,'message'=>'Request created. Operator verification required for elevated priority.']);
            } elseif ($action === 'request') {
                $request->validate(['id'=>'required|string|max:32','hospital_id'=>'required|in:A,B,C,D,E']);
                $record=collect($service->state()['requests'])->firstWhere('id',$request->id);
                if (!$record || ($profile['role']!=='admin' && ($profile['role']!=='patient' || ($record['owner_id'] ?? null)!==$profile['id']))) return response()->json(['message'=>'You can request a bed only for your own patient request.'],403);
                $service->requestBed($request->id,$request->hospital_id,$profile['role']==='patient'?$profile['id']:null);
            } else {
                if (!DemoAccess::operator()) return response()->json(['message'=>'Hospital operator access required.'],403);
                if ($action==='reset' && $profile['role']!=='admin') return response()->json(['message'=>'Only Admin can reset demo data.'],403);
                if ($action === 'reset') $service->reset();
                else {
                    $request->validate(['id'=>'required|string|max:32']);
                    $record=collect($service->state()['requests'])->firstWhere('id',$request->id);
                    if (!$record) return response()->json(['message'=>'Request not found.'],404);
                    if ($profile['role']==='hospital' && !(($action==='verify' && $record['status']==='New') || $record['hospital_id']===$profile['hospital'])) return response()->json(['message'=>'This request belongs to another hospital.'],403);
                    if ($action === 'verify') { $request->validate(['severity'=>'required|in:Critical,Urgent,Stable']); $service->verify($request->id,$request->severity,$profile['hospital']); }
                    else $service->action($request->id,$action,$profile['hospital']);
                }
            }
            return response()->json(['message'=>'Updated successfully.']);
        } catch (RuntimeException $e) { return response()->json(['message'=>$e->getMessage()],409); }
    }
}
