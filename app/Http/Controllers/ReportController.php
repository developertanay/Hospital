<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\SubjectType;
use App\Models\Subject;
use App\Models\SectionAllotment;
use App\Models\College;
use App\Models\Course;
use App\Models\UserSubjectMapping;
use App\Models\UserApprovedSubjects;
use App\Models\AcademicYear;
use App\Models\User_Profile;
use App\Models\Course_Subject_Mapping;
use App\Models\Category;
use App\Models\Gender;
use App\Models\Faculty;
use App\Models\Section;
use App\Models\TimeTable;
use App\Models\AssignmentMast;
use App\Models\AttendanceHead;
use App\Models\AttendanceBody;
use App\Models\LectureType;
use App\Models\SessionDuration;
use App\Models\HourMast;
use App\Models\RoomMast;
use App\Models\StudentIdData;
use App\Models\CityMast;




use Auth;
use DB;
use PDF;
use Session;


class ReportController extends Controller
{
       
    public function student_subject_listing(Request $request)
    {
        // dd($request);
        $college_id = !empty($request->college_id)?$request->college_id:null;
        $course = !empty($request->course)?$request->course:null;
        $subject_type = !empty($request->subject_type)?$request->subject_type:null;
        $subject = !empty($request->subject)?$request->subject:null;
        $semester = !empty($request->semester)?$request->semester:null;
        //added afterwards
        $user_id = Auth::user()->id;
        $role_id = Auth::user()->role_id;
        $faculty_college = '';
        if($role_id == 4) {
            $user_profile_data = User_Profile::getDataFromUsersId($user_id);
            $college_id = !empty($user_profile_data->college_id)?$user_profile_data->college_id:'';
        }

        // dd($college_id,$course,$subject_type,$subject,$semester);
        $data=UserSubjectMapping::getStudentRecords($college_id, $course,$subject_type,$subject,$semester);
        // dd($data);
        // $user_profile_mast=User_Profile::getStudentName($data->name);
        $course_mast=Course::pluckActiveCodeAndName($college_id); 
        $subject_type_mast=SubjectType::pluckActiveCodeAndName($college_id);
        $subject_mast=Subject::pluckActiveCodeAndName($college_id);
        // dd($data);

        $final_data_arr = [];
        $rowspan_count = [];
        $student_name_arr = [];
        $student_semester_arr = [];
        $student_course_arr = [];
        foreach($data as $key => $value) {
            $student_name_arr[$value->user_profile_id] = $value->name;
            $student_semester_arr[$value->user_profile_id] = $value->semester;
            $student_course_arr[$value->user_profile_id] = $value->course_id;
            $rowspan_count[$value->user_profile_id] = (!empty($rowspan_count[$value->user_profile_id])?$rowspan_count[$value->user_profile_id]:0) + 1;
            $final_data_arr[$value->user_profile_id][$value->subject_type_id][] = $value->subject_id;
        }

        // dd($final_data_arr);
        return view('Reports/student_subject_listing',[
            'data'=>$data,
            'college_id'=>$college_id,
            'course_mast'=>$course_mast,
            'subject_type_mast'=>$subject_type_mast,
            'subject_mast'=>$subject_mast,
            'student_name_arr' => $student_name_arr,
            'rowspan_count' => $rowspan_count,
            'final_data_arr' => $final_data_arr,
            'student_semester_arr' => $student_semester_arr,
            'student_course_arr' => $student_course_arr,
        ]);

    }


    public function student_subject_listing3(Request $request)
    {
        // dd($request);
        
        //added afterwards
        // $college_id = !empty($request->college_id)?$request->college_id:null;
        // $user_id = Auth::user()->id;
        // $role_id = Auth::user()->role_id;
        // $faculty_college = '';
        // if($role_id == 4) {
        //     $user_profile_data = User_Profile::getDataFromUsersId($user_id);
        //     $college_id = !empty($user_profile_data->college_id)?$user_profile_data->college_id:'';
        // }

        $college_id = Auth::user()->college_id;

        $course = !empty($request->course)?$request->course:null;
        $subject_type = !empty($request->subject_type)?$request->subject_type:null;
        $subject = !empty($request->subject)?$request->subject:null;
        $priority = !empty($request->priority)?$request->priority:null;
        
        $semester = !empty($request->semester)?$request->semester:null;
        $session_flash = !empty($request->session_flash)?$request->session_flash:0;
        // dd($college_id,$course,$subject_type,$subject,$semester);
        // dd($data);
        // $user_profile_mast=User_Profile::getStudentName($data->name);
        $course_mast=Course::getActiveCoursesFromCollege($college_id); 
        $subject_type_mast=SubjectType::pluckActiveCodeAndName($college_id);
        $subject_mast=Subject::pluckActiveCodeAndName($college_id,$subject_type);
        if($course == null && $subject_type==null && $subject==null) {
            if($session_flash) {
                Session::flash('error', 'You need to select atleast one filter');
            }
            return view('Reports/student_subject_listing3',[
                'data'=>[],
                'course_mast'=>$course_mast,
                'college_id'=>$college_id,
                'subject_type_mast'=>$subject_type_mast,
                'subject_mast'=>$subject_mast,
                
                'student_name_arr' => [],
                'rowspan_count' => [],
                'final_data_arr' => [],
                'student_semester_arr' => [],
                'student_course_arr' => [],
            ]);
        }
        $data=UserSubjectMapping::getStudentRecords($college_id, $course,$subject_type,$subject,$semester,$priority);
        // dd($data);

        $final_data_arr = [];
        $rowspan_count = [];
        $student_name_arr = [];
        $student_semester_arr = [];
        $student_course_arr = [];
        $student_contact_no = [];
        $student_email = [];
        foreach($data as $key => $value) {
            $student_name_arr[$value->user_profile_id] = $value->name;
            $student_contact_no[$value->user_profile_id] = $value->contact_no;
            $student_email[$value->user_profile_id] = $value->email;
            $student_semester_arr[$value->user_profile_id] = $value->semester;
            $student_course_arr[$value->user_profile_id] = $value->course_id;
            $rowspan_count[$value->user_profile_id] = (!empty($rowspan_count[$value->user_profile_id])?$rowspan_count[$value->user_profile_id]:0) + 1;
            $final_data_arr[$value->user_profile_id][$value->subject_type_id][] = $value->subject_id;
        }

        // dd($final_data_arr);
        return view('Reports/student_subject_listing3',[
            'data'=>$data,
            'course_mast'=>$course_mast,
            'college_id'=>$college_id,
            'subject_type_mast'=>$subject_type_mast,
            'subject_mast'=>$subject_mast,
            'student_name_arr' => $student_name_arr,
            'rowspan_count' => $rowspan_count,
            'final_data_arr' => $final_data_arr,
            'student_semester_arr' => $student_semester_arr,
            'student_course_arr' => $student_course_arr,
            'student_contact_no' => $student_contact_no,
            'student_email' => $student_email
        ]);

    }

    public static function remaining_student_subject_report(){

        $users_mapped_arr = UserSubjectMapping::get_mapped_users();
        dd($users_mapped_arr);
        $users_remaining = User_Profile::where('semester', '!=', NULL)
                                        ->whereNotIn('id', $users_mapped_arr)
                                        ->get();
        dd($users_remaining);
            return view('Reports/defaulters');


    }
    public static function course_subject_report(Request $request)
    {
        // dd($request);
        $course_id=!empty($request->course_id)?$request->course_id:null;
        $semest=!empty($request->semest)?$request->semest:null;
        $subject_type_id=!empty($request->subject_type_id)?$request->subject_type_id:null;
        $subject_id=!empty($request->subject_id)?$request->subject_id:null;

        $data = Course_Subject_Mapping::where('status',1)
                                        ;
                // dd($data);
        if (!empty($course_id)) {
                $data->where('course_id', $course_id);
            }
            if (!empty($semest)) {
                $data->where('semester', $semest);
            }
            if (!empty($subject_type_id)) {
                $data->where('subject_type_id', $subject_type_id);
            }
            if (!empty($subject_id)) {
                $data->where('subject_id', $subject_id);
            }

            $final_data = $data->get();
            // dd($final_data);


        $course_mast=Course::pluckActiveCodeAndName($college_id);
        $sem =DB::table('course_mast')->where('status',1)
                                        ->where('semesters',8)
                                        ->pluck('semesters','id')
                                        ->first();


        $subject_type_mast=SubjectType::pluckActiveCodeAndName($college_id);
        $subject_type_short_mast=SubjectType::pluckActiveData($college_id);

        $subject_mast=Subject::pluckActiveCodeAndName($college_id);
        // dd($subject_type_short_mast);
        $new_data_arr = [];

        foreach ($final_data as $record) {
            $course_id = $record->course_id;
            $semester = $record->semester;
            $subject_type_id = $record->subject_type_id;
            $subject_id = $record->subject_id;

            // Initialize the nested arrays if they don't exist
            if (!isset($new_data_arr[$course_id])) {
                $new_data_arr[$course_id] = [];
            }
            if (!isset($new_data_arr[$course_id][$semester])) {
                $new_data_arr[$course_id][$semester] = [];
            }

            // Store the subject_id in the appropriate location
            if (!isset($new_data_arr[$course_id][$semester][$subject_type_id])) {
                $new_data_arr[$course_id][$semester][$subject_type_id] = [];
            }
            $new_data_arr[$course_id][$semester][$subject_type_id][] = $subject_id;
        }

// Now $new_data_arr contains all the data organized as needed

        // dd($new_data_arr);
       

        // dd($data,$course_mast,$subject_type_mast,$subject_mast);
        return view('Reports/course_subject_report',[
            'final_data'=>$final_data,
            'course_mast'=>$course_mast,
            'subject_type_mast'=>$subject_type_mast,
            'subject_mast'=>$subject_mast,
            'new_data_arr'=>$new_data_arr,
            'subject_type_short_mast'=>$subject_type_short_mast,
            'sem'=>$sem,
        ]);


    }
        public static function course_subject_mapping_report(Request $request){
            // dd($request);

            // $logged_in_user_id = Auth::user()->id;
            // $logged_in_user_role_id = Auth::user()->role_id;
            // if($logged_in_user_role_id == 3) {
            //     $logged_in_user_data = DB::table('user_profile')
            //                                 ->where('users_id', $users_id)
            //                                 ->first();
            //     $college = $logged_in_user_data->college_id;
            // }
            // if(!empty($college)) {

            // }
            // else {
            //     $college = !empty($request->college)?$request->college:null;
            // }
            $college = Auth::user()->college_id;
            // dd($college);
            $course=!empty($request->course)?$request->course:null;
            $semester=!empty($request->semester)?$request->semester:null;
            $subject_type=!empty($request->subject_type)?$request->subject_type:null;
            $subject=!empty($request->subject)?$request->subject:null;
            // dd($subject,$course,$semester,$subject_type,$college);

            if(empty($course)&&empty($semester)&&empty($subject_type)&&empty($subject)) {
                $final_data = [];
            }
            else{
                $data = DB::table('course_subject_mapping')->where('status',1);
                // dd($data);
                if (!empty($college)) {
                    $data->where('college_id', $college);
                }

                if (!empty($course)) {
                    $data->where('course_id', $course);
                }
                if (!empty($semester)) {
                    $data->where('semester', $semester);
                }
                if (!empty($subject_type)) {
                    $data->where('subject_type_id', $subject_type);
                }
                if (!empty($subject)) {
                    $data->where('subject_id', $subject);
                }

                $final_data = $data->orderBy('course_id', 'asc')
                                    ->orderBy('subject_type_id', 'asc')
                                    ->get();
                
            }

            // dd($final_data);


            $college_mast = College::pluckActiveCodeAndName($college);
            $course_mast = Course::getActiveCoursesFromCollege($college);
            $subject_mast = Subject::pluckActiveCodeAndName($college);
            $subject_type_mast = SubjectType::pluckActiveCodeAndName($college);
            // dd($course_mast,$subject_mast,$subject_type_mast);
            return view('Reports/course_subject_mapping_report',[
                'final_data'=>$final_data,
                'college_mast'=>$college_mast,
                'course_mast'=>$course_mast,
                'subject_mast'=>$subject_mast,
                'subject_type_mast'=>$subject_type_mast,

            ]);

        }

     public function student_subject_listing2(Request $request)
    {
        // dd($request);
        
        //added afterwards
        $college_id = Auth::user()->college_id;
        $user_id = Auth::user()->id;
        $role_id = Auth::user()->role_id;
        $faculty_college = '';
        if($role_id == 4) {
            $user_profile_data = User_Profile::getDataFromUsersId($user_id);   
            $college_id = !empty($user_profile_data->college_id)?$user_profile_data->college_id:'';
        }

        $course = !empty($request->course)?$request->course:null;
        $subject_type = !empty($request->subject_type)?$request->subject_type:null;
        $subject = !empty($request->subject)?$request->subject:null;
        $semester = !empty($request->semester)?$request->semester:null;
        // dd($college_id);
        $academic_year=AcademicYear::getLatestYears($college_id);
        // dd($academic_year);

        if($course || $subject_type || $subject || $semester) {
            $data=UserSubjectMapping::getStudentRecords($academic_year,$college_id, $course,$subject_type,$subject,$semester);
        }
        else {
            $data = [];
        }
        // dd($data);
        // $user_profile_mast=User_Profile::getStudentName($data->name);
        $course_mast=Course::pluckActiveCodeAndName($college_id); 
        $subject_type_mast=SubjectType::pluckActiveCodeAndName($college_id);
        $subject_mast=Subject::pluckActiveCodeAndName($college_id,$subject_type);
        // dd($college_id);


        $final_data_arr = [];
        $rowspan_count = [];
        $rowspan_count_2 = [];
        $student_name_arr = [];
        $student_semester_arr = [];
        $student_course_arr = [];
        $final_data_arr1 = [];
        foreach($data as $key => $value) {
            $student_name_arr[$value->user_profile_id] = $value->name;
            $student_semester_arr[$value->user_profile_id] = $value->semester;
            $student_course_arr[$value->user_profile_id] = $value->course_id;
            $rowspan_count[$value->user_profile_id] = (!empty($rowspan_count[$value->user_profile_id])?$rowspan_count[$value->user_profile_id]:0) + 1;
            $rowspan_count_2[$value->user_profile_id][$value->subject_type_id] = (!empty($rowspan_count_2[$value->user_profile_id][$value->subject_type_id])?$rowspan_count_2[$value->user_profile_id][$value->subject_type_id]:0) + 1;

            // $final_data_arr[$value->user_profile_id][$value->course_id][$value->subject_type_id][] = !empty($subject_mast[$value->subject_id])?($subject_mast[$value->subject_id].'('.date('d/M/Y',strtotime($value->created_at)).')'):NULL;
            $final_data_arr1[$value->user_profile_id][$value->course_id][$value->subject_type_id][ !empty($subject_mast[$value->subject_id])?($subject_mast[$value->subject_id].'('.date('d/M/Y',strtotime($value->created_at)).')'):0][] = $value->preference;
        }
        // dd($rowspan_count_2,$rowspan_count);

// dd($final_data_arr1);
        return view('Reports/student_subject_listing2',[
            'data'=>$data,
            'course_mast'=>$course_mast,
            'college_id'=>$college_id,
            'subject_type_mast'=>$subject_type_mast,
            'subject_mast'=>$subject_mast,
            'student_name_arr' => $student_name_arr,
            'rowspan_count' => $rowspan_count,
            'final_data_arr' => $final_data_arr1,
            'student_semester_arr' => $student_semester_arr,
            'student_course_arr' => $student_course_arr,
            'rowspan_count_2' => $rowspan_count_2,
        ]);

    }


    public function student_subject_listing2PDF(Request $request)
    {
        // dd($request);
        ini_set('max_execution_time', '0');
        //added afterwards
        $college_id = !empty($request->college_id)?$request->college_id:null;
        $user_id = Auth::user()->id;
        $role_id = Auth::user()->role_id;
        $faculty_college = '';
        if($role_id == 4) {
            $user_profile_data = User_Profile::getDataFromUsersId($user_id);
            $college_id = !empty($user_profile_data->college_id)?$user_profile_data->college_id:'';
        }

        $course = !empty($request->course)?$request->course:null;
        $subject_type = !empty($request->subject_type)?$request->subject_type:null;
        $subject = !empty($request->subject)?$request->subject:null;
        $semester = !empty($request->semester)?$request->semester:null;
        // dd($college_id,$course,$subject_type,$subject,$semester);
        $data=UserSubjectMapping::getStudentRecords($college_id, $course,$subject_type,$subject,$semester);
        // dd($data);
        // $user_profile_mast=User_Profile::getStudentName($data->name);
        $course_mast=Course::pluckActiveCodeAndName($college_id); 
        $subject_type_mast=SubjectType::pluckActiveCodeAndName($college_id);
        $subject_mast=Subject::pluckActiveCodeAndName($college_id);
        // dd($data);

        $final_data_arr = [];
        $rowspan_count = [];
        $student_name_arr = [];
        $student_semester_arr = [];
        $student_course_arr = [];
        foreach($data as $key => $value) {
            $student_name_arr[$value->user_profile_id] = $value->name;
            $student_semester_arr[$value->user_profile_id] = $value->semester;
            $student_course_arr[$value->user_profile_id] = $value->course_id;
            $rowspan_count[$value->user_profile_id] = (!empty($rowspan_count[$value->user_profile_id])?$rowspan_count[$value->user_profile_id]:0) + 1;
            $rowspan_count_2[$value->user_profile_id][$value->subject_type_id] = (!empty($rowspan_count_2[$value->user_profile_id][$value->subject_type_id])?$rowspan_count_2[$value->user_profile_id][$value->subject_type_id]:0) + 1;

            $final_data_arr[$value->user_profile_id][$value->course_id][$value->subject_type_id][] = $value->subject_id;
        }
        // dd($rowspan_count_2,$rowspan_count);

// dd($final_data_arr);
        $pdf = \PDF::loadView('pdf.student_subject_listing2',[
            'data'=>$data,
            'course_mast'=>$course_mast,
            'college_id'=>$college_id,
            'subject_type_mast'=>$subject_type_mast,
            'subject_mast'=>$subject_mast,
            'student_name_arr' => $student_name_arr,
            'rowspan_count' => $rowspan_count,
            'final_data_arr' => $final_data_arr,
            'student_semester_arr' => $student_semester_arr,
            'student_course_arr' => $student_course_arr,
            'rowspan_count_2' => $rowspan_count_2,
        ]);

             $pdf->setPaper('A4', 'potrait');
             return $pdf->stream('Student Subject Listing.pdf');
        }
        


