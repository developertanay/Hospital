<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class HospitalDepartment extends Model
{
    protected $table = 'hospital_department_mapping';
    protected $guarded = [];
    public function hospital() { return $this->belongsTo(Hospital::class); }
    public function department() { return $this->belongsTo(Department::class); }
}
