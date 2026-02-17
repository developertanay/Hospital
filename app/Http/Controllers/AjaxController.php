<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course;
use App\Models\User_Profile;
use App\Models\College;
use App\Models\Section;
use App\Models\Subject;
use App\Models\Group;
use App\Models\TimeTable;
use App\Models\LectureType;
use App\Models\Role;
use App\Models\Company;
use App\Models\LetterApplication;
use App\Models\HourMast;
use App\Models\SessionDuration;
use App\Models\Course_Subject_Mapping;
use App\Models\UserSubjectMapping;
use App\Models\AcademicYear;
use App\Models\Faculty;
use App\Http\Controller\PdfController;
use App\Models\SocietyMast;
// use App\Models\Section;


use DB;
use Auth;
use Session;

class AjaxController extends Controller
{
    //
    public function getCoursesFromCollege(Request $request) {
        $college_id = $request->college_id;
        $data['code'] = 200;
        if(!empty($college_id)) {
            $course_mast = Course::getCoursesFromCollege($college_id);
            $course_arr = [];
            $semesters_in_course_arr = [];
            foreach ($course_mast as $key => $value) {
                $course_arr[$value->id] = $value->name;
                if(!empty($value->semesters)) {
                    $semesters_in_course_arr[$value->id] = $value->semesters;
                }
            }
            if(count($course_arr)>0) {
                $data['course_arr'] = $course_arr;
                $data['semesters_in_course_arr'] = $semesters_in_course_arr;
            }
            else {
                $data['alert_message'] = 'No Courses are currently mapped to the College';    
            }
        }
        else {
            $data['alert_message'] = 'Unable to send College Id to server';
        }

        return json_encode($data);
    }
   
    public function getSemesterFromCourse(Request $request) {
        $course_id = !empty($request->course_id)?$request->course_id:NULL;
        $data['code'] = 200;
        if($course_id == null) {
            $data['alert_message'] = 'Course Not Recieved in Ajax';
        }
        else {
            $semester = Course::getSemesterFromCourseId($course_id);
            $data['semester'] = $semester;
        }
        dd($semester);
        return json_encode($data);
    }
    public static function getstudentsbyCollegeCourseSemester(Request $request){
        // dd($request);
        $college_arr =!empty($request->college)?(array)$request->college:[];
        $course_arr=!empty($request->course)?(array)$request->course:[];
        $semester_arr=!empty($request->semester)?(array)$request->semester:[];
        // dd($course_arr, $semester_arr,$college_arr);

        $student = DB::table('user_profile')
                        ->where('semester', '!=', NULL);

        // if(count($course_arr)>0) {
        //     $student->whereIn('course_id',$course_arr);
        // }
        // if(count($college_arr)>0) {
        //     $student->whereIn('college_id',$college_arr);
        // }

        // if(count($semester_arr)>0) {
        //     $student->whereIn('semester',$semester_arr);
                // }
        if (count($course_arr) > 0) {
            $course_arr = array_map('intval', $course_arr); // Convert array values to integers
            $student->whereIn('course_id', $course_arr);
        }
        if (count($college_arr) > 0) {
            $college_arr = array_map('intval', $college_arr); // Convert array values to integers
            $student->whereIn('college_id', $college_arr);
        }

        if (count($semester_arr) > 0) {
            $semester_arr = array_map('intval', $semester_arr); // Convert array values to integers
            $student->whereIn('semester', $semester_arr);
        }
        
        $student_mast = $student->where('status',1)
                                ->orderBy('name', 'asc')
                                // ->pluck(DB::raw("CONCAT(enrolment_no, '-', '(',name,')') as name"), 'id');
                                ->get();
        $student_arr = [];
        // dd($student_arr);

        foreach($student_mast as $key => $value) {
            $student_arr[$value->id] = $value->enrolment_no.' - '.$value->name;
        }
        // dd($student_arr);

                $data['code']=200;
                $data['student_list']=$student_arr;
        return json_encode($data);
     }




        public function getFaculty(Request $request) {
            $collegeId = $request->input('college_id');
            $deptId = $request->input('dept_id');
            $faculty_arr=DB::table('faculty_mast')->where('college_id',$collegeId)
                                                  ->where('department_id',$deptId)
                                                   ->get();
            // dd($faculty);
            foreach($faculty_arr as $key => $value)
            {
                $faculty_name[$value->id]=$value->firstname.' '.$value->lastname;
            }
           
            if(count($faculty_name)>0) {
                $data['code'] = 200;
                $data['faculty'] = $faculty_name;
            }
            else{
            $data['alert_message'] = 'Course Not Recieved in Ajax';
            $data['code'] = 401;
            }
            // dd($data);
            return json_encode($data);

        }