    public static function mapping_count_report(Request $request) {

        $college_id = !empty($request->college_id)?$request->college_id:null;
        $user_id = Auth::user()->id;
        $role_id = Auth::user()->role_id;
        $faculty_college = '';
        if($role_id == 4) {
            $user_profile_data = User_Profile::getDataFromUsersId($user_id);
            $college_id = !empty($user_profile_data->college_id)?$user_profile_data->college_id:'';
        }

        $course = !empty($request->course)?$request->course:[];
        $semester = !empty($request->semester)?$request->semester:null;


        $course_mast = Course::getCoursesFromCollege($college_id, $course);

    $distinctCourseIds = UserSubjectMapping::distinct()->pluck('course_id')->toArray();


    // Fetch course names based on course IDs
    $course_arr = Course::whereIn('id', $distinctCourseIds)->pluck('name', 'id');

    $student_counts = [];

    // Fetch distinct data grouped by course_id
    $distinctData = UserSubjectMapping::select('course_id', 'user_profile_id')->distinct()->get()->groupBy('course_id');

    foreach ($distinctData as $courseId => $userProfileIds) {
        // Assuming you have a 'users' table that stores student names
        $studentNames = User_Profile::whereIn('id', $userProfileIds->pluck('user_profile_id'))->pluck('name');

        $student_counts[$courseId] = [
            'count' => count($studentNames),
            'students' => $studentNames->toArray(),
        ];
    }
    // dd($distinctData);

    return view('Reports/mapping_count_report', [
        'distinctData' => $distinctData,
        'course_arr' => $course_arr,
        'student_counts' => $student_counts,
    ]);
    }

    public static function student_subject_mapping_report(Request $request)
    {
        // dd($request);
        $college_id = !empty($request->college_id)?$request->college_id:null;
        $course = !empty($request->course)?$request->course:null;
        $approval_status = !empty($request->approval_status)?$request->approval_status:null;
        $subject_type = !empty($request->subject_type)?$request->subject_type:null;
        $subject = !empty($request->subject)?$request->subject:null;
        $semester = !empty($request->semester)?$request->semester:null;
        // $data=UserSubjectMapping::getStudentRecords($college_id, $course,$subject_type,$subject,$semester);
        $data = UserSubjectMapping::getStudentRecordsWithApproval($college_id, $course, $subject_type, $subject, $semester,$approval_status);



        if (is_array($data) || is_object($data)) {
            // dd('hi');
            foreach ($data as $record) {
                // dd($record);
                switch ($record['approval_status']) {
                    case 1:
                        $record['approval_status'] = 'Approved';
                        break;
                    case 2:
                        $record['approval_status'] = 'Disapproved';
                        break;
                    default:
                        $record['approval_status'] = 'Pending';
                        break;
                }
            }
        }
        else {
            // Handle the case where $data is not iterable (e.g., it's `false` or `null`)
            dd('ashd');
        }
        // Now, $data contains the updated approval_status values.
        // dd($users_mapped_arr);
        $user_name = User_Profile::pluckCodeAndName();

        $course_mast=Course::pluckActiveCodeAndName($college_id); 
        $subject_type_mast=SubjectType::pluckActiveCodeAndName($college_id);
        $subject_mast=Subject::pluckActiveCodeAndName($college_id);

        // dd($user_name);
        $approval_status = $request->input('approval_status');
        // dd($data);



                return view('Reports/student_subject_mapping_report', [
                        'data' => $data,
                        'course_mast' => $course_mast,
                        'subject_type_mast' => $subject_type_mast,
                        'subject_mast' => $subject_mast,
                        'approval_status' => $approval_status,
                        'user_name' => $user_name,

            ]);
    }



    public static function mapping_defaulters(Request $request) {
        
        // $college_id = !empty($request->college_id)?$request->college_id:null;
        $user_id = Auth::user()->id;
        $role_id = Auth::user()->role_id;

        $user_profile_data = User_Profile::getDataFromUsersId($user_id);
        // $college_id = !empty($user_profile_data->college_id)?$user_profile_data->college_id:'';
        $gender = null;
        $category = null;
        $semester = !empty($request->semester)?$request->semester:null;
        $college_id = Auth::user()->college_id;

        $course=!empty($request->course)?$request->course:null;
        $mobile_number=!empty($request->mobile_number)?$request->mobile_number:null;
        $form_number=!empty($request->form_number)?$request->form_number:null;
       
        $academic_year=AcademicYear::getLatestYear($college_id);
        $data = User_Profile::getStudentRecords($course,$semester,$gender, $category, $mobile_number,$form_number,$college_id);
        $mapped_user_profile_id_arr = UserSubjectMapping::get_mapped_users($college_id);
        // dd($data,$mapped_user_profile_id_arr)''
        // dd($data,$course,$gender, $category, $mobile_number,$form_number,$college);
        // dd($mapped_user_profile_id_arr);
       // dd($data->take(10));

        // $arr_cus_def = [];
        // foreach($data as $key => $value) {
        //     if(!in_array($value->id, $mapped_user_profile_id_arr)) {
        //         $arr_cus_def[] = $value->email;
        //     }
        // }
        // dd($arr_cus_def);

        $course_mast =Course::pluckActiveCodeAndName();
        // dd($course_mast);
       // dd($course_mast,$user_profile_data,$data);
        return view('Reports/mapping_defaulters',[
            'data' => $data,
            'course_mast' => $course_mast,
            'mapped_user_profile_id_arr' => $mapped_user_profile_id_arr
        ]);
    }


     public function approve_students_report(Request $request)
    {
        // dd($request);
        
        //added afterwards
        $college = Auth::user()->college_id;
        $college_id = $college;
        $user_id = Auth::user()->id;
        $role_id = Auth::user()->role_id;
        $faculty_college = '';
        if($role_id == 4) {
            $user_profile_data = User_Profile::getDataFromUsersId($user_id);
            $college_id = !empty($user_profile_data->college_id)?$user_profile_data->college_id:'';
        }
        
        $user_profile_arr = User_Profile::pluckEnrollmentNo();
            // dd($college_id);
        $course = !empty($request->course)?$request->course:null;
        $subject_type = !empty($request->subject_type)?$request->subject_type:null;
        $subject = !empty($request->subject)?$request->subject:null;
        $user_profile_id = !empty($request->student)?$request->student:null;
        $semester = !empty($request->semester)?$request->semester:null;
        $filter=!empty($request->filter)?$request->filter:null;
        $session_flash = !empty($request->session_flash)?$request->session_flash:0;
        $student= User_Profile::getAllRecordsOfstudents($college);
        $academic_year_value = !empty($request->academic_year)?$request->academic_year:null;
        // dd($student);
        // dd($college_id,$course,$subject_type,$subject,$semester);
        // dd($data);
        // $user_profile_mast=User_Profile::getStudentName($data->name);

        $course_mast=Course::pluckActiveCodeAndName($college_id); 
        $subject_type_mast=SubjectType::pluckActiveCodeAndName($college_id);
        $subject_mast=Subject::pluckActiveCodeAndName($college_id,$subject_type);
        $academic_year = AcademicYear::pluckDataFromCollege($college);
        // dd($academic_year);

        // dd($subject_mast, $subject_type);
        if($course == null && $subject_type==null && $subject==null && $user_profile_id ==null) {
            if($session_flash) {
                Session::flash('error', 'Select atleast one filter from course, student, subject_type, subject');
            }
            return view('Reports/approve_students_report',[
                'data'=>[],
                'course_mast'=>$course_mast,
                'college_id'=>$college_id,
                'subject_type_mast'=>$subject_type_mast,
                'subject_mast'=>$subject_mast,
                'student'=>$student,
                'student_name_arr' => [],
                'rowspan_count' => [],
                'final_data_arr' => [],
                'student_semester_arr' => [],
                'student_course_arr' => [],
                'academic_year_value' => $academic_year_value,
                'academic_year' => $academic_year,
                'student_enrolment_no_arr' => []
            ]);
        }
        $data=UserApprovedSubjects::getStudentApprovedWithFilter($college_id, $course,$subject_type,$subject,$semester,$user_profile_id,$academic_year_value,$filter);
        // dd($college_id, $course,$subject_type,$subject,$semester,$user_profile_id,$academic_year_value,$filter);
        // dd($data);
        // foreach ($data as $key => $value) {
        //     $csas_no[$value->id] = !empty($user_profile_arr[$value->user_profile_id])?$user_profile_arr[$value->user_profile_id]:$value->user_profile_id;
        // }
        // dd($user_profile_arr,$csas_no);
        // dd($csas_no);
        $final_data_arr = [];
        $rowspan_count = [];
        $student_name_arr = [];
        $student_semester_arr = [];
        $student_course_arr = [];
        $student_contact_no = [];
        $student_email = [];
        $student_enrolment_no_arr = [];
        $approval_date_arr =[];
        $student_college_roll_no_arr = [];
        $student_csas_form_no_arr = [];
        $student_examination_roll_no_arr = [];
        foreach($data as $key => $value) {
            $student_name_arr[$value->user_profile_id] = $value->name;
            $student_enrolment_no_arr[$value->user_profile_id] = !empty($value->enrolment_no)?$value->enrolment_no:'-';
            $student_college_roll_no_arr[$value->user_profile_id] = !empty($value->college_roll_no)?$value->college_roll_no:'-';
            $student_contact_no[$value->user_profile_id] = $value->contact_no;
            $student_email[$value->user_profile_id] = $value->email;
            $student_semester_arr[$value->user_profile_id] = $value->semester;
            $student_course_arr[$value->user_profile_id] = $value->course_id;
            $rowspan_count[$value->user_profile_id] = (!empty($rowspan_count[$value->user_profile_id])?$rowspan_count[$value->user_profile_id]:0) + 1;
            $final_data_arr[$value->user_profile_id][$value->subject_type_id][] = $value->subject_id;
            $approval_date_arr[$value->user_profile_id][$value->subject_type_id][$value->subject_id] = !empty($value->approved_at)?date('d-M-Y', strtotime($value->approved_at)):'-';

            $student_csas_form_no_arr[$value->user_profile_id] = !empty($value->csas_form_number)?$value->csas_form_number:'-';
            $student_examination_roll_no_arr[$value->user_profile_id] = !empty($value->examination_roll_no)?$value->examination_roll_no:'-';
        }

        // dd($approval_date_arr);
        return view('Reports/approve_students_report',[
            'data'=>$data,
            'course_mast'=>$course_mast,
            'subject_type_mast'=>$subject_type_mast,
            'subject_mast'=>$subject_mast,
            'student_name_arr' => $student_name_arr,
            'rowspan_count' => $rowspan_count,
            'final_data_arr' => $final_data_arr,
            'student_semester_arr' => $student_semester_arr,
            'student_course_arr' => $student_course_arr,
            'student_contact_no' => $student_contact_no,
            'student_email' => $student_email,
            'student_enrolment_no_arr' => $student_enrolment_no_arr,
            'college_id' => $college_id,
            'academic_year' => $academic_year,
            'academic_year_value' => $academic_year_value,
            'approval_date_arr' => $approval_date_arr,
            'student'=>$student,
            'student_college_roll_no_arr' => $student_college_roll_no_arr,
            'student_csas_form_no_arr' => $student_csas_form_no_arr,
            'student_examination_roll_no_arr' => $student_examination_roll_no_arr,

        ]);

    }

    public function students_subject_approved_report(Request $request){

        // dd(2132);
        // dd($request);
        $user_data=Auth::user();
        $college_id = $user_data->college_id;
        $user_id = $user_data->id;
        $role_id = $user_data->role_id;
        
        $course = !empty($request->course)?$request->course:null;
        $subject_type = !empty($request->subject_type)?$request->subject_type:null;
        $subject = !empty($request->subject)?$request->subject:null;
        $user_profile_id = !empty($request->student)?$request->student:null;
        $semester = !empty($request->view_for_sem)?$request->view_for_sem:null;
        $subject_type_mast=SubjectType::pluckActiveCodeAndName($college_id);
        $subject_mast=Subject::pluckActiveCodeAndName($college_id,$subject_type);

        $course_mast=Course::pluckActiveCodeAndName($college_id);
        $student_mast= User_Profile::getAllRecordsOfstudents($college_id);
        // dd($college_id, $course,$subject_type,$subject,$semester,$user_profile_id);
        $final_data_arr = [];
        $rowspan_count = [];
        $student_name_arr = [];
        $student_semester_arr = [];
        $student_course_arr = [];
        $student_contact_no = [];
        $student_email = [];
        $student_enrolment_no_arr = [];
        $approval_date_arr =[];
        if($request->find && empty($course) && empty($semester) && empty($user_profile_id))
        {
            Session::flash('error', 'You need to select atleast one filter');
    
    
            return view('Reports/students_subject_approved_report',[
                'course'=>$course,
                'student_mast'=>$student_mast,
                'college_id'=>$college_id,
                'subject_type'=>$subject_type,
                'subject_type_mast'=>$subject_type_mast,
                'subject'=>$subject,
                'student'=>!empty($request->student)?$request->student:'',
                'subject_mast'=>$subject_mast,
                'student_name_arr' => $student_name_arr,
                'rowspan_count' => $rowspan_count,
                'final_data_arr' => $final_data_arr,
                'student_enrolment_no_arr' => $student_enrolment_no_arr,
                'college_id' => $college_id,
                'user_profile_id'=>$user_profile_id,
                'semester'=>$semester,
                'course_mast'=>$course_mast,
            ]
        );
        }
        elseif($request->find){


            $data=UserApprovedSubjects::getStudentApproved($college_id, $course,$subject_type,$subject,$semester,$user_profile_id);
        

 // dd($csas_no);
//  dd($data);
       
        
        foreach($data as $key => $value) 
        {
// dd($value);
            $student_name_arr[$value->user_profile_id] = $value->name;
            $student_enrolment_no_arr[$value->user_profile_id] = $value->enrolment_no;
            
            
            
            
            $student_subject_type[$value->user_profile_id] = $value->subject_type_id;
            $rowspan_count[$value->user_profile_id] = (!empty($rowspan_count[$value->user_profile_id])?$rowspan_count[$value->user_profile_id]:0) + 1;
            $final_data_arr[$value->user_profile_id][$value->subject_type_id][] = $value->subject_id;
        }
        // $student= User_Profile::getAllRecordsOfstudents($college);
        // dd($student);
        // dd($college_id,$course,$subject_type,$subject,$semester);
        // dd($data);
        // $user_profile_mast=User_Profile::getStudentName($data->name);
    }
    
  
        return view('Reports/students_subject_approved_report',[
            'course'=>$course,
            'student_mast'=>$student_mast,
            'college_id'=>$college_id,
            'subject_type'=>$subject_type,
            'subject_type_mast'=>$subject_type_mast,
            'subject'=>$subject,
            'student'=>!empty($request->student)?$request->student:'',
            'subject_mast'=>$subject_mast,
            'student_name_arr' => $student_name_arr,
            'rowspan_count' => $rowspan_count,
            'final_data_arr' => $final_data_arr,
            // 'student_semester_arr' => $student_semester_arr,
            // 'student_course_arr' => $student_course_arr,
            // 'student_contact_no' => $student_contact_no,
            // 'student_email' => $student_email,
            'student_enrolment_no_arr' => $student_enrolment_no_arr,
            'college_id' => $college_id,
            // 'approval_date_arr' => $approval_date_arr,
            'user_profile_id'=>$user_profile_id,
            'semester'=>$semester,
            'course_mast'=>$course_mast,
        ]
    );

    }


