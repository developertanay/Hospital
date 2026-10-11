<?php
namespace App\Http\Controllers;

use App\Services\EmergencyBedService;
use Illuminate\Http\Request;
use RuntimeException;
use App\Support\RoleAccess;

class EmergencyController extends Controller
{
    public function index($page = 'dashboard')
    {
        $profile = RoleAccess::profile();
        if (!$profile) return redirect(auth()->check() ? '/dashboard' : '/login');
        abort_unless(RoleAccess::canPage($page), 403, 'This page is not assigned to your role.');

        $summary = null;
        if ($profile['role'] === 'admin' && $page === 'dashboard') {
            $summary = [
                ['label' => 'Hospitals', 'value' => \App\Models\Hospital::where('status', '!=', 9)->count(), 'url' => 'Hospital'],
                ['label' => 'Departments', 'value' => \App\Models\Department::where('status', '!=', 9)->count(), 'url' => 'Department'],
                ['label' => 'Ambulances', 'value' => \App\Models\Ambulance::where('status', '!=', 9)->count(), 'url' => 'Ambulance'],
                ['label' => 'Bed inventory records', 'value' => \App\Models\BedInven::where('status','!=',9)->count(), 'url' => 'BedInven'],
                ['label' => 'Blood units', 'value' => \App\Models\BloodInv::where('status', '!=', 9)->sum('unit'), 'url' => 'BloodInven'],
                ['label' => 'Accounts', 'value' => \App\Models\User::where('college_id', $profile['college_id'])->count(), 'url' => 'Accounts'],
            ];
        }
        $careTypes = app(EmergencyBedService::class)->careTypes();
        $details = $page === 'dashboard' ? $this->dashboardDetails($profile) : [];
        return view('emergency', compact('summary', 'page', 'careTypes', 'details'));
    }

    private function dashboardDetails($profile)
    {
        $menu = RoleAccess::menu();
        if ($profile['role'] === 'admin') {
            return [
                'hospitals' => isset($menu['Hospital']) ? \App\Models\Hospital::where('status','!=',9)->orderByDesc('id')->limit(5)->get() : collect(),
                'ambulances' => isset($menu['Ambulance']) ? \App\Models\Ambulance::with(['category','hospital'])->where('status','!=',9)->orderByDesc('id')->limit(5)->get() : collect(),
            ];
        }
        if ($profile['role'] === 'hospital') {
            $id = $profile['hospital'];
            return [
                'hospital' => $id ? \App\Models\Hospital::find($id) : null,
                'bedStatus' => $id ? \App\Models\CareBed::where('hospital_id',$id)->selectRaw('status, count(*) as total')->groupBy('status')->get() : collect(),
                'departments' => $id ? \App\Models\HospitalDepartment::with('department')->where('hospital_id',$id)->where('status',1)->whereHas('department',fn($q)=>$q->where('status',1))->get() : collect(),
            ];
        }
        return ['requests' => \App\Models\CareRequest::with(['hospital','type'])->where('owner_id',$profile['id'])->orderByDesc('created_at')->limit(5)->get()];
    }

