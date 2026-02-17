<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $table = "permission";
    protected $guarded = [];
    public static function getAllRecords() {
        return Permission::where('status','!=',9)
                    ->get();
    }
    public static function getDataFromId($college_id,$course_id,$semesters) {
        return Permission::where('status',1)
                                    ->where('college_id',$college_id,)
                                    ->where('course_id',$course_id,)
                                    ->where('semesters',$semesters)
                                    ->get();

    }
}