    public static function my_subject_report(Request $request){
        // dd($request);
        $users_id = Auth::user()->id;
        $user=User_Profile::pluckUser($users_id);
        $course_id= $user->course_id;
        $college_id= $user->college_id;
        $user_profile_id = $user->id;
        // dd($user_profile_id);
        $academic_year=DB::table('academic_year_mast')->where('status',1)->where('college_id',$college_id)->orderBy('start_year','desc')->pluck('start_year')->first();
        // dd($academic_year);
        $semester= $user->semester; //user ke semester
        $semesters=!empty($request->semester)?$request->semester:$semester; //filter ka semester
        
        $semester_in_course=Course::pluckSemester($course_id);  //course ka semester
        $subjects=UserApprovedSubjects::getApprovedSubjects($academic_year,$user_profile_id,$semesters);
        // dd($subjects);
        $subject_type_mast=SubjectType::pluckActiveCodeAndName($college_id);
        $subject_mast=Subject::pluckActiveCodeAndName($college_id);
        // $upc_mast=Subject::pluckUpcCode();
        // dd($upc_mast);
        $alloted_sections = [];
        if(count($subjects)>0){
        foreach($subjects as $key =>$value){
              $subject_arr[$value->subject_type_id][]=$value->subject_id;
              $alloted_sections[$value->subject_id] = !empty($value->section)?$value->section:'';
           }
        }
        else{
            
            $subject_arr=[];
        }
        // dd($subject_arr);
        return view('Reports/my_subject_report',[
            'subject_arr'=>$subject_arr,
            'subject_type_mast'=>$subject_type_mast,
            'subject_mast'=>$subject_mast,
            // 'upc_mast'=>$upc_mast,
            'semester_in_course'=>$semester_in_course,
            'semester'=>$semester,
            'alloted_sections' => $alloted_sections
        ]);

    }
    public static function student_in_subject_count(Request $request){
        // dd($request);
        $semesters=!empty($request->semester)?$request->semester:null;
        $subject_type=!empty($request->subject_type)?$request->subject_type:null;
        $subject=!empty($request->subject)?$request->subject:null;
        // dd($subject);
        $users_id = Auth::user()->id;
        $user=User_Profile::pluckUser($users_id);
        $college_id= $user->college_id;
        $subject_mast=Subject::pluckActiveCodeAndName($college_id);
        $subject_type_mast=SubjectType::pluckActiveCodeAndName($college_id);
        $subject_arr=[];
        $section_arr=[];

        if(!empty($semesters)){
        $subjects=UserApprovedSubjects::where('status',1)
                                    ->where('college_id',$college_id)  
                                    ->where('semester',$semesters)
                                    ->where('approval_status',1)
                                    // ->groupBy('subject_id')
                                    // ->groupBy('section')
                                    // ->orderBy('section','asc')->count('user_profile_id')
                                    // ->get()
                                    ;
        // dd($subjects);
                if(!empty($subject_type)){
                $subjects->where('subject_type_id',$subject_type);
                }
                if(!empty($subject)){
                $subjects->where('subject_id',$subject);
}       
                $subjects=$subjects->orderBy('subject_id')->orderBy('section')->get(); 
                // dd($subjects);
        


if($subject){
                if(count($subjects)>0){
        foreach($subjects as $key =>$value){
            // dd($value);
            if(!empty($section_arr[$value->section])){

              $section_arr[$value->section] += 1;
            }
            else{
              $section_arr[$value->section] = 1;

            }
            $subject_arr[$value->subject_id]=$section_arr;

          
           }
        }
        else{
            $subject_arr=[];
        }
    } else{
        if(count($subjects)>0){
            foreach($subjects as $key =>$value){
                if(!empty($subject_arr[$value->subject_id])){
                if(!empty($subject_arr[$value->subject_id][$value->section])){

                
                    $subject_arr[$value->subject_id][$value->section] += 1;
                }else{
                    $subject_arr[$value->subject_id][$value->section]=1;
                }
              
                 
                 }else{
                $subject_arr[$value->subject_id][$value->section]=1;

               }

            }

            }
            else{
                $subject_arr=[];
            }
    }
// } 
    }
    
        // dd($subject_arr);
        return view('Reports/student_in_subject_count',[
            'subject_arr'=>$subject_arr,
            'subject_mast'=>$subject_mast,
            'college_id'=>$college_id,
            'subject_type_mast'=>$subject_type_mast,
        ]);

    }


        // dd($request);
        public static function student_in_subject_coursewise_count(Request $request){
        // dd($request);
        $semesters=!empty($request->semester)?$request->semester:null;
        $subject_type=!empty($request->subject_type)?$request->subject_type:null;
        $subject=!empty($request->subject)?$request->subject:null;
        $users_id = Auth::user()->id;
        $user=User_Profile::pluckUser($users_id);
        $college_id= $user->college_id;
        $subject_arr=[];
        $subject_mast=Subject::pluckActiveCodeAndName($college_id);
        $subject_type_mast=SubjectType::pluckActiveCodeAndName($college_id);
        $course_mast=Course::pluckActiveCodeAndName($college_id);
        $heading_arr=[];
        $content_arr=[];
        $column_arr=[];

        if(!empty($semesters)){
        $subjects=UserApprovedSubjects::getAllApprovedData($college_id,$semesters,$subject_type,$subject);
        // dd($subjects);
        if(count($subjects)>0){
        foreach($subjects as $key =>$value){
        // $subject_arr[$value->subject_id][$value->course_id][]=$value->approval_status;
            
            if(!empty($subject_arr[$value->subject_id][$value->course_id])){
                    $subject_arr[$value->subject_id][$value->course_id] += 1;
                }
             else{
                    $subject_arr[$value->subject_id][$value->course_id] = 1;

              }
            
          }
        }
        else{
            $subject_arr=[];
        }
    }
        // dd($subject_arr);
        return view('Reports/student_in_subject_coursewise_count',[
            'subject_arr'=>$subject_arr,
            'subject_mast'=>$subject_mast,
            'course_mast'=>$course_mast,
            'college_id'=>$college_id,
            'subject_type_mast'=>$subject_type_mast,
            'semesters' => $semesters,
            'column_arr' => $column_arr,
            'heading_arr' => $heading_arr,
            'content_arr' => $content_arr,
        ]);

    }

        // dd($academic_year);
    public static function section_allotment(Request $request)
    {
        $semesters=!empty($request->semester)?$request->semester:null;
        $subject_type=!empty($request->subject_type)?$request->subject_type:null;
        $subject=!empty($request->subject)?$request->subject:null;
        
        $users_data = Auth::user();
        $users_id = $users_data->id;
        // $user = User_Profile::pluckUser($users_id);
        $college_id= $users_data->college_id;
        
        $academic_year = DB::table('academic_year_mast')
                            ->where('college_id',$college_id)
                            ->where('status',1)
                            ->orderBy('start_year','desc')
                            ->pluck('start_year')
                            ->first();
        
        $subject_arr=[];
        $subject_mast=Subject::pluckActiveCodeAndName($college_id);
        $subject_type_mast=SubjectType::pluckActiveCodeAndName($college_id);
        $course_mast=Course::pluckActiveCodeAndName($college_id);
        
        $section_mast=[];


        $user_approved_arr = [];
        $section_allotment_arr = [];

        if(!empty($semesters)){ //filter set hue hain
            // $user_approved_data = UserApprovedSubjects::getDataForAttendance($academic_year,$college_id,$semesters,$subject_type,$subject);
            // $user_approved_data = UserApprovedSubjects::getUsersForSectionAndGroup($college_id,$subject_type,$semesters,'',$academic_year,$subject,2);
            // dd($user_approved_data);
            // dd($subjects);


            $user_approved_arr = UserApprovedSubjects::selectRaw("course_id, count(*) as counter")
                                                        ->where('college_id', $college_id)
                                                        ->where('academic_year', $academic_year)
                                                        ->where('subject_id', $subject)
                                                        ->where('semester', $semesters)
                                                        ->where('status', 1)
                                                        ->groupBy('course_id')
                                                        // ->get()->toArray();
                                                        ->pluck('counter', 'course_id')->toArray();
            // dd($user_approved_arr);


            //get data from section allotment mast
            $section_allotment_arr = SectionAllotment::where('college_id', $college_id)
                                                        ->where('academic_year', $academic_year)
                                                        ->where('subject_id', $subject)
                                                        ->where('semester', $semesters)
                                                        ->where('status',1)
                                                        ->pluck('section', 'course_id')
                                                        ->toArray();
            // dd($section_allotment_arr);


            // dd(count($user_approved_data));
            // if(count($user_approved_data)>0) {
            //     $section_mast=DB::table('section_mast')
            //                     ->where('college_id',$college_id)
            //                     ->where('academic_year',$academic_year)
            //                     ->where('subject_id',$subject)
            //                     ->where('semester',$semesters)
            //                     ->where('status',1)
            //                     ->pluck('name','id');

                // if(!empty($college_id)){
                //     $section;
                // }
                // if(!empty($academic_year)){
                //     $section->;
                // }
                // if(!empty($subject)){
                //     $section;
                // }
                // if(!empty($semesters)){
                //     $section;
                // }
                // $section_mast=$section;

                // $alloted = [];
                // $non_alloted = [];

                // foreach($user_approved_data as $key => $value) {
                //     if(!empty($value->section)) {
                //         if(!empty($section_allotment_arr[$value->course_id][$value->section])){
                //             $section_allotment_arr[$value->course_id][$value->section] += 1;
                //         }
                //         else{
                //             $section_allotment_arr[$value->course_id][$value->section] = 1;
                //         }
                //     }
                //     else {
                //         if(!empty($non_alloted[$value->course_id])){
                //             $non_alloted[$value->course_id][] += 1;
                //         }
                //         else{
                //             $non_alloted[$value->course_id][$value->section] = 1;
                //         }   
                //     }
                // }

                // foreach($user_approved_data as $key =>$value){
                //     if(!empty($subject_arr[$value->subject_id][$value->course_id])){
                //         $subject_arr[$value->subject_id][$value->course_id] += 1;
                //     }
                //     else{
                //         $subject_arr[$value->subject_id][$value->course_id] = 1;
                //     }
                // }
                // foreach($subjects as $key =>$value){
                //     $academic_year=$value->academic_year;
                //     $college_id=$value->college_id;
                //     $semester=$value->semester;
                //     $subject_id=$value->subject_id;
                //     $section_allotment_arr=SectionAllotment::getAllotedSections($academic_year,$college_id,$semester,$subject_id);
                // }
            // }
            // else{
            //     $subject_arr=[];
            //     $section_allotment_arr=[];
            // }
        }

        // dd($section_allotment_arr);
        return view('Reports/section_allotment',[
            'subject_arr'=>$subject_arr,
            'section_allotment_arr'=>$section_allotment_arr,
            'subject_mast'=>$subject_mast,
            'course_mast'=>$course_mast,
            'section_mast'=>$section_mast,
            'college_id'=>$college_id,
            'subject_type_mast'=>$subject_type_mast,
            'semesters' => $semesters,
            'user_approved_arr' => $user_approved_arr,
            'section_allotment_arr' => $section_allotment_arr,
            'subject' => $subject
        ]);

    }
     public static function student_section_listing_report(Request $request){
        // dd($request);
        $subject_id = !empty($request->subject) ? $request->subject : null;
        $semester = !empty($request->semester) ? $request->semester : null;
        $section = !empty($request->section) ? $request->section : null;
        $users_id = Auth::user()->id;
        $college_id = User_Profile::pluckCollegeId($users_id);
        $course_arr = Course::getActiveCoursesFromCollege($college_id);
        $subject_mast = Subject::pluckActiveCodeAndName($college_id);
        $academic_year = AcademicYear::getLatestYears($college_id);
        

                


        if ($subject_id !== null && $semester !== null && $section !== null) {
            $user_profile_data = UserSubjectMapping::getUserIdFromCollegeSemesterSubjectSection($college_id, $subject_id, $semester, $section);
            $user_data = User_Profile::where('status', 1)->whereIn('id', $user_profile_data)->get();
           
            // dd($course_arr);

            // dd($user_profile_data, $user_data);
            // dd('if');
            // $arr_cus = [];
            // foreach($user_data as $key => $value) {
            //     $arr_cus[] = $value->id;
            // }

            // dd($user_profile_data, $user_data, array_diff($user_profile_data, $arr_cus));

            return view('Reports/student_section_listing_report', [
                'course_arr' => $course_arr,
                'subject_mast' => $subject_mast,
                'academic_year'=>$academic_year,
                'user_data' => $user_data,
                'college_id' => $college_id,
            ]);
        } else {
            
            // dd('else');
            return view('Reports/student_section_listing_report', [
                'course_arr' => $course_arr,
                'user_data'=>[],
                'academic_year'=>$academic_year,
                'subject_mast' => $subject_mast,
                'college_id' => $college_id,
            ]);
        }

     }
     public static function update_subject_userwise_report(Request $request){
        // dd($request);
        $academic_years = !empty($request->academic_year) ? $request->academic_year : null;
        $college_id = !empty($request->college_id) ? $request->college_id : null;
        $course_id = !empty($request->course_id) ? $request->course_id : null;
        $semester = !empty($request->semester) ? $request->semester : null;
        $subject_type_id = !empty($request->subject_type_id) ? $request->subject_type_id : null;
        $student = !empty($request->student) ? $request->student : null;
        $time = 20;


        $data = UserSubjectMapping::getDataFromStudentAcademicSubjectTypeSemesterAndCourse($college_id,$subject_type_id,$semester,$course_id,$student,$academic_years);
        // dd($subject_ids);
        $users_id = Auth::user()->id;
        $college_id = User_Profile::pluckCollegeId($users_id);
        $academic_year = AcademicYear::getLatestYear($college_id);
        $course_mast = Course::pluckActiveCodeAndName($college_id);
        $subject_type_mast = SubjectType::pluckActiveCodeAndName($college_id);
        $subject_mast=Subject::pluckActiveCodeAndName($college_id);
        $college_mast = College::pluckActiveCodeAndName($college_id);
        // dd($academic_years);
        // dd(count($data),$data);
        if($data){

        $subject_ids = Subject::pluckActiveCodeAndName($college_id,$subject_type_id);
        $subject_arr = Subject::whereIn('id',$data)->pluck('subject_name','id');
        return view('Reports/update_subject_userwise_report',[
            'college_id' => $college_id,
            'course_mast'=>$course_mast,
            'subject_arr'=>$subject_arr,
            'subject_ids'=>$subject_ids,
            'college_mast' => $college_mast,
            'academic_year' => $academic_year,
            'time'=>60,
            'subject_type_mast'=>$subject_type_mast,
            'subject_mast' => $subject_mast,
                ]);
        }
       

         return view('Reports/update_subject_userwise_report',[
        'college_id' => $college_id,
        'course_mast'=>$course_mast,
        'subject_arr'=>[],
        'subject_ids'=>[],
        'time'=>$time,
        'college_mast' => $college_mast,
        'academic_year' => $academic_year,
        'subject_type_mast'=>$subject_type_mast,
        'subject_mast' => $subject_mast,
            ]);
       
        // dd($data,$subject_arr);

        // dd($academic_year);

     }
     public static function sectionwise_student_allotment(Request $request)

    {
        // dd($request);
        $sections=!empty($request->section)?$request->section:null;
        // dd($request,$sections);
        $semesters=!empty($request->semester)?$request->semester:null;
        $subject_type=!empty($request->subject_type)?$request->subject_type:null;
        $subject=!empty($request->subject)?$request->subject:null;
        $users_id = Auth::user()->id;
        $user=User_Profile::pluckUser($users_id);
        $college_id= $user->college_id;
        $academic_year=AcademicYear::getLatestYears($college_id);
        $course_id=!empty($request->course)?$request->course:null;
        $subject_arr=[];
        $subject_mast=Subject::pluckActiveCodeAndName($college_id);
        
        $subject_type_mast=SubjectType::pluckActiveCodeAndName($college_id);
        $course_mast=Course::pluckActiveCodeAndName($college_id);
        $section_mast=[];
        // dd($semesters);
        
        $userprofileid = UserApprovedSubjects::getStudentsFromStudentAcademicSubjectTypeSemesterAndCourse($college_id,$subject_type,$semesters,$course_id,$academic_year,$subject,$sections);
        // dd($college_id,$subject_type,$semesters,$course_id,$academic_year,$subject,$sections);
        // dd($userprofileid);
        $user_data_arr = UserApprovedSubjects::getUsersForSectionAndGroup($college_id, $subject_type, $semesters,$course_id,$academic_year,$subject,$sections);


         // $user_data_arr = UserSubjectMapping::getUsersForSectionAndGroup($college_id, $subject_type, $semesters,$course_id,$academic_year,$subject);

        //  dd($user_data_arr);
       $data = [];
        if ($subject !== null) {
        $data = User_Profile::whereIn('id',$userprofileid)->orderBy('name', 'asc')->get();
        // dd($data);
        }
        // dd($request,$college_id,$academic_year,$data);
        // dd($data,$userprofileid,$user_data_arr, array_unique($userprofileid));
        // $thirtynine_array = [];
        // foreach($data as $key => $value) {
        //     // if(!in_array($value->id,$userprofileid)) {
        //     //     dd($value);
        //     // }
        //     $thirtynine_array[] = $value->id;
        // }

        // foreach($userprofileid as $value) {
        //     if(!in_array($value,$thirtynine_array)) {
        //         dd($value);
        //     }
        // }

        // dd(123);
        if(!empty($semesters)){
        $subjects=UserApprovedSubjects::getDataForAttendance($academic_year,$college_id,$semesters,$subject_type,$subject);
        // dd($subjects);
        if(count($subjects)>0){

            $section=DB::table('section_mast')->where('status',1);
        if(!empty($college_id)){
            $section->where('college_id',$college_id);
        }
        if(!empty($academic_year)){
            $section->where('academic_year',$academic_year);
        }
        if(!empty($subject)){
            $section->where('subject_id',$subject);
        }
        if(!empty($semesters)){
            $section->where('semester',$semesters);
        }
        $section_mast=$section->pluck('name','id');

         // dd($section_mast );
        foreach($subjects as $key =>$value){    
            if(!empty($subject_arr[$value->subject_id][$value->course_id])){
                    $subject_arr[$value->subject_id][$value->course_id] += 1;
                }
             else{
                    $subject_arr[$value->subject_id][$value->course_id] = 1;

              }
            
          }
        foreach($subjects as $key =>$value){
            $academic_year=$value->academic_year;
            $college_id=$value->college_id;
            $semester=$value->semester;
            $subject_id=$value->subject_id;
            $section_allotment_arr=SectionAllotment::getAllotedSections($academic_year,$college_id,$semester,$subject_id);

        }
        }
        else{
            $subject_arr=[];
            $section_allotment_arr=[];
        }
    }
        else{
            $section_allotment_arr=[];
        }
        // dd($section_allotment_arr);
        return view('Reports/sectionwise_student_allotment',[
            'subject_arr'=>$subject_arr,
            'section_allotment_arr'=>$section_allotment_arr,
            'subject_mast'=>$subject_mast,
            'data'=> $data,
            'user_data_arr'=>$user_data_arr,
            'course_mast'=>$course_mast,
            'section_mast'=>$section_mast,
            'college_id'=>$college_id,
            'subject_type_mast'=>$subject_type_mast,
            'semesters' => $semesters
        ]);

    }
     
