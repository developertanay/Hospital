<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;
use DB;

class TimeTable extends Model 
{

   
    protected $table = "time_table_mast";
    protected $guarded = [];



    public static function getAllRecords($faculty_id, $session_duration,$academic_year){

        $data=TimeTable::where('status',1);
        if(!empty($faculty_id)){
            $data->where('faculty_id',$faculty_id);
        }
        if(!empty($session_duration)) {
            $data->where('session_duration',$session_duration);
        }
        if(!empty($academic_year)) {
            $data->where('academic_year',$academic_year);
        }
        $final_data=$data->get();
        return $final_data;
     
    }
    public static function getRoomNo($room_no=''){

        $data=TimeTable::where('status',1);
        if(!empty($room_no)){
            $data->where('room_id',$room_no);
        }
        $final_data=$data->get();
        return $final_data;
     
    }

    public static function getDataForAttendance($academic_year='',$college_id='',$faculty_id='',$dayOfWeek='')
    {
        $data= TimeTable::select('subject_id','lecture_type_id','section','group','semester')
                        ->where('academic_year',$academic_year)
                        ->where('college_id',$college_id)
                        ->where('faculty_id',$faculty_id);
            if($dayOfWeek){

                $data->where('day',$dayOfWeek);
            }
                        
            $final_data=$data->where('status',1)
                ->groupBy('subject_id')
                ->groupBy('lecture_type_id')
                ->groupBy('semester')
                ->groupBy('section')
                ->groupBy('group')
                ->get();
        return $final_data;
    }

    public static function getDataForMonthlyAttendance($academic_year='',$college_id='',$faculty_id='', $session_duration_id='') {
        // dd($academic_year,$college_id,$faculty_id,$session_duration_id);
        //this function is also used in assignment report to get subjects particular to faculty
        $data= TimeTable::select('subject_id','lecture_type_id','section','group','semester')
                        ->where('academic_year',$academic_year)
                        ->where('college_id',$college_id)
                        ->where('session_duration', $session_duration_id)
                        ->where('status',1);
        if($faculty_id){
           $data->where('faculty_id',$faculty_id);
           // dd('hi');
        }
        // dd($data->get());
        $final_data = $data->groupBy('subject_id')
                            ->groupBy('lecture_type_id')
                            ->groupBy('semester')
                            ->groupBy('section')
                            ->groupBy('group')
                            ->get();
        // dd($final_data);

        return $final_data;
    }
    public static function getDataForMonthlyAttendanceWithoutLectureType($academic_year='',$college_id='',$faculty_id='', $session_duration_id='') {
        // dd($academic_year,$college_id,$faculty_id);
        //this function is also used in assignment report to get subjects particular to faculty
        $lecture_data= TimeTable::select('subject_id','section','group','semester')
                        ->where('academic_year',$academic_year)
                        ->where('college_id',$college_id)
                        ->where('session_duration', $session_duration_id)
                        ->where('status',1);
        if($faculty_id){
           $lecture_data->where('faculty_id',$faculty_id);
        }
        $final_data = $lecture_data->groupBy('subject_id')
                            ->groupBy('semester')
                            ->groupBy('section')
                            ->groupBy('group')
                            ->get();

        return $final_data;
    }