        public function getFacultyOnDepart(Request $request) {
            
            
            $department_id=$request->departmentId;

            $college_id = Auth::user()->college_id;

            $faculty_arr=DB::table('faculty_mast')->where('college_id',$college_id)
                                                  ->where('department_id',$department_id)
                                                  ->get();
            // dd($faculty_arr);
            $faculty_name=[];
            foreach($faculty_arr as $key => $value)
            {
                $faculty_name[$value->id]=$value->firstname.' '.$value->lastname;
            }
           
            if(count($faculty_name)>0) {
                $data['code'] = 200;
                $data['faculty'] = $faculty_name;
            }
            else{
            $data['alert_message'] = 'Course Not Recieved in Ajax';
            $data['code'] = 401;
            }
            return json_encode($data);

        }

        public function getSemestersByCourse(Request $request) {
            $courseId = $request->input('course_id');
            $semester=DB::table('course_mast')->where('id',$courseId)->pluck('semesters');
            // dd($semester);
            if(count($semester)>0) {
                $data['code'] = 200;
                $data['semester'] = $semester;
            }
            else{
            $data['alert_message'] = 'Course Not Recieved in Ajax';
            $data['code'] = 401;
            }
            // dd($data);
            return json_encode($data);

        }

        public function getSubject(Request $request) {
            $subject_type_id = $request->input('subject_type_id');
            $subject_type_id = !empty($request->subject_type_id)?$request->subject_type_id:null;
            $college_id = Auth::user()->college_id;
            
            $subject = Subject::where('status',1);
            if(!empty($college_id)) {
                $subject->where('college_id', $college_id);
            }
            else {
                dd('college_id not passed');
            }
            if(!empty($subject_type_id)) {
                $subject->where('subject_type', $subject_type_id);
            }

            $final_data = $subject->pluck('subject_name','id');
        
            $data['code'] = 200;
            
            if(count($final_data)>0) {
                $data['subject'] = $final_data;
            }
            else{
                $data['subject'] = [];

            }
            // dd($data);
            return json_encode($data);

        }

        /*
        $role_id = Auth::user()->role_id;
        // dd($role_id);
        if($role_id == 4) {   //faculty
            $users_id = Auth::user()->id;
            $user_profile_data = User_Profile::getDataFromUsersId($users_id);
            if(empty($user_profile_data)) {
                dd('User Profile not created for the logged in user');
            }
            // dd($users_id, $user_profile_data);
            $college_to_select = $user_profile_data->college_id;
            $college_arr = College::pluckActiveCodeAndName($college_to_select);
        }
        else {
            $college_arr = College::pluckActiveCodeAndName();
        // dd($college_arr);
        }

          // dd($course_arr,$semester_arr,$college_arr);
        $student_list = User_Profile::pluckStudents($college_arr,$course_arr,$semester_arr);
        dd($student_list);
    */


    public function getSubjectForTimeTable(Request $request) {
        // dd($request);
            $college_id = !empty($request->college_id)?$request->college_id:null;
            $course_id = !empty($request->course_id)?$request->course_id:null;
            $semester = !empty($request->semesters)?$request->semesters:null;
            $subject_type_id = !empty($request->subject_type_id)?$request->subject_type_id:null;
            // dd($college_id,$course_id,$semester,$subject_type_id);
            
            $subject = Course_Subject_Mapping::where('status',1);
            if(!empty($college_id)) {
                $subject->where('college_id', $college_id);
            }
            else {
                dd('college_id not passed');
            }
            if(!empty($course_id)) {
                $subject->where('course_id', $course_id);
            }
            if(!empty($subject_type_id)) {
                $subject->where('subject_type_id', $subject_type_id);
            }
            if(!empty($semester)) {
                $subject->where('semester', $semester);
            }

            $final_data = $subject->pluck('subject_id','id');
            // dd($final_data);
            $data['code'] = 200;
            
            if(count($final_data)>0) {
                $data['subject'] = $final_data;
            }
            else{
                $data['subject'] = [];

            }
            // dd($data);
            return json_encode($data);

        }

    public static function getSubjectFromSubjectType(Request $request) {
        // dd($request);

    $subject_type = $request->input('subject_type');

    $subject = DB::table('subject_mast')->where('subject_type', $subject_type)->pluck('subject_name','id');
// dd($subject);
    if ($subject) {
        $data['code'] = 200;
        $data['subject_info'] = $subject;
    } else {
        $data['code'] = 404; // Assuming 404 indicates subject not found
        $data['message'] = 'Subject not found';
    }

    return response()->json($data);
    }

    public function getSelectedSubjects(Request $request)
    {
        // dd($request);
        // Retrieve selected subjects based on the provided parameters
        // You can use the $request data to query your database and fetch the subjects

        $userProfileId = $request->input('userProfileId');
        $courseId = $request->input('courseId');
        $semester = $request->input('semester');
        $subjectTypeId = $request->input('subjectTypeId');
        $collegeId = $request->input('collegeId'); // Assuming you pass collegeId in the request

        // Replace this with your actual query to get selected subjects from your database
        $selectedSubjects = UserSubjectMapping::where('college_id', $collegeId)
            ->where('user_profile_id', $userProfileId)
            ->where('course_id', $courseId)
            ->where('semester', $semester)
            ->where('subject_type_id', $subjectTypeId)
            ->pluck('subject_name');

        return response()->json(['subjects' => $selectedSubjects]);
    }
    
