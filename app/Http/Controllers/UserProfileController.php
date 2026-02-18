<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Hash;

use Illuminate\Http\Request;
use App\Models\Gender;
use App\Models\Category;
use App\Models\User_Profile;
use App\Models\State;
use App\Models\Hospital;
use App\Models\Course;
use App\Models\Qualification;
use App\Models\Documents;
use App\Models\Subject;
use App\Models\SubjectType;
use App\Models\User_Qualification;
use App\Models\User_Document;
use App\Models\Department;
use App\Models\UserApprovedSubjects;
use App\Models\AttendanceBody;
use App\Models\AttendanceHead;
use App\Models\AcademicYear;



use Auth;
use DB;
use Session;
use Crypt;

class UserProfileController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

     public function __construct() {
        $this->current_menu = 'UserProfileMast';
      }


    public function index() {
        $auth_data = Auth::user();
        $users_id = $auth_data->id;
        $username = $auth_data->name;
        $enrollment_no = $auth_data->email;
        $role_id = $auth_data->role_id;
        $college_id  = $auth_data->college_id;
        $college_mast = Hospital::pluckActiveCodeAndName();

        $college = '';
        $course = '';

        $csas_form_number='';
        $college_roll_no='';
        $semester='';
        $user_profile_data = User_Profile::getDataFromUsersId($users_id);
        if(!empty($user_profile_data)) {
            $college = $college_mast[$college_id];
            $csas_form_number=!empty($user_profile_data->csas_form_number)?$user_profile_data->csas_form_number:'';
            $college_roll_no=!empty($user_profile_data->roll_no)?$user_profile_data->roll_no:'';
            $semester=!empty($user_profile_data->semester)?$user_profile_data->semester:'';
            if($role_id==3) {
                // $course_mast =Course::pluckCodeAndName();
                $course = !empty($course_mast[$user_profile_data->course_id])?$course_mast[$user_profile_data->course_id]:'';
            }
            else if($role_id==4) {
                // $department_mast = Department::pluckCodeAndName();
                $course = !empty($department_mast[$user_profile_data->course_id])?$department_mast[$user_profile_data->course_id]:'';
            }
            else {
                $course = '';
            }
        }
        else {

        }

        $user_profile_id= User_Profile::getDataFromUsersId($users_id);
        // $academic_year =  AcademicYear::getLatestYear($college_id);
        $subjects = [];
        $attendance_head_id = [];
        $attendance_body_data = [];
        
        // $subjects = UserApprovedSubjects::getApprovedSubjectsid($user_profile_id->academic_year,$user_profile_id->id,$user_profile_id->semester);
        // // dd($user_profile_id,$academic_year,$subjects,$user_profile_id->academic_year);
        // $attendance_head_id = AttendanceHead::getAttendanceHeadId($user_profile_id->academic_year,$college_id,$user_profile_id->semester,$subjects);
        // $attendance_body_data=AttendanceBody::pluckAttendanceData($attendance_head_id,$user_profile_id->id);
        // $present_count=0;
        // $total_attendance=0;
        // foreach($attendance_body_data as $key =>$value){
        //         $total_attendance=$total_attendance+$value->total_lectures;
        //         $present_count=$present_count+$value->lectures;
        //     }

        // dd($total_attendance,$present_count);
        $user_arr = [
            'users_id' => $users_id, 
            'username' => $username, 
            'enrollment_no' => $enrollment_no, 
            'role_id' => $role_id, 
            'college' => $college, 
            'course' => $course, 
            'user_profile_data' => $user_profile_data,
            
        ];
        // dd($user_arr);
        return view('profile', [
            'user_arr' => $user_arr,
            'csas_form_number' => $csas_form_number,
            'college_roll_no' => $college_roll_no,
            'semester' => $semester,
            // 'total_attendance'=>$total_attendance,
            // 'present_count'=>$present_count,
            'user_profile_id'=>$user_profile_id,
            // 'academic_year'=>$academic_year,
            'role_id'=>$role_id,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        $gender_mast = Gender::pluckActiveCodeAndName();
        $category_mast = Category::pluckActiveCodeAndName();
        $state_mast = State::pluckCodeAndName();
        $college_mast = Hospital::pluckActiveCodeAndName();
        $course_mast =Course::pluckCodeAndName();
        $qualification_mast= Qualification::pluckCodeAndName();
        $document_mast= Documents::pluckCodeAndName();
        // $SEC=Subject::pluckSEC();
        // $data = User_Profile::getAllRecords();
        
        return view($this->current_menu.'/create',[
                 'current_menu' => $this->current_menu,
                 'gender_mast'=>$gender_mast,
                 'category_mast'=>$category_mast,
                 'state_mast'=>$state_mast,
                 'college_mast'=>$college_mast,
                 'course_mast'=>$course_mast ,
                 'qualification_mast'=>$qualification_mast,
                 'document_mast'=>$document_mast,

        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // dd($requezst);
        $name = !empty($request->name)?$request->name:NULL;
        $father_name = !empty($request->father_name)?$request->father_name:NULL;
        $mother_name = !empty($request->mother_name)?$request->mother_name:NULL;
        $contact_no = !empty($request->contact_no)?$request->contact_no:NULL;
        $parent_contact_no = !empty($request->parent_contact_no)?$request->parent_contact_no:NULL;
        $dob = !empty($request->dob)?date('Y-m-d', strtotime($request->dob)):NULL;
        $gender = !empty($request->gender)?$request->gender:NULL;
        $category = !empty($request->category)?$request->category:NULL;
        $email = !empty($request->email)?$request->email:NULL;
        $current_address = !empty($request->current_address)?$request->current_address:NULL;
        $current_state = !empty($request->current_state)?$request->current_state:NULL;
        $current_pin_code = !empty($request->current_pin_code)?$request->current_pin_code:NULL;
        $address_same_checkbox = !empty($request->address_same)?1:NULL;
        $permanent_address = !empty($request->permanent_address)?$request->permanent_address:NULL;
        $permanent_state = !empty($request->permanent_state)?$request->permanent_state:NULL;
        $permanent_pin_code = !empty($request->permanent_pin_code)?$request->permanent_pin_code:NULL;
        $college = !empty($request->college)?$request->Hospital:NULL;
        $course = !empty($request->course)?$request->course:NULL;
        $semester = !empty($request->semester)?$request->semester:NULL;
        $enrollment_no = !empty($request->enrollment_no)?$request->enrollment_no:NULL;
        $cuet_score = !empty($request->cuet_score)?$request->cuet_score:NULL;


        $qualification_arr = !empty($request->qualification)?$request->qualification:[];
        $board_university_name_arr = !empty($request->board_university_name)?$request->board_university_name:[];
        $school_college_name_arr = !empty($request->school_college_name)?$request->school_college_name:[];
        $marks_arr = !empty($request->marks)?$request->marks:[];
        $passing_year_arr = !empty($request->passing_year)?$request->passing_year:[];
        $academic_docs_arr = !empty($request->file('document'))?$request->file('document'):[];
        $document_type_arr = !empty($request->document_type)?$request->document_type:[];
        // dd( $document_type_arr);
         $additional_docs = !empty($request->file('document1'))?$request->file('document1'):[];
       
        $updated_at = NULL;
        $updated_by = NULL;
        $created_at = date('Y-m-d H:i:s');
        $created_by = Auth::user()->id;

        $file = !empty($request->file('image'))?$request->file('image'):null;
        // dd($file);

        if(!empty($file)) {
            $extension = $file->getClientOriginalExtension();
            $fileName = date('YmdHis').rand(10,99).'.'.$extension;
                
            $destinationPath_profile = public_path('/images/userprofile');
            $pathForDB_profile = 'public/images/userprofile/'.$fileName;
            
            $file->move($destinationPath_profile, $fileName);
        }
        else {
            $pathForDB_profile = NULL;
        }




        $myArr =[
            'name' => $name,
            'father_name' => $father_name,
            'mother_name' => $mother_name,
            'contact_no' => $contact_no,
            'parent_contact_no' => $parent_contact_no,
            'dob' => $dob,
            'gender_id' => $gender,
            'category_id' => $category,
            'email' => $email,
            'photo' => $pathForDB_profile,
            'current_address' => $current_address,
            'current_state_id' => $current_state,
            'current_pincode' => $current_pin_code,
            'address_same_flag' => $address_same_checkbox,
            'permanent_address' => $permanent_address,
            'permanent_state_id' => $permanent_state,
            'permanent_pincode' => $permanent_pin_code,
            'college_id' => $college,
            'course_id' => $course,
            'enrolment_no' => $enrollment_no,
            'semester' => $semester,
            'cuet_score' => $cuet_score,
            'updated_at' => $updated_at,
            'updated_by' => $updated_by,
            'created_at' => $created_at,
            'created_by' => $created_by
            ];
 
            DB::beginTransaction();
            $query=User_Profile::create($myArr);
            if($query){
            DB::commit();
            }  else {
            DB::rollback();
            }
            $user_id=$query->id;

            // dd($query->id);


            $final_academics_arr=[];
            foreach($qualification_arr as $key => $value){

                 if(!empty($academic_docs_arr[$key])) {
                     $file = $academic_docs_arr[$key];
                        $extension = $file->getClientOriginalExtension();
                        $docsName = date('YmdHis').rand(10,99).'.'.$extension;
                            
                        $destinationPath_academics = public_path('/images/academics_documents');
                        $pathForDB_academics = 'public/images/academics_documents/'.$docsName;
                        
                        $file->move($destinationPath_academics, $docsName);
                    }
                    else {
                        $pathForDB_academics = NULL;
                    }

                $insert_academics_arr =[
                    'qualification_id'=>$value,
                    'board_university'=>$board_university_name_arr[$key],
                    'school_college'=>$school_college_name_arr[$key],
                    'marks'=>$marks_arr[$key],
                    'passing_year'=>$passing_year_arr[$key],
                    'document'=>$pathForDB_academics,
                    'user_profile_id'=>$user_id,
                    'updated_at' => $updated_at,
                    'updated_by' => $updated_by,
                    'created_at' => $created_at,
                    'created_by' => $created_by,
                ];

                $final_academics_arr[] = $insert_academics_arr;

            }
            // dd($final_academics_arr);

            $final_additional_docs_arr =[];

            foreach($document_type_arr as $key => $value){

                if(!empty($additional_docs[$key])) {
                        $file=$additional_docs[$key];
                        $extension = $file->getClientOriginalExtension();
                        $docsName = date('YmdHis').rand(10,99).'.'.$extension;
                            
                        $destinationPath_additional = public_path('/images/additional_documents');
                        $pathForDB_additional = 'public/images/additional_documents/'.$docsName;
                        
                        $file->move($destinationPath_additional, $docsName);
                    }
                    else {
                        $pathForDB_academics = NULL;
                    }


                    $insert_additional_docs =[

                            'document_id'=>$value,
                            'file_path'=>$pathForDB_additional,
                            'user_profile_id'=>$user_id,  
                            'updated_at' => $updated_at,
                            'updated_by' => $updated_by,
                            'created_at' => $created_at,
                            'created_by' => $created_by,
                    ];

                    $final_additional_docs_arr[]= $insert_additional_docs;

            }

              // dd($final_additional_docs_arr);      
          

           DB::beginTransaction();
            $academics_query=User_Qualification::insert($final_academics_arr);
             if( $academics_query){
            DB::commit();
            }  else {
            DB::rollback();
            }

            DB::beginTransaction();
            $additional_docs_query=User_Document::insert($final_additional_docs_arr);
           if($additional_docs_query ){
            DB::commit();
            $message = 'Entry Saved Successfuly';
            Session::flash('message', $message);
            }  else {
            DB::rollback();
            $message = 'Something Went Wrong';
            Session::flash('error', $message);
            }

           return redirect($this->current_menu); 

       
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($encid)
    {
        // $gender_mast = Gender::pluckActiveCodeAndName();
        // $category_mast = Category::pluckActiveCodeAndName();
        // $state_mast = State::pluckCodeAndName();
        // $college_mast = Hospital::pluckActiveCodeAndName();
        // $course_mast =Course::pluckCodeAndName();
        // $qualification_mast= Qualification::pluckCodeAndName();
        // $document_mast= Documents::pluckCodeAndName();
        // // $SEC=Subject::pluckSEC();
        // // $data = User_Profile::getAllRecords();
        
        // return view($this->current_menu.'/edit',[
        //          'current_menu' => $this->current_menu,
        //          'gender_mast'=>$gender_mast,
        //          'category_mast'=>$category_mast,
        //          'state_mast'=>$state_mast,
        //          'college_mast'=>$college_mast,
        //          'course_mast'=>$course_mast ,
        //          'qualification_mast'=>$qualification_mast,
        //          'document_mast'=>$document_mast,

        // ]);
        // dd(123, 'rahul', $encid);
        $id=Crypt::decrypt($encid);
        $gender_mast = Gender::pluckActiveCodeAndName();
        $category_mast = Category::pluckActiveCodeAndName();
        $state_mast = State::pluckCodeAndName();
        $college_mast = Hospital::pluckActiveCodeAndName();
        $course_mast =Course::pluckCodeAndName();
        $qualification_mast= Qualification::pluckCodeAndName();
        $document_mast= Documents::pluckCodeAndName();
        $data=DB::table('user_profile')->where('id',$id)->first();
        // dd($data);
        // $SEC=Subject::pluckSEC();
        // $data = User_Profile::getAllRecords();
        
        return view($this->current_menu.'/edit',[
            'current_menu' => $this->current_menu,
            'gender_mast'=>$gender_mast,
            'category_mast'=>$category_mast,
            'state_mast'=>$state_mast,
            'college_mast'=>$college_mast,
            'course_mast'=>$course_mast ,
            'qualification_mast'=>$qualification_mast,
            'document_mast'=>$document_mast,
            'data'=>$data

        ]);
        
        
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }



    public function update_step1(Request $request , $id)
    {
                    
                    if(!empty($request->image)) {
                        $extension = $request->image->getClientOriginalExtension();
                        $fileName = date('YmdHis').rand(10,99).'.'.$extension;
                            
                        $destinationPath_profile = public_path('/images/userprofile');
                        $pathForDB_profile = 'images/userprofile/'.$fileName;
                        
                        $request->image->move($destinationPath_profile, $fileName);
                    }
                    else {
                        $pathForDB_profile = NULL;
                    }
                $user=DB::table('user_profile')->where('id',$id)->first();

                $arr=[

                'name' =>$request->name, 
                'father_name' =>$request->father_name, 
                'mother_name' =>$request->mother_name, 
                'contact_no' =>$request->contact_no, 
                'parent_contact_no' =>$request->parent_contact_no, 
                'dob' =>$request->dob, 
                'photo'=>$pathForDB_profile,
                'gender_id' =>$request->gender_id, 
                'category_id' =>$request->category_id, 
                'email' =>$request->email, 
                'current_address' =>$request->current_address, 
                'current_state_id' =>$request->current_state_id, 
                'current_pincode' =>$request->current_pincode, 
                'address_same_flag' =>$request->address_same_flag, 
                'permanent_address' =>$request->permanent_address, 
                'permanent_state_id' =>$request->permanent_state_id, 
                'permanent_pincode' =>$request->permanent_pincode, 
                'college_id' =>$request->college_id, 
                'course_id' =>$request->course_id, 
                'semester' =>$request->semester, 
                'enrolment_no' =>$request->enrolment_no, 
                'cuet_score' =>$request->cuet_score, 
                ];
                // dd($arr);
                $query= DB::table('user_profile')->where('id', $id)->update($arr);
    }
    public function update_basic_details(Request $request) {
    

        // dd($request->document); 
        $user_profile_id=$request->id;
        $qualification_id_arr=$request-> qualification;
        $board_university_arr = $request->board_university_name;
        $school_college_arr = $request->school_college_name;
        $marks_arr = $request->marks;
        $passing_year_arr = $request->passing_year;
        
        $document_arr =[];

        foreach($request->document as $key=>$value){

            if(!empty($value)) {
                $extension = $value->getClientOriginalExtension();
                $fileName = date('YmdHis').rand(10,99).'.'.$extension;
                // $file_arr=[
                //     $fileName
                // ];
             
                $destinationPath_profile = public_path('document');
                    $pathForDB_profile = 'document/'.$fileName;
                    
                    $filePath=$value->move($destinationPath_profile, $fileName);
                    
               
            }
            else {
                $filePath = NULL;
            }
            $document_arr[$key]=$pathForDB_profile;
        }
        // dd($document_arr);

        
        // dd($document_arr);
        $insert_arr = [];
        $final_insert_arr = [];

             foreach($qualification_id_arr as $key => $value) {
                if(!empty($value)) {
                    $insert_arr = [
                        'user_profile_id' => $user_profile_id,
                        'qualification_id' => $value,
                        'board_university' => $board_university_arr[$key],
                        'school_college' => $school_college_arr[$key],
                        'marks' => $marks_arr[$key],
                        'passing_year' => $passing_year_arr[$key],
                        'document' => $document_arr[$key],
                        'status' => 1,
                        'sequence' => 100,
                        'updated_at' => NULL,
                        'updated_by' => NULL,
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => Auth::user()->id,
                    ]; 
                    $final_insert_arr[] = $insert_arr;
                }

             }
            //  dd($final_insert_arr);
            //  dd($arr2);

            //  dd($arr[]);
            
   
        //    if (!$request->id || DB::table('user_qualification')->where('id', $request->id)->doesntExist()) {
        if (count($final_insert_arr)>0) {
            $delete_query = DB::table('user_qualification')->where('user_profile_id', $user_profile_id)->delete();
            // Insert a new record
            $insert_query = DB::table('user_qualification')->insert($final_insert_arr);
        }
    

    }
    public function update_documents(Request $request) {

        $user_profile_id = $request->id;
    
        $insert_arr = [];
        $final_insert_arr = [];
        
        $document_arr=[];
        foreach($request->document1 as $key=>$value){

            if(!empty($value)) {
                $extension = $value->getClientOriginalExtension();
                $fileName = date('YmdHis').rand(10,99).'.'.$extension;
                // $file_arr=[
                //     $fileName
                // ];
                    
                $destinationPath_profile = public_path('document');
                    $pathForDB_profile = 'document/'.$fileName;
                    
                    $filePath=$value->move($destinationPath_profile, $fileName);
               
            }
            else {
                $filePath = NULL;
            }
            $document_arr[$key]=$pathForDB_profile;
        }
        // dd($document_arr);


        foreach($request->document_type as $key=>$value)
        {
            if(!empty($value)) {
                $insert_arr = [
           
             'user_profile_id'=> $user_profile_id,
             'document_id'=>$value,
             'file_path'=>$document_arr[$key],
             'status'=>1,
             'sequence'=>100,
             'updated_at'=>null,
             'updated_by'=>null,
             'created_at'=>date('Y-m-d H:i:s'),
             'created_by'=>Auth::user()->id,
                ];
                // dd($insert_arr);
                $final_insert_arr[] = $insert_arr;

            }
        }
    // dd($final_insert_arr);
        if (count($final_insert_arr)>0) {
            $delete_query = DB::table('user_documents')->where('user_profile_id', $user_profile_id)->delete();
            // Insert a new record
            $insert_query = DB::table('user_documents')->insert($final_insert_arr);
        }

        return redirect('UserProfileMast');
    }


    public function update_summary_profile(Request $request) {
        // dd($request);
        $users_id = Auth::user()->id;
        $present_mob_no = Auth::user()->email;  //login is with mobile number
        $role_id = Auth::user()->role_id;

        $mobile = !empty($request->mobile)?$request->mobile:'';
        $email_id = !empty($request->email)?$request->email:NULL;
        $profile_picture = !empty($request->profile_picture)?$request->profile_picture:NULL;

        $users_update_arr = [];
        $user_profile_update_arr = [];

        if(!empty($mobile) && !empty($email_id)) {
            if($role_id == 4) { //for faculty only
                if($present_mob_no == $mobile) {
                    //no need to update mobile number in users and user_profile
                }
                else{
                    $new_password_hash = Hash::make($mobile);
                    $users_update_arr = [
                        'email' => $mobile,
                        'password' => $new_password_hash
                    ];

                    
                $query1 = DB::table('users')
                            ->where('id', $users_id)
                            ->update($users_update_arr);
                }
            }
            $user_profile_update_arr['contact_no'] = $mobile;
            $user_profile_update_arr['email'] = $email_id;

            $file = !empty($request->file('profile_picture'))?$request->file('profile_picture'):null;

            if(!empty($file)) {
                $extension = $file->getClientOriginalExtension();
                $fileName = date('YmdHis').rand(10,99).'.'.$extension;
                    
                $destinationPath = public_path('/images/userprofile');
                $pathForDB = 'images/userprofile/'.$fileName;
                
                $file->move($destinationPath, $fileName);
                $user_profile_update_arr['photo'] = $pathForDB;
            }
            
            //update queries

            $query2 = DB::table('user_profile')
                        ->where('users_id', $users_id)
                        ->update($user_profile_update_arr);

            // dd($user_profile_update_arr, $users_update_arr);
        }
        else {
        
        }
        $message = 'Profile Updated Successfuly';
            Session::flash('message', $message);
        return redirect('UserProfileMast');

        
    }

}
