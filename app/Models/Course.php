<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;
// use DB;

class Course extends Model 
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "course_mast";
    protected $guarded = [];
    

    public static function getAllRecords($college_id, $department_id, $name) {
        $data = Course::where('status','!=',9);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        if(!empty($department_id)) {
            $data->where('department_id', $department_id);
        }
        if(!empty($name)) {
            $data->where('id', $name);
        }

        $final_data = $data->get();
        return $final_data;
    }

    public static function getDataFromId($id) {
        return Course::where('id', $id)
                    ->first();
    }

    public static function updateDataFromId($id, $arr_to_update) {
        return Course::where('id', $id)
                    ->update($arr_to_update);
    }

    
    public static function pluckCodeAndName($college_id = '') {
        $data = Course::where('status','!=', 9);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        return $data->pluck('name', 'id');
    }

    public static function pluckActiveCodeAndName($college_id = '') {
        $data =  Course::where('status', 1);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        return $data->pluck('name', 'id');
    }

    public static function getCoursesFromCollege($college_id) {
        return Course::where('status', 1)
                    ->where('college_id', $college_id)
                    ->get();
    }
    public static function getSemesterFromCourseId($course_id){
        $data =  Course::where('status',1)
                    ->where('id',$course_id)
                    ->pluck('semesters')->toArray();
        if(count($data)>0) {
            $semester = $data[0];
        }
        else {
            $semester = '';
        }
        return $semester;
    }

    public function getFilteredCoursesFromCollege($college_id, $course_id_arr) {
        $data = Course::where('status',1)
                    ->where('college_id', $college_id);

        if(count($course_id_arr)>0) {
            $data->whereIn('id', $course_id_arr);
        }
        $final_data = $data->pluck('name', 'id');
    }

    public static function getActiveCoursesFromCollege($college_id ='') {
        return Course::where('status', 1)
                    ->where('college_id', $college_id)
                    ->pluck('name','id');
    }
    public static function pluckSemester($course_id){
        return Course::where('status',1)
                    ->where('id',$course_id)
                    ->pluck('semesters')
                    ->first();

    }

    public static function pluckAllCourses() {
        return Course::where('status', 1)
                    ->pluck('name','id');
    }


}