    public static function createSection(Request $request){
       // dd($request);
       $subject_id=!empty($request->subject_id)?$request->subject_id:null;
       $semester=!empty($request->section_semester)?$request->section_semester:null;
       $section=!empty($request->section)?$request->section:null;
    
       $users_id = Auth::user()->id;
       $user=User_Profile::pluckUser($users_id);
       $college_id= Auth::user()->college_id;
       $academic_year=DB::table('academic_year_mast')->where('status',1)->where('college_id',$college_id)->orderBy('start_year','desc')->pluck('start_year')->first();
        $updated_at = NULL;
        $updated_by = NULL;
        $created_at = date('Y-m-d H:i:s');
        $created_by = Auth::user()->id;
    
        $insert_arr=[
               'college_id'=>$college_id,
               'academic_year'=>$academic_year,
               'subject_id'=>$subject_id,
               'semester'=>$semester,
               'name'=>$section,
               'updated_at'=>$updated_at,
               'updated_by'=>$updated_by,
               'created_at'=>$created_at,
               'created_by'=>$created_by,
        ];
        // dd($insert_arr);
         DB::beginTransaction();
           $query = DB::table('section_mast')->insert($insert_arr);
           
           $data['code']=200;
           if($query) {
               DB::commit();
           }
           else {
               DB::rollback();
               $data['message'] = 'Unable to Create Section';
           }
           return json_encode($data);
       }
       public function getSections(Request $request) {
           $subjectId = $request->input('subject');
           $semesters = $request->input('semester');
           $collegeId = $request->input('college');
           $academicYear=$request->input('academic_year'); // Use the provided college_id
           // dd($request,$academicYear);
        
           // Fetch sections based on subject, semester, and college
        $sections = Section::where('college_id', $collegeId)
                            ->where('academic_year',$academicYear)
                            ->where('subject_id', $subjectId)
                            ->where('semester', $semesters)
                            ->pluck('name', 'id');
                  // dd($sections);          
        $data['code']=200;
        $data['section']=$sections;

// dd($data);
        return json_encode($data);
    }
    ///for subjects from faculty

    public function getSubjects(Request $request) {
        
        
        $faculty_id = $request->input('faculty_id');
        
        $collegeId = $request->input('college');
        $academicYear=$request->input('academic_year'); // Use the provided college_id
        // dd($request,$academicYear);
        // Fetch sections based on subject, semester, and college
        $ex=TimeTable::getDataForMonthlyAttendance($academicYear,$collegeId,$faculty_id);

        $subject_mast=Subject::pluckCodeAndName($collegeId);

        // dd($ex);
        $subject = [];
        foreach($ex as $key=>$value)
        {
            $arr=[
                'subject_id'=>$subject_mast[$value->subject_id],
                'section'=>'Sec:'.$value->section,
                'semester'=>'Sem:'.$value->semester
            ];

            $custom_key=$value->subject_id.'|'.$value->section.'|'.$value->semester;
            $subject[$custom_key]=implode('||',$arr);
        }
                //   dd($subject);          
        $data['code']=200;
        $data['data']=$subject;

// dd($data);

        return json_encode($data);
    }


    public function getRoomData(Request $request) {
        // dd($request);
        $day_value = $request->input('day');
        $time_value = $request->input('time');
        $room = $request->input('room_id');
        $college_id = !empty($request->college_id)?$request->college_id:NULL;
        $academic_year = !empty($request->academic_year)?$request->academic_year:NULL;
        // $primary_id = !empty($request->primary_id)?$request->primary_id:NULL;
        $lecture_type = !empty($request->lecture_type)?$request->lecture_type:NULL;
        $session_duration = !empty($request->session_duration_value)?$request->session_duration_value:NULL;
        // dd($lecture_type);
        $semester = !empty($request->semesters)?$request->semesters:NULL;
        $subject_id = !empty($request->subject_id)?$request->subject_id:NULL;

        // Fetch sections based on subject, semester, and college
        $overlap_id=LectureType::getOverlapStatus($college_id,$lecture_type);
        // dd($overlap_id);
        if($overlap_id==1){
             $room_data = TimeTable::where('academic_year', $academic_year)
                        ->where('college_id', $college_id)
                        ->where('session_duration', $session_duration)
                        ->where('day', $day_value)
                        ->where('timing',$time_value)
                        ->where('room_id', $room)
                        ->where('status',1)
                        ->get();

        $flag=1;
            foreach($room_data as $key =>$value){
            // dd($value);
                    if(($value->academic_year == $academic_year) && ($value->session_duration == $session_duration) && ($value->college_id == $college_id) && ($value->lecture_type_id == $lecture_type) && ($value->semester == $semester) && ($value->subject_id == $subject_id))
                    {
                        // dd($flag);
                    }else{
                        $flag=0;
                        break;
                    }

            }

            if($flag==1){
                $room=[];
            }
            else{
                 $room = TimeTable::where('academic_year', $academic_year)
                                    ->where('college_id', $college_id)
                                    ->where('day', $day_value)
                                    ->where('timing',$time_value)
                                    ->where('room_id', $room)
                                    ->where('session_duration', $session_duration)
                                    ->where('status',1)
                                    // ->where('id','!=', $primary_id)
                                    ->get();

            }   
        }
        else{
            // dd(1);
            $room = TimeTable::where('academic_year', $academic_year)
                                    ->where('college_id', $college_id)
                                    ->where('session_duration', $session_duration)
                                    ->where('day', $day_value)
                                    ->where('timing',$time_value)
                                    ->where('room_id', $room)
                                    ->where('status',1)
                                    // ->where('id','!=', $primary_id)
                                    ->first();
        }
        // dd($room);          
        $data['code']=200;
        $data['room']=$room;
        return json_encode($data);

        }                  

