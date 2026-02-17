<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentIdData extends Model
{
    use HasFactory;
    protected $table = "student_id_data";

    public static function getDataFromUserProfileId($users_id){
        return StudentIdData::where('users_id',$users_id)
                              ->first();
    }

    public static function doesStudentIdExist($users_id){
        return StudentIdData::where('status',1)
                              ->where('users_id',$users_id)
                              ->pluck('blood_group');
    }
}
