<?php
namespace App\Services;
use App\Models\PatientDependent;
use App\Models\User;
use RuntimeException;
class SosRecipientService {
    public function resolve($p,$input,$profile){
        $for=$input['reported_for']??'Self';$dependent=null;
        if($for==='Self'){$patient=$profile;$name=$profile['name']??User::find($p['id'])->name;$phone=$profile['mobile']??'';}
        else{
            $name=$input['reporter_name'];$phone=$input['reporter_phone'];
            if($for==='Family'){$dependent=PatientDependent::where('owner_id',$p['id'])->where('status',1)->find($input['dependent_id']);if(!$dependent)throw new RuntimeException('Family member belongs to another account or is unavailable.');$patient=['name'=>$dependent->name,'age'=>$dependent->age,'mobile'=>$dependent->mobile??'','emergency_name'=>$name,'emergency_phone'=>$phone];}
            else{$patient=['name'=>$input['patient_name']??'Unknown patient','age'=>$input['patient_age']??null,'mobile'=>$input['patient_phone']??'','emergency_name'=>$name,'emergency_phone'=>$phone];}
        }
        if($for==='Other' && !empty($input['coverage_id']))throw new RuntimeException('Your insurance cannot be applied to an unrelated patient.');
        $coverage=app(PatientCoverageService::class)->select($p['id'],$input['coverage_id']??null,$dependent?$dependent->id:null);
        $summary=$coverage?$coverage->only(['id','organisation_id','tpa_id','policy_number','member_id','valid_from','valid_until','covers_family']):null;
        if($summary)$summary['organisation']=$coverage->organisation->name;
        return ['patient'=>$patient,'reported_for'=>$for,'dependent_id'=>$dependent?$dependent->id:null,'coverage_id'=>$coverage?$coverage->id:null,'coverage'=>$summary,'reporter'=>['name'=>$name,'phone'=>$phone,'with_patient'=>$for==='Self'?true:(bool)$input['reporter_with_patient']],'description'=>$input['description']??null,'pickup_address'=>$input['pickup_address']??null];
    }
    public static function rules(){return ['reported_for'=>'nullable|in:Self,Family,Other','dependent_id'=>'required_if:reported_for,Family|nullable|integer','coverage_id'=>'nullable|integer','patient_name'=>'nullable|string|max:80','patient_age'=>'nullable|integer|min:1|max:120','patient_phone'=>'nullable|regex:/^\+?[0-9]{8,15}$/','reporter_name'=>'required_if:reported_for,Family,Other|nullable|string|max:80','reporter_phone'=>'required_if:reported_for,Family,Other|nullable|regex:/^\+?[0-9]{8,15}$/','reporter_with_patient'=>'required_if:reported_for,Family,Other|nullable|boolean','description'=>'required_if:reported_for,Family,Other|nullable|string|max:2000','pickup_address'=>'nullable|string|max:200'];}
}