    public function getStudents(Request $request) {
        $subjectId = $request->input('subject_type_id');
        $semester = $request->input('semester');
        $course_id = $request->input('course_id');
        $collegeId = $request->input('college_id'); // Use the provided college_id
        $academic_year = $request->input('academic_year');

        // Fetch distinct user_profile_id based on subject, semester, and college
        $student = UserSubjectMapping::where('subject_type_id', $subjectId)
            ->where('semester', $semester)
            ->where('academic_year', $academic_year)
            ->where('approval_status', 1)
            ->where('course_id', $course_id)
            ->where('college_id', $collegeId)
            ->distinct()
            ->pluck('user_profile_id');
        $students = User_Profile::whereIn('id',$student)->pluck('name','id');
            $c=count($students);
        if($c>0) {
            $data['code'] = 200;
            $data['student'] = $students;
        }
        else{
        
        $data['code'] = 401;
        }
        // dd($students,$data['code']);

        return json_encode($data);
    }
    public function updateStudents(Request $request){
        // dd($request);
        // $subjectId = $request->input('subjectId');
        // $key = $request->input('key');
        // $courseId = $request->input('courseId');
        // $semester = $request->input('semester');
        // $subjectTypeId = $request->input('studentId');

        $college_id=!empty($request->collegeId)?$request->collegeId:null;
        $academic_year=!empty($request->academicYear)?$request->academicYear:null;
        $subject_id=!empty($request->subjectId)?$request->subjectId:null;
        $subject_type_id=!empty($request->subjectTypeId)?$request->subjectTypeId:null;
        $course_id=!empty($request->courseId)?$request->courseId:null;
        $semester=!empty($request->semester)?$request->semester:null;
        $student = !empty($request->student)?$request->student:null;
        $status = !empty($request->status)?$request->status: 1;
        $approval_status = !empty($request->approval_status)?$request->approval_status: 1;
        $sequence = !empty($request->sequence)?$request->sequence: 100;
        $updated_at = NULL;
        $updated_by = NULL;
        $created_at = date('Y-m-d H:i:s');
        $created_by = Auth::user()->id;
        $myArr = [
            'college_id' => $college_id,
            'subject_type_id' => $subject_type_id,
            'subject_id' => $subject_id,
            'semester' => $semester,
            'course_id' => $course_id,
            'academic_year'=>$academic_year,
            'user_profile_id'=>$student,
            'status' => $status,
            'approval_status' => $approval_status,
            'updated_at' => $updated_at,
            'updated_by' => $updated_by,
            'created_at' => $created_at,
            'created_by' => $created_by,
        ];
        DB::beginTransaction();
        $query = DB::table('temp')->insert($myArr);
        if($query){
            $data['code'] = 200;
        }


        // dd($data['code']);
        
        if($query) {
            DB::commit();
            $message = 'Entry Updated Successfuly';
            Session::flash('message', $message);
        }else {
            DB::rollback();
            $message = 'Something Went Wrong';
            Session::flash('error', $message);
        }

        return json_encode($data);
        } 
        public function getSection(Request $request) {
        $subjectId = $request->input('subject_id');
        $semester = $request->input('section_semester');
        $collegeId = $request->input('college_id'); // Use the provided college_id
        $academic_year = $request->input('academic_year');
        // dd($request,$subjectId,$semester,$collegeId,$academic_year);

        // Fetch distinct user_profile_id based on subject, semester, and college
        $sections = Section::where('subject_id', $subjectId)
            ->where('semester', $semester)
            ->where('academic_year', $academic_year)
            ->where('status',1)
            ->where('college_id', $collegeId)
            ->distinct()
            ->pluck('name');
        // dd($sections);
        
        if($sections) {
            $data['code'] = 200;
            $data['section'] = $sections;
        }
        else{
        
        $data['code'] = 401;
        }
        // dd($sections,$data['code']);

        return json_encode($data);
    }
    public function getGroups(Request $request) {
        // dd($request);
        $subjectId = $request->input('subject');
        $semesters = $request->input('semester');
        $collegeId = $request->input('college'); // Use the provided college_id
        $sections =$request->input('section');
        $academic_year=$request->input('academic_year');
        // dd($request,$subjectId,$semesters,$collegeId,$sections);

        // Fetch distinct user_profile_id based on subject, semester, and college
        $groups = Group::where('subject_id', $subjectId)
            ->where('semester', $semesters)
            ->where('academic_year', $academic_year)
            ->where('section', $sections)
            ->where('status',1)
            ->where('college_id', $collegeId)
            ->distinct()
            ->pluck('name');
        // dd($sections);
        
        if($sections) {
            $data['code'] = 200;
            $data['group'] = $groups;
        }
        else{
        
        $data['code'] = 401;
        }
        // dd($sections,$data['code']);

        return json_encode($data);
    }
    public static function createGroup(Request $request){
    // dd($request);
    $subject_id=!empty($request->subject_id)?$request->subject_id:null;
    $semester=!empty($request->group_semester)?$request->group_semester:null;
    $section=!empty($request->sec)?$request->sec:null;
    $group=!empty($request->group)?$request->group:null;

    $users_id = Auth::user()->id;
    $user=User_Profile::pluckUser($users_id);
    $college_id= $user->college_id;
    $academic_year=DB::table('academic_year_mast')->where('status',1)->where('college_id',$college_id)->orderBy('start_year','desc')->pluck('start_year')->first();
     $updated_at = NULL;
     $updated_by = NULL;
     $created_at = date('Y-m-d H:i:s');
     $created_by = Auth::user()->id;

     $insert_arr=[
            'college_id'=>$college_id,
            'academic_year'=>$academic_year,
            'subject_id'=>$subject_id,
            'semester'=>$semester,
            'name'=>$group,
            'section'=>$section,
            'updated_at'=>$updated_at,
            'updated_by'=>$updated_by,
            'created_at'=>$created_at,
            'created_by'=>$created_by,
     ];
     // dd($insert_arr);
      DB::beginTransaction();
        $query = DB::table('group_mast')->insert($insert_arr);
        
        $data['code']=200;
        if($query) {
            DB::commit();
        }
        else {
            DB::rollback();
            $data['message'] = 'Unable to Create Section';
        }
        return json_encode($data);
    }