    public static function sectionwise_group_allotment(Request $request)

    {
        // dd($request);
        $semesters=!empty($request->semester)?$request->semester:null;
        $subject_type=!empty($request->subject_type)?$request->subject_type:null;
        $subject=!empty($request->subject)?$request->subject:null;
        $section=!empty($request->section)?$request->section:null;
        $users_id = Auth::user()->id;
        $user=User_Profile::pluckUser($users_id);
        $college_id= $user->college_id;
        $academic_year=AcademicYear::getLatestYears($college_id);
        $course_id=!empty($request->course)?$request->course:null;
        $subject_arr=[];
        $subject_mast=Subject::pluckActiveCodeAndName($college_id);
        $subject_type_mast=SubjectType::pluckActiveCodeAndName($college_id);
        $course_mast=Course::pluckActiveCodeAndName($college_id);
        $group_mast=[];
        // dd($semesters);
        $userprofileid = UserApprovedSubjects::getStudentsFromStudentAcademicSubjectTypeSemesterAndCourseSectionNotNull($college_id, $subject_type, $semesters,$course_id,$academic_year,$subject,$section);
         $user_data_arr = UserSubjectMapping::getUsersForSectionAndGroup($college_id, $subject_type, $semesters,$course_id,$academic_year,$subject);

        $data = [];
        if ($subject !== null) {
        $data = User_Profile::whereIn('id',$userprofileid)->get();
        }
        // dd($request,$college_id,$academic_year,$data);
        // dd($data,$userprofileid);

        if(!empty($semesters)){
        $subjects=UserSubjectMapping::getAllApprovedData($college_id,$semesters,$subject_type,$subject);
        // dd($subjects);
        if(count($subjects)>0){

            $group=DB::table('group_mast')->where('status',1);
        if(!empty($college_id)){
            $group->where('college_id',$college_id);
        }
        if(!empty($academic_year)){
            $group->where('academic_year',$academic_year);
        }
        if(!empty($subject)){
            $group->where('subject_id',$subject);
        }
        if(!empty($semesters)){
            $group->where('semester',$semesters);
        }
        if(!empty($section)){
            $group->where('section',$section);
        }

        
        $group_mast=$group->pluck('name','id');
        // dd($section_mast);

        foreach($subjects as $key =>$value){    
            if(!empty($subject_arr[$value->subject_id][$value->course_id])){
                    $subject_arr[$value->subject_id][$value->course_id] += 1;
                }
             else{
                    $subject_arr[$value->subject_id][$value->course_id] = 1;

              }
            
          }
        foreach($subjects as $key =>$value){
            $academic_year=$value->academic_year;
            $college_id=$value->college_id;
            $semester=$value->semester;
            $subject_id=$value->subject_id;
            $section_allotment_arr=SectionAllotment::getAllotedSections($academic_year,$college_id,$semester,$subject_id);

        }
        }
        else{
            $subject_arr=[];
            $section_allotment_arr=[];
        }
    }
        else{
            $section_allotment_arr=[];
        }
        // dd($section_allotment_arr);
        return view('Reports/sectionwise_group_allotment',[
            'subject_arr'=>$subject_arr,
            'academic_year'=>$academic_year,
            'section_allotment_arr'=>$section_allotment_arr,
            'subject_mast'=>$subject_mast,
            'data'=> $data,
            'user_data_arr'=>$user_data_arr,
            'course_mast'=>$course_mast,
            'group_mast'=>$group_mast,
            'college_id'=>$college_id,
            'subject_type_mast'=>$subject_type_mast,
            'semesters' => $semesters
        ]);

    }
    public static function student_group_listing_report(Request $request){
        // dd($request);
        $subject_id = !empty($request->subject) ? $request->subject : null;
        $semester = !empty($request->semester) ? $request->semester : null;
        $section = !empty($request->section) ? $request->section : null;
        $group = !empty($request->group) ? $request->group : null;
        $users_id = Auth::user()->id;
        $college_id = User_Profile::pluckCollegeId($users_id);
        $course_arr = Course::getActiveCoursesFromCollege($college_id);
        $subject_mast = Subject::pluckActiveCodeAndName($college_id);
                


        if ($subject_id !== null && $semester !== null && $section !== null && $group !== null) {
            $user_profile_data = UserSubjectMapping::getUserIdFromCollegeSemesterSubjectSectionGroup($college_id, $subject_id, $semester, $section,$group);
            $user_data = User_Profile::where('status', 1)->whereIn('id', $user_profile_data)->get();
           
            // dd($user_data);

            // dd($user_profile_data, $user_data);
            // dd('if');
            // $arr_cus = [];
            // foreach($user_data as $key => $value) {
            //     $arr_cus[] = $value->id;
            // }

            // dd($user_profile_data, $user_data, array_diff($user_profile_data, $arr_cus));

            return view('Reports/student_group_listing_report', [
                'course_arr' => $course_arr,
                'subject_mast' => $subject_mast,
                'user_data' => $user_data,
                'college_id' => $college_id,
            ]);
        } else {
            
            // dd('else');
            return view('Reports/student_group_listing_report', [
                'course_arr' => $course_arr,
                'user_data'=>[],
                'subject_mast' => $subject_mast,
                'college_id' => $college_id,
            ]);
        }

     }
     public static function categorywise_report(){
        $college_id=Auth::user()->college_id;
        $data = User_Profile::getcategory($college_id);
        $category = Category::pluckActiveCodeAndName();
        $category_arr=[];

        foreach($data as $key =>$value){
            if(!empty($category_arr[$value])){

              $category_arr[$value] += 1;
            }
            else{
              $category_arr[$value] = 1;

            }

        }
        // dd($data,$key,$value,$category,$category_arr);

        return view('Reports/categorywise_report',[
            'category_arr'=>$category_arr,
            'category'=>$category,
        ]);
    }
    
    public static function subject_sectionwise_report(Request $request){
        $semester = !empty($request->semester) ? $request->semester : null;
        $section = !empty($request->section) ? $request->section : null;
        $subject_id = !empty($request->subject) ? $request->subject : null;
        $college_id=Auth::user()->college_id;
        $subject_mast=Subject::pluckCodeAndName($college_id);
        $course_arr = Course::getActiveCoursesFromCollege($college_id);
        $academic_year = AcademicYear::getLatestYears($college_id);
        // dd($academic_year,$college_id,$semester,$section,$subject_id);
        if(!empty($academic_year) && !empty($college_id) && !empty($semester) && !empty($section) &&  !empty($subject_id)){
        $user_profile_id = UserApprovedSubjects::getUserProfileIdFromAcdemicYearSubjectSectionSemester($academic_year,$college_id,$semester,$section,$subject_id);
        // dd($user_profile_id);

        }
        $data=[];
        if(!empty($user_profile_id)){

        $data = User_Profile::pluckstudentformidarray($user_profile_id);
        }
        // dd($data,$request);
        // dd($academic_year,$college_id,$semester,$section,$subject_id);
        // dd($request,$data);
        // dd($college_id,$subject_mast,$academic_year);
        
        // dd($section);
        return view('Reports/subject_sectionwise_report',[
            'subject_mast'=>$subject_mast,
            'academic_year'=>$academic_year,
            'college_id'=>$college_id,
            'data'=>$data,
            'course_arr'=>$course_arr,
            'section'=>$section,
        ]);
    }
    public static function genderwise_report(){
      
        $college_id=Auth::user()->college_id;
        $data = User_Profile::getgender($college_id);
        $gender = Gender::pluckActiveCodeAndName($college_id);
        $gender_arr=[];

        foreach($data as $key =>$value){
            if(!empty($gender_arr[$value])){
              $gender_arr[$value] += 1;
            }
            else{
              $gender_arr[$value] = 1;
            }
        }
        // dd($gender,$gender_arr);

        return view('Reports/genderwise_report',[
            'gender_arr'=>$gender_arr,
            'gender'=>$gender,
        ]);
    }
   



    public static function assignment_report(Request $request)
    
    {


        // dd($request);
               ///all faculty are running on their user_profile_id and name pair below
        $college_id = Auth::user()->college_id;
       $subject_id=!empty($request->subject)?$request->subject:'';
       
       $semester=!empty($request->semester)?$request->semester:'';
       $section=!empty($request->section)?$request->section:'';
       $user_profile_id=!empty($request->faculty)?$request->faculty:'';

       $faculty = Faculty::where('user_profile_id',$user_profile_id)->pluck('id')->first();

        $user=Auth::user();
        
        $role_id=$user->role_id;
        $user_id=$user->id;
       
        $user_profile_data=User_Profile::getDataFromUsersId($user_id);
        $academic_year=AcademicYear::getLatestYears($college_id);
        // $college_id=$user->college_id;
        if($role_id==4 ){
            $user_profile_id=!empty($user_profile_data->id)?$user_profile_data->id:'';
            $faculty_mast=Faculty::getFacultyFullNameByUserProfile($college_id,$user_profile_id);
        }
        
        else{
            $faculty_mast=Faculty::getFacultyFullNameByUserProfile($college_id);

        }
        // dd($college_id, $subject_id, $semester,$section,$user_profile_id,$academic_year);
   
        
        $subject_mast=Subject::pluckCodeAndName($college_id);
       
        $academic_year=AcademicYear::getLatestYears($college_id);
        $assignments=AssignmentMast::student_assignment_data1($college_id, $subject_id, $semester,$section,$user_profile_id,$academic_year);
        // dd($assignments);
        $assigned_students=[];
        $submissions=[];
        $marks=[];
        $approval=[];
         foreach($assignments as $key=>$value){
            $semester=!empty($value->semester)?$value->semester:'';
            $section=!empty($value->section)?$value->section:'';
            $academic_year=!empty($value->academic_year)?$value->academic_year:'';
            $subject_id=!empty($value->subject_id)?$value->subject_id:'';


            $assigned_students[$value->id] =UserApprovedSubjects::get_assessement_assigned_students($college_id,$semester,$section,$academic_year,$subject_id)->count();

            $submissions[$value->id]=DB::Table('student_assignment_data')->where('college_id',$college_id)
            ->where('assignment_id',$value->id)->count();


            $marks[$value->id]=DB::Table('student_assignment_data')->where('college_id',$college_id)
            ->where('marks','!=',NULL)
            ->where('assignment_id',$value->id)->count();
            $approval[$value->id]=DB::Table('student_assignment_data')->where('college_id',$college_id)
            ->where('approved',2)
            ->where('assignment_id',$value->id)->count();
            // dd($assigned_students);
                
            }
            // dd($faculty);
            $session_duration_arr = SessionDuration::pluckCurrentlyRunningSessionId($college_id);
           
            $subjects_id = TimeTable::where('faculty_id',$faculty)
                                        ->where('session_duration',$session_duration_arr)
                                        ->where('semester',$semester)
                                        ->groupBy('subject_id')
                                        // ->groupBy('lecture_type_id')
                                        ->groupBy('semester')
                                        ->groupBy('section')
                                        ->groupBy('group')
                                        ->pluck('subject_id')->toArray();
            // getAllRecords($faculty,$session_duration_arr);
            // dd($subjects_teacher);
            $subjects = Subject::whereIn('id',$subjects_id)->pluck('subject_name','id');
            
            $sections = Section::where('subject_id',$subject_id)->pluck('name');
            // dd($sections);
 

        return view('Reports/assignment_report',[
            'faculty_mast'=>$faculty_mast,
            'academic_year'=>$academic_year,
            'college_id'=>$college_id,
            'assigned_students'=>$assigned_students,
            'role_id'=>$role_id,
            'marks'=>$marks,
            'approval'=>$approval,
            'submissions'=>$submissions,
            'user_profile_id'=>$user_profile_id,
            'assignments'=>$assignments,
            'subject_mast'=>$subject_mast,
            'data' => $request,
            'subjects' => $subjects,
            'sections' => $sections,
        ]);

    }

    public static function studentwise_assignment_report(Request $request) {
        
        $find_button_pressed = !empty($request->find_button_pressed)?$request->find_button_pressed:'';
        $subject_str=!empty($request->subject)?$request->subject:'';
        if($find_button_pressed) {
            if($subject_str == '') {
                Session::flash('error', 'Please select Subject Also');
            }
        }

        

        $subject_arr=explode('|',$subject_str);
               
        $subject_id=!empty($subject_arr[0])?$subject_arr[0]:'';
        $section=!empty($subject_arr[1])?$subject_arr[1]:'';
        $semester=!empty($subject_arr[2])?$subject_arr[2]:'';
        $faculty_id=!empty($request->faculty)?$request->faculty:'';

        
        $user=Auth::user();
        
        $role_id=$user->role_id;
        $user_id=$user->id;

        $user_profile_data=User_Profile::getDataFromUsersId($user_id);
        $academic_year=$user_profile_data->academic_year;
        $college_id=$user->college_id;

        
        if($role_id==4 ){
            
            $faculty_arr=Faculty::getFacultyFromUserProfileId($college_id,$user_profile_data->id);
            // dd($faculty);
            $faculty=array_keys($faculty_arr)[0];
            $faculty_id=!empty($faculty)?$faculty:'';
            $faculty_mast=Faculty::getFacultyFullNameByCollege($college_id,$faculty_id);
        

        }
        else {
            $faculty_mast=Faculty::getFacultyFullNameByCollege($college_id);
        }
        
        
        $subject_mast=Subject::pluckCodeAndName($college_id);
        
        $academic_year=AcademicYear::getLatestYears($college_id);
        
        $ex=TimeTable::getDataForMonthlyAttendance($academic_year,$college_id,$faculty_id);
        // dd($ex);
        $subject = [];
        foreach($ex as $key=>$value){

            $arr=[
                'subject_id'=>$subject_mast[$value->subject_id],
                'section'=>'Sec:'.$value->section,
                'semester'=>'Sem:'.$value->semester
            ];
            $custom_key=$value->subject_id.'|'.$value->section.'|'.$value->semester;
            $subject[$custom_key]=implode('||',$arr);
        }

        

                    // dd($assignments);

        $students =UserApprovedSubjects::student_data($college_id,$semester,$section,$academic_year,$subject_id);
        
        $data=[];
        $assigned_students_ids=[];
        foreach($students as$key=>$value)
            {
            
               $assigned_students_ids[]=$value->user_profile_id;
            }
        
        // dd($faculty_id,$user_profile_data->id);
        $user_profile_id=Faculty::getUserProfileIdByFacultyId($faculty_id);

// dd($user_profile_id);
        $assignments=AssignmentMast::student_assignment_data1($college_id, $subject_id, $semester,$section,$user_profile_id,$academic_year);
        // DD($assignments);
        $assg_id_arr = [];
        foreach ($assignments as $key => $value) 
           {
            $assg_id_arr[] = $value->id;
           }

        $student_submission_data = DB::table('student_assignment_data')
                                        ->whereIn('assignment_id', $assg_id_arr)
                                        ->get();


        $new_arr_data = [];

        foreach($student_submission_data as $key => $value)
           {
            $new_arr_data[$value->user_profile_id][$value->assignment_id] = $value->marks;
           }


        if(!empty($assigned_students_ids))
           {

        $data = User_Profile::pluckstudentformidarray($assigned_students_ids);
           }

        
   
// dd($subject);
        return view('Reports/studenwise_assignment',[
            'faculty_mast'=>$faculty_mast,
            'assignments'=>$assignments,
            'academic_year'=>$academic_year,
            'college_id'=>$college_id,
            'data'=>$data,
            'student_assignment_data'=>$new_arr_data,
            'user_profile_id'=>$faculty_id,
            'subject_mast'=>$subject,
        ]);    
     }


