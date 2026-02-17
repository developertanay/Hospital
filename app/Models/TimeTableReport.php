<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeTableReport extends Model
{
    protected $table = "time_table_mast";
    protected $guarded = [];



    public static function getRoomWiseTimeTable($college_id='',$academic_year='',$room='',$semester=''){
        $data=TimeTable::where('status',1);
        if(!empty($college_id)){
            $data->where('college_id',$college_id);
        }
        if(!empty($academic_year)){
            $data->where('academic_year',$academic_year);
        }
        if(!empty($room)){
            $data->where('room_id',$room);
        }
        if(!empty($semester)){
            $data->where('semester',$semester);
        }
        $final_data=$data->get();
        return $final_data;
     
    }
    public static function getCourseWiseTimeTable($college_id='',$academic_year='',$course_id='',$semester=''){
        $data=TimeTable::where('status',1);
        if(!empty($college_id)){
            $data->where('college_id',$college_id);
        }
        if(!empty($academic_year)){
            $data->where('academic_year',$academic_year);
        }
        if(!empty($course_id)){
            $data->where('course_id',$course_id);
        }
        if(!empty($semester)){
            $data->where('semester',$semester);
        }
        $final_data=$data->get();
        return $final_data;
     
    }
    public static function getFacultyWiseTimeTable($college_id='',$academic_year='',$faculty='',$semester=''){
        $data=TimeTable::where('status',1);
        if(!empty($college_id)){
            $data->where('college_id',$college_id);
        }
        if(!empty($academic_year)){
            $data->where('academic_year',$academic_year);
        }
        if(!empty($faculty)){
            $data->where('faculty_id',$faculty);
        }
        if(!empty($semester)){
            $data->where('semester',$semester);
        }
        // dd($college_id,$academic_year,$faculty,$semester);
        $final_data=$data->get();
        return $final_data;
     
    }
    public static function getStudentWiseTimeTable($college_id='',$academic_year='',$semester='',$section='',$subject_id=''){
        $data=TimeTable::where('status',1);
        if(!empty($college_id)){
            $data->where('college_id',$college_id);
        }
        if(!empty($academic_year)){
            $data->where('academic_year',$academic_year);
        }
        if(!empty($semester)){
            $data->where('semester',$semester);
        }
        if(!empty($section)){
            $data->where('section',$section);
        }
        if(!empty($subject_id)){
            $data->where('subject_id',$subject_id);
        }
        $final_data=$data->get();
        // dd($college_id,$academic_year,$semester,$section,$subject_id,$final_data);
        return $final_data;
     
    }
}