    public static function getTimeTableData(Request $request){
        // dd($request);
        $college_id=!empty($request->college_id)?$request->college_id:null;
        $academic_year=!empty($request->academic_year)?$request->academic_year:null;
        $faculty_id=!empty($request->faculty_id)?$request->faculty_id:null;
        $dayOfWeek=!empty($request->dayOfWeek)?$request->dayOfWeek:null;

        $time_table_data=TimeTable::getDataForAttendance($academic_year,$college_id,$faculty_id,$dayOfWeek);
        // dd($time_table_data);getSubjectTime
        $data['code']=200;
        if(count($time_table_data)>0){
            $data['time_table_data']=$time_table_data;
        }
        else{
            $data['time_table_data']=[];
        }
        return json_encode($data);
    
    }

    public static function getSubjectTime(Request $request){
        // dd($request);
        $college_id=!empty($request->college_id)?$request->college_id:null;
        $academic_year=!empty($request->academic_year)?$request->academic_year:null;
        $faculty_id=!empty($request->faculty_id)?$request->faculty_id:null;
        $day = !empty($request->date) ? date('l', strtotime($request->date)) : null;
        
        $time_table_data=TimeTable::getDataForAttendance($academic_year,$college_id,$faculty_id,$day);
        // dd($time_table_data);getSubjectTime
        $data['code']=200;
        if(count($time_table_data)>0){
            $data['time_table_data']=$time_table_data;
        }
        else{
            $data['time_table_data']=[];
        }
        return json_encode($data);
    
    }

    public static function getAttendanceTime(Request $request){

        // dd($request);
        $academic_year=!empty($request->academic_year)?$request->academic_year:null;
        $college_id=!empty($request->college_id)?$request->college_id:null;
        $faculty_id=!empty($request->faculty_id)?$request->faculty_id:null;
        // dd($faculty_id);
        $subject_id=!empty($request->subject_id)?$request->subject_id:null;
        $lecture_type_id=!empty($request->lecture_type_id)?$request->lecture_type_id:null;
        $semester=!empty($request->semester)?$request->semester:null;
        $section=!empty($request->section)?$request->section:null;
        $group=!empty($request->group)?$request->group:null;
        $dayOfWeek=!empty($request->dayOfWeek)?$request->dayOfWeek:null;

        $data_from_time_table= TimeTable::getAttendanceTimeData($academic_year,$college_id,$faculty_id,$subject_id,$lecture_type_id,$semester,$section,$group,$dayOfWeek);
        // dd($data_from_time_table);
        $data_from_time_table_arr = $data_from_time_table->toArray();

        $data_from_hour_mast=HourMast::where('college_id',$college_id)
                                    ->whereIn('start_time',$data_from_time_table_arr)
                                    // ->get();
                                    ->pluck('end_time','start_time');

        // dd($college_id,$data_from_time_table_arr,$data_from_hour_mast);
        $data['code']=200;
        if(count($data_from_hour_mast)>0){
            $data['hour_data']=$data_from_hour_mast;
        }
        else{
            $data['hour_data']=[];
        }
        return json_encode($data);


    }
    public static function getStudent(Request $request){

        $college_id=!empty($request->college_id)?$request->college_id:null;
        $course =!empty($request->course_id)?$request->course_id:null;
        $students = User_Profile::getAllStudentsfromCourse($college_id,$course);

         if($students) {
            $data['code'] = 200;
            $data['student'] = $students;
        }
        else{
        
        $data['code'] = 401;
        }
        // dd($students,$data['code']);

        return json_encode($data);
        // dd($request,$college_id,$course,$student);

    }

