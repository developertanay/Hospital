<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobApplication extends Model
{
    // use HasFactory;
    protected $table = "job_application";
    protected $guarded = [];

    public static function getPreviouslyAppliedJobs($users_id) {
        return JobApplication::where('users_id', $users_id)
                            ->get();
    }
    public static function getAllApplicants() {
        return JobApplication::where('status','!=',9)
                            ->get();
    }
    public static function pluckAllStudent($job_id='') {
        return JobApplication::where('job_id',$job_id)
                              ->where('status',1)
                              ->pluck('users_id')->toArray();
    }

    public static function pluckAlreadyAppliedJobs($users_id) {
        return JobApplication::where('users_id', $users_id)
                              ->where('job_type_id',1)
                              ->pluck('job_id')->toArray();
    }
    public static function pluckAlreadyAppliedInternship($users_id) {
        return JobApplication::where('users_id', $users_id)
                              ->where('job_type_id',2)
                              ->pluck('job_id')->toArray();
    }


}