     public static function my_attendance_report(Request $request){
        // dd($request);
        $users_id = Auth::user()->id;

        $user=User_Profile::pluckUser($users_id);

        $course_id= $user->course_id;
        $college_id= $user->college_id;
        $user_profile_id = $user->id;
        $user_semester = $user->semester;
        $academic_year=AcademicYear::getLatestYears($college_id);
        $user_approved_subjects=UserApprovedSubjects::getApprovedSubjectsid($academic_year,$user_profile_id,$user_semester);
        // dd($user_approved_subjects);
        
        // $academic_year=$academic_year-1;
        // $semester= $user->semester; //user ke semester
        // $semester_selected=!empty($request->semester)?$request->semester:$semester; //filter ka semester
        $total_attendance=AttendanceHead::getAttendanceByStudent($academic_year,$college_id,$user_profile_id, $user_semester,$user_approved_subjects);
        $subject_mast=Subject::pluckActiveCodeAndName($college_id);
        // dd($total_attendance);
        $semester_in_course=Course::pluckSemester($course_id);  //course ka semester

        $subject_wise_attendance=[];
        $semester=[];
        foreach($total_attendance as $key=>$value){
            $subject_wise_attendance[$value->subject_id][]=$value;
            $semester[$value->subject_id]=$value->semester;
        }    
        

        //MONTHLY ATTENDANCE REPORT STARTS HERE

        // dd($request->monthly);
        if($request->monthly){
            $month_mast=['January','February','March','April','May','June','July','August','September','October','November','December'];

            foreach($total_attendance as $key=>$value){
                if($value->subject_id==$request->monthly){

                    $month_wise_attendance[$value->month][]=$value;
                }

            }               
            // dd($request->subject_semester);
            // dd($month_wise_attendance);
            return view('Reports/my_monthly_attendance_report',[
                'semester'=>$request->subject_semester,
                'month_wise_attendance'=>!empty($month_wise_attendance)?$month_wise_attendance:[],
                 'month_mast'=>$month_mast,
                 'subject_mast'=>$subject_mast,
                 'subject'=>$request->monthly
            ]);

        }

        //MONTHLY ATTENDANCE REPORT ENDS HERE

        // DAILY ATTENDANCE REPORT STARTS HERE

        if($request->daily){
            $month_mast=['January','February','March','April','May','June','July','August','September','October','November','December'];

            foreach($total_attendance as $key=>$value){
                if($value->subject_id==$request->subject && $value->month==$request->daily ){
                    // $month_wise_attendance[$value->month][]=$value;
                    $from_date = !empty($value->from_date)?date('d-M-Y', strtotime($value->from_date)):NULL;
                    if(!empty($from_date)) {
                        $attendance_arr[$from_date][$value->start_time] = !empty($value->lectures)?$value->lectures:0;
                    }
                }
            }               
            // dd('Currently not working');
            return view('Reports/my_daily_attendance_report',[
                'semester'=>$request->semester,
                'attendance_arr'=>!empty($attendance_arr)?$attendance_arr:[],
                 'month_mast'=>$month_mast,
                 'month' => $request->daily,
                 'subject_mast'=>$subject_mast,
                 'subject' => $request->subject
            ]);

        }


        // DAILY ATTENDANCE REPORT ENDS HERE


        // dd($subject_wise_attendance, $semester_in_course, $semester);
        return view('Reports/my_attendance',[
            

            'subject_mast'=>$subject_mast,
            'subject_wise_attendance'=>$subject_wise_attendance,
            // 'upc_mast'=>$upc_mast,
            'semester_in_course'=>$semester_in_course,
            'semester'=>$semester,
            // 'alloted_sections' => $alloted_sections
  ]);

}
    public static function my_attendance_report2(Request $request){
        // dd($request);
        $users_id = Auth::user()->id;

        $user=User_Profile::pluckUser($users_id);

        $course_id= $user->course_id;
        $college_id= $user->college_id;
        $user_profile_id = $user->id;
        $user_semester = $user->semester;
        
        // dd($user_profile_id);
        
        $academic_year=AcademicYear::getLatestYears($college_id);
        // $academic_year=$academic_year-1;
        // $semester= $user->semester; //user ke semester
        // $semester_selected=!empty($request->semester)?$request->semester:$semester; //filter ka semester
        $total_attendance=AttendanceHead::getAttendanceByStudent($academic_year,$college_id,$user_profile_id, $user_semester);
        // dd($total_attendance);
        $subject_mast=Subject::pluckActiveCodeAndName($college_id);
        // dd($total_attendance);
        $semester_in_course=Course::pluckSemester($course_id);  //course ka semester

        $subject_wise_attendance=[];
        $semester=[];
        foreach($total_attendance as $key=>$value){
            $subject_wise_attendance[$value->subject_id][]=$value;
            $semester[$value->subject_id]=$value->semester;
        }    
        

        //MONTHLY ATTENDANCE REPORT STARTS HERE

        // dd($request->monthly);
        if($request->monthly){
            $month_mast=['January','February','March','April','May','June','July','August','September','October','November','December'];

            foreach($total_attendance as $key=>$value){
                if($value->subject_id==$request->monthly){

                    $month_wise_attendance[$value->month][$value->attendance_type][]=$value;
                }

            }               
            // dd($request->subject_semester);
            // dd($month_wise_attendance);
            return view('Reports/my_monthly_attendance_report',[
                'semester'=>$request->subject_semester,
                'month_wise_attendance'=>!empty($month_wise_attendance)?$month_wise_attendance:[],
                 'month_mast'=>$month_mast,
                 'subject_mast'=>$subject_mast,
                 'subject'=>$request->monthly
            ]);

        }

        //MONTHLY ATTENDANCE REPORT ENDS HERE

        // DAILY ATTENDANCE REPORT STARTS HERE

        if($request->daily){
            $month_mast=['January','February','March','April','May','June','July','August','September','October','November','December'];

            foreach($total_attendance as $key=>$value){
                if($value->subject_id==$request->subject && $value->month==$request->daily ){
                    // $month_wise_attendance[$value->month][]=$value;
                    $from_date = !empty($value->from_date)?date('d-M-Y', strtotime($value->from_date)):NULL;
                    if(!empty($from_date)) {
                        $attendance_arr[$from_date][$value->start_time] = !empty($value->lectures)?$value->lectures:0;
                    }
                }
            }               
            // dd('Currently not working');
            return view('Reports/my_daily_attendance_report',[
                'semester'=>$request->semester,
                'attendance_arr'=>!empty($attendance_arr)?$attendance_arr:[],
                 'month_mast'=>$month_mast,
                 'month' => $request->daily,
                 'subject_mast'=>$subject_mast,
                 'subject' => $request->subject
            ]);

        }


        // DAILY ATTENDANCE REPORT ENDS HERE

        // dd($subject_wise_attendance, $semester_in_course, $semester);
        return view('Reports/my_attendance',[
            

            'subject_mast'=>$subject_mast,
            'subject_wise_attendance'=>$subject_wise_attendance,
            // 'upc_mast'=>$upc_mast,
            'semester_in_course'=>$semester_in_course,
            'semester'=>$semester,
            // 'alloted_sections' => $alloted_sections
        ]);

    }

    public static function monthly_attendance_report(Request $request){
    
        $users_id = Auth::user()->id;
        
        $user=User_Profile::pluckUser($users_id);
        // dd($user);

        $course_id= $user->course_id;
        $college_id= $user->college_id;
        $user_profile_id = $user->id;
        $user_semester = $user->semester;
        $academic_year=AcademicYear::getLatestYears($college_id); 
        $user_approved_subjects=UserApprovedSubjects::getApprovedSubjectsid($academic_year,$user_profile_id,$user_semester);

        // dd($user_approved_subjects);

        // dd($users_id,$course_id,$user);

        $total_attendance=AttendanceHead::getMonthlyAttendanceByStudent($academic_year,$college_id,$user_profile_id, $user_semester,$user_approved_subjects);
        // dd($total_attendance);

        $subject_mast=Subject::pluckActiveCodeAndName($college_id);

        // dd($subject_mast);

        $subject_wise_attendance=[];
        $semester=[];
        foreach($total_attendance as $key=>$value){
            // dd($value);
            $subject_wise_attendance[$value->subject_id][]=$value;
            
            $semester[$value->subject_id]=$value->semester;
        }    
        // dd($subject_wise_attendance);

        if($request->monthly){
            $month_mast=['January','February','March','April','May','June','July','August','September','October','November','December'];

            foreach($total_attendance as $key=>$value){
                // dd($value);
                if($value->subject_id==$request->monthly){
                    $month_wise_attendance[$value->month][]=$value;
                }

            }               
            // dd($request->subject_semester);
            // dd($month_wise_attendance);
            return view('Reports/my_monthly_report_analysis',[
                'semester'=>$request->subject_semester,
                'month_wise_attendance'=>!empty($month_wise_attendance)?$month_wise_attendance:[],
                 'month_mast'=>$month_mast,
                 'subject_mast'=>$subject_mast,
                 'subject'=>$request->monthly
            ]);

        }

      
        return view('Reports/my_monthly_attendance_report2',[
            'subject_mast' => $subject_mast,
            'subject_wise_attendance'=>$subject_wise_attendance,
            'semester'=>$semester,
        ]);


    }
   

    public static function college_id_card(){
        $id = Auth::user()->id;
        $user_data = User_Profile::getDataFromUsersId($id);
        // dd($user_data);
        $student_table_data = StudentIdData::getDataFromUserProfileId($id);
        $college_id = Auth::user()->college_id;
        $course = Course::pluckActiveCodeAndName($college_id);
        $city_mast = CityMast::pluckActiveCodeAndName();
        $print_card_data =DB::table("id_card_details")->where('user_id',$id)->first();
        // dd($print_card_data);

            $perma_add = !empty($user_data->permanent_address)?$user_data->permanent_address:NULL;
            $perma_city = !empty($student_table_data->permanent_city_id)?$city_mast[$student_table_data->permanent_city_id]:NULL;
            $perma_pin = !empty($user_data->permanent_pincode)?$user_data->permanent_pincode:NULL;
            // dd($print_card_data);
            return view('Reports/college_id_generation',[
                'user_data'=>$user_data,
                'student_data'=>$student_table_data,
                'course' => $course,
                'print_card_data' => $print_card_data,
                'address' => $perma_add . ', ' . $perma_city . ' ,' . $perma_pin
            ]);
       
    }

    
public static function college_id_card_store(Request $request){
        // dd($request);
        $college_id=Auth::user()->college_id;
        $current_academic_year=AcademicYear::getLatestYears($college_id);
        // dd($current_academic_year);
        $name = !empty($request->name)?$request->name:null;
        $fathers_name = !empty($request->fathers_name)?$request->fathers_name:null;
        $dob = !empty($request->dob)?$request->dob:null;
        $blood_group = !empty($request->blood_group)?$request->blood_group:null;
        $roll_no = !empty($request->roll_no)?$request->roll_no:null;
        $contact_no = !empty($request->contact_no)?$request->contact_no:null;
        $emergency_contact_no = !empty($request->emergency_contact_no)?$request->emergency_contact_no:null;
        $course_id = !empty($request->course_id)?$request->course_id:null;
        $signature_photo = !empty($request->signature_photo)?$request->signature_photo:null;
        $id_card_photo = !empty($request->id_card_photo)?$request->id_card_photo:null;
        $email = !empty($request->email)?$request->email:null;
        $semester = !empty($request->semester)?$request->semester:null;
        $address = !empty($request->address)?$request->address:null;
        $print_card_data = DB::table('id_card_details')->where('user_id',Auth::user()->id)->first();
// dd($print_card_data);
        if(!empty($print_card_data)){
            $application_no=$print_card_data->application_no;
            $rf_id=$print_card_data->rf_id;
        }
        else{
            $application_no=date('YmdHis');
            $rf_id=NULL;
        }
        $my_arr = [
            'user_id' => Auth::user()->id,
            'academic_year'=>$current_academic_year,
            'student_name'=>$name,
            'father_name'=>$fathers_name,
            'dob'=>$dob,
            'college_roll_no'=>$roll_no,
            'course_id'=>$course_id,
            'semester'=>$semester,
            'student_no'=>$contact_no,
            'email'=>$email,
            'blood_group'=>$blood_group,
            'emergency_contact_no'=>$emergency_contact_no,
            'id_card_photo'=>$id_card_photo,
            'signature_photo'=>$signature_photo,
            'address'=>$address,
            'printed_status'=>2,
            'rf_id'=>$rf_id,
            'flag'=>1,
            'status'=>1,
            'created_at'=>date('Y-m-d H:i:s'),
            'created_by'=>Auth::user()->id,
            'application_no'=>$application_no
        ];

        // dd($my_arr);

        DB::beginTransaction();
        if(!empty($print_card_data)){
        $query = DB::table("id_card_details")->where('user_id',Auth::user()->id)->update($my_arr);

        }else{
        $query = DB::table("id_card_details")->insert($my_arr);

        }

        if($query) {
            DB::commit();
            $message = 'Card Submitted Successfuly';
            Session::flash('message', $message);

        }else {
            DB::rollback();
            $message = 'Something Went Wrong';
            Session::flash('error', $message);

        }

        return redirect()->route('student_id_card_generation');
}
    public static function consolidated_attendance_report(){
        $auth_data = Auth::user(); 
        $users_id = $auth_data->id;  
        
        // dd($user);

        
        $college_id= Auth::user()->college_id;
        // dd($college_id);
        // $course_id= $user->course_id;
        $college_id= $auth_data->college_id;

        $user=User_Profile::pluckUser($users_id);
        $user_profile_id = $user->id;
        $course_id= $user->course_id;
        $user_semester = $user->semester;
        $academic_year=AcademicYear::getLatestYears($college_id);
        // dd($user_profile_id,$user_semester,$academic_year); 
        $user_approved_subjects=UserApprovedSubjects::where('academic_year',$academic_year)
                                                    ->where('college_id',$college_id)
                                                    ->where('semester',$user_semester)
                                                    ->where('user_profile_id',$user_profile_id)
                                                    ->where('approval_status',1)
                                                    ->where('status',1)
                                                    ->get();
        // dd($user_approved_subjects);
        foreach($user_approved_subjects as $key =>$value){
            // dd($value);
            $user_subject_arr[]=$value->subject_id;
            $user_section_arr[$value->subject_id]=$value->section;
        }
        // dd($user_subject_arr,$user_section_arr);

        $total_attendance=AttendanceHead::getAllAttendanceByStudent3($academic_year,$college_id,$user_profile_id, $user_semester,$user_subject_arr,$user_section_arr);
        // dd($total_attendance);
        $total_daily_attendance=[];
        $total_monthly_attendance=[];

        foreach($total_attendance as $key=>$value){
            if($value->attendance_type ==1 ){
                $total_daily_attendance[]=$value;
            }
            elseif($value->attendance_type == 2){
                $total_monthly_attendance[]=$value;
            }
        }

        // dd($total_daily_attendance,$total_monthly_attendance);

        $subject_mast=Subject::pluckActiveCodeAndName($college_id);
        $month_mast=['January','February','March','April','May','June','July','August','September','October','November','December'];
        $semester=[];
        $total_months=[];
        $subject_wise_attendance=[];
        $dict=[];

        // dd($subject);
        if(!empty($total_monthly_attendance)){
            foreach($total_monthly_attendance as $key=>$value){
                // dd($value);
                $semester[$value->subject_id][]=$value->semester;
                $subject_wise_attendance[$value->subject_id][$value->month][]=$value;
                $dict[$value->subject_id.' '.$value->lecture_type_id.' '.$value->month][]=true;
                // $dict[] = $value->subject_id.' '.$value->lecture_type_id.' '.$value->month;
            }   
        }
         
        // dd($subject_wise_monthly_attendance);
        // dd($dict);
        
        // dd($dict,$total_daily_attendance,$total_monthly_attendance);
        // dd(1);
        if(!empty($subject_wise_attendance)){
            foreach($total_daily_attendance as $key=>$value){
                if(array_key_exists($value->subject_id.' '.$value->lecture_type_id.' '.$value->month,$dict)==false){
                    $subject_wise_attendance[$value->subject_id][$value->month][]=$value;
                }
                
            }
        }
        else{
            foreach($total_daily_attendance as $key=>$value){
                $subject_wise_attendance[$value->subject_id][$value->month][] = $value;
            }
        }
        // dd($subject_wise_daily_attendance,$subject_wise_monthly_attendance);

        foreach($total_attendance as $key=>$value){
            if(in_array($value->month,$total_months)!=true){
                $total_months[]=$value->month;
            }
        }
        // dd($total_months,$subject_wise_attendance);
        // dd($subject_wise_attendance,$month_mast,$semester,$subject_mast,$total_months);

        
        return view('Reports/my_consolidated_attendance',[
            'subject_wise_attendance' => $subject_wise_attendance,
            'month_mast' => $month_mast,
            'semester' => $semester,
            'subject_mast' => $subject_mast,
            'total_months' => $total_months,
            'college_id' => $college_id
        ]);
    }

