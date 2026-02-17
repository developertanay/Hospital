<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;
// use DB;

use Illuminate\Database\Eloquent\Factories\HasFactory;


class User_Profile extends Model
{

     protected $table = "user_profile";
     protected $guarded = [];
public static function getDatafromId($id,$college_id){
    $data = User_Profile::where('id',$id)
                        ->where('college_id',$college_id)
                        ->first();
    return $data;
}
     public static function getAllRecords($course_id) {
          $data = User_Profile::where('status','!=',9);
        if(!empty($course_id)) {
            $data->where('course_id', $course_id);
        }
        $final_data = $data->get();
        return $final_data;
     }
     


     public static function getStudentRecords($course='',$semester='',$gender='', $category='', $mobile_number='',$form_number='',$college_id='', $college_rno='', $exam_rollno='', $enrolment_no='') {

          $data =User_Profile::where('status', '!=',9)
                              ->where('semester', '!=', Null)
                              ->where('college_id',$college_id)
                              ->orderBy('name', 'asc');
          if(!empty($course)) {
            $data->where('course_id', $course);
           }
           if(!empty($semester)) {
            $data->where('semester', $semester);
           }  
           if(!empty($gender)) {
            $data->where('gender_id', $gender);
           } 
           if(!empty($category)) {
            $data->where('category_id', $category);
           } 
           if(!empty($mobile_number)) {
            $data->where('contact_no', $mobile_number);
           } 
           if(!empty($form_number)) {
            $data->where('csas_form_number', $form_number);
           }
           if(!empty($college_rno)) {
            $data->where('roll_no', $college_rno);
           }
           if(!empty($exam_rollno)) {
            $data->where('examination_roll_no', $exam_rollno);
           }
           if(!empty($enrolment_no)) {
            $data->where('enrolment_number_org', $enrolment_no);
           }


           $final_data=$data->get();
           return $final_data;
     }     

     public static function pluckCodeAndName($college_id='') {
        $data = User_Profile::where('status','!=', 9);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data = $data->pluck('name', 'id');
        return $final_data;
     }
     
     public static function pluckEnrollmentNo() {
        return User_Profile::where('status','!=', 9)
                    ->pluck('enrolment_no', 'users_id');
     }    
           

     public static function getDataFromUsersId($users_id) {
          return User_Profile::where('users_id', $users_id)
                              ->first();
     }
     public static function updateDataFromId($id, $arr_to_update) {
        return User_Profile::where('id', $id)
                    ->update($arr_to_update);
    }
     
     
     
     

     public static function getAllStudents() {
          return User_Profile::join('users', 'user_profile.users_id', 'users.id')
                              ->where('users.role_id', 3)   //3 stands for student
                              ->get();
     }
    public static function pluckStudents($college_arr, $course_arr, $semester_arr) {
    $filteredStudents = [];

    $data = User_Profile::where('status', '!=', 9)
                         ->whereNotNull('semester')
                         ->orderBy('id', 'asc')
                         ->get();

    $college_ids = [];
    foreach ($college_arr as $key => $value) {
        $college_ids[] = $key;
    }

    $course_ids = [];
    foreach ($course_arr as $value) {
        $course_ids[] = (int)$value;
    }

    $semester_ids = [];
    foreach ($semester_arr as $value) {
        $semester_ids[] = (int)$value;
    }
    // dd($semester_ids,$college_ids,$course_ids);

    $final_data=[];
        foreach($data as $key => $value){
            if(in_array($value->college_id,$college_ids)){
                
                $final_data[$value->id]=$value->name;
            }
             if(in_array($value->course_id,$course_ids)){
                $final_data[$value->id]=$value->name;
            }
            if(in_array($value->semester,$semester_ids)){
                $final_data[$value->id]=$value->name;
            }
        }
        dd($final_data);
        return $final_data;
    }
    
     public static function pluckCollegeId($users_id){
        return User_Profile::where('users_id', $users_id)
                          ->pluck('college_id')
                          ->first();
     }

     public static function pluckUser($users_id){
        return User_Profile::where('users_id', $users_id)
                          ->first();

     }


     public static function getAllStudentsOfSemester($college_id, $semester) {
        return User_Profile::where('college_id', $college_id)
                            ->where('semester', $semester)
                            ->where('status', '!=',9)
                            ->orderBy('name', 'asc')
                            ->get();
     }

     public static function getAllStudentsOfSemesterAndCourse($college_id, $semester) {
        return User_Profile::where('college_id', $college_id)
                            ->where('semester', $semester)
                            ->where('status', '!=',9)
                            ->orderBy('course_id', 'asc')
                            ->get();
     }
     

