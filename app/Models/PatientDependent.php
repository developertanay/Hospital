<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PatientDependent extends Model { protected $table='patient_dependents';protected $guarded=[];protected $casts=['owner_id'=>'integer','age'=>'integer']; }
