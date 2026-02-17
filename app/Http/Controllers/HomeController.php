<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Auth;

use App\Models\User_Profile;
use App\Models\Course;
use App\Models\Faculty;
use App\Models\Category;
use App\Models\Gender;
use App\Models\User;
use App\Models\Mailer;
use App\Mail\CustomMail2;

use App\Models\IndustryMast;
use App\Models\EmployeeCount;
use App\Models\CompanyTypeMast;
use App\Models\State;
use App\Models\StudentIdData;

use DB;

use Session;
use Mail;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {

        $auth_data = Auth::user();
        $users_id = $auth_data->id;
        $role_id = $auth_data->role_id;
        $college_id = $auth_data->college_id;
        $company_id = $auth_data->company_id;
        $email_verified_at = $auth_data->email_verified_at;

        $register_type = $auth_data->register_type;

        if($college_id==NULL && $company_id==NULL) {
            if(empty($email_verified_at)) {
                $myArr = [
                    'users_id' => $users_id,                    
                ];

                $random_number = rand(100000,999999);
                $otp_store = User::where('id', $users_id)
                                ->update(['remember_token'=>$random_number]);
                if(!$otp_store) {
                    dd('Unable to generate OTP at the present moment, Please try again later');
                }
                $user_email_id = 'vbhagg@gmail.com';
                $subject = 'New User Registration';
                $content = "Greetings of the day. Please use ".$random_number." as your OTP to proceed.";
                $blade_file = 'emails.forgot_password';
                $set_credentials = Mailer::set_credentials($college_id, $company_id);
                $response = Mail::to($user_email_id)
                                ->send(new CustomMail2($subject, $content, $blade_file));
                Session::forget('user_details');
                Session::push('user_details', $myArr);
                Auth::logout();
                return view('verify_otp');
            }
            else {
                if($register_type==1) { //company
                    $myArr = [
                        'users_id' => $users_id,                    
                    ];
                    Session::forget('user_details');
                    Session::push('user_details', $myArr);
                    Auth::logout();
                    $industry_arr = IndustryMast::pluckActiveData();
                    $employee_count_arr = EmployeeCount::pluckActiveData();
                    $company_type_arr = CompanyTypeMast::pluckActiveData();
                    $state_arr = State::pluckCodeAndName();
                    return view('company_register', [
                        'industry_arr' => $industry_arr,
                        'employee_count_arr' => $employee_count_arr,
                        'company_type_arr' => $company_type_arr,
                        'state_arr' => $state_arr
                    ]);
                }

                else if($register_type==2) {    //job seeker
                    return redirect('Jobs');
                }

                else {
                    dd('Undefined register type', $register_type);
                }

            }

        }
        else if($college_id==NULL && $company_id!=NULL) {
                return redirect('Jobdesk'); 
        }
        else if($college_id!=NULL && $company_id==NULL){
            if($role_id == 3) {
                $user=Auth::user();
                $college_id = $user->college_id;
                $user_profile_id = User_Profile:: getuserprofile($user->id);
                    return redirect('UserProfileMast');
            }
            else if($role_id == 4){
                if($college_id==44) {
                    return redirect('Attendance');
                }
                else {
                    return redirect('UserProfileMast');       
                }

            }
            else{
                $data = User_Profile::getStudentRecords('','','','','','',$college_id);
                $total_student_count = 0;
                $gender = [];
                $category = [];
                $state = [];
                $category_mast_cus=[];
                $course_arr = [];

                foreach($data as $key => $value) {
                    if(empty($value->passout_year)) {
                        $total_student_count++;
                    }
                    if(!in_array($value->course_id, $course_arr)) {
                        $course_arr[] = $value->course_id;
                    }
                    $gender[$value->gender_id] = !empty($gender[$value->gender_id])?($gender[$value->gender_id]+1):1;
                    $category[$value->category_id] = !empty($category[$value->category_id])?($category[$value->category_id]+1):1;

                }
                $course_count = count($course_arr);
                $faculty_count = Faculty::faculty_count_of_college($college_id);
                $category_mast = Category::pluckActiveCodeAndName($college_id);
                $gender_mast = Gender::pluckCodeAndName($college_id);
                $category_labels = [];
                $category_values = [];
                foreach($category as $catg_id => $catg_count) {
                        $category_labels[] = !empty($category_mast[$catg_id])?$category_mast[$catg_id]:'Not Mapped';
                        $category_mast_cus[] = $catg_id;
                        $category_values[] = $catg_count;
                }
                $course_count = Course::where('college_id', $college_id)->where('status',1)->count();
                return view('index',[
                    'total_student_count' => $total_student_count,
                    'gender' => $gender,
                    'course_count' => $course_count,
                    'faculty_count' => $faculty_count,
                    'category_labels' => $category_labels,
                    'category_values' => $category_values,
                    'gender_mast' => $gender_mast,
                    'category_mast_cus' => $category_mast_cus
                ]);
            }
        }
        else {
            dd('A user cant be registered in both college and company');
        }

    }

    public function dashboard() {
        $role_id = Auth::user()->role_id;
        $college_id = Auth::user()->college_id;
        if($role_id == 3) {
            return redirect('UserProfileMast');
        }
        // else if($role_id == 4  || $role_id == 5){
        else if($role_id == 4 ){
            if($college_id==44) {
                return redirect('Attendance');
            }
            else {
                return redirect('UserProfileMast');       
            }

        }
        else{
            $data = User_Profile::getStudentRecords('','','','','','',$college_id);
            $total_student_count = 0;
            $gender = [];
            $category = [];
            $state = [];
            $category_mast_cus=[];
            $course_arr = [];
            foreach($data as $key => $value) {
                if(empty($value->passout_year)) {
                    $total_student_count++;
                }
                
                if(!in_array($value->course_id, $course_arr)) {
                    $course_arr[] = $value->course_id;
                }
                $gender[$value->gender_id] = !empty($gender[$value->gender_id])?($gender[$value->gender_id]+1):1;
                $category[$value->category_id] = !empty($category[$value->category_id])?($category[$value->category_id]+1):1;

            }
            $course_count = count($course_arr);
            $faculty_count = Faculty::faculty_count_of_college($college_id);
            $category_mast = Category::pluckActiveCodeAndName($college_id);
            $gender_mast = Gender::pluckCodeAndName($college_id);

            $category_labels = [];
            $category_values = [];
            
            foreach($category as $catg_id => $catg_count) {
                    $category_labels[] = !empty($category_mast[$catg_id])?$category_mast[$catg_id]:'Not Mapped';
                    $category_mast_cus[] = $catg_id;
                    $category_values[] = $catg_count;
            }

            $course_count = Course::where('college_id', $college_id)->where('status',1)->count();

            return view('index',[
                'total_student_count' => $total_student_count,
                'gender' => $gender,
                'course_count' => $course_count,
                'faculty_count' => $faculty_count,
                'category_labels' => $category_labels,
                'category_values' => $category_values,
                'gender_mast' => $gender_mast,
                'category_mast_cus' => $category_mast_cus
            ]);
        }
    }
}
