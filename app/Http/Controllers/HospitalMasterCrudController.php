<?php
namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Models\Hospital;
use App\Models\Department;

// Shared CRUD for the small master screens; each resource specifies its model.
abstract class HospitalMasterCrudController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            abort_unless(isset(\App\Support\RoleAccess::menu()[$this->menu]),403,'This module is not assigned to your role.');
            return $next($request);
        });
    }
    protected function records() { return (new $this->model)->newQuery(); }
    public function index()
    {
        $query = $this->records()->where('status','!=',9);
        if (!$this->field) $query->with(['hospital','department']);
        $rows = $query->get();
        return view('hospital-masters.index', ['rows'=>$rows,'menu'=>$this->menu,'title'=>$this->title,'field'=>$this->field]);
    }
    public function create() { return $this->form(new $this->model); }
    public function edit($id) { return $this->form($this->records()->where('status','!=',9)->findOrFail($id)); }
    protected function form($record)
    {
        return view('hospital-masters.form', ['record'=>$record,'menu'=>$this->menu,'title'=>$this->title,'field'=>$this->field,
            'hospitals'=>Hospital::where('status',1)->get(),'departments'=>Department::where('status',1)->get()]);
    }
    public function store(Request $request) { return $this->save($request, new $this->model); }
    public function update(Request $request, $id) { return $this->save($request, $this->records()->where('status','!=',9)->findOrFail($id)); }
    protected function save(Request $request, $record)
    {
        $rules = ['status'=>'required|in:1,2'];
        if ($this->field) {
            $rules[$this->field] = ['required','string','max:100',Rule::unique($record->getTable(),$this->field)->ignore($record->id)];
        } else {
            $rules['hospital_id'] = ['required','integer',Rule::exists('hospital_mast','id')->where('status',1)];
            $unique = Rule::unique('hospital_department_mapping','department_id')->where('hospital_id',$request->hospital_id)->ignore($record->id);
            if (!$record->exists) $unique->where(function ($query) { $query->where('status','!=',9); });
            $rules['department_id'] = ['required','integer',Rule::exists('department_mast','id')->where('status',1),$unique];
        }
        $data = $request->validate($rules);
        if (!$this->field && !$record->exists) {
            $record = $this->records()->where('hospital_id',$data['hospital_id'])->where('department_id',$data['department_id'])->where('status',9)->first() ?? $record;
        }
        if (!$record->exists) $data['created_by'] = auth()->id();
        $data['updated_by'] = auth()->id();
        $record->fill($data)->save();
        return redirect()->route($this->menu.'.index')->with('message','Record saved successfully.');
    }
    public function destroy($id)
    {
        $record = $this->records()->where('status','!=',9)->findOrFail($id);
        if ($this->menu === 'Department' && \App\Models\HospitalDepartment::where('department_id',$record->id)->where('status','!=',9)->exists()) {
            return back()->withErrors(['department'=>'Remove hospital mappings before deleting this department.']);
        }
        $record->status = 9;
        $record->updated_by = auth()->id();
        $record->save();
        return redirect()->route($this->menu.'.index')->with('message','Record deleted.');
    }
}
