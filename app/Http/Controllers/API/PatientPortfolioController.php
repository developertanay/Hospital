<?php
namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Models\PatientCoverage;
use App\Models\PatientDependent;
use App\Models\CareRequest;
use App\Support\RoleAccess;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Storage;
class PatientPortfolioController extends Controller {
    private function patient(){ $p=RoleAccess::profile();abort_unless($p && $p['role']==='patient',403);return $p['id']; }
    private function model(Request $r){return strpos($r->path(),'/dependents')!==false?PatientDependent::class:PatientCoverage::class;}
    public function index(){return response()->json(app(\App\Services\PatientCoverageService::class)->state($this->patient()))->header('Cache-Control','private,no-store');}
    public function store(Request $r){$model=$this->model($r);return $this->save($r,new $model);}
    public function update(Request $r,$id){$model=$this->model($r);return $this->save($r,$model::where('owner_id',$this->patient())->where('status',1)->findOrFail($id));}
    private function save(Request $r,$record){
        $owner=$this->patient();
        $rules=$record instanceof PatientDependent?['name'=>'required|string|max:80','relationship'=>'required|string|max:40','age'=>'nullable|integer|min:1|max:120','mobile'=>'nullable|regex:/^\+?[0-9]{8,15}$/']:[
            'dependent_id'=>['nullable','integer',Rule::exists('patient_dependents','id')->where('owner_id',$owner)->where('status',1)],
            'organisation_id'=>['required','integer',Rule::exists('coverage_organisations','id')->where('status',1)],
            'tpa_id'=>['nullable','integer',Rule::exists('coverage_organisations','id')->where('status',1)->where('kind','TPA')],
            'policy_number'=>'nullable|string|max:100','member_id'=>'nullable|string|max:100','valid_from'=>'nullable|date','valid_until'=>'nullable|date','covers_family'=>'required|boolean',
        ];
        $data=$r->validate($rules);
        if(!empty($data['valid_from']) && !empty($data['valid_until']) && strtotime($data['valid_until'])<strtotime($data['valid_from']))throw \Illuminate\Validation\ValidationException::withMessages(['valid_until'=>'Expiry must not be before the start date.']);
        if($record instanceof PatientCoverage){
            $r->validate(['card_base64'=>'nullable|string|max:2800000','remove_card'=>'nullable|boolean']);
            if($r->card_base64){$bytes=base64_decode($r->card_base64,true);$image=$bytes===false?false:@getimagesizefromstring($bytes);abort_unless($image && strlen($bytes)<=2097152 && in_array($image['mime'],['image/jpeg','image/png','image/webp']),422,'Upload JPG, PNG or WebP up to 2 MB.');$path='insurance-cards/'.bin2hex(random_bytes(16));Storage::disk('local')->put($path,$bytes);$data['card_path']=$path;}
            elseif($r->boolean('remove_card'))$data['card_path']=null;
        }
        \Illuminate\Support\Facades\DB::transaction(function()use($record,$data,$owner){
            $record->fill($data+['owner_id'=>$owner,'status'=>1])->save();
            if($record instanceof PatientCoverage)CareRequest::where('coverage_id',$record->id)->whereNotIn('status',['Cancelled','Discharged'])->update(['insurance_status'=>'Pending','cashless_status'=>'NotRequested','insurance_note'=>'Coverage details changed; re-verification required.']);
        });
        return response()->json(['id'=>$record->id,'message'=>'Details saved. Coverage requires hospital verification.']);
    }
    public function destroy(Request $r,$id){$model=$this->model($r);$record=$model::where('owner_id',$this->patient())->where('status',1)->findOrFail($id);$record->status=9;$record->save();return response()->json(['message'=>'Record archived. Previous bookings are preserved.']);}
    public function card($id){
        $p=RoleAccess::profile();abort_unless($p,403);$c=PatientCoverage::findOrFail($id);
        $own=$p['role']==='patient' && $c->owner_id===$p['id'];$hospital=$p['role']==='hospital' && RoleAccess::canPage('approvals') && CareRequest::where('coverage_id',$id)->where('hospital_id',$p['hospital'])->exists();
        abort_unless($own||$hospital,403);abort_unless($c->card_path && Storage::disk('local')->exists($c->card_path),404);
        return Storage::disk('local')->response($c->card_path,null,['Cache-Control'=>'private,no-store']);
    }
    public function review(Request $r){
        $p=RoleAccess::profile();abort_unless($p && $p['role']==='hospital' && RoleAccess::canPage('approvals'),403);
        $data=$r->validate(['request_id'=>'required|string|max:40','insurance_status'=>'required|in:Pending,Verified,Rejected','cashless_status'=>'required|in:NotRequested,Pending,Approved,Rejected','insurance_note'=>'required|string|max:1000']);
        abort_unless(CareRequest::where('id',$data['request_id'])->where('hospital_id',$p['hospital'])->exists(),403);
        try{app(\App\Services\EmergencyBedService::class)->reviewCoverage($data['request_id'],$p['hospital'],$data);}
        catch(\RuntimeException $e){return response()->json(['message'=>$e->getMessage()],409);}
        return response()->json(['message'=>'Insurance verification and cashless decision recorded separately.']);
    }
}