     public static function pluckstudentformid($user_profile_id){
        return User_Profile::where('id', $user_profile_id)
                          ->get();
    }
    public static function pluckFirstByIdandCollege($college_id, $user_profile_id) {
        // dd($college_id, $user_profile_id);
        return User_Profile::join('users', 'user_profile.users_id', '=', 'users.id')
                            ->where('user_profile.id', $user_profile_id)
                            ->where('user_profile.college_id', $college_id)
                            ->where('status', 1)
                            ->select('user_profile.*','users.role_id')

                            ->first();
    }
    
    public static function getuserprofile($users_id) {
          return User_Profile::where('users_id', $users_id)
                              ->pluck('id');

    }

    public static function getAllRecordsOfCollege($college_id) {
        return User_Profile::where('college_id', $college_id)
                            ->where('status',1)
                            ->get();
    }

     
      public static function getcategory($college_id='') {
          return User_Profile::where('college_id', $college_id)
                            ->where('semester','!=',null)
                              ->pluck('category_id','id');
     }
     public static function getgender($college_id='') {
          return User_Profile::where('college_id', $college_id)
                            ->where('semester','!=',null)
                              ->pluck('gender_id','id');
     }
     public static function pluckstudentformidarray($user_profile_id=''){
        return User_Profile::whereIn('id', $user_profile_id)
                          ->orderby('roll_no','asc')
                          ->orderby('name','asc')
                          ->get();
    }
    public static function getAllRecordsOfstudents($college_id) {
        return User_Profile::where('college_id', $college_id)
                            ->where('semester','!=',null)
                            ->where('status',1)
                            ->orderBy('name', 'asc')
                            ->pluck('name','id');
    }
    public static function getAllStudentsfromCourse($college_id='', $course_id='') {
        return User_Profile::where('college_id', $college_id)
                            ->where('course_id', $course_id)
                            ->where('semester','!=',null)
                            ->where('status', '!=',9)
                            ->orderBy('name', 'asc')
                            ->pluck('name','id');
     }

    public static function getUserfromNamePhoneRole($college_id, $name='', $contact_no='', $role='') {
        $data = User_Profile::join('users', 'user_profile.users_id', '=', 'users.id')
                            ->where('user_profile.college_id', $college_id)
                            ->where('user_profile.status', '!=', 9)
                            ->select('user_profile.*');
    
        if (!empty($name)) {
            $data->where('user_profile.name', 'like', '%' . $name . '%');
        }
        if (!empty($contact_no)) {
            $data->where('user_profile.contact_no', $contact_no);
        }
        if (!empty($role)) {
            $data->where('users.role_id', $role);
        }
    
        $final_data = $data->get();
        // dd($final_data);
        // Now you should have the role_id in the result set
        return $final_data;

    }
    public static function getuser($college_id) {
        return User_Profile::where('college_id', $college_id)
                          ->where('semester','=',null)
                          ->where('users_id','!=',null)
                            ->pluck('name','users_id');
   }
 
   public static function pluckstudentformidid($user_profile_id=''){
    // dd($user_profile_id);
    return User_Profile::where('id', $user_profile_id)
                      
                      ->first();
    }
   
   public static function getRecordsFromCourseAndSemester($college_id, $course_id, $semester) {
        return User_Profile::where('college_id', $college_id)
                          ->where('course_id', $course_id)
                          ->where('semester',$semester)
                          ->orderBy('name', 'asc')
                          ->get();    
   }


   public static function pluckRecordsFromCourseAndSemester($college_id, $course_id, $semester) {
        return User_Profile::where('college_id', $college_id)
                          ->where('course_id', $course_id)
                          ->where('semester',$semester)
                          ->pluck('id');    
   }
    
    
    public static function getAllStudentsOfSemester2($college_id) {
        return User_Profile::where('college_id', $college_id)
                            ->where('semester', '!=', NULL)
                            ->where('status', '!=',9)
                            ->orderBy('name', 'asc')
                            ->get();
     }
     public static function getStudentsDataForInternship($users_id) {
          return User_Profile::whereIn('users_id', $users_id)
                              ->get();
     }

     public static function getStudentsDataFromCollege($college_id) {
        return User_Profile::where('college_id', $college_id)
                            ->where('semester', '!=', NULL)
                            ->get();
     }

     public static function getStudentsDataFromCollegeCourseSemester($college_id, $course, $semester) {
        $data = User_Profile::where('college_id', $college_id);
        if(!empty($course)){
            $data->where('course_id',$course);
        }
        if(!empty($semester)){
            $data->where('semester',$semester);
        }  
         $final_data=$data->orderBy('name', 'asc')
                            ->get();

        return $final_data; 
     }
     

     
}


?>     