    public static function getFacultyPeriodsData($academic_year='',$college_id='',$faculty_id='',$dayOfWeek='',$subject_id='',$lecture_type_id=''){
        return TimeTable::where('academic_year',$academic_year)
                        ->where('college_id',$college_id)
                        ->where('faculty_id',$faculty_id)
                        ->where('subject_id',$subject_id)
                        ->where('lecture_type_id',$lecture_type_id)
                        ->where('day',$dayOfWeek)
                        ->where('status',1)
                        ->get();
    }
     public static function getRoomWiseTimeTable($college_id='',$academic_year='',$room='',$semester='',$session_id='',$acc_year=''){
        $data=TimeTable::where('status',1);
        if(!empty($college_id)){
            $data->where('college_id',$college_id);
        }
        if(!empty($acc_year)){
            $data->where('academic_year',$acc_year);
        }
        if(!empty($session_id)){
            $data->where('session_duration',$session_id);
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


    public static function getRoomWiseTimeTable2($college_id,$academic_year,$room,$session_id){
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
            $data->where('session_duration',$session_id);
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

public static function getAttendanceTimeData($academic_year='',$college_id='',$faculty_id='',$subject_id='',$lecture_type_id='',$semester='',$section='',$group='',$dayOfWeek=''){
        $data=TimeTable::where('status',1);
        if(!empty($academic_year)){
            $data->where('academic_year',$academic_year);
        }
        if(!empty($college_id)){
            $data->where('college_id',$college_id);
        }
        if(!empty($faculty_id)){
            $data->where('faculty_id',$faculty_id);
        }
        if(!empty($subject_id)){
            $data->where('subject_id',$subject_id);
        }
        if(!empty($lecture_type_id)){
            $data->where('lecture_type_id',$lecture_type_id);
        }
        if(!empty($semester)){
            $data->where('semester',$semester);
        }
        if(!empty($section)){
            $data->where('section',$section);
        }
        if(!empty($group)){
            $data->where('group',$group);
        }
        if(!empty($dayOfWeek)){
            $data->where('day',$dayOfWeek);
        }
        $final_data=$data->pluck('timing');
        // dd($college_id,$academic_year,$semester,$section,$subject_id,$final_data);
        return $final_data;


    }


    public static function getFacultyHavingPeriodOnbParticularDay($academic_year='',$college_id='',$dayOfWeek='')
    {
        $data=TimeTable::where('status',1);
        if(!empty($academic_year)){
            $data->where('academic_year',$academic_year);
        }
        if(!empty($college_id)){
            $data->where('college_id',$college_id);
        }
        if(!empty($dayOfWeek)){
            $data->where('day',$dayOfWeek);
        }
        $final_data=$data->distinct('faculty_id')->pluck('faculty_id');
        return($final_data);
    }


    public static function getFacultyTimeTable2($college_id, $academic_year, $faculty_id, $session_duration) {
        return TimeTable::where('college_id',$college_id)
                        ->where('academic_year',$academic_year)
                        ->where('faculty_id',$faculty_id)
                        ->where('session_duration',$session_duration)
                        ->where('status',1)
                        ->get();
    }

    public static function getSemesterwiseFacultyTimeTable($college_id, $academic_year, $faculty_id, $session_duration,$semester) {
        return TimeTable::where('college_id',$college_id)
                        ->where('academic_year',$academic_year)
                        ->where('faculty_id',$faculty_id)
                        ->where('semester',$semester)
                        ->where('session_duration',$session_duration)
                        ->where('status',1)
                        ->get();
    }

    public static function getDataFromDay($college_id, $academic_year, $session_duration, $day, $start_time) {
        return TimeTable::where('college_id',$college_id)
                        ->where('academic_year',$academic_year)
                        ->where('session_duration',$session_duration)
                        ->where('day',$day)
                        ->where('timing',$start_time)
                        ->where('status',1)
                        ->get();
    }
    public static function getDataFromDayandRoom($college_id, $academic_year, $session_id, $day, $hour_filter,$room_filter) {
        $data = TimeTable::where('college_id',$college_id)
                        ->where('academic_year',$academic_year)
                        ->where('session_duration',$session_id)
                        ->where('day',$day);
        if(!empty($hour_filter)){
            $data->where('timing',$hour_filter);
        }
            if(!empty($room_filter)){
                $data->where('room_id',$room_filter);
            }
                        $final_data= $data->where('status',1)
                                        ->get();
        return $final_data;
    }
    public static function getDataForMonthlyAttendance2($academic_year,$college_id,$faculty_id,$session_duration) {
        // dd($academic_year,$college_id,$faculty_id);
        //this function is also used in assignment report to get subjects particular to faculty
        $data = TimeTable::where('college_id', $college_id)
                        ->where('academic_year', $academic_year)
                        ->where('session_duration', $session_duration)
                        ->where('status', 1)
                        ->get();

        // dd($data,$academic_year,$college_id,$faculty_id,$session_duration);
        if($faculty_id){
           $data->where('faculty_id',$faculty_id);
        }
        $final_data = $data->groupBy('subject_id')
                            ->groupBy('lecture_type_id')
                            ->groupBy('semester')
                            ->groupBy('section')
                            ->groupBy('group')
                            ->get();

        return $final_data;
    }

    public static function getActiveFaculty($college_id) {
        return TimeTable::where('college_id', $college_id)
                            ->where('status',1)
                            ->pluck('faculty_id')
                            ->toArray();
    }


    public static function attendance_uploaded_on_portal($college_id,$academic_year,$session_duration_id,$faculty,$lecture_type){
        // dd($faculty);
        $data=TimeTable::selectRaw('faculty_id, subject_id, lecture_type_id, section, count(*) as counter')
                        ->where('college_id',$college_id)
                        ->where('academic_year',$academic_year)
                        ->where('session_duration',$session_duration_id)
                        ->where('status',1);
        if(!empty($faculty)){
         $data->where('faculty_id',$faculty);   
        }
        if(!empty($lecture_type)){
         $data->where('lecture_type_id',$lecture_type);   
        }
        $final_data=$data->groupBy('faculty_id')
                        ->groupBy('subject_id')
                        ->groupBy('lecture_type_id')
                        ->groupBy('section')
                        ->orderBy('faculty_id')
                        ->get();


        return $final_data;
    }

    public static function attendance_uploaded_on_portal_all_lecture_type($college_id,$academic_year,$session_duration_id,$faculty){
        // dd($faculty);
        $data=TimeTable::selectRaw('faculty_id, subject_id, lecture_type_id, section, count(*) as counter')
                        ->where('college_id',$college_id)
                        ->where('academic_year',$academic_year)
                        ->where('session_duration',$session_duration_id)
                        ->where('status',1);
        if(!empty($faculty)){
         $data->where('faculty_id',$faculty);   
        }
        $final_data=$data->groupBy('faculty_id')
                        ->groupBy('subject_id')
                        ->groupBy('lecture_type_id')
                        ->groupBy('section')
                        ->orderBy('faculty_id')
                        ->get();


        return $final_data;
    }
                                            
}
