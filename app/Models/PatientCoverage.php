<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PatientCoverage extends Model { protected $table='patient_coverages';protected $guarded=[];protected $casts=['owner_id'=>'integer','covers_family'=>'boolean'];public function organisation(){return $this->belongsTo(CoverageOrganisation::class);}public function tpa(){return $this->belongsTo(CoverageOrganisation::class,'tpa_id');} }