    public static function print_PDF(Request $request){
        // dd($request);
        $pdf_heading=!empty($heading_arr)?$heading_arr:[];
        $column_arr=!empty($column_arr)?$column_arr:[];
        $content_arr=!empty($content_arr)?$content_arr:[];

        $data=PdfController::print_pdf($pdf_heading,$column_arr,$content_arr);
    }
    
    public static function get_course_subject_mapping_data(Request $request) {
        $college_id = Auth::user()->college_id;
        $course_id = !empty($request->course_id)?$request->course_id:NULL;
        $semester = !empty($request->semester)?$request->semester:NULL;
        $subject_type_id = !empty($request->subject_type_id)?$request->subject_type_id:NULL;
        $course_subject_mapping_data = Course_Subject_Mapping::getSubjectsFromSubjectType($college_id, $course_id, $semester, $subject_type_id);
        $subject_saved_arr = [];
        $preference = '';
        $subject_count = '';
        if($course_subject_mapping_data->isEmpty()) {
            $data['present_flag'] = 0;    
        }
        else{
            $data['present_flag'] = 1;    
            foreach($course_subject_mapping_data as $key => $value) {
                $subject_saved_arr[$value->subject_id] = $value->subject_id;
                $preference = !empty($value->preferences)?$value->preferences:'';
                $subject_count = !empty($value->subject_count)?$value->subject_count:'';
            }
        }
        $data['code']=200;
        $data['subject_saved_arr'] = $subject_saved_arr;
        $data['preference'] = $preference;
        $data['subject_count'] = $subject_count;
        return json_encode($data);
    }

    public static function getstudentsbyCollegeCourseSemester2(Request $request){
        // dd($request);
        $college_arr =!empty($request->college)?$request->college:"";
        $course_arr=!empty($request->course)?$request->course:"";
        $semester_arr=!empty($request->semester)?$request->semester:"";
        // dd($course_arr, $semester_arr,$college_arr);
        $student_arr = [];
        if(!empty($college_arr) && !empty($course_arr) && !empty($semester_arr)){
        $student = DB::table('user_profile')
                        ->where('semester', '!=', NULL)
                        ->where('course_id', $course_arr)
                        ->where('college_id', $college_arr)
                        ->where('semester', $semester_arr);
        // if(count($course_arr)>0) {
        //     $student->whereIn('course_id',$course_arr);
        // }
        // if(count($college_arr)>0) {
        //     $student->whereIn('college_id',$college_arr);
        // }

        // if(count($semester_arr)>0) {
        //     $student->whereIn('semester',$semester_arr);
                // }
       
        
        $student_mast = $student->where('status',1)
                                ->orderBy('name', 'asc')
                                ->get();
        
        // dd($student_arr);

        foreach($student_mast as $key => $value) {
            $student_arr[$value->id] = $value->enrolment_no.' - '.$value->name;
        }
    }
        // dd($student_arr);

                $data['code']=200;
                $data['student_list']=$student_arr;
        return json_encode($data);
     }
     
     public static function getrolebyuser(Request $request){

          $college_id=!empty($request->college_id)?$request->college_id:'';
          $user_id=!empty($request->user_id)?$request->user_id:'';
          $id=Auth::User()
                    ->where('id',$user_id)
                    ->where('college_id',$college_id)
                    ->pluck('role_id')
                    ->first();

        $role=Role::pluckNameFromId($id);
                    // dd($role);
                    $data['code'] = 200;

        $data['course_arr'] = $role;
        return json_encode($data);


     }

     public function getSocietyName(Request $request){
        // dd($request);
        $college_id=$request->input('college');
        $users_id = !empty( $request->input('userId'))? $request->input('userId'):NULL;
        $faculty_id = Faculty::where('users_id',$users_id)->pluck('id');
        $society_mast = SocietyMast::where('faculty_id',$faculty_id)->pluck('name','id');
        $data['code']=200;
        $data['data']=$society_mast;
        
        return json_encode ($data);

    }
    
    public function getEventNames(Request $request){
        $college_id=$request->input('college');
        $society_id=$request->input('societyId');
        $events = DB::table('notice_event_module')->where('college_id',$college_id)
        ->where('society_id',$society_id)
        ->pluck('title','id');
        $start_date = DB::table('notice_event_module')->where('college_id',$college_id)
        ->where('society_id',$society_id)
        ->pluck('start_date','id');
        $end_date = DB::table('notice_event_module')->where('college_id',$college_id)
        ->where('society_id',$society_id)
        ->pluck('end_date','id');
        $data['code'] = 200;
        $data['events'] = $events;
        $data['start_date'] = $start_date;
        $data['end_date'] = $end_date;
        


        return json_encode($data);
    }

