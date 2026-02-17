<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course_Subject_Mapping extends Model
{
    protected $table = "course_subject_mapping";
    protected $guarded = [];
    


    public static function getAllRecords() {
        return Course_Subject_Mapping::where('status','!=',9)
                    ->get();
    }
    public static function getDataFromId($college_id,$course_id,$semester) {
        return Course_Subject_Mapping::where('status','!=',9)
                                    ->where('college_id',$college_id)
                                    ->where('course_id',$course_id)
                                    ->where('semester',$semester)
                                    ->get();

    }

    public static function getFilteredDataFromId($college_id,$course_id,$semester,$already_studied_subjects) {
        return Course_Subject_Mapping::where('status','!=',9)
                                    ->where('college_id',$college_id)
                                    ->where('course_id',$course_id)
                                    ->where('semester',$semester)
                                    ->whereNotIn('subject_id', $already_studied_subjects)
                                    ->get();

    }

    public static function countMappedSubjects($college_id,$course_arr,$semester_arr){
        return Course_Subject_Mapping::where('status','!=',9)
                                     ->where('college_id',$college_id)
                                     ->whereIn('course_id',$course_arr)
                                     ->whereIn('semester',$semester_arr)
                                     ->get();
    }

    public static function getUniqueRecords($college_id='') {
        $data = Course_Subject_Mapping::select('college_id', 'course_id', 'semester')
                                    ->where('status','!=',9);

        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data = $data->groupBy('college_id', 'course_id', 'semester')
                           ->get();

        return $final_data;
    }

    public static function getSubjectsFromSubjectType($college_id,$course_id,$semester, $subject_type_id) {
        return Course_Subject_Mapping::where('college_id',$college_id)
                                    ->where('course_id',$course_id)
                                    ->where('semester',$semester)
                                    ->where('subject_type_id',$subject_type_id)
                                    ->where('status','!=',9)
                                    ->get();

    } 

    public static function pluckDSCSubjects($academic_year='',$college_id='',$semester='',$course_id='',$subject_type_id=''){
        // return Course_Subject_Mapping::where('academic_year',$academic_year)
        //                               ->where('college_id',$college_id)
        //                               ->where('semester',$semester)
        //                               ->where('course_id',$course_id)
        //                               ->where('subject_type_id',$subject_type_id)
        //                               ->where('status',1)
        //                               ->pluck('subject_id')->toArray();  

        return Course_Subject_Mapping::where('college_id',$college_id)
                                      ->where('semester',$semester)
                                      ->where('course_id',$course_id)
                                      ->where('subject_type_id',$subject_type_id)
                                      ->where('status',1)
                                      ->pluck('subject_id')->toArray();  

    }


     public static function getUniqueRecords2($college_id, $course_id, $semester) {
        $data = Course_Subject_Mapping::select('college_id', 'course_id', 'semester')
                                    ->where('status','!=',9);

        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        if(!empty($course_id)) {
            $data->where('course_id', $course_id);
        }
        if(!empty($semester)) {
            $data->where('semester', $semester);
        }

        $final_data = $data->groupBy('college_id', 'course_id', 'semester')
                           ->get();

        return $final_data;
    }
   
}
