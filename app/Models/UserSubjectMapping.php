<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserSubjectMapping extends Model
{
   protected $table = "user_subject_mapping";
    protected $guarded = [];
    public $timestamps = false;
    


    public static function getAllRecords($college_ids='',$course='',$semester='',$subject_type=''){
        $data = UserSubjectMapping::where('status',1);
        if(!empty($college_ids)){
            
            $data->whereIn('college_id',$college_ids);
        }
        if(!empty($course)){
            $data->where('course_id',$course);
        }
        if(!empty($semester)){
            $data->where('semester',$semester);
        }
        if(!empty($subject_type)){
            $data->where('subject_type_id',$subject_type);
        }
        // if(!empty($Approve)){
        //     $data->where('Approve',$Approve);
        // }
        $final_data =$data->get();
        return $final_data;
    }
     public static function getAllRecordsWithStatus($college_ids='',$course='',$semester='',$subject_type='',$approval_status=''){
        $data = UserSubjectMapping::where('status','!=',9);
        if(!empty($college_ids)){
            
            $data->whereIn('college_id',$college_ids);
        }
        if(!empty($course)){
            $data->where('course_id',$course);
        }
        if(!empty($semester)){
            $data->where('semester',$semester);
        }
        if(!empty($subject_type)){
            $data->where('subject_type_id',$subject_type);
        }
        if(!empty($approval_status)){
            $data->where('approval_status',$approval_status);
        }
        $final_data =$data->get();
        return $final_data;
    }
    public static function getStudentRecordsWithApproval($college_id = '', $course = '', $subject_type = '', $subject = '', $semester = '', $approval_status = '')
    {
        $data = UserSubjectMapping::join('user_profile', function ($join) {
            $join->on('user_subject_mapping.user_profile_id', '=', 'user_profile.id');
            $join->on('user_subject_mapping.semester', '=', 'user_profile.semester');
        })
            ->select('user_subject_mapping.*', 'user_profile.name as name')
            ->where('user_subject_mapping.status', '!=', 9);

        if (!empty($college_id)) {
            $data->where('user_subject_mapping.college_id', $college_id);
        }
        if (!empty($course)) {
            $data->where('user_subject_mapping.course_id', $course);
        }
        if (!empty($subject_type)) {
            $data->where('user_subject_mapping.subject_type_id', $subject_type);
        }
        if (!empty($subject)) {
            $data->where('user_subject_mapping.subject_id', $subject);
        }

        if (!empty($approval_status)) {
            if ($approval_status == 1) {
                $data->where('user_subject_mapping.approval_status', 1);
            } elseif ($approval_status == 2) {
                $data->where('user_subject_mapping.approval_status', 2);
            }
            else {
                // Include this block to filter for null approval_status
                $data->whereNull('user_subject_mapping.approval_status');
            }
        } 


        if (!empty($semester)) {
            if($semester>0){
            $data->where('user_subject_mapping.semester', $semester);
            }
            
            else{
                dd('Enter the Correct Semester');

            }
        }

        $final_data = $data->orderBy('user_subject_mapping.course_id', 'asc')
            ->orderBy('user_profile.name', 'asc')
            ->get();
        $final_data_arr = [];
            foreach ($final_data as $key => $value) {
                $a_status = $value->approval_status;
                // dd($key,$value,$a_status);
                if($a_status == 1){
                    $final_data_arr[] = $value;
                }
                elseif($a_status == 2){
                   $final_data_arr[] = $value;
                }
                else{
                    $final_data_arr[] = $value;
                }
            }

        // dd($final_data_arr);
        return $final_data_arr;
    }

    public static function getDataFromId($college_id,$course_id,$semester)
    {
        return UserSubjectMapping::where('status','!=',9)->where('college_id',$college_id,)
        ->where('course_id',$course_id,)->where('semester',$semester)
        ->get();

    }

    public static function getUniqueRecords() {
        return UserSubjectMapping::select('college_id', 'course_id', 'semester')
                                    ->where('status','!=',9)
                                    ->groupBy('college_id', 'course_id', 'semester')
                                    ->get();
    }

    public static function getUserRecords($user_profile_id, $user_college, $user_course, $user_semester) {
        return UserSubjectMapping::where('user_profile_id', $user_profile_id)
                                ->where('college_id', $user_college)
                                ->where('course_id', $user_course)
                                ->where('semester', $user_semester)
                                ->where('status', 1)
                                ->orderBy('subject_type_id', 'asc')
                                ->orderBy('preference', 'asc')
                                ->get();
    }
    public static function getUserRecordsExceptDSC($user_profile_id, $user_college, $user_course, $user_semester) {
        return UserSubjectMapping::where('user_profile_id', $user_profile_id)
                                ->where('college_id', $user_college)
                                ->where('course_id', $user_course)
                                ->where('semester', $user_semester)
                                ->where('subject_type_id','!=', 6)
                                ->where('status', 1)
                                ->orderBy('subject_type_id', 'asc')
                                ->orderBy('preference', 'asc')
                                ->get();
    }
  
   public static function getStudentRecords($academic_year,$college_id='',$course='',$subject_type='',$subject='',$semester='',$priority=''){
                         // join('user_profile', 'user_subject_mapping.user_profile_id', 'user_profile.id')
                        //  dd($priority);
         $data=UserSubjectMapping::join('user_profile', function($join){
                                    $join->on('user_subject_mapping.user_profile_id', '=', 'user_profile.id');
                                    $join->on('user_subject_mapping.semester', '=', 'user_profile.semester');
                                })
                                ->select('user_subject_mapping.*', 'user_profile.name as name', 'user_profile.contact_no as contact_no', 'user_profile.email as email')
                                ->where('user_subject_mapping.status','!=',9);
        if(!empty($academic_year)) {
            $data->where('user_subject_mapping.academic_year', $academic_year);
        }
        if(!empty($college_id)) {
            $data->where('user_subject_mapping.college_id', $college_id);
        }    
        if(!empty($course)) {
            $data->where('user_subject_mapping.course_id', $course);
        }
        if(!empty($subject_type)) {
            $data->where('user_subject_mapping.subject_type_id', $subject_type);
        }if(!empty($subject)) {
            $data->where('user_subject_mapping.subject_id', $subject);
        }
        if(!empty($semester)) {
            $data->where('user_subject_mapping.semester', $semester);
        }
        if(!empty($priority)) {
            $data->where('user_subject_mapping.preference', $priority);
        }
        

        $final_data = $data->orderBy('user_subject_mapping.course_id', 'asc')
                            ->orderBy('user_profile.name', 'asc')->orderBy('subject_type_id')->orderBy('preference')
                            ->get();
        return $final_data;
    }
    public static function getStudentApproved($college_id='',$course='',$subject_type='',$subject='',$semester=''){
                         // join('user_profile', 'user_subject_mapping.user_profile_id', 'user_profile.id')
         $data=UserSubjectMapping::join('user_profile', function($join){
                                    $join->on('user_subject_mapping.user_profile_id', '=', 'user_profile.id');
                                    $join->on('user_subject_mapping.semester', '=', 'user_profile.semester');
                                })
                                ->select('user_subject_mapping.*', 'user_profile.name as name', 'user_profile.contact_no as contact_no', 'user_profile.email as email', 'user_profile.enrolment_no as enrolment_no')
                                ->where('user_subject_mapping.status','!=',9)
                                ->where('user_subject_mapping.approval_status',1);
        if(!empty($college_id)) {
            $data->where('user_subject_mapping.college_id', $college_id);
        }    
        if(!empty($course)) {
            $data->where('user_subject_mapping.course_id', $course);
        }
        if(!empty($subject_type)) {
            $data->where('user_subject_mapping.subject_type_id', $subject_type);
        }if(!empty($subject)) {
            $data->where('user_subject_mapping.subject_id', $subject);
        }
        if(!empty($semester)) {
            $data->where('user_subject_mapping.semester', $semester);
        }

        $final_data = $data->orderBy('user_subject_mapping.course_id', 'asc')
                            ->orderBy('user_profile.name', 'asc')
                            ->get();
        return $final_data;
    }
    public static function get_mapped_users($college_id = '' ) {
            if(!empty($college_id)) {
                return UserSubjectMapping::where('status', 1)
                                         ->where('college_id', $college_id)
                                         ->distinct('user_profile_id')
                                         ->pluck('user_profile_id')
                                         ->toArray();
            }else{
                 return UserSubjectMapping::where('status', 1)
                                         ->distinct('user_profile_id')
                                         ->pluck('user_profile_id')
                                         ->toArray();
            }
        
    }

    public static function getUserArrFromSubject($college_id, $subject_id) {
        // dd($college_id);
        if(!empty($college_id)) {
            return UserSubjectMapping::where('college_id', $college_id)
                                    ->where('subject_id', $subject_id)
                                    ->where('status', 1)
                                    ->pluck('user_profile_id')->toArray();
        }
        else {
            dd('Please Select College Id Also or login from faculty');
        }
    }

    public static function getUserArrFromSubjectAndPreference($college_id, $subject_id, $preference) {
        // dd($college_id);

        if(!empty($college_id)) {
            $data = UserSubjectMapping::where('college_id', $college_id);
            if(!empty($subject_id)) {
                $data->where('subject_id', $subject_id);
            }
            if(!empty($preference)) {
                $data->where('preference', $preference);
            }
            $final_data = $data->where('status', 1)
                                ->pluck('user_profile_id')
                                ->toArray(); 
            return $final_data;
        }
        else {
            dd('Please Select College Id Also or login from faculty');
        }
    }

    public static function getSubjects($academic_year,$user_profile_id,$semesters){
        return UserSubjectMapping::where('academic_year',$academic_year)
                                  ->where('user_profile_id',$user_profile_id)
                                  ->where('approval_status',1)
                                  ->where('semester',$semesters)
                                  ->get();
    }

    public static function getAllApprovedData($college_id,$semesters,$subject_type,$subject){
        $data =UserSubjectMapping::where('status',1) 
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
    public static function getUserIdFromCollegeSemesterSubjectSection($college_id='', $subject_id='', $semester='',$section='') {
        // dd($college_id);
        // dd($college_id, $subject_id, $semester,$section);
        if(!empty($college_id)) {
            $data = UserSubjectMapping::where('college_id', $college_id);
            if(!empty($subject_id)) {
                $data->where('subject_id', $subject_id);
            }
            if(!empty($semester)) {
                $data->where('semester', $semester);
            }
            if(!empty($section)) {
                $data->where('section', $section);
            }
            $final_data = $data->where('status', 1)
                                ->where('approval_status', 1)
                                ->pluck('user_profile_id')
                                ->toArray(); 
            return $final_data;
        }
    }
        public static function getUserIdFromCollegeSemesterSubjectSectionGroup($college_id='', $subject_id='', $semester='',$section='',$group='') {
        // dd($college_id);
        // dd($college_id, $subject_id, $semester,$section);
        if(!empty($college_id)) {
            $data = UserSubjectMapping::where('college_id', $college_id);
            if(!empty($subject_id)) {
                $data->where('subject_id', $subject_id);
            }
            if(!empty($semester)) {
                $data->where('semester', $semester);
            }
            if(!empty($section)) {
                $data->where('section', $section);
            }
            if(!empty($group)) {
                $data->where('group_cus', $group);
            }
            $final_data = $data->where('status', 1)
                                ->where('approval_status', 1)
                                ->pluck('user_profile_id')
                                ->toArray(); 
            return $final_data;
        }
       
    }
     public static function getDataFromStudentAcademicSubjectTypeSemesterAndCourse($college_id='', $subject_type_id='', $semester='',$course_id='',$student = '',$academic_years='') {
        // dd($college_id);
        // dd($college_id, $subject_type_id,$student,$academic_years, $semester,$course_id);
        if(!empty($college_id)) {
            $data = UserSubjectMapping::where('college_id', $college_id);
            if(!empty($subject_type_id)) {
                $data->where('subject_type_id', $subject_type_id);
            }
            if(!empty($student)) {
                $data->where('user_profile_id', $student);
            }
            if(!empty($academic_year )) {
                $data->where('academic_year', $academic_years );
            }
            if(!empty($semester)) {
                $data->where('semester', $semester);
            }
            if(!empty($course_id)) {
                $data->where('course_id', $course_id);
            }
            // dd($data);
            $final_data = $data->where('status', 1)
                                ->where('approval_status', 1)
                                ->pluck('subject_id')
                                ->toArray(); 
            // dd($final_data);
            return $final_data;
        }
        
    }
    public static function getStudentsFromStudentAcademicSubjectTypeSemesterAndCourse($college_id='', $subject_type_id='', $semester='',$course_id='',$academic_year='',$subject = '',$section ='') {
        // dd($college_id);
        // dd($college_id, $subject_type_id,$subject,$academic_year, $semester,$course_id);
        if(!empty($college_id)) {
            $data = UserSubjectMapping::where('college_id', $college_id);
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
            if(!empty($section)) {
                if($section == 1){
                    $data->whereNull('section');
                }
                else{
                    $data->whereNotNull('section');
                }
            }
            if(!empty($course_id)) {
                $data->where('course_id', $course_id);
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
    public static function getStudentsFromStudentAcademicSubjectTypeSemesterAndCourseSectionNotNull($college_id='', $subject_type_id='', $semester='',$course_id='',$academic_year='',$subject = '',$section='') {
        // dd($college_id);
        // dd($college_id, $subject_type_id,$subject,$academic_year, $semester,$course_id);
        if(!empty($college_id)) {
            $data = UserSubjectMapping::where('college_id', $college_id);
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
   
    public static function getUsersFromSecionAndUsersid($user_profile_id='',$semester=''){
        return UserSubjectMapping::whereIn('user_profile_id', $user_profile_id)
                                ->where('semester',$semester)
                                ->where('status',1)
                                ->where('approval_status',1)
                                ->pluck('section','subject_id');
    }

    
} 
   