    public static function total_attendance_report(Request $request){

        // dd($request);
         
        // "lecture" => "{"subject_id":42,"lecture_type_id":"2","section":null,"group":null,"semester":5}"
         $lecture_obj=!empty($request->lecture)?$request->lecture:'';
         $subject_id=!empty($lecture_obj->subject_id)?$lecture_obj->subject_id:'';
         $section=!empty($lecture_obj->section)?$lecture_obj->section:'';
         $lecture_type_id=!empty($lecture_obj->lecture_type_id)?$lecture_obj->lecture_type_id:'';
         $group=!empty($lecture_obj->group)?$lecture_obj->group:'';
         $semester=!empty($lecture_obj->semester)?$lecture_obj->semester:'';
         $faculty_id = !empty($request->faculty_id)?$request->faculty_id:'';
         $session =!empty($request->session)?$request->session:'';
         $session_flash =!empty($request->session_flash)?$request->session_flash:'';
         $month=null;



         $auth_data = Auth::user();
         $users_id = $auth_data->id;
         $role_id = $auth_data->role_id;
         $college_id = $auth_data->college_id;
        
        $academic_year=!empty($request->academic_year)?$request->academic_year:'';

        
        $session=SessionDuration::pluckCurrentlyRunningSession($college_id);
        $academic_year_mast=AcademicYear::get3academic_year($college_id);
        // dd($session);
        $user_profile_data_id = User_Profile::getDataFromUsersId($users_id)->id;
        $user_profile_data =User_Profile::getAllRecordsOfCollege($college_id);
        // dd($user_profile_data);
        $user_profile_mast=[];
        $enrollment_no=[];
        foreach($user_profile_data as $key=>$value){
            $user_profile_mast[$value->id]=$value->name;
            $enrollment_no[$value->id]=$value->enrolment_no;      
            // dd($value);
        }
    //   dd($enrollment_no);
        $user_profile_id=($role_id ==4)?$user_profile_data_id:'';
        $subjects=Subject::pluckCodeAndName($college_id);

        $faculty_mast=Faculty::getFacultyFromUserProfileId($college_id,$user_profile_id);
        $lecture_type_mast=LectureType::pluckActiveCodeAndName($college_id);
        $session_mast=SessionDuration::pluckCurrentlyRunningSession($college_id);
// dd('academic_year',$academic_year,'college_id',$college_id,'faculty_id',$faculty_id,'subject_id',$subject_id,'lecture_type_id',$lecture_type_id,'semester',$semester,'section',$section,'group',$group,'month',$month);
        $total_attendance=AttendanceHead::getAttendanceByFaculty($academic_year,$college_id,$faculty_id,$subject_id,$lecture_type_id,$semester,$section,$group,$month);
        // dd($total_attendance); 
        $total_lectures=[];
        $lectures=[];
        $months=[];
        foreach($total_attendance as $key=>$value){

            $total_lectures[$value->user_profile_id][$value->month][]=$value->total_lectures;
            $lectures[$value->user_profile_id][$value->month][]=$value->lectures;
            $months[$value->month]=$value->month;
        }
         
// dd($lectures);
// $lectures,Array_unique($months),$months,$month_name);
        return view('Reports/total_attendance_report',[
            'academic_year'=>$academic_year,
            'session'=>$session,
            'college_id'=>$college_id,
            'total_lectures'=>$total_lectures,
            'academic_year_mast'=>$academic_year_mast,
            'lecture'=>$lectures,
            'lecture_obj'=>$lecture_obj,
            'enrollment_no'=>$enrollment_no,
            'months'=>$months,
            'user_profile_mast'=>$user_profile_mast,
            'session_mast'=>$session_mast,
            'subjects'=>$subjects,
            'user_profile_data_id'=>$user_profile_data_id,
            'faculty_mast'=>$faculty_mast,
            'lecture_type_mast'=>$lecture_type_mast
        ]);
    }


    public function timeTableSummary(Request $request) {
        // dd($request);
        $college_id = Auth::user()->college_id;
        $hour_filter = !empty($request->hour_filter)?$request->hour_filter:NULL;
        // $date = !empty($request->date)?date('Y-m-d', strtotime($request->date)):date('Y-m-d');
        $day = !empty($request->day)?$request->day:null;
        //  dd($day);
        // dd($date);
        
        // $date = !empty($request->date) ? $request->date : NULL;
        $form_submit = !empty($request->form_submit)?$request->form_submit:NULL;
        $room_filter = !empty($request->room_filter)?$request->room_filter:Null;
        

        $hour_data = HourMast::getActiveHours($college_id);
        $hour_mast = [];
        foreach($hour_data as $key => $value) {
            $start_time = date('h:i A', strtotime($key));
            $end_time = date('h:i A', strtotime($value));
            $hour_mast[$key] = $start_time.' - '.$end_time;
        }
        // dd($hour_filter,$college_id,$day,$room_filter);
        $room_mast = RoomMast::where('college_id',$college_id)->pluck('name','id');
       $Time_Table_Mast = TimeTable::where('college_id',$college_id)
                                      ->where('day',$day);
        if(!empty($hour_filter)){
        $Time_Table_Mast->where('timing',$hour_filter);
    }
        if(!empty($room_filter)){
            $Time_Table_Mast->where('room_id',$room_filter);
        }
       $Time_Table_Mast_id=$Time_Table_Mast->get();
                                      
    //    dd($Time_Table_Mast_id);

        // $form_submit = 1;
        // $hour_filter = '11:45';

        $data_to_print = [];
        $return_arr = [
            'hour_mast' => $hour_mast,
            'room_mast' => $room_mast,
            'day' => $day,
            'hour_filter' => $hour_filter,
            'room_filter' => $room_filter
        ];

        if(!empty($form_submit) && !empty($day)) {
            //get current academic year
            $academic_year=DB::table('academic_year_mast')
                                ->where('college_id',$college_id)
                                ->where('status',1)
                                ->orderBy('start_year','desc')
                                ->pluck('start_year')
                                ->first();

            //get current session duration
            $session=SessionDuration::pluckCurrentlyRunningSession($college_id);
            foreach($session as $key => $value) {
                $session_id = $key;
            }
            $day_arr = [
                1 => 'Monday',
                2 => 'Tuesday',
                3 => 'Wednesday',
                4 => 'Thursday',
                5 => 'Friday',
                6 => 'Saturday',
                7 => 'Sunday'
            ];
            // dd($hour_filter);
            $day_numeric = date('N', strtotime($day));
           
            $day_string = $day_arr[$day_numeric];
            // dd($day_string);
            // dd($college_id, $academic_year, $session_id, $day_string, $hour_filter, $room_filter);

            $time_table_data = TimeTable::getDataFromDayandRoom($college_id, $academic_year, $session_id, $day_string, $hour_filter, $room_filter);
            // dd();
            // dd($time_table_data);
            if($time_table_data->isEmpty()) {
                Session::flash('error', 'No Class Exists');
            }
            else {
                $lecture_type_mast=LectureType::pluckActiveCodeAndName($college_id);
                $subject_mast=Subject::pluckActiveCodeAndName($college_id);
                $faculty_mast = Faculty::getFacultyFullNameByCollege($college_id);
                $room_mast = RoomMast::pluckActiveCodeAndName($college_id);
                foreach($time_table_data as $key => $value) {
                    $data_to_print['id']=!empty($value->id)?$value->id:null;
                    $data_to_print['room_no'][] = !empty($room_mast[$value->room_id])?$room_mast[$value->room_id]:'-';
                    $data_to_print['subject_name'][] = !empty($subject_mast[$value->subject_id])?strtoupper($subject_mast[$value->subject_id]):'-';
                    $data_to_print['lecture_type'][] = !empty($lecture_type_mast[$value->lecture_type_id])?$lecture_type_mast[$value->lecture_type_id]:'-';
                    $data_to_print['faculty'][] = !empty($faculty_mast[$value->faculty_id])?$faculty_mast[$value->faculty_id]:'-';
                } 

            }
        }

        $return_arr['data_to_print'] = $data_to_print;
        // dd($data_to_print);
        return view('Reports/timeTableSummary',$return_arr
              );

    }

    public function destroylecture(Request $request)
    {
        // dd($request->encid);
        $decrypted_id = Crypt::decryptString($request->encid);
        DB::beginTransaction();
        $lecture = TimeTable::findOrFail($decrypted_id);
        $lecture->status = 9;
        $lecture->save();
        DB::commit();
        return redirect('timeTableSummary');

    }


    public function time_table_attendance_use(Request $request) {
        // dd($request);
        $auth_data = Auth::user();
        $college_id = $auth_data->college_id;
        // dd($college_id);
        $faculty_arr = Faculty::pluckNameAndFacultyId($college_id);
        // dd($faculty_arr);
        $attendance_arr = AttendanceHead::getActiveFaculty($college_id);
        $timetable_arr = TimeTable::getActiveFaculty($college_id);
        $data = $request->all();
                // dd($data);
        // dd($faculty_arr,$attendance_arr,$timetable_arr);
        return view('Reports/time_table_attendance_use',[
            'faculty_arr'=>$faculty_arr,
            'attendance_arr'=>$attendance_arr,
            'timetable_arr'=>$timetable_arr,
            'data'=>$data,
            
        ]);
    }


    public function course_student_subject(Request $request) {
        // dd($request);
        $auth_data = Auth::user();
        $college_id = $auth_data->college_id;
        $course_filter = !empty($request->course_filter)?$request->course_filter:NULL;
        $semester_filter = !empty($request->semester_filter)?$request->semester_filter:NULL;
        $btn_status = !empty($request->button_status)?$request->button_status:NULL;
        $current_session = SessionDuration::pluckCurrentlyRunningSessionId($college_id);



        $course_arr = Course::pluckCodeAndName($college_id);
        $subject_type_arr = SubjectType::pluckActiveData($college_id);

        $subject_arr = Subject::pluckActiveCodeAndName($college_id);
        $user_profile_arr = [];
        $data = [];
        if(!empty($course_filter) || !empty($semester_filter)) {
            $current_academic_year = AcademicYear::getLatestYears($college_id);
            // dd($current_academic_year);
            if(empty($current_academic_year)) {
                dd('CURRENT ACADEMIC YEAR NOT DEFINED');
            }
            // dd($college_id,$current_academic_year,$current_session, $course_filter,$semester_filter);
            $user_approved_subjects = UserApprovedSubjects::getStudentApproved($college_id,$current_academic_year,$current_session, $course_filter, '','',$semester_filter,'');
            // dd($user_approved_subjects);
            $user_profile_data = User_Profile::getStudentsDataFromCollegeCourseSemester($college_id,$course_filter,$semester_filter);
            // dd($user_profile_data);

            foreach($user_profile_data as $key => $value){
                $user_profile_arr[$value->id]['name'] = $value->name;
                $user_profile_arr[$value->id]['roll_no'] = $value->roll_no;
                $user_profile_arr[$value->id]['examination_roll_no'] = $value->examination_roll_no;
            }




            foreach($user_approved_subjects as $key => $value) {
                // dd($user_approved_subjects[1]);
                $data[$value->user_profile_id][$value->subject_type_id][$value->subject_id][] = !empty($subject_arr[$value->subject_id])?$subject_arr[$value->subject_id].' ('.date('d/M/Y H:i:s A',strtotime($value->created_at)).')':$value->subject_id;
            }

        }
        else{

            $data = [];
        }
        // dd($data,$course_arr);

        // dd($user_profile_arr);

        return view('Reports/course_student_subject', [
            'course_mast' => $course_arr,
            'course_filter' => $course_filter,
            'semester_filter' => $semester_filter,
            'data' => $data,
            'subject_type_arr' => $subject_type_arr,
            'user_profile_arr' => $user_profile_arr,
            'college_id'=>$college_id,
            'flag'=>null
        ]);
    }
    public function course_student_subject_copy(Request $request) {
        // dd($request);
        $auth_data = Auth::user();
        $college_id = $auth_data->college_id;
        $course_filter = !empty($request->course_filter)?$request->course_filter:NULL;
        $semester_filter = !empty($request->semester_filter)?$request->semester_filter:NULL;
        $btn_status = !empty($request->button_status)?$request->button_status:NULL;
        $current_session = SessionDuration::pluckCurrentlyRunningSessionId($college_id);



        $course_arr = Course::pluckCodeAndName($college_id);
        $subject_type_arr = SubjectType::pluckActiveData($college_id);

        $subject_arr = Subject::pluckActiveCodeAndName($college_id);
        $user_profile_arr = [];
        $data = [];
        if(!empty($course_filter) || !empty($semester_filter)) {
            $current_academic_year = AcademicYear::getLatestYears($college_id);
            // dd($current_academic_year);
            if(empty($current_academic_year)) {
                dd('CURRENT ACADEMIC YEAR NOT DEFINED');
            }
            
            $user_profile_ids=User_Profile::where('status',1)->pluck('id');
            // dd($college_id,$current_academic_year,$current_session, $course_filter,$semester_filter);
            // $user_approved_subjects = UserApprovedSubjects::getStudentApproved($college_id,$current_academic_year,'', $course_filter, '','',$semester_filter,'');
// dd($user_profile_ids);
            
            $user_approved_subjects=UserApprovedSubjects::join('user_profile', function($join){
                $join->on('user_approved_subjects.user_profile_id', '=', 'user_profile.id');
                $join->on('user_approved_subjects.semester', '=', 'user_profile.semester');
            })
            ->select('user_approved_subjects.*', 'user_profile.name as name', 'user_profile.contact_no as contact_no', 'user_profile.email as email', 'user_profile.enrolment_no as enrolment_no')
            ->whereNotIn('user_approved_subjects.status',[5,9])
            ->where('user_approved_subjects.approval_status',1);
                if(!empty($college_id)) {
                $user_approved_subjects->where('user_approved_subjects.college_id', $college_id);
                }
                if(!empty($current_academic_year)) {
                $user_approved_subjects->where('user_approved_subjects.academic_year', $current_academic_year);
                }
                if(!empty($course_filter)) {
                $user_approved_subjects->where('user_approved_subjects.course_id', $course_filter);
                }
                if(!empty($semester_filter)) {
                $user_approved_subjects->where('user_approved_subjects.semester', $semester_filter);
                }
                $user_approved_subjects->whereIn('user_approved_subjects.user_profile_id', $user_profile_ids);
                

              $user_approved_subjects= $user_approved_subjects->orderBy('user_approved_subjects.course_id', 'asc')
                        ->orderBy('user_profile.name', 'asc')
                        ->get();

// dd($user_approved_subjects);








            // dd($user_approved_subjects);
            $user_profile_data = User_Profile::getStudentsDataFromCollegeCourseSemester($college_id,$course_filter,$semester_filter);
            // dd($user_profile_data);

            foreach($user_profile_data as $key => $value){
                $user_profile_arr[$value->id]['name'] = $value->name;
                $user_profile_arr[$value->id]['roll_no'] = $value->roll_no;
                $user_profile_arr[$value->id]['examination_roll_no'] = $value->examination_roll_no;
            }




            foreach($user_approved_subjects as $key => $value) {
                // dd($user_approved_subjects[1]);
                $data[$value->user_profile_id][$value->subject_type_id][$value->subject_id][] = !empty($subject_arr[$value->subject_id])?$subject_arr[$value->subject_id].' (Section - '.$value->section.')':$value->subject_id;
            }

        }
        else{

            $data = [];
        }
        // dd($data);

        // dd($user_profile_arr);

        return view('Reports/course_student_subject_copy', [
            'course_mast' => $course_arr,
            'course_filter' => $course_filter,
            'semester_filter' => $semester_filter,
            'data' => $data,
            'subject_type_arr' => $subject_type_arr,
            'user_profile_arr' => $user_profile_arr,
            'college_id'=>$college_id,
            'flag'=>null
        ]);
    }

    public static function reject_dsc_subject(Request $request){
        // dd($request);
        $college_id=!empty($request->college_id)?$request->college_id:null;
        $user_profile_id=!empty($request->user_profile_id)?$request->user_profile_id:null;
        $course_id=!empty($request->course_filter)?$request->course_filter:null;
        $semester=!empty($request->semester_filter)?$request->semester_filter:null;
        $subject_type_id=!empty($request->subject_type_id)?$request->subject_type_id:null;
        $subject_id=!empty($request->subject_id)?$request->subject_id:null;
// dd($college_id,$user_profile_id,$course_id,$semester,$subject_id,$subject_type_id);
        $student = UserApprovedSubjects::getStudentRecord($college_id,$user_profile_id,$course_id,$semester,$subject_id,$subject_type_id)->first();
    // dd($student);
    if(!empty($student)){
        DB::beginTransaction();
        $query = UserApprovedSubjects::where('id',$student)
                ->update(['approval_status'=>2]);
                // dd($query);
        if($query){
            DB::commit();
            $message = 'Subject Deleted Successfuly';
            Session::flash('message', $message);
        }
        else {
            DB::rollback();
                $message = 'Something Went Wrong';
                Session::flash('error', $message);
            }
        }
        return redirect()->back()->with('flag',$subject_type_id);
    }
    public static function subjectwise_student_allotment(Request $request) {
        $auth_data = Auth::user();
        $college_id = $auth_data->college_id;

        $course_id=!empty($request->course)?$request->course:null;
        $semester=!empty($request->semester)?$request->semester:null;
        $subject_type=!empty($request->subject_type)?$request->subject_type:null;

        //filter arrays
        $course_arr = Course::pluckActiveCodeAndName($college_id);
        $subject_type_arr = SubjectType::pluckActiveData($college_id);

        $data = [];
        $subject_arr = [];
        if(!empty($course_id) && !empty($semester) && !empty($subject_type)) {
            $subject_arr = Subject::pluckActiveCodeAndName($college_id, $subject_type);
            $data = User_Profile::getStudentsDataFromCollegeCourseSemester($college_id, $course_id, $semester);
        }

        return view('Reports/subjectwise_student_allotment',[
            'course_id' => $course_id,
            'semester' => $semester,
            'subject_type' => $subject_type,
            'course_arr' => $course_arr,
            'subject_type_arr' => $subject_type_arr,
            'data' => $data,
            'subject_arr' => $subject_arr
        ]);

    }

