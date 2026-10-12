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
    protected $casts = ['verified'=>'boolean','owner_id'=>'integer','created_at'=>'integer','expires_at'=>'integer'];
    public function owner() { return $this->belongsTo(User::class, 'owner_id'); }
    public function hospital() { return $this->belongsTo(Hospital::class); }
    public function type() { return $this->belongsTo(bedtype::class, 'bed_type_id'); }
    public static function detailRules()
    {
        return [
            'department_id'=>['nullable','integer',\Illuminate\Validation\Rule::exists('department_mast','id')->where('status',1)],
            'problem_type'=>'nullable|in:Internal,External',
            'assistance_preference'=>'nullable|in:Ambulance,Hospital',
            'description'=>'nullable|string|max:2000',
            'preferred_hospital_id'=>['nullable','integer',\Illuminate\Validation\Rule::exists('hospital_mast','id')->where('status',1)],
            'location_captured_at'=>'nullable|integer|min:0',
        ];
    }
}
