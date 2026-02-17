<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentAssignmentData extends Model
{
    use HasFactory;
    protected $table=('student_assignment_data');

    public static function getCount($college_id,$assignmnet_id){
                           
        return  StudentAssignmentData::where('college_id',$college_id)
                                        ->where('assignment_id',$assignmnet_id)
                                        ->whereNotNull('marks')
                                        ->count();
    }

    public static function getDataByUserProfile($user_profile_id,$assignment_id){
        return StudentAssignmentData::where('user_profile_id',$user_profile_id)
                                        ->where('assignment_id',$assignment_id)
                                        ->first();
    }

    public static function getDataFromAsignmnetId($assignment_id){
        return StudentAssignmentData::where('assignment_id',$assignment_id)
                                        ->get();

    }
     public static function getCount2($college_id,$assignmnet_id){
                           
        return  StudentAssignmentData::where('college_id',$college_id)
                                        ->where('assignment_id',$assignmnet_id)
                                        // ->whereNotNull('marks')
                                        ->count();
    }
}
