<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;

class UserApprovedSubjects extends Model
  {
        protected $table = "user_approved_subjects";
        protected $guarded = [];
        public $timestamps=false;

    public static function getAllRecords() 
        {
            return UserApprovedSubjects::get();

        }
    public static function getUserRecords($user_profile_id, $user_college, $user_course, $user_semester) {
        return UserApprovedSubjects::where('user_profile_id', $user_profile_id)
                                ->where('college_id', $user_college)
                                ->where('course_id', $user_course)
                                ->where('semester', $user_semester)
                                ->where('status', 1)
                                ->orderBy('subject_type_id', 'asc')
                                ->orderBy('preference', 'asc')
                                ->get();
    }
    public static function getUserRecordsExceptDSC($user_profile_id, $user_college, $user_course, $user_semester) {
        return UserApprovedSubjects::where('user_profile_id', $user_profile_id)
                                ->where('college_id', $user_college)
                                ->where('course_id', $user_course)
                                ->where('semester', $user_semester)
                                ->where('subject_type_id','!=', 6)
                                ->where('status', 1)
                                ->orderBy('subject_type_id', 'asc')
                                ->orderBy('preference', 'asc')
                                ->get();
    }
    
    public static function getStudentsFromStudentAcademicSubjectTypeSemesterAndCourse($college_id='', $subject_type_id='', $semester='',$course_id='',$academic_year='',$subject = '',$section ='') 
    {
        // dd($college_id);
        // dd($college_id, $subject_type_id,$subject,$academic_year, $semester,$course_id, $section);
        if(!empty($college_id)) {
            $data = UserApprovedSubjects::where('college_id', $college_id);
            if(!empty($subject_type_id)) {
                $data->where('subject_type_id', $subject_type_id);
            }
            if(!empty($academic_year )) {
                $data->where('academic_year', $academic_year );
            }
            if(!empty($course_id )) {
                $data->where('course_id', $course_id );
            }
            if(!empty($subject )) {
                $data->where('subject_id', $subject );
            }
            if(!empty($semester)) {
                $data->where('semester', $semester);
            }
            if(!empty($section)) {
                if($section == 1){
                    $data->whereNull('section');
                }
                else{
                    $data->whereNotNull('section');

                }
            }
            // dd($data);
            $final_data = $data->where('status', 1)
                                ->where('approval_status', 1)
                                ->pluck('user_profile_id')
                                ->toArray(); 
            // dd($final_data);
            return $final_data;
        }  
        
    }
    
    public static function getAllApprovedData($college_id,$semesters,$subject_type,$subject){
        $data =UserApprovedSubjects::where('status',1) 
                                  ->where('college_id',$college_id)  
                                  ->where('semester',$semesters)
                                  ->where('approval_status',1);
                                  
        if(!empty($subject_type)){
            $data->where('subject_type_id',$subject_type);
        }
        if(!empty($subject)){
            $data->where('subject_id',$subject);
        }       
        $final_data=$data->get();  
        return $final_data;                   
    }
    public static function getStudentsFromStudentAcademicSubjectTypeSemesterAndCourseSectionNotNull($college_id='', $subject_type_id='', $semester='',$course_id='',$academic_year='',$subject = '',$section='') {
        // dd($college_id);
        // dd($college_id, $subject_type_id,$subject,$academic_year, $semester,$course_id);
        if(!empty($college_id)) {
            $data = UserApprovedSubjects::where('college_id', $college_id);
            if(!empty($subject_type_id)) {
                $data->where('subject_type_id', $subject_type_id);
            }
            if(!empty($academic_year )) {
                $data->where('academic_year', $academic_year );
            }
            if(!empty($subject )) {
                $data->where('subject_id', $subject );
            }
            if(!empty($semester)) {
                $data->where('semester', $semester);
            }
            if(!empty($course_id)) {
                $data->where('course_id', $course_id);
            }
            if(!empty($section)) {
                $data->where('section', $section);
            }
            // dd($data);
            $final_data = $data->where('status', 1)
                                ->where('approval_status', 1)
                                ->distinct('user_profile_id')
                                ->pluck('user_profile_id')
                                ->toArray(); 
            // dd($final_data);
            return $final_data;
        }
    }

    public static function getDataForAttendance($academic_year,$college_id,$semesters,$subject_type,$subject){

        $data =UserApprovedSubjects::where('academic_year',$academic_year)
                                    ->where('college_id',$college_id)  
                                    ->where('semester',$semesters)
                                    ->where('status',1) 
                                    ->where('approval_status',1);
                                  
        if(!empty($subject_type)){
            $data->where('subject_type_id',$subject_type);
        }
        if(!empty($subject)){
            $data->where('subject_id',$subject);
        }       
        $final_data=$data->get();  
        return $final_data;   
    }

     public static function getUsersForSectionAndGroup($college_id='', $subject_type_id='', $semester='',$course_id='',$academic_year='',$subject = '',$section=''){
        // dd($college_id, $subject_type_id, $semester,$course_id,$academic_year,$subject = '',$sections);
        if(!empty($college_id)) {
            $data = UserApprovedSubjects::where('college_id', $college_id);
            if(!empty($subject_type_id)) {
                $data->where('subject_type_id', $subject_type_id);
            }
            if(!empty($academic_year )) {
                $data->where('academic_year', $academic_year );
            }
            if(!empty($subject )) {
                $data->where('subject_id', $subject );
            }
            if(!empty($semester)) {
                $data->where('semester', $semester);
            }
            if(!empty($course_id)) {
                $data->where('course_id', $course_id);
            }
           if(!empty($section)) {
                if($section == 1){
                    $data->whereNull('section');
                }
                else{
                    $data->whereNotNull('section');
                }
            }
            // dd($data);
            $final_data = $data->where('status', 1)
                                ->where('approval_status', 1)
                                ->get(); 
            // dd($final_data);
            return $final_data;
        }

    }

    public static function getApprovedSubjects($academic_year,$user_profile_id,$semesters) {
        return UserApprovedSubjects::where('academic_year',$academic_year)
                                    ->where('user_profile_id',$user_profile_id)
                                    ->where('approval_status',1)
                                    ->where('status',1)
                                    ->where('semester',$semesters)
                                    ->get();
    }

    public static function getStudentRecord($college_id,$user_profile_id,$course_id,$semester,$subject_id,$subject_type_id){
        return UserApprovedSubjects::where('status', 1)
        ->where('college_id',$college_id)
        ->where('subject_id', $subject_id)
        ->where('course_id', $course_id)
        ->where('semester', $semester)
        ->where('subject_type_id', $subject_type_id)
        ->where('user_profile_id',$user_profile_id)
        ->where('approval_status','!=',2)
        ->pluck('id');
    }

    public static function getStudentApproved($college_id='',$academic_year='',$session_duration='',$course='',$subject_type='',$subject='',$semester='',$user_profile_id=''){
                         // join('user_profile', 'user_subject_mapping.user_profile_id', 'user_profile.id')
                        //  dd($college_id,$course,$subject_type,$subject,$semester,$user_profile_id);
         $data=UserApprovedSubjects::join('user_profile', function($join){
                                    $join->on('user_approved_subjects.user_profile_id', '=', 'user_profile.id');
                                    $join->on('user_approved_subjects.semester', '=', 'user_profile.semester');
                                })
                                ->select('user_approved_subjects.*', 'user_profile.name as name', 'user_profile.contact_no as contact_no', 'user_profile.email as email', 'user_profile.enrolment_no as enrolment_no')
                                ->whereNotIn('user_approved_subjects.status',[5,9])
                                ->where('user_approved_subjects.approval_status',1);
        if(!empty($college_id)) {
            $data->where('user_approved_subjects.college_id', $college_id);
        }
        if(!empty($academic_year)) {
            $data->where('user_approved_subjects.academic_year', $academic_year);
        }
        if(!empty($session_duration)) {
            $data->where('user_approved_subjects.session_duration', $session_duration);
        }    
        // if(!empty($academic_year)) {
        //     $data->where('user_approved_subjects.academic_year', $academic_year);
        // }
        // if(!empty($session_duration)) {
        //     $data->where('user_approved_subjects.session_duration', $session_duration);
        // }
        if(!empty($course)) {
            $data->where('user_approved_subjects.course_id', $course);
        }
        if(!empty($subject_type)) {
            $data->where('user_approved_subjects.subject_type_id', $subject_type);
        }if(!empty($subject)) {
            $data->where('user_approved_subjects.subject_id', $subject);
        }
        if(!empty($semester)) {
            $data->where('user_approved_subjects.semester', $semester);
        }
        if(!empty($user_profile_id)) {
            $data->where('user_approved_subjects.user_profile_id', $user_profile_id);
        }

        $final_data = $data->orderBy('user_approved_subjects.course_id', 'asc')
                            ->orderBy('user_profile.name', 'asc')
                            ->get();
                            // dd($final_data);
        return $final_data;
    }

    public static function getStudentApprovedWithFilter($college_id='',$course='',$subject_type='',$subject='',$semester='',$user_profile_id='',$academic_year='',$filter=''){
        // join('user_profile', 'user_subject_mapping.user_profile_id', 'user_profile.id')
        $data=UserApprovedSubjects::join('user_profile', function($join){
                        $join->on('user_approved_subjects.user_profile_id', '=', 'user_profile.id');
                        $join->on('user_approved_subjects.semester', '=', 'user_profile.semester');
                    })
                    ->select('user_approved_subjects.*', 'user_profile.name as name', 'user_profile.contact_no as contact_no', 'user_profile.email as email', 'user_profile.enrolment_number_org as enrolment_no', 'user_profile.roll_no as college_roll_no', 'user_profile.csas_form_number as csas_form_number', 'user_profile.examination_roll_no as examination_roll_no')
                    ->whereNotIn('user_approved_subjects.status',[5,9])
                    ->where('user_approved_subjects.approval_status',1);
        if(!empty($college_id)) {
        $data->where('user_approved_subjects.college_id', $college_id);
        }
        if(!empty($academic_year)) {
        $data->where('user_approved_subjects.academic_year', $academic_year);
        }    
        if(!empty($course)) {
        $data->where('user_approved_subjects.course_id', $course);
        }
        if(!empty($subject_type)) {
        $data->where('user_approved_subjects.subject_type_id', $subject_type);
        }if(!empty($subject)) {
        $data->where('user_approved_subjects.subject_id', $subject);
        }
        if(!empty($semester)) {
        $data->where('user_approved_subjects.semester', $semester);
        }
        
        if(!empty($user_profile_id)) {
        $data->where('user_approved_subjects.user_profile_id', $user_profile_id);
        }
        if(!empty($filter)?($filter==1):0) {
            $data->orderBy('user_approved_subjects.approved_at','desc');
            }
        else{
            $data->orderBy('user_approved_subjects.approved_at','asc');
        }

        $final_data = $data->orderBy('user_approved_subjects.course_id', 'asc')
                ->orderBy('user_profile.name', 'asc')
                ->get();
        return $final_data;
        }

    public static function getUsersFromSecionAndUsersid($user_profile_id='',$semester=''){
        return UserApprovedSubjects::whereIn('user_profile_id', $user_profile_id)
                                ->where('semester',$semester)
                                ->where('status',1)
                                ->where('approval_status',1)
                                ->pluck('section','subject_id');
    }

    public static function getDataFromUserAndSemester($college_id, $user_profile_id, $semester) {
        return UserApprovedSubjects::where('college_id',$college_id)
                                    // ->where('academic_year',$academic_year)
                                    ->where('user_profile_id', $user_profile_id)
                                    ->where('semester',$semester)
                                    ->where('status',1)
                                    ->where('approval_status',1)
                                    ->get();   
    }


    public static function getUsersFromSectionAndUsersid($user_profile_id='',$semester=''){
        return UserApprovedSubjects::where('user_profile_id', $user_profile_id)
                                ->where('semester',$semester)
                                ->where('status',1)
                                ->where('approval_status',1)
                                ->pluck('section','group_cus');
    }
    public static function getApprovedSubjectsid($academic_year,$user_profile_id,$semesters) {
        return UserApprovedSubjects::where('academic_year',$academic_year)
                                    ->where('user_profile_id',$user_profile_id)
                                    ->where('approval_status',1)
                                    ->where('status',1)
                                    ->where('semester',$semesters)
                                    ->pluck('subject_id');
    }
     public static function getSectionandGroup($academic_year,$user_profile_id,$semesters) {
        
        return UserApprovedSubjects::where('academic_year',$academic_year)
                                    ->where('user_profile_id',$user_profile_id)
                                    ->where('approval_status',1)
                                    ->where('status',1)
                                    ->where('semester',$semesters)
                                    ->get();
    }
    public static function getSectionId($academic_year,$user_profile_id,$semesters) {
        
        return UserApprovedSubjects::where('academic_year',$academic_year)
                                    ->where('user_profile_id',$user_profile_id)
                                    ->where('approval_status',1)
                                    ->where('status',1)
                                    ->where('semester',$semesters)
                                    ->pluck('section');
    }
// added by rahul
    
    public static function getDataByUserProfileaandCollege_id($college_id,$academic_year,$user_profile_id) {
        

        return UserApprovedSubjects::where('academic_year',$academic_year)
                                    ->where('user_profile_id',$user_profile_id)
                                    ->where('status',1)
                                    ->get();
    }

//added by rahul
    public static function getUserProfileIdFromAcdemicYearSubjectSectionSemester($academic_year='',$college_id='',$semester='',$section='',$subject_id='') {
        $data = UserApprovedSubjects::where('approval_status',1)
                                        ->where('status',1);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }    
        if(!empty($section)) {
            $data->where('section', $section);
        }
        if(!empty($academic_year)) {
            $data->where('academic_year', $academic_year);
        }if(!empty($subject_id)) {
            $data->where('subject_id', $subject_id);
        }
        if(!empty($semester)) {
            $data->where('semester', $semester);
        }
        $final_data = $data->pluck('user_profile_id');
        return $final_data;
    }
        

    public static function get_assessement_assigned_students($college_id='',$semester='',$section='',$academic_year='',$subject_id='') {

        // dd($college_id,$semester,$section,$academic_year,$subject_id);

        // $temp = UserApprovedSubjects::join('user_profile', 'user_approved_subjects.user_profile_id', 'user_profile.id')
        //                     ->select('user_profile.name', 'user_profile.enrolment_no')
        //                     ->where('user_approved_subjects.college_id',$college_id)        
        //                     ->where('user_approved_subjects.section',$section)        
        //                     ->where('user_approved_subjects.academic_year',$academic_year)        
        //                     ->where('user_approved_subjects.subject_id',$subject_id)        
        //                     ->where('user_approved_subjects.semester',$semester)    
        //                     ->orderBy('user_profile.name', 'asc')
        //                     ->get();

        // dd($temp);


        $data= UserApprovedSubjects::where('status',1);

        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }    
        if(!empty($section)) {
            $data->where('section', $section);
        }
        if(!empty($academic_year)) {
            $data->where('academic_year', $academic_year);

        }
        if(!empty($subject_id)) {
            $data->where('subject_id', $subject_id);
        }
        if(!empty($semester)) {
            $data->where('semester', $semester);
        }
       
        $final_data=$data->get();   
        // dd($final_data);     
        return $final_data;

    }
   


        public static function student_data($college_id,$semester,$section,$academic_year,$subject_id){


            $data = UserApprovedSubjects::
            where('status', 1);
            
        
            if(!empty($college_id)) {
                $data->where('college_id', $college_id);
            }    
            if(!empty($section)) {
                $data->where('section', $section);
            }
            if(!empty($academic_year)) {
                $data->where('academic_year', $academic_year);

            }
            if(!empty($subject_id)) {
                $data->where('subject_id', $subject_id);
            }
            if(!empty($semester)) {
                $data->where('semester', $semester);
            }
            
            
           
            $final_data=$data->get();   
            // dd($final_data);     
            return $final_data;

        }


        public static function getSubjectApprovedData($user_profile_id,$college_id='')
        
        {
            // dd($action);
            $data = UserApprovedSubjects::
            where('status', 1);
            
            if(!empty($user_profile_id)) {
                $data->where('user_profile_id', $user_profile_id);
            }
            
            if(!empty($college_id)) {
                $data->where('college_id', $college_id);
            }    
            
                $final_data=$data->get();
                        
            // dd($final_data);     
            return $final_data;

        }
        public static function DeleteSubjectApprovedData($user_profile_id,$college_id='')
        
        {
            // dd($action);
            $data = UserApprovedSubjects::
            where('status', 1);
            
            if(!empty($user_profile_id)) {
                $data->where('user_profile_id', $user_profile_id);
            }
            
            if(!empty($college_id)) {
                $data->where('college_id', $college_id);
            }    
            
                $final_data=$data->delete();
                        
            // dd($final_data);     
            return $final_data;

        }
        public static function StatusChangeSubjectApprovedData($user_profile_id,$college_id='')
        
        {
            // dd($action);
            
            $data = UserApprovedSubjects::where('status', 1);
            
            if(!empty($user_profile_id)) {
                $data->where('user_profile_id', $user_profile_id);
            }
            
            if(!empty($college_id)) {
                $data->where('college_id', $college_id);
            }    
            
                $final_data=$data->first();
                // dd($final_data);
            if(!empty($final_data)) {

                $final_data=$data->update(['status'=>9]);

            }
            else {
                $final_data = 1;
            }
            // dd($final_data);     
            return $final_data;

        }

        
    public static function getPreviouslyApprovedSubjects($college_id, $user_profile_id, $semester) {
        $data = UserApprovedSubjects::where('college_id', $college_id)
                                    ->where('user_profile_id', $user_profile_id)
                                    ->where('semester', '!=', $semester)
                                    ->where('status', 1)
                                    ->where('approval_status', 1)
                                    ->pluck('subject_id')
                                    ->toArray();
        return $data;
    }
        
    public static function getPreviouslySelectedSubject($college_id, $user_profile_id,$semester,$course_id='') {
        // dd($college_id, $user_profile_id,$semester,$course_id='');
        $data = UserApprovedSubjects::where('college_id', $college_id)
                                    ->where('user_profile_id', $user_profile_id)
                                    ->where('semester', $semester);
        if(!empty($course_id)){

            $data->where('course_id',$course_id);
        }
                                   $final_data=$data->where('status', 1)
                                    ->where('approval_status', 1)
                                    ->pluck('subject_id')
                                    ->toArray();
        return $final_data;
    }

    public static function getAllDataPreviouslySelectedSubject($college_id, $user_profile_id,$semester,$course_id='') {
        // dd($college_id, $user_profile_id,$semester,$course_id='');
        $data = UserApprovedSubjects::where('college_id', $college_id)
                                    ->where('user_profile_id', $user_profile_id);
        if(!empty($course_id)){

            $data->where('course_id',$course_id);
        }
                                   $final_data=$data->where('status', 1)
                                    ->where('approval_status', 1)
                                    ->get();
        return $final_data;
    
    }
    public static function getStudentDataAssign($academic_year,$college_id,$course_id,$subject_type,$semester,$user_profile_ids) {
        // $academic_year=2024;
        // dd($academic_year,$college_id,$course_id,$subject_type,$semester,$user_profile_ids);
            return UserApprovedSubjects::where('status', 1)
                ->where('approval_status', 1)
                ->whereIn('user_profile_id',$user_profile_ids)
                ->where('academic_year', $academic_year)
                ->where('course_id', $course_id)
                ->where('subject_type_id', $subject_type)
                ->where('semester', $semester)
                ->get();
        
    }

    public static function updateStatusForStudent($college_id,$academic_year,$course_id,$semester,$subject_type,$subject_id,$user_profile_id_arr){
        // $academic_year=2024;
            $user_id=Auth::user()->id;
            return UserApprovedSubjects::where('status', 1)
                                        ->where('college_id',$college_id)
                                        ->where('academic_year', $academic_year)
                                        ->whereIn('user_profile_id',$user_profile_id_arr)
                                        ->where('course_id', $course_id)
                                        ->where('subject_id', $subject_id)
                                        ->where('subject_type_id', $subject_type)
                                        ->where('semester', $semester)
                                        ->update(['status'=>9,'updated_at'=>date('Y-m-d H:i:s'),'updated_by'=>$user_id]);
                                        // ->get();

    }



    public static function removePreviousMapping($college_id,$academic_year,$course_id,$semester,$subject_type,$subject_arr,$user_profile_id_arr, $update_arr){
        // $academic_year=2024;

            return UserApprovedSubjects::where('status', 1)
                                        ->where('college_id',$college_id)
                                        ->where('academic_year', $academic_year)
                                        ->where('course_id', $course_id)
                                        ->where('semester', $semester)
                                        ->where('subject_type_id', $subject_type)
                                        // ->whereIn('subject_id', $subject_arr)
                                        ->whereIn('user_profile_id',$user_profile_id_arr)
                                        ->update($update_arr);
                                        // ->get();

    }
    
    
}
