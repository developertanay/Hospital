<?php
namespace App\Http\Controllers;

use App\Services\EmergencyBedService;
use Illuminate\Http\Request;
use RuntimeException;
use App\Support\DemoAccess;

class EmergencyController extends Controller
{
    public function index($page = 'dashboard')
    {
        $profile = DemoAccess::profile();
        if (!$profile) return redirect(auth()->check() ? '/dashboard' : '/login');
        abort_unless(DemoAccess::canPage($page), 403, 'This page is not assigned to your role.');
        if (in_array($page, ['inventory', 'blood', 'settings'])) {
            $state = app(EmergencyBedService::class)->state();
            $hospital = collect($state['hospitals'])->firstWhere('id', $profile['hospital']);
            abort_unless($hospital, 404, 'Hospital not found.');
            return view('demo.hospital-management', compact('page', 'hospital'));
        }

        $summary = null;
        if ($profile['role'] === 'admin' && $page === 'dashboard') {
            $summary = [
                ['label' => 'Hospitals', 'value' => \App\Models\Hospital::where('status', '!=', 9)->count(), 'url' => 'Hospital'],
                ['label' => 'Inventory beds', 'value' => \App\Models\BedInven::where('status', '!=', 9)->sum('count'), 'url' => 'BedInven'],
                ['label' => 'Blood units', 'value' => \App\Models\BloodInv::where('status', '!=', 9)->sum('unit'), 'url' => 'BloodInven'],
                ['label' => 'Users', 'value' => \App\Models\User::where('college_id', $profile['college_id'])->count(), 'url' => 'NewUser'],
            ];
        }
        return view('emergency', compact('summary', 'page'));
    }

    public function state(Request $request, EmergencyBedService $service)
    {
        $p=DemoAccess::profile();
        if (!$p) return response()->json(['message'=>'Sign in with a demo account.'],401);
        $page = $request->query('page', 'dashboard');
        abort_unless(DemoAccess::canPage($page), 403, 'This data is not assigned to your role.');
        if ($p['role'] === 'admin') {
            return response()->json(['requests'=>[], 'hospitals'=>[], 'events'=>[], 'stats'=>[], 'operator'=>false, 'profile'=>$p, 'csrf_token'=>csrf_token()])->header('Cache-Control', 'no-store, private');
        }
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
            $state['events']=array_values(array_filter($state['events'],fn($e)=>in_array($e['request_id'] ?? null,$visibleIds,true) || ($p['role'] === 'hospital' && ($e['hospital_id'] ?? null) === $p['hospital'])));
        }
        unset($state['limits']);
        $state['stats'] = [
            'available' => collect($state['hospitals'])->where('status', 'Open')->flatMap(fn($h) => $h['beds'])->where('status', 'Available')->count(),
            'requests' => count($state['requests']),
            'pending' => collect($state['requests'])->where('status', 'Pending')->count(),
            'reserved' => collect($state['requests'])->where('status', 'Reserved')->count(),
        ];
        foreach ($state['requests'] as &$record) {
            unset($record['client'], $record['owner_id']);
            if ($page !== 'requests') unset($record['matches']);
        }
        unset($record);
        if ($p['role'] === 'patient') {
            foreach ($state['hospitals'] as &$hospital) {
                $hospital['beds'] = array_map(fn($bed) => ['care'=>$bed['care'], 'status'=>$bed['status']], $hospital['beds']);
            }
            unset($hospital);
        }
        if ($page !== 'beds') $state['hospitals'] = [];
        if ($page !== 'history') $state['events'] = [];
        if (in_array($page, ['dashboard', 'beds', 'history'])) $state['requests'] = [];
        return response()->json($state + ['operator'=>DemoAccess::operator(),'profile'=>$p, 'csrf_token'=>csrf_token()])->header('Cache-Control', 'no-store, private');
    }

    public function mutate(Request $request, EmergencyBedService $service)
    {
        $data = $request->validate(['action'=>'required|in:create,login,logout,verify,request,accept,reject,arrive,reset']);
        $action = $data['action'];
        $profile=DemoAccess::profile();
        if (!$profile) return response()->json(['message'=>'Sign in with a demo account.'],401);
        if (in_array($action,['login','logout'])) return response()->json(['message'=>'Use the account login/logout controls. Operator PIN access has been replaced by role logins.'],403);
        $requiredPage = in_array($action, ['create', 'request']) ? 'requests' : ($action === 'reset' ? 'dashboard' : 'approvals');
        abort_unless(DemoAccess::canPage($requiredPage), 403, 'This action is not assigned to your role.');
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

    public function manage(Request $request, EmergencyBedService $service)
    {
        $profile = DemoAccess::profile();
        abort_unless($profile && $profile['role'] === 'hospital', 403, 'Hospital operator access required.');
        $input = $request->validate(['action'=>'required|in:capacity,discharge,blood,settings']);
        $page = ['capacity'=>'inventory', 'discharge'=>'inventory', 'blood'=>'blood', 'settings'=>'settings'][$input['action']];
        abort_unless(DemoAccess::canPage($page), 403, 'This module is not assigned to your role.');
        $rules = [
            'capacity'=>['care'=>'required|in:General,ICU,Ventilator', 'total'=>'required|integer|min:0|max:200'],
            'discharge'=>['bed_id'=>'required|string|max:80'],
            'blood'=>['blood_type'=>'required|in:A+,A-,B+,B-,AB+,AB-,O+,O-', 'units'=>'required|integer|min:0|max:10000'],
            'settings'=>['status'=>'required|in:Open,Offline', 'contact'=>'nullable|string|max:40', 'address'=>'nullable|string|max:200'],
        ];
        $data = $request->validate($rules[$input['action']]);
        try {
            // Hospital identity comes only from the authenticated account.
            $service->manageHospital($profile['hospital'], $input['action'], $data);
            return redirect(url('emergency/'.$page))->with('management_success', 'Hospital records updated successfully.');
        } catch (RuntimeException $e) {
            return redirect(url('emergency/'.$page))->withErrors(['management'=>$e->getMessage()])->withInput();
        }
    }
}
