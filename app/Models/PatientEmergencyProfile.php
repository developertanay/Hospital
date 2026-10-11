<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PatientEmergencyProfile extends Model
{
    protected $table='patient_emergency_profiles';
    protected $primaryKey='user_id';
    public $incrementing=false;
    protected $guarded=[];
    protected $casts=['user_id'=>'integer','age'=>'integer'];
}