    public function subjectwise_student_allotment_store(Request $request) {
        // dd($request);
        $auth_data = Auth::user();
        $college_id = $auth_data->college_id;
        $current_academic_year = AcademicYear::getLatestYears($college_id);
        $current_session = SessionDuration::pluckCurrentlyRunningSessionId($college_id);

        $users_id = $auth_data->id;
        $datetime = date('Y-m-d H:i:s');


        $course_id = !empty($request->course_id)?$request->course_id:null;
        $semester = !empty($request->semester)?$request->semester:null;
        $subject_type_id = !empty($request->subject_type_id)?$request->subject_type_id:null;
        $subject_arr = !empty($request->subject_id)?$request->subject_id:[];
        $user_profile_arr = !empty($request->student)?$request->student:[];


        if(empty($course_id)||empty($semester)||empty($subject_type_id)||empty($subject_arr)||empty($user_profile_arr)) {
            dd('REQUEST PARAMETERS MISSING, CONTACT ADMIN');
        }
        else {
            foreach($user_profile_arr as $key => $user_profile_id) {
                foreach($subject_arr as $key => $subject_id) {
                    $new_arr[] = [
                        'academic_year' => $current_academic_year,
                        'session_duration' => $current_session,
                        'user_profile_id' => $user_profile_id,
                        'college_id' => $college_id,
                        'course_id' => $course_id,
                        'semester' => $semester,
                        'subject_type_id' => $subject_type_id,
                        'subject_id' => $subject_id,
                        'preference' => NULL,
                        'status' => 1,
                        'sequence' => 1000,
                        'approval_status' => 1,
                        'approved_at' => $datetime,
                        'approved_by' => $users_id,
                        'section' => NULL,
                        'section_allotment_id' => NULL,
                        'group_cus' => NULL,
                        'updated_at' => NULL,
                        'updated_by' => NULL,
                        'created_at' => $datetime,
                        'created_by' => $users_id
                    ];
                }
            }
            // dd($new_arr);
            //delete old mappings and insert new mapping
            DB::beginTransaction();

            $delete_query = DB::table('user_approved_subjects') 
                                ->where('college_id',$college_id)
                                ->where('academic_year',$current_academic_year)
                                ->where('session_duration',$current_session)
                                ->where('course_id',$course_id)
                                ->where('semester',$semester)
                                ->where('subject_type_id',$subject_type_id)
                                ->where('status',1)
                                ->where('approval_status',1)
                                ->whereIn('user_profile_id',$user_profile_arr)
                                // ->get();
                                // dd($delete_query);
                                ->update([
                                    'approval_status' => 9,
                                    'status' => 9,
                                    'updated_at' => $datetime,
                                    'updated_by' => $users_id
                                ]);

            $insert_query = DB::table('user_approved_subjects')->insert($new_arr);
            if($insert_query) {
                DB::commit();
                $message = 'Data Assigned Successfuly';
                Session::flash('message', $message);
            }
            else {
                DB::rollback();
                $message = 'Something Went Wrong';
                Session::flash('error', $message);
            }
            return redirect('subjectwise_student_allotment');
        }

    }
    public static function subjectwise_student_report(Request $request) {
            // dd($request);


        $college_id = Auth::user()->college_id;
        $subject_id = !empty($request->subject_id)?$request->subject_id:NULL;
        $subject_type = !empty($request->subject_type)?$request->subject_type:NULL;
        $semesters = !empty($request->semesters)?$request->semesters:NULL;
        $section = !empty($request->section)?$request->section:NULL;
        $academic_year = AcademicYear::getLatestYears($college_id);




        $faculty_id = TimeTable::where('college_id', $college_id)
                                ->where('semester', $semesters)
                                ->where('section', $section)
                                ->where('subject_id', $subject_id)
                                ->where('status', 1)
                                ->distinct()
                                ->pluck('faculty_id');

        $student_arr = UserApprovedSubjects::where('academic_year',$academic_year)
                                    ->where('college_id',$college_id)  
                                    ->where('semester',$semesters)
                                    ->where('section',$section)
                                    ->where('status',1) 
                                    ->where('approval_status',1)
                                    ->where('subject_type_id',$subject_type)
                                    ->where('subject_id',$subject_id)
                                    ->pluck('user_profile_id');
        // dd($student_arr);
        $faculty_mast = Faculty::getFacultyName($college_id);
        $course_mast = Course::pluckCodeAndName($college_id);
        $student_name_arr = User_Profile::pluckstudentformidarray($student_arr);
        // dd($student_name_arr);

        // dd($faculty_mast);
        if(count($student_name_arr)==0){
            $status = 9;
            // dd($status);
        }
        else{
            $status = 1;
        }

        // dd($request,$subject_id,$semesters,$section,$faculty_id,$student_arr);

        $report_log = [
            'subject_type_id' => $subject_type,
            'subject_id' => $subject_id,
            'section' => $section,
            'semester' => $semesters,
            'status' => $status,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => Auth::user()->id,
        ];
        // dd($report_log);
        if(!empty($request->subject_id)){

            DB::beginTransaction();

           $query=DB::table('report_approval_log')->insert($report_log);
           if($query){
            DB::commit();
            $message = 'Entry Saved Successfuly';
            }  else {
            DB::rollback();
            $message = 'Something Went Wrong';
            }
        }
    



        $subject_arr =Subject::pluckSubjectforUpdate($college_id,$subject_type);
        $subject_type_mast=SubjectType::pluckSubjectTypeFromCollege($college_id);
                                                        // dd($academic_year);

        return view('Reports/subjectwise_student_report',[
            'college_id' => $college_id,
            'subject_type_mast' => $subject_type_mast,
            'subject_arr' => $subject_arr,
            'academic_year' => $academic_year,
            'faculty_id' => $faculty_id,
            'student_arr' => $student_arr,
            'faculty_mast' => $faculty_mast,
            'course_mast' => $course_mast,
            'student_name_arr' => $student_name_arr,
            'data' => $request,
        ]);

    }
    
    public static function studentwise_attendance_report(Request $request){
        // dd($request);
        $auth_data = Auth::user();
        // dd($auth_data);
        $college_id = $auth_data->college_id;
        $selected_academic_year = !empty($request->academic_year)?$request->academic_year:date('Y');
        $course_filter = !empty($request->course_filter)?$request->course_filter:NULL;
        $semester_filter = !empty($request->semester_filter)?$request->semester_filter:NULL;
        // dd($course_filter,$semester_filter);
        $course_arr = Course::pluckCodeAndName($college_id);
        $user_profile_id_arr=[];
        $users_arr=[];
        $user_subject_arr=[];
        $user_section_arr=[];
        $total_attendance_arr=[];
        $total_daily_attendance_arr=[];
        $total_monthly_attendance_arr=[];
        if(!empty($course_filter)){
        $user_profile_arr=User_Profile::whereNotNull('semester')
                                            ->where('academic_year',$selected_academic_year)
                                            ->where('college_id',$college_id)
                                            ->where('course_id',$course_filter)
                                            ->where('semester',$semester_filter)
                                            ->orderBy('roll_no','asc')
                                            ->orderBy('name','asc')
                                            ->get();
        }
        else{
            $user_profile_arr=[];
        }
        // dd($user_profile_arr);
        foreach($user_profile_arr as $key => $value){
            $user_profile_id_arr[]=$value->id;
            $users_arr[$value->id]=$value;
        }   
        // dd($user_profile_id_arr); 
        $user_approved_subjects=UserApprovedSubjects::where('academic_year',$selected_academic_year)
                                                    ->where('college_id',$college_id)
                                                    ->where('semester',$semester_filter)
                                                    ->whereIn('user_profile_id',$user_profile_id_arr)
                                                    ->where('approval_status',1)
                                                    ->where('status',1)
                                                    ->orderBy('user_profile_id','desc')
                                                    ->get();
        foreach($user_approved_subjects as $key =>$value){
            $user_subject_arr[$value->user_profile_id][]=$value->subject_id;
            $user_section_arr[$value->user_profile_id][$value->subject_id]=$value->section;
        }
        // dd($user_section_arr);
        // dd($user_subject_arr);
        foreach($user_subject_arr as $key => $value){
        $total_attendance=AttendanceHead::getAllAttendanceByStudent($selected_academic_year,$college_id,$key, $semester_filter,$value);
        // dd($total_attendance);

        foreach($total_attendance as $key2=>$value2){
            // dd($value2);
            if($value2->attendance_type === 1){
                // dd(1);
                $total_daily_attendance_arr[$key][]=$value2;
            }
            elseif($value2->attendance_type === 2){
                $total_monthly_attendance_arr[$key][]=$value2;
            }
            $total_attendance_arr[$key][]=$value2;
        }
        
        }
        // foreach($total_monthly_attendance_arr as $user_id=>$value){
        //     if($user_id == 6877){
        //         // dd($value);
        //     }
        // }
         // dd($total_monthly_attendance_arr);
        $subject_mast=Subject::pluckActiveCodeAndName($college_id);
        $month_mast=['January','February','March','April','May','June','July','August','September','October','November','December'];
        $subject_wise_attendance=[];
        $semester=[];
        $total_months=[];
        $dict=[];
        if(!empty($total_monthly_attendance_arr)){
            foreach($total_monthly_attendance_arr as $key=>$value){
                foreach($value as $key2 =>$value2){
                    // dd($value2);
                $subject_wise_attendance[$key][$value2->subject_id][$value2->month][]=$value2;
                $dict[$key][$value2->subject_id.' '.$value2->month.' '.$value2->lecture_type_id]=true;
    
                }
            } 
        }
        // dd(!empty($subject_wise_attendance[4667]));
        // dd($user_profile_id_arr);
        foreach($user_profile_id_arr as $id => $profile_id){
            if(!empty($subject_wise_attendance[$profile_id])){
                foreach($total_daily_attendance_arr as $key=>$value){
                    if($key == $profile_id){
                    foreach($value as $key2 =>$value2){
                    if(array_key_exists($value2->subject_id.' '.$value2->month.' '.$value2->lecture_type_id,$dict[$key])==false){
                        $subject_wise_attendance[$key][$value2->subject_id][$value2->month][]=$value2;
                            }    
                        }

                    }
                    } 

                }
            else{

                foreach($total_daily_attendance_arr as $key=>$value){
                    if($key == $profile_id){
                    foreach($value as $key2 =>$value2){
                        // dd($value2);
                    $subject_wise_attendance[$key][$value2->subject_id][$value2->month][]=$value2;
        
                    }
                        
                    }
                }  
             }
        }
        
        // dd($total_attendance_arr);
        foreach($total_attendance_arr as $key=>$value){
            foreach($value as $key2 => $value2){
            if(in_array($value2->month,$total_months)!=true){
                $total_months[]=$value2->month;
            }

            }
        }

        // foreach($subject_wise_attendance as $user_id=>$value){
        //     if($user_id == 6877){
        //         // dd($value);
        //     }
        // }
        // dd($subject_wise_attendance);
        return view('Reports/studentwise_attendance_report',[
            'course_arr' =>$course_arr,
            'course_filter' =>$course_filter,
            'academic_year' =>$selected_academic_year,
            'semester_filter' =>$semester_filter,
            'subject_wise_attendance' => $subject_wise_attendance,
            'month_mast' => $month_mast,
            'subject_mast' => $subject_mast,
            'total_months' => $total_months,
            'user_profile_arr' => $user_profile_arr,
            'users_arr' => $users_arr,
            'user_subject_arr' => $user_subject_arr,
            
        ]);
    }

    
    public static function studentwise_previous_report(Request $request){
        // dd($request);
        $auth_data = Auth::user();
        // dd($auth_data);
        $college_id = $auth_data->college_id;
        $selected_admission_year = !empty($request->academic_year)?$request->academic_year:date('Y');
        $course_filter = !empty($request->course_filter)?$request->course_filter:NULL;
        $semester = !empty($request->semester)?$request->semester:NULL;
        $college_roll_no = !empty($request->college_roll_no)?$request->college_roll_no:NULL;
      
      
        // dd($course_filter,$semester);
        $course_arr = Course::pluckCodeAndName($college_id);
        $user_profile_id_arr=[];
        $users_arr=[];
        $user_subject_arr=[];
        $user_section_arr=[];
        $total_attendance_arr=[];
        $total_daily_attendance_arr=[];
        $total_monthly_attendance_arr=[];
        if(!empty($college_roll_no)){
        $user_profile_arr=User_Profile::whereNotNull('semester')
                                            // ->where('admission_year',$selected_admission_year)
                                            ->where('college_id',$college_id)
                                            ->where('roll_no',$college_roll_no)
                                            ->orderBy('roll_no','asc')
                                            ->orderBy('name','asc')
                                            ->get();

        }else{
         $user_profile_arr=User_Profile::whereNotNull('semester')
                                            ->where('admission_year',$selected_admission_year)
                                            ->where('college_id',$college_id)
                                            ->where('course_id',$course_filter)
                                            ->orderBy('roll_no','asc')
                                            ->orderBy('name','asc')
                                            ->get();

        }
        
        // dd($user_profile_arr);
        foreach($user_profile_arr as $key => $value){
            $user_profile_id_arr[]=$value->id;
            $users_arr[$value->id]=$value;
        }   
        // dd($user_profile_id_arr); 
        $user_approved_subjects=UserApprovedSubjects::where('college_id',$college_id)
                                                    ->whereIn('user_profile_id',$user_profile_id_arr)
                                                    ->where('semester',$semester)
                                                    ->where('approval_status',1)
                                                    ->where('status',1)
                                                    ->orderBy('user_profile_id','desc')
                                                    ->get();
        foreach($user_approved_subjects as $key =>$value){
            $user_subject_arr[$value->user_profile_id][]=$value->subject_id;
            $user_section_arr[$value->user_profile_id][$value->subject_id]=$value->section;
        }
        // dd($user_section_arr);
        // dd($user_subject_arr,$user_section_arr);
        
        foreach($user_subject_arr as $key => $value){
        $total_attendance=AttendanceHead::getAllAttendanceByCourseAndYear($semester,$college_id,$key,$value);
        

        foreach($total_attendance as $key2=>$value2){
            // dd($value2);
            if($value2->attendance_type === 1){
                // dd(1);
                $total_daily_attendance_arr[$key][]=$value2;
            }
            elseif($value2->attendance_type === 2){
                $total_monthly_attendance_arr[$key][]=$value2;
            }
            $total_attendance_arr[$key][]=$value2;
        }
        // dd($total_attendance);
        }
        // foreach($total_monthly_attendance_arr as $user_id=>$value){
        //     if($user_id == 6877){
        //         // dd($value);
        //     }
        // }
         // dd($total_monthly_attendance_arr);
        $subject_mast=Subject::pluckActiveCodeAndName($college_id);
        $month_mast=['January','February','March','April','May','June','July','August','September','October','November','December'];
        $subject_wise_attendance=[];
        // $semester=[];
        $total_months=[];
        $dict=[];
        if(!empty($total_monthly_attendance_arr)){
            foreach($total_monthly_attendance_arr as $key=>$value){
                foreach($value as $key2 =>$value2){
                    // dd($value2);
                $subject_wise_attendance[$key][$value2->subject_id][$value2->month][]=$value2;
                $dict[$key][$value2->subject_id.' '.$value2->month.' '.$value2->lecture_type_id]=true;
    
                }
            } 
        }
        // dd(!empty($subject_wise_attendance[4667]));
        // dd($user_profile_id_arr);
        foreach($user_profile_id_arr as $id => $profile_id){
            if(!empty($subject_wise_attendance[$profile_id])){
                foreach($total_daily_attendance_arr as $key=>$value){
                    if($key == $profile_id){
                    foreach($value as $key2 =>$value2){
                    if(array_key_exists($value2->subject_id.' '.$value2->month.' '.$value2->lecture_type_id,$dict[$key])==false){
                        $subject_wise_attendance[$key][$value2->subject_id][$value2->month][]=$value2;
                            }    
                        }

                    }
                    } 

                }
            else{

                foreach($total_daily_attendance_arr as $key=>$value){
                    if($key == $profile_id){
                    foreach($value as $key2 =>$value2){
                        // dd($value2);
                    $subject_wise_attendance[$key][$value2->subject_id][$value2->month][]=$value2;
        
                    }
                        
                    }
                }  
             }
        }
        
        // dd($total_attendance_arr);
        foreach($total_attendance_arr as $key=>$value){
            foreach($value as $key2 => $value2){
            if(in_array($value2->month,$total_months)!=true){
                $total_months[]=$value2->month;
            }

            }
        }

        // foreach($subject_wise_attendance as $user_id=>$value){
        //     if($user_id == 6877){
        //         // dd($value);
        //     }
        // }
        // dd($subject_mast,$user_subject_arr);
        return view('Reports/studentwise_previous_report',[
            'course_arr' =>$course_arr,
            'course_filter' =>$course_filter,
            'semester' =>$semester,
            'academic_year' =>$selected_admission_year,
            'subject_wise_attendance' => $subject_wise_attendance,
            'month_mast' => $month_mast,
            'subject_mast' => $subject_mast,
            'total_months' => $total_months,
            'user_profile_arr' => $user_profile_arr,
            'users_arr' => $users_arr,
            'user_subject_arr' => $user_subject_arr,
            'college_roll_no' => $college_roll_no,
            
        ]);
    }