    public function state(Request $request, EmergencyBedService $service)
    {
        $p=RoleAccess::profile();
        if (!$p) return response()->json(['message'=>'Sign in with a demo account.'],401);
        if ($request->is('api/mobile/*')) return $this->mobileState($p,$service);
        $page = $request->query('page', 'dashboard');
        abort_unless(RoleAccess::canPage($page), 403, 'This data is not assigned to your role.');
        if ($p['role'] === 'admin') {
            return response()->json(['requests'=>[], 'hospitals'=>[], 'events'=>[], 'stats'=>[], 'operator'=>false, 'profile'=>$p, 'csrf_token'=>csrf_token()])->header('Cache-Control', 'no-store, private');
        }
        $state=$service->state();
        $state['care_types']=$service->careTypes();
        if ($p['role']==='patient') {
            $state['requests']=array_values(array_filter($state['requests'],fn($r)=>($r['owner_id'] ?? null)===$p['id']));
        }
        if ($p['role']==='hospital') {
            $state['hospitals']=array_values(array_filter($state['hospitals'],fn($h)=>$h['id']===$p['hospital']));
            $state['requests']= $p['hospital'] ? array_values(array_filter($state['requests'],fn($r)=>($page==='approvals' && $r['status']==='New') || $r['hospital_id']===$p['hospital'])) : [];
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
            'occupied' => collect($state['requests'])->where('status', 'Occupied')->count(),
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
        return response()->json($state + ['operator'=>RoleAccess::operator(),'profile'=>$p, 'csrf_token'=>csrf_token()])->header('Cache-Control', 'no-store, private');
    }

    private function mobileState($p, EmergencyBedService $service)
    {
        $base=['profile'=>$p,'operator'=>RoleAccess::operator(),'care_types'=>$service->careTypes(),
            'requests'=>[],'hospitals'=>[],'events'=>[],'capabilities'=>[]];
        if (in_array($p['role'],['admin','driver'],true)) return response()->json($base)->header('Cache-Control','no-store, private');
        $requestAccess=RoleAccess::canPage($p['role']==='hospital'?'approvals':'requests');
        $reservationAccess=$p['role']==='patient' && RoleAccess::canPage('reservations');
        $bedAccess=$p['role']==='hospital' && RoleAccess::canPage('beds');
        $historyAccess=RoleAccess::canPage('history');
        $s=$service->state();
        $requests=array_values(array_filter($s['requests'],fn($r)=>$p['role']==='patient'
            ? ($r['owner_id']??null)===$p['id']
            : ($p['hospital'] && ($r['status']==='New' || $r['hospital_id']===$p['hospital']))));
        if ($requestAccess || $reservationAccess) {
            foreach ($requests as &$r) { unset($r['client'],$r['owner_id']); if (!$requestAccess) unset($r['matches']); }
            unset($r); $base['requests']=$requests;
        }
        if ($bedAccess || ($p['role']==='patient' && $requestAccess)) {
            $base['hospitals']=array_values(array_filter($s['hospitals'],fn($h)=>$p['role']==='patient' || $h['id']===$p['hospital']));
            if ($p['role']==='patient') foreach ($base['hospitals'] as &$h) $h['beds']=array_map(fn($b)=>['care'=>$b['care'],'status'=>$b['status']],$h['beds']);
            unset($h);
        }
        if ($historyAccess) {
            $ids=array_column($requests,'id');
            $base['events']=array_values(array_filter($s['events'],fn($e)=>in_array($e['request_id']??null,$ids,true)
                || ($p['role']==='hospital' && $p['hospital'] && ($e['hospital_id']??null)===$p['hospital'])));
        }
        $base['capabilities']=array_values(array_filter([$requestAccess?($p['role']==='patient'?'create':'approve'):null,$requestAccess && $p['role']==='patient'?'request':null]));
        $base['now']=$s['now']; $base['ttl']=$s['ttl'];
        return response()->json($base)->header('Cache-Control','no-store, private');
    }

    public function mutate(Request $request, EmergencyBedService $service)
    {
        if (RoleAccess::profile() && RoleAccess::profile()['role']==='driver') return response()->json(['message'=>'Use ambulance dispatch actions.'],403);
        $data = $request->validate(['action'=>'required|in:create,verify,request,accept,reject,arrive']);
        $action = $data['action'];
        $profile=RoleAccess::profile();
        if (!$profile) return response()->json(['message'=>'Sign in with a demo account.'],401);
        $requiredPage = in_array($action, ['create', 'request']) ? 'requests' : 'approvals';
        abort_unless(RoleAccess::canPage($requiredPage), 403, 'This action is not assigned to your role.');
        try {
            if ($action === 'create') {
                if ($profile['role']==='hospital') return response()->json(['message'=>'Use the Patient login to submit emergencies.'],403);
                $input = $request->validate(['patient'=>'required|string|max:80','location'=>'required|string|max:120','severity'=>'required|in:Critical,Urgent,Stable','care'=>['required',\Illuminate\Validation\Rule::in($service->careTypes())], 'latitude'=>'nullable|numeric|between:-90,90|required_with:longitude','longitude'=>'nullable|numeric|between:-180,180|required_with:latitude']);
                $id = $service->create($input, hash('sha256',$request->ip()),$profile['id']);
                return response()->json(['id'=>$id,'message'=>'Request created. Operator verification required for elevated priority.']);
            } elseif ($action === 'request') {
                $request->validate(['id'=>'required|string|max:40','hospital_id'=>'required|integer|exists:hospital_mast,id']);
                $request->merge(['hospital_id'=>(string)$request->hospital_id]);
                $record=collect($service->state()['requests'])->firstWhere('id',$request->id);
                if (!$record || ($profile['role']!=='admin' && ($profile['role']!=='patient' || ($record['owner_id'] ?? null)!==$profile['id']))) return response()->json(['message'=>'You can request a bed only for your own patient request.'],403);
                $service->requestBed($request->id,$request->hospital_id,$profile['role']==='patient'?$profile['id']:null);
            } else {
                if (!RoleAccess::operator()) return response()->json(['message'=>'Hospital operator access required.'],403);
                {
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
        $profile = \App\Support\RoleAccess::profile();
        abort_unless($profile && $profile['role']==='hospital' && \App\Support\RoleAccess::canPage('inventory'),403);
        $data=$request->validate(['action'=>'required|in:discharge','bed_id'=>'required|string|max:80']);
        try { $service->manageHospital($profile['hospital'],'discharge',$data); }
        catch (RuntimeException $e) { return back()->withErrors(['discharge'=>$e->getMessage()]); }
        return redirect()->route('hospital.inventory.index')->with('management_success','Patient discharged; bed available.');
    }
}