    public function getEventDates(Request $request){
        $college_id=$request->input('college');
        $event_id=$request->input('eventId');
        $start_date = DB::table('notice_event_module')->where('college_id',$college_id)
        ->where('event_id',$event_id)
        ->pluck('start_date','id');
        $end_date = DB::table('notice_event_module')->where('college_id',$college_id)
        ->where('event_id',$event_id)
        ->pluck('end_date','id');
    }
    public static function getLectureData(Request $request){
        $college_id=!empty($request->college_id)?$request->college_id:null;
        $academic_year=!empty($request->academic_year)?$request->academic_year:null;
        $faculty_id=!empty($request->faculty_id)?$request->faculty_id:null;
        $session = !empty($request->session_duration) ?$request->session_duration : null;
        // dd($request,$college_id,$academic_year,$faculty_id,$session);
        
        $time_table_data=TimeTable::getDataForMonthlyAttendance2($academic_year,$college_id,$faculty_id,$session);
        // dd($time_table_data);
        $data['code']=200;
        if(count($time_table_data)>0){
            $data['time_table_data']=$time_table_data;
        }
        else{
            $data['time_table_data']=[];
        }
        return json_encode($data);
    
    }

    public static function getLectureDataForMonthlyAttendance(Request $request) {
        // dd($request);
        $academic_year=!empty($request->academic_year)?$request->academic_year:NULL;
        $college_id=!empty($request->college_id)?$request->college_id:NULL;
        $faculty_id=!empty($request->faculty_id)?$request->faculty_id:NULL;
        $session_duration_id=!empty($request->current_session_duration_id)?$request->current_session_duration_id:NULL;
        $lecture_data= TimeTable::select('subject_id','lecture_type_id','section','group','semester')
                        ->where('academic_year',$academic_year)
                        ->where('college_id',$college_id)
                        ->where('session_duration', $session_duration_id)
                        ->where('status',1);
        if($faculty_id){
           $lecture_data->where('faculty_id',$faculty_id);
        }
        $final_data = $lecture_data->groupBy('subject_id')
                            ->groupBy('lecture_type_id')
                            ->groupBy('semester')
                            ->groupBy('section')
                            ->groupBy('group')
                            ->get();

        // dd($final_data);
        $data['code']=200;
        if(count($final_data)>0){
            $data['time_table_data']=$final_data;
        }
        else{
            $data['time_table_data']=[];
        }
        return json_encode($data);
    }
    public static function getLectureDataForMonthlyAttendance2(Request $request) {
        // dd($request);
        $academic_year=!empty($request->academic_year)?$request->academic_year:NULL;
        $college_id=!empty($request->college_id)?$request->college_id:NULL;
        $faculty_id=!empty($request->faculty_id)?$request->faculty_id:NULL;
        $session_duration_id=!empty($request->current_session_duration_id)?$request->current_session_duration_id:NULL;
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

        // dd($final_data);
        $data['code']=200;
        if(count($final_data)>0){
            $data['time_table_data']=$final_data;
        }
        else{
            $data['time_table_data']=[];
        }
        return json_encode($data);
    }

