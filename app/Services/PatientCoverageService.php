<?php
namespace App\Services;
use App\Models\PatientCoverage;
use App\Models\PatientDependent;
use App\Models\CoverageOrganisation;
use App\Models\HospitalCoverage;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
class PatientCoverageService {
    public function state($owner){return ['dependents'=>PatientDependent::where('owner_id',$owner)->where('status',1)->get()->toArray(),'coverages'=>PatientCoverage::with(['organisation','tpa'])->where('owner_id',$owner)->where('status',1)->get()->map(function($c){$r=$c->toArray();$r['has_card']=(bool)$c->card_path;unset($r['card_path']);return $r;})->all(),'organisations'=>CoverageOrganisation::where('status',1)->orderBy('name')->get(['id','name','kind'])->toArray()];}
    public function select($owner,$coverageId,$dependentId=null){
        if($dependentId && !PatientDependent::where('owner_id',$owner)->where('status',1)->where('id',$dependentId)->exists())throw new RuntimeException('Choose a family member from your account.');
        if(!$coverageId)return null;
        $c=PatientCoverage::with(['organisation','tpa'])->where('owner_id',$owner)->where('status',1)->find($coverageId);
        if(!$c || !$c->organisation || $c->organisation->status!=1)throw new RuntimeException('Coverage is unavailable or belongs to another account.');
        if($dependentId ? !($c->dependent_id==$dependentId || ($c->dependent_id===null && $c->covers_family)) : $c->dependent_id!==null)throw new RuntimeException('This coverage does not cover the selected patient.');
        return $c;
    }
    public function compatibility($coverageId,$hospital){
        if(!$coverageId || !Schema::hasTable('patient_coverages'))return ['status'=>'Not supplied','cashless'=>'Not requested'];
        $c=PatientCoverage::with('organisation')->find($coverageId);
        if(!$c || $c->status!=1 || !$c->organisation || $c->organisation->status!=1)return ['status'=>'Details not verified','cashless'=>'Not approved'];
        if(($c->valid_until && $c->valid_until<date('Y-m-d')) || ($c->valid_from && $c->valid_from>date('Y-m-d')))return ['status'=>'Outside policy validity','cashless'=>'Not approved'];
        $mapping=HospitalCoverage::where('hospital_id',$hospital)->where('status',1)->whereHas('organisation',fn($q)=>$q->where('status',1))->whereIn('organisation_id',array_filter([$c->organisation_id,$c->tpa_id]))->orderByRaw("CASE WHEN settlement_mode = 'Cashless' THEN 0 ELSE 1 END")->first();
        return ['status'=>$mapping?'Empanelled · verification required':'Not empanelled','organisation'=>$c->organisation->name,'settlement_mode'=>$mapping->settlement_mode??null,'cashless'=>'Not approved'];
    }
}