    public static function studentwise_attendance_report_percentage(Request $request){
        // dd($request);
        $auth_data = Auth::user();
        $percentage=$request->percentage;
        // dd($auth_data);
        $college_id = $auth_data->college_id;
        $current_academic_year = AcademicYear::getLatestYears($college_id);
        $course_filter = !empty($request->course_filter)?$request->course_filter:NULL;
        $semester_filter = !empty($request->semester_filter)?$request->semester_filter:NULL;
        // dd($course_filter,$semester_filter);
        $course_arr = Course::pluckCodeAndName($college_id);
        $user_profile_id_arr=[];
        $users_arr=[];
        $user_subject_arr=[];
        $total_attendance_arr=[];
        $total_daily_attendance_arr=[];
        $total_monthly_attendance_arr=[];
        if(!empty($course_filter)){
        $user_profile_arr=User_Profile::whereNotNull('semester')
                                            ->where('academic_year',$current_academic_year)
                                            ->where('college_id',$college_id)
                                            ->where('course_id',$course_filter)
                                            ->where('semester',$semester_filter)
                                            ->orderBy('roll_no','asc')
                                            ->orderBy('name','asc')
                                            ->get();
        }
        else{
            $user_profile_arr=User_Profile::whereNotNull('semester')
                                            ->where('academic_year',$current_academic_year)
                                            ->where('college_id',$college_id)
                                            ->where('semester',$semester_filter)
                                            ->orderBy('roll_no','asc')
                                            ->orderBy('name','asc')
                                            ->get();
        }
        foreach($user_profile_arr as $key => $value){
            $user_profile_id_arr[]=$value->id;
            $users_arr[$value->id]=$value;
        }   
        // dd($user_profile_id_arr); 
        $user_approved_subjects=UserApprovedSubjects::whereIn('user_profile_id',$user_profile_id_arr)->orderBy('user_profile_id','desc')->where('status',1)->get();
        foreach($user_approved_subjects as $key =>$value){
            $user_subject_arr[$value->user_profile_id][]=$value->subject_id;
        }
        // dd($user_approved_subjects);
        // dd($user_subject_arr);
        foreach($user_subject_arr as $key => $value){
        $total_attendance=AttendanceHead::getAllAttendanceByStudent($current_academic_year,$college_id,$key, $semester_filter,$value);
        // dd($total_attendance);
        foreach($total_attendance as $key2=>$value2){
            // dd($value2);
            if($value2->attendance_type === 1){
                // dd(1);
                $total_daily_attendance_arr[$key][]=$value2;
            }
            elseif($value2->attendance_type === 2){
                $total_monthly_attendance_arr[$key][]=$value2;
            }
            $total_attendance_arr[$key][]=$value2;
        }
        
        }
        // foreach($total_monthly_attendance_arr as $user_id=>$value){
        //     if($user_id == 6877){
        //         // dd($value);
        //     }
        // }
        //  dd($total_monthly_attendance_arr);
        $subject_mast=Subject::pluckActiveCodeAndName($college_id);
        $month_mast=['January','February','March','April','May','June','July','August','September','October','November','December'];
        $subject_wise_attendance=[];
        $semester=[];
        $total_months=[];
        $dict=[];
        if(!empty($total_monthly_attendance_arr)){
            foreach($total_monthly_attendance_arr as $key=>$value){
                foreach($value as $key2 =>$value2){
                    // dd($value2);
                $subject_wise_attendance[$key][$value2->subject_id][$value2->month][]=$value2;
                $dict[$key][$value2->subject_id.' '.$value2->month.' '.$value2->lecture_type_id]=true;
    
                }
            } 
        }

        foreach($user_profile_id_arr as $id => $profile_id){
            if(!empty($subject_wise_attendance[$profile_id])){
                foreach($total_daily_attendance_arr as $key=>$value){
                    if($key == $profile_id){
                    foreach($value as $key2 =>$value2){
                    if(array_key_exists($value2->subject_id.' '.$value2->month.' '.$value2->lecture_type_id,$dict[$key])==false){
                        $subject_wise_attendance[$key][$value2->subject_id][$value2->month][]=$value2;
                            }    
                        }

                    }
                    } 

                }
            else{

                foreach($total_daily_attendance_arr as $key=>$value){
                    if($key == $profile_id){
                    foreach($value as $key2 =>$value2){
                        // dd($value2);
                    $subject_wise_attendance[$key][$value2->subject_id][$value2->month][]=$value2;
        
                    }

                    }
                }  
             }
        }
        // dd($total_attendance_arr);
        foreach($total_attendance_arr as $key=>$value){
            foreach($value as $key2 => $value2){
            if(in_array($value2->month,$total_months)!=true){
                $total_months[]=$value2->month;
            }

            }
        }

        // foreach($subject_wise_attendance as $user_id=>$value){
        //     if($user_id == 6877){
        //         // dd($value);
        //     }
        // }
        return view('Reports/studentwise_attendance_report_percentage',[
            'course_arr' =>$course_arr,
            'course_filter' =>$course_filter,
            'semester_filter' =>$semester_filter,
            'subject_wise_attendance' => $subject_wise_attendance,
            'month_mast' => $month_mast,
            'subject_mast' => $subject_mast,
            'total_months' => $total_months,
            'user_profile_arr' => $user_profile_arr,
            'users_arr' => $users_arr,
            'user_subject_arr' => $user_subject_arr,
            'percentage' => $percentage,
            
        ]);
    }

    public function user_profile_id_report(Request $request){
        $college_id = Auth::user()->college_id;
        $course = !empty($request->course) ? $request->course :NULL;
        $semester = !empty($request->semester) ? $request->semester : NULL;
        // $course_id = DB::table('course_mast')->pluck('name', 'id');\
        $course_arr = Course::pluckCodeAndName($college_id); 


        return view('Reports/user_profile_id_report', [
            'course' => $course,
            'semester' => $semester,
            'course_arr' => $course_arr,
        ]);
    }
    
    public function getuserprofile(Request $request)
    {
        // dd($request);
        $college_id = Auth::user()->college_id;
        $course = !empty($request->course) ? $request->course :NULL;
        $semester = !empty($request->semester) ? $request->semester : NULL;
        // $course_id = DB::table('course_mast')->pluck('name', 'id');
        $course_arr = Course::pluckCodeAndName($college_id); 
        // dd($course_arr);
        $user_data = DB::table('user_profile');
        if(!empty($course_arr))
        {
            $user_data->where('course_id',$course);
        }
        if(!empty($semester))
        {
            $user_data->where('semester',$semester);
        }
        $data=$user_data->where('semester','!=','NULL')->pluck('name','id');
        // dd($user_data);
        return view('Reports/user_profile_id_report', [
            'course' => $course,
            'semester' => $semester,
            'course_arr' => $course_arr,
            'user_data' => $data,
        ]);
    }

    public function student_course_create() {
        $college_id=Auth::user()->college_id;
             $students = User_Profile::where('college_id',$college_id)->whereNotNull('semester')->orderBy('name')->get();
             // dd($students);
            $newcourse = Course::where('college_id',$college_id)->where('status', 1)->pluck('name', 'id');

            return view('UpdateCourse.create', compact('students','newcourse'));
        }




        public function getCourseDetail(Request $request){
            $courseId = $request->courseId;
            $college_id =Auth::user()->college_id;
            $userprofile = User_Profile::where('college_id',$college_id)->where('id', $courseId)->pluck('course_id')->first();

            if (is_null($userprofile)) {
                return response()->json(['error' => 'No user profile found for the given ID'], 404);
            }

            $courseRes = DB::table('course_mast')->where('id', $userprofile)->pluck('name')->first();

            if (is_null($courseRes)) {
                return response()->json(['error' => 'No course found for the given ID'], 404);
            }

            return response()->json([$courseRes]);
        }

        public function coursesubmit(Request $request)
        {
        // dd($request);
        $student_id=!empty($request->student_id)?$request->student_id:NULL;
        $course_id=!empty($request->course_id)?$request->course_id:NULL;
        $college_id=Auth::user()->college_id;

            $query=DB::table('user_profile')
            ->where('college_id',$college_id)
            ->where('id',$student_id)
            ->update(['course_id' =>$course_id]);

            if($query){
                   DB::commit();
                    $message = 'Course Updated Successfuly';
                    Session::flash('message', $message);
                }else {
                    DB::rollback();
                    $message = 'Something Went Wrong';
                    Session::flash('error', $message);
                }

            // $courseName = $request->input('course_name');
            
            return redirect('student_course_create');
        }
        public function id_card_report(Request $request)
        {
        // dd('hi'); 
            // dd($request);
        $course_id=!empty($request->course)?$request->course:NULL;
        $printed_status=!empty($request->printed_status)?$request->printed_status:NULL;
        $semester=!empty($request->semester)?$request->semester:NULL;
        $college_id=Auth::user()->college_id;
        $academic_year=!empty($request->academic_year)?$request->academic_year:NULL;
        $current_academic_year=AcademicYear::getLatestYears($college_id);

        $student_id_data=DB::table('id_card_details');
        if(!empty($academic_year)){
            $student_id_data->where('academic_year',$academic_year);
        }
        else{
            $student_id_data->where('academic_year',$current_academic_year);

        }
        if(!empty($course_id)){
            $student_id_data->where('course_id',$course_id);
        }
        if(!empty($printed_status)){
            $student_id_data->where('printed_status',$printed_status);
        }
         if(!empty($semester)){
            $student_id_data->where('semester',$semester);
        }
            $Final_student_data=$student_id_data->orderBy('college_roll_no','ASC')->get();
        // dd($all_student_id_data);
            
        $course_mast=Course::pluckActiveCodeAndName($college_id);
           // dd($Final_student_data,$course_mast);
            return view('Reports/id_card_report', [
                'Final_student_data' => $Final_student_data,
                'course_mast' => $course_mast,
                'course_id' => $course_id,
                'semester' => $semester,
                'printed_status' => $printed_status,
                'current_academic_year' => $current_academic_year,
                'academic_year' => $academic_year,
            ]);
        }

        public function id_card_printed_excel(Request $request)
        {
            // dd($request);
        $course_id=!empty($request->course)?$request->course:NULL;
        $printed_status=!empty($request->printed_status)?$request->printed_status:NULL;
        $semester=!empty($request->semester)?$request->semester:NULL;
        $college_id=Auth::user()->college_id;
        $academic_year=!empty($request->academic_year)?$request->academic_year:NULL;
        $current_academic_year=AcademicYear::getLatestYears($college_id);
            // dd($student_data);

        $student_id_data=DB::table('id_card_details');
        if(!empty($academic_year)){
            $student_id_data->where('academic_year',$academic_year);
        }
        else{
            $student_id_data->where('academic_year',$current_academic_year);

        }
        if(!empty($course_id)){
            $student_id_data->where('course_id',$course_id);
        }
        if(!empty($printed_status)){
            $student_id_data->where('printed_status',$printed_status);
        }
        if(!empty($semester)){
            $student_id_data->where('semester',$semester);
        }
            $Final_student_data=$student_id_data->orderBy('college_roll_no','ASC')->get();
        // $student_data=[];
        // dd($all_student_id_data);
        $course_mast=Course::pluckActiveCodeAndName($college_id);
        //    dd($Final_student_data);
       
        $str = "<table border='1'>";
        $str .= "<thead>";
        $str .= "<tr style='background-color:green;color:white;'>";
        $str .= "<th >S.No</th>";
        $str .= "<th >RFID</th>";
        $str .= "<th >RFID GENEREATED ON</th>";
        $str .= "<th >NAME</th>";
        $str .= "<th >COURSE</th>";
        $str .= "<th >ROLL NO</th>";
        $str .= "<th >FATHER NAME</th>";
        $str .= "<th >ADDRESS</th>";
        $str .= "<th >MOBILE NO</th>";
        $str .= "<th >PICTURE</th>";
        $str .= "<th >SIGNATURE</th>";
        $str .= "<th >GUARDIAN Mobile NO</th>";
        $str .= "<th >BLOOD GROUP</th>";
        $str .= "<th >EMAIL ID</th>";
        $str .= "<th >DOB</th>";
        $str  .= "</tr>";
        $i=0;

        foreach($Final_student_data as $key => $value){
            $student_name=!empty($value->student_name)?$value->student_name:'-';
            $ref_id=!empty($value->rf_id)?$value->rf_id:'-';
            $rf_id_generated_on=!empty($value->rf_id_generated_date)?date('d/m/Y',strtotime($value->rf_id_generated_date)):'-';
            $course_id=!empty($course_mast[$value->course_id])?$course_mast[$value->course_id]:'-';
            $college_roll_no=!empty($value->college_roll_no)?$value->college_roll_no:'-';
            $father_name=!empty($value->father_name)?$value->father_name:'-';
            $address=!empty($value->address)?$value->address:'-';
            $student_no=!empty($value->student_no)?$value->student_no:'-';
            $id_card_photo=!empty($value->id_card_photo)?$value->id_card_photo:'-';
            $signature_photo=!empty($value->signature_photo)?$value->signature_photo:'-';
            $emergency_contact_no=!empty($value->emergency_contact_no)?$value->emergency_contact_no:'-';
            $blood_group=!empty($value->blood_group)?$value->blood_group:'-';
            $email=!empty($value->email)?$value->email:'-';
            $dob=!empty($value->dob)?$value->dob:'-';
            $str .= "<tr style='color:black;'>";
            $str .= "<td>".++$i."</td>";    
            $str .= "<td>".$ref_id."</td>";
            $str .= "<td>".$rf_id_generated_on."</td>";
            $str .= "<td>".$student_name."</td>";
            $str .= "<td>".$course_id."</td>";
            $str .= "<td>".$college_roll_no."</td>";
            $str .= "<td>".$father_name."</td>";
            $str .= "<td>".$address."</td>";
            $str .= "<td>".$student_no."</td>";
            $str .= "<td>".$id_card_photo."</td>";
            $str .= "<td>".$signature_photo."</td>";
            $str .= "<td>".$emergency_contact_no."</td>";
            $str .= "<td>".$blood_group."</td>";
            $str .= "<td>".$email."</td>";
            $str .= "<td>".$dob."</td>";
            $str  .= "</tr>";
        }
        header ( "Content-type: application/vnd.ms-excel" );
            header ( "Content-Disposition: attachment; filename= ID Card Data.xls" );
            header("Pragma: no-cache");
            header("Expires: 0");
            echo $str;    
            die();
        }

        public function submit_ref_id(Request $request){
            // dd(1);
            // dd($request);
            $college_roll_no=!empty($request->roll_number)?$request->roll_number:NULL;
            if(empty($college_roll_no)){
                return view('Reports/id_card_form');
            }else{
                $users_id=DB::table('user_profile')->where('roll_no',$college_roll_no)->pluck('users_id')->first();
                $id_card_data=DB::table('id_card_details')->where('user_id',$users_id)->first();
                $course_mast= DB::table('course_mast')->where('status',1)->pluck('name','id');
                // dd($id_card_data);
                return view('Reports/view_for_reference_id',[
                    'student_data' => $id_card_data,
                    'roll_no' => $college_roll_no,
                    'course' => $course_mast,
                    'users_id' => $users_id
                ]);

            }
        }

        public function store_reference_id(Request $request){
            // dd($request);
            $users_id=!empty($request->users_id)?$request->users_id:NULL;
            $ref_id=!empty($request->ref_id)?$request->ref_id:NULL;
            $id_card_data=DB::table('id_card_details')->where('user_id',$users_id)->first();
            if(!empty($id_card_data->rf_id)){
                $final_rf_if=$id_card_data->rf_id.','.$ref_id;

                $update_ref_id=DB::table('id_card_details')
                            ->where('user_id',$users_id)
                            ->update(['rf_id' => $final_rf_if,
                                    'printed_status'=> 1,
                                    'rf_id_generated_date' => date('Y-m-d')]);
            }else{
            $update_ref_id=DB::table('id_card_details')
                            ->where('user_id',$users_id)
                            ->update(['rf_id' => $ref_id,
                                    'printed_status'=> 1,
                                    'rf_id_generated_date' => date('Y-m-d')]);
            }
            if($update_ref_id){
                DB::commit();
                    $message = 'Reference ID Generated Successfully';
                    Session::flash('message', $message);
                }else {
                    DB::rollback();
                    $message = 'Something Went Wrong';
                    Session::flash('error', $message);
                }
        return redirect('submit_ref_id');
        }
}
 