    public function getCity(Request $request) {
            $state_id = !empty($request->state_id)?$request->state_id:NULL;
            $city_arr=DB::table('city_mast')->where('state_id',$state_id)->pluck('city_name','id');

            if(!empty($city_arr)) {
                $data['code'] = 200;
                $data['city'] = $city_arr;
            }
            else{
            $data['alert_message'] = 'Course Not Recieved in Ajax';
            $data['code'] = 401;
            }
            // dd($data);
            return json_encode($data);

        }
        public function getPost(Request $request) {
            $society_id = !empty($request->society_id)?$request->society_id:NULL;
            // dd($society_id);
            $society_mast=DB::table('society_designation_mast')->where('society_id',$society_id)->where('status',1)->pluck('name','id');
            $data=[];
            if(!empty($society_mast)) {
                $data['code'] = 200;
                $data['post'] = $society_mast;
            }
            else{
            $data['alert_message'] = 'Course Not Recieved in Ajax';
            $data['code'] = 401;
            }
            // dd($data);
            return json_encode($data);

        }

        
        public function storeAppliedNOCData(Request $request) {
            $college_id=Auth::user()->college_id;
            $letter_id=2;
            $users_id = !empty($request->users_id)?$request->users_id:NULL;
            $status=1;
            $company_id = !empty($request->company_id)?$request->company_id:NULL;
            $job_id = !empty($request->job_id)?$request->job_id:NULL;
            $job_title_id = !empty($request->job_title_id)?$request->job_title_id:NULL;
            $from_date = !empty($request->from_date)?date('Y-m-d',strtotime($request->from_date)):NULL;
            $to_date = !empty($request->to_date)?date('Y-m-d',strtotime($request->to_date)):NULL;
            $reference_id = !empty($request->reference_id)?$request->reference_id:NULL;
            // dd($society_id);
            // $society_mast=DB::table('society_designation_mast')->where('society_id',$society_id)->where('status',1)->pluck('name','id');
            $company_data=Company::where('id',$company_id)->first();
            $user_data=User_Profile::where('users_id',$reference_id)->first();
            $course_id=$user_data->course_id;
            $course_name=Course::where('id',$course_id)->pluck('name')->first();
            $semester=$user_data->semester;
            $roll_no=$user_data->roll_no;
            $student_name=$user_data->name;
            $company_name=$company_data->name;
            $company_website=$company_data->website;
            $hr_details=User_Profile::where('users_id',$reference_id)->first();
            // dd($hr_details);
            $hr_name=$hr_details->name;
            $hr_email=$hr_details->email;
            $hr_contact_no=$hr_details->contact_no;
            $created_by=Auth::user()->id;
            $created_at=date('Y-m-d H:i:s');
            $insert_arr=[
                'college_id'=>$college_id,
                'letter_id'=>$letter_id,
                'users_id'=>$users_id,
                'status'=>$status,
                'created_at'=>$created_at,
                'created_by'=>$created_by,
                'course_id'=>$course_id,
                'course_name'=>$course_name,
                'semester'=>$semester,
                'roll_no'=>$roll_no,
                'student_name'=>$student_name,
                'company_name'=>$company_name,
                'company_id'=>$company_id,
                'company_website'=>$company_website,
                'profile'=>$job_title_id,
                'from_date'=>$from_date,
                'to_date'=>$to_date,
                'hr_name'=>$hr_name,
                'hr_email'=>$hr_email,
                'hr_contact_no'=>$hr_contact_no,
                'job_id'=>$job_id,
            ];
            $insert_query=LetterApplication::create($insert_arr);
            // dd($insert_query);
            $noc_letter_id=$insert_query->id;
            $update_query=DB::table('job_application')->where('users_id',$users_id)->where('job_id',$job_id)->update(['noc_letter_id'=>$noc_letter_id]);
            // dd($update_query);
            $data=[];
            if($insert_query) {
                $data['code'] = 200;
            }
            else{
            $data['alert_message'] = 'Course Not Recieved in Ajax';
            $data['code'] = 401;
            }
            // dd($data);
            return json_encode($data);

        }
        public function getSubjects2(Request $request) {
        
        
        $faculty_ids = $request->input('faculty_id');
        
        $collegeId = $request->input('college');
        $academicYear=$request->input('academic_year'); // Use the provided college_id
        $faculty_id = Faculty::where('user_profile_id',$faculty_ids)->pluck('id')->first();
        // dd($faculty_ids,$faculty_id);
        // dd($request,$academicYear);
        // Fetch sections based on subject, semester, and college
        $session_duration_arr = SessionDuration::pluckCurrentlyRunningSessionId($collegeId);
        $ex=TimeTable::getDataForMonthlyAttendance($academicYear,$collegeId,$faculty_id,$session_duration_arr);
        // dd($ex,$session_duration_arr);
        $subject_mast=Subject::pluckCodeAndName($collegeId);

        // dd($ex);
        $subjects = [];
            foreach ($ex as $key => $value) {
        $subject_id = $value->subject_id;
        $subject_name = $subject_mast[$subject_id]; // Get subject name using subject_id

        // Add subject to the subjects array if not already added
        if (!in_array($subject_name, $subjects)) {
            $subjects[$subject_id] = $subject_name;
        }
    }

    $data['code'] = 200;
    $data['data'] = $subjects;

    return response()->json($data);
}

public function getSubjectFromFaculty(Request $request)
{
    // dd($request);
    $college_id = $request->college_id;
    $faculty_id = $request->faculty_id;
    $subject = !empty($request->subject)?$request->subject:null;
    // dd($college_id,$subject);
    
    $subject = DB::table('time_table_mast')->select('subject_id','lecture_type_id','section','group','semester')->where('status',1);
    if(!empty($college_id)) {
        $subject->where('college_id', $college_id);
    }
    else {
        dd('college_id not passed'); 
    }
    if(!empty($faculty_id)) {
        $subject->where('faculty_id', $faculty_id);
    }
    
    $final_data = $subject->groupBy('subject_id')
    ->groupBy('lecture_type_id')
    ->groupBy('semester')
    ->groupBy('section')
    ->groupBy('group')
    ->get();
    // dd($final_data);
    $data['code'] = 200;
        
    if(count($final_data)>0){
        $data['time_table_data']=$final_data;
    }
    else{
        $data['time_table_data']=[];
    }
   
    return json_encode($data);

}
        public function id_card_details_unlock(Request $request){
    // dd($request);
                $user=Auth::user();
                $user_id=!empty($request->user_id)?$request->user_id:'';
                $arr=[
                    'flag'=>2,
                    'created_by'=>$user->id,
                    'created_at'=>date('y-m-d H:i:s'),
                ];
                $query=DB::table('id_card_details')->where('user_id',$user_id)->update($arr);
                // dd($query);
                $data['code'] = 200;

                return json_encode($data);
                
            }

}