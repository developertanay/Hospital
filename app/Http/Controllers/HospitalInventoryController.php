<?php
namespace App\Http\Controllers;

use App\Services\EmergencyBedService;
use App\Support\DemoAccess;
use Illuminate\Http\Request;
use RuntimeException;

class HospitalInventoryController extends Controller
{
    private function context(Request $request)
    {
        $profile = DemoAccess::profile();
        $type = $request->segment(2);
        abort_unless($profile && $profile['role'] === 'hospital' && DemoAccess::canPage($type), 403, 'Hospital inventory access required.');
        $hospital = collect(app(EmergencyBedService::class)->state()['hospitals'])->firstWhere('id', $profile['hospital']);
        abort_unless($hospital, 404);
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
        return view('demo.inventory-form', compact('type', 'hospital', 'record', 'entry'));
    }

    public function edit(Request $request, $record)
    {
        [$type, $hospital] = $this->context($request);
        $entry = $type === 'inventory' ? collect($hospital['beds'])->firstWhere('id', $record) : (array_key_exists($record, $hospital['blood'] ?? []) ? ['blood_type'=>$record, 'units'=>$hospital['blood'][$record]] : null);
        abort_unless($entry, 404, 'Record not found in your hospital.');
        return view('demo.inventory-form', compact('type', 'hospital', 'record', 'entry'));
    }

    public function store(Request $request) { return $this->save($request, 'create'); }
    public function update(Request $request, $record) { return $this->save($request, 'update', $record); }
    public function destroy(Request $request, $record) { return $this->save($request, 'delete', $record); }

    private function save(Request $request, $operation, $record = null)
    {
        [$type, $hospital] = $this->context($request);
        $data = $operation === 'delete' ? [] : $request->validate($type === 'inventory'
            ? ['care'=>'required|in:General,ICU,Ventilator', 'name'=>'required|string|max:60']
            : ['blood_type'=>'required|in:A+,A-,B+,B-,AB+,AB-,O+,O-', 'units'=>'required|integer|min:0|max:10000']);
        try {
            app(EmergencyBedService::class)->inventoryCrud($hospital['id'], $type, $operation, $record, $data);
            return redirect()->route('hospital.'.$type.'.index')->with('management_success', 'Record '.['create'=>'created', 'update'=>'updated', 'delete'=>'deleted'][$operation].' successfully.');
        } catch (RuntimeException $e) {
            return redirect()->back()->withErrors(['inventory'=>$e->getMessage()])->withInput();
        }
    }
}
