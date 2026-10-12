<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class CareRequest extends Model
{
    protected $table = 'care_requests';
    protected $guarded = [];
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';
    protected $casts = ['verified'=>'boolean','owner_id'=>'integer','created_at'=>'integer','expires_at'=>'integer','reporter_with_patient'=>'boolean'];
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function hospital() { return $this->belongsTo(Hospital::class); }
    public function type() { return $this->belongsTo(bedtype::class, 'bed_type_id'); }
    public static function detailRules()
    {
        return [
            'dependent_id'=>'nullable|integer','coverage_id'=>'nullable|integer','reported_for'=>'nullable|in:Self,Family,Other','reporter_name'=>'nullable|string|max:80','reporter_phone'=>'nullable|regex:/^\+?[0-9]{8,15}$/','reporter_with_patient'=>'nullable|boolean',
            'department_id'=>['nullable','integer',\Illuminate\Validation\Rule::exists('department_mast','id')->where('status',1)],
            'problem_type'=>'nullable|in:Internal,External',
            'assistance_preference'=>'nullable|in:Ambulance,Hospital',
            'description'=>'nullable|string|max:2000',
            'preferred_hospital_id'=>['nullable','integer',\Illuminate\Validation\Rule::exists('hospital_mast','id')->where('status',1)],
            'location_captured_at'=>'nullable|integer|min:0',
        ];
    }
}
