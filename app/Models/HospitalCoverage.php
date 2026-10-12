<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HospitalCoverage extends Model { protected $table='hospital_coverage_mapping';protected $guarded=[];public function hospital(){return $this->belongsTo(Hospital::class);}public function organisation(){return $this->belongsTo(CoverageOrganisation::class);} }
