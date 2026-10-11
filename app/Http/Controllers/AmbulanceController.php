<?php
namespace App\Http\Controllers;
use App\Models\Ambulance;
use App\Models\AmbulanceCategory;
use App\Models\Hospital;
use App\Support\RoleAccess;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;

class AmbulanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware(function ($request, $next) {
            abort_unless(isset(RoleAccess::menu()['Ambulance']),403,'Ambulance Details is not assigned to your role.');
            return $next($request);
        });
    }
    public function index()
    {
        $ambulances = Ambulance::with(['category','hospital'])->where('status','!=',9)->orderBy('vehicle_number')->get();
        return view('ambulances.index',compact('ambulances'));
    }
    public function create() { return $this->form(new Ambulance); }
    public function edit($id) { return $this->form($this->record($id)); }
    private function record($id) { return Ambulance::where('status','!=',9)->findOrFail($id); }
    private function form($ambulance)
    {
        // Keep an existing inactive selection visible when editing its record.
        $categories = AmbulanceCategory::where(function ($query) use ($ambulance) {
            $query->where('status',1)->orWhere('id',$ambulance->ambulance_category_id);
        })->orderBy('name')->get();
        $hospitals = Hospital::where(function ($query) use ($ambulance) {
            $query->where('status',1)->orWhere('id',$ambulance->hospital_id);
        })->orderBy('hospital_name')->get();
        return view('ambulances.form',compact('ambulance','categories','hospitals'));
    }
    public function store(Request $request) { return $this->save($request,new Ambulance); }
    public function update(Request $request,$id) { return $this->save($request,$this->record($id)); }
    private function save(Request $request,Ambulance $ambulance)
    {
        $request->merge(['vehicle_number'=>strtoupper(preg_replace('/\s+/','',trim((string)$request->vehicle_number)))]);
        $category = Rule::exists('ambulance_category_mast','id')->where(function ($query) use ($ambulance) {
            $query->where('status',1);
            if ($ambulance->exists) $query->orWhere('id',$ambulance->ambulance_category_id);
        });
        $hospital = Rule::exists('hospital_mast','id')->where(function ($query) use ($ambulance) {
            $query->where('status',1);
            if ($ambulance->exists && $ambulance->hospital_id) $query->orWhere('id',$ambulance->hospital_id);
        });
        $contact = 'string|max:30|regex:/^[0-9+() .-]{7,30}$/';
        $rules = [
            'vehicle_number'=>['required','string','max:30','regex:/^[A-Z0-9-]+$/',Rule::unique('ambulance_details','vehicle_number')->ignore($ambulance->id)],
            'ambulance_category_id'=>['required','integer',$category],
            'operation_type'=>'required|in:hospital,private',
            'hospital_id'=>['exclude_unless:operation_type,hospital','required','integer',$hospital],
            'operator_name'=>'nullable|string|max:100','operator_contact'=>'nullable|'.$contact,
            'doctor_name'=>'nullable|required_with:doctor_contact|string|max:100',
            'doctor_contact'=>'nullable|required_with:doctor_name|'.$contact,
            'driver_name'=>'required|string|max:100','driver_contact'=>'required|'.$contact,
            'status'=>'required|in:1,2',
        ];
        foreach (['photo','doctor_photo','driver_photo'] as $field) {
            $rules[$field] = 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048';
            $rules['remove_'.$field] = 'nullable|boolean';
        }
        $data = $request->validate($rules);
        $data['hospital_id'] = $data['operation_type']==='hospital' ? $data['hospital_id'] : null;
        $newPhotos = [];
        $oldPhotos = [];
        try {
            foreach (['photo','doctor_photo','driver_photo'] as $field) {
                $column = $field.'_path';
                if ($request->hasFile($field)) {
                    $path = $request->file($field)->store('ambulance-photos','local');
                    if (!$path) throw new \RuntimeException('Photo could not be saved.');
                    $newPhotos[] = $path;
                    $data[$column] = $path;
                    if ($ambulance->$column) $oldPhotos[] = $ambulance->$column;
                } elseif ($request->boolean('remove_'.$field)) {
                    if ($ambulance->$column) $oldPhotos[] = $ambulance->$column;
                    $data[$column] = null;
                }
                unset($data[$field],$data['remove_'.$field]);
            }
            if (!$ambulance->exists) $data['created_by'] = auth()->id();
            $data['updated_by'] = auth()->id();
            $ambulance->fill($data)->save();
        } catch (\Throwable $exception) {
            foreach ($newPhotos as $path) Storage::disk('local')->delete($path);
            throw $exception;
        }
        foreach ($oldPhotos as $path) Storage::disk('local')->delete($path);
        return redirect()->route('Ambulance.index')->with('message','Ambulance saved successfully.');
    }
    public function destroy($id)
    {
        $ambulance = $this->record($id);
        $ambulance->status = 9;
        $ambulance->updated_by = auth()->id();
        $ambulance->save();
        return redirect()->route('Ambulance.index')->with('message','Ambulance deleted.');
    }
    public function photo($id,$kind)
    {
        abort_unless(in_array($kind,['photo','doctor_photo','driver_photo']),404);
        $ambulance = $this->record($id);
        $path = $ambulance->{$kind.'_path'};
        abort_unless($path && Storage::disk('local')->exists($path),404);
        return response()->file(Storage::disk('local')->path($path),['Cache-Control'=>'private, no-store','X-Content-Type-Options'=>'nosniff']);
    }
}
