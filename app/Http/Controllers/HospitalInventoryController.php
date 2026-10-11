<?php
namespace App\Http\Controllers;

use App\Services\EmergencyBedService;
use App\Support\RoleAccess;
use Illuminate\Http\Request;
use RuntimeException;
use App\Models\BloodInv;
use App\Models\bloodtype;
use App\Models\CareEvent;
use Illuminate\Support\Facades\DB;

class HospitalInventoryController extends Controller
{
    private function context(Request $request)
    {
        $profile = RoleAccess::profile();
        $type = $request->segment(2);
        abort_unless($profile && $profile['role'] === 'hospital' && RoleAccess::canPage($type), 403, 'Hospital inventory access required.');
        $hospital = collect(app(EmergencyBedService::class)->state()['hospitals'])->firstWhere('id', $profile['hospital']);
        abort_unless($hospital, 404);
        $hospital['blood_records']=[];
        if ($type==='blood') {
            $names=bloodtype::pluck('blood_type','id');
            foreach (BloodInv::where('hospital_id',$hospital['id'])->where('status',1)->get() as $stock) $hospital['blood_records'][]=['id'=>$stock->id,'blood_type'=>$names[$stock->blood_id] ?? 'Unknown type','units'=>$stock->unit];
        }
        return [$type, $hospital];
    }

    public function index(Request $request)
    {
        [$type, $hospital] = $this->context($request);
        return view('demo.inventory-list', compact('type', 'hospital'));
    }

    public function create(Request $request)
    {
        [$type, $hospital] = $this->context($request);
        $record = null; $entry = null;
        $careTypes=app(EmergencyBedService::class)->careTypes(); $bloodTypes=app(EmergencyBedService::class)->bloodTypes();
        return view('demo.inventory-form', compact('type', 'hospital', 'record', 'entry','careTypes','bloodTypes'));
    }

    public function edit(Request $request, $record)
    {
        [$type, $hospital] = $this->context($request);
        $entry = $type === 'inventory' ? collect($hospital['beds'])->firstWhere('id', $record) : collect($hospital['blood_records'])->firstWhere('id',(int)$record);
        abort_unless($entry, 404, 'Record not found in your hospital.');
        $careTypes=app(EmergencyBedService::class)->careTypes(); $bloodTypes=app(EmergencyBedService::class)->bloodTypes();
        return view('demo.inventory-form', compact('type', 'hospital', 'record', 'entry','careTypes','bloodTypes'));
    }

    public function store(Request $request) { return $this->save($request, 'create'); }
    public function update(Request $request, $record) { return $this->save($request, 'update', $record); }
    public function destroy(Request $request, $record) { return $this->save($request, 'delete', $record); }

    private function save(Request $request, $operation, $record = null)
    {
        [$type, $hospital] = $this->context($request);
        $data = $operation === 'delete' ? [] : $request->validate($type === 'inventory'
            ? ['care'=>['required',\Illuminate\Validation\Rule::in(app(EmergencyBedService::class)->careTypes())], 'name'=>'required|string|max:60']
            : ['blood_type'=>['required',\Illuminate\Validation\Rule::in(app(EmergencyBedService::class)->bloodTypes())], 'units'=>'required|integer|min:0|max:10000']);
        try {
            if ($type==='blood') $this->saveBlood($hospital,$operation,$record,$data);
            else app(EmergencyBedService::class)->inventoryCrud($hospital['id'], $type, $operation, $record, $data);
            return redirect()->route('hospital.'.$type.'.index')->with('management_success', 'Record '.['create'=>'created', 'update'=>'updated', 'delete'=>'deleted'][$operation].' successfully.');
        } catch (RuntimeException $e) {
            return redirect()->back()->withErrors(['inventory'=>$e->getMessage()])->withInput();
        }
    }

    private function saveBlood($hospital,$operation,$id,$data)
    {
        DB::transaction(function () use ($hospital,$operation,$id,$data) {
            DB::table('care_workflow_lock')->where('id',1)->lockForUpdate()->first();
            $stock=$operation==='create' ? new BloodInv : BloodInv::where('hospital_id',$hospital['id'])->where('status',1)->findOrFail($id);
            if ($operation==='delete') $stock->status=9;
            else {
                $type=bloodtype::where('blood_type',$data['blood_type'])->where('status',1)->firstOrFail();
                $duplicate=BloodInv::where('hospital_id',$hospital['id'])->where('blood_id',$type->id)->where('status',1)->when($stock->exists,fn($q)=>$q->where('id','!=',$stock->id))->exists();
                if ($duplicate) throw new RuntimeException('This blood group already exists. Edit its record instead.');
                $stock->hospital_id=$hospital['id']; $stock->blood_id=$type->id; $stock->unit=$data['units']; $stock->status=1;
                if (!$stock->exists) $stock->created_by=auth()->id();
            }
            $stock->updated_by=auth()->id(); $stock->save();
            CareEvent::create(['hospital_id'=>$hospital['id'],'request_id'=>null,'message'=>$hospital['name'].' '.$operation.'d blood inventory record '.$stock->id.'.','at'=>time()]);
        });
    }
}
