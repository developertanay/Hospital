<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// use App\Models\College;
// use App\Models\Department;
// use App\Models\Gender;
use App\Models\Faculty;
// use App\Models\User_Profile;
// use App\Models\LeaveGroup;
use App\Models\Modules;
use App\Models\Role;
use App\Models\Activity_Restriction;
// use App\Models\CollegeDeptMapping;
use App\Mail\CustomMail2;
use App\Models\Mailer;

use Auth;
use DB;
use Crypt;
use Session;
use Mail;

class ActivityRestrictionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct() {
        $this->current_menu = 'ActivityRestriction';
    }


    public function index(Request $request)
    {   
      
        $college_id = Auth::user()->college_id;
        // $college = College::pluckActiveCodeAndName($users_id);
        $data=Activity_Restriction::getAllData($college_id);
        $faculty_mast=Faculty::getNameOnUserId($college_id);

        $module_mast=Modules::pluckUrlAndNameByCollege($college_id);
        // dd($module_mast);
        

        $Role_mast = Role::pluckActiveCodeAndName($college_id);
// dd($data);
        return view($this->current_menu.'/index', [
            'current_menu'=>$this->current_menu,
            'data' => $data,
            'faculty_mast'=>$faculty_mast,
            'role_mast'=>$Role_mast,
            'module_mast'=>$module_mast,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $college_id = Auth::user()->college_id;
        $faculty_mast=Faculty::getNameOnUserId($college_id);
        $module_mast=Modules::pluckUrlAndNameByCollege($college_id);
        $Role_mast = Role::pluckActiveCodeAndName($college_id);
        // dd($module_mast);
        
        // dd($department_id,$department_mast);
        // $department_mast = Department::getActiveDepartmentsFromUniversity(1); 
        return view($this->current_menu.'/create', [
            'current_menu' => $this->current_menu,
            'college_id' => $college_id,
            'faculty_mast'=>$faculty_mast,
            'module_mast' => $module_mast,
            'role_mast'=>$Role_mast,
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
        // dd($request);
        $user_data=Auth::user();
        $college_id=$user_data->college_id;
        $user_id=$user_data->id;
        $faculty_id=!empty($request->faculty_id)?$request->faculty_id:null;
        $role = !empty($request->role)?$request->role:NULL;
        $module_id = !empty($request->module_url)?$request->module_url:NULL;
        $applicable_till=!empty($request->applicable_till)?date('Y-m-d',strtotime($request->applicable_till)):null;
        $allowed_past_days = isset($request->allowed_past_days) ? $request->allowed_past_days : NULL; 
        $allowed_future_days = isset($request->allowed_future_days)?$request->allowed_future_days:NULL;
        $status = !empty($request->status)?$request->status:NULL;

        $created_at = date('Y-m-d H:i:s');
        $created_by = $user_id;
        

    //   dd($applicable_till);


        $myArr = [
         'college_id'=>$college_id,
         'module_url'=>$module_id,
         'role_id'=>$role,
         'users_id'=>$faculty_id,
         'applicable_till'=>$applicable_till,
         'allowed_past_days'=>$allowed_past_days,
         'allowed_future_days'=>$allowed_future_days,
         'request_date'=>date('Y-m-d H:i:s'),
         
         'status'=>$status,
         'sequence'=>100,
         'created_at' => $created_at,
         'created_by' => $created_by
        ];
// dd($myArr);
        DB::beginTransaction();
        $query = Activity_Restriction::create($myArr);
        
        if($query) {
            DB::commit();
            $message = 'Entry Saved Successfuly';
            Session::flash('message', $message);
        }else {
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
    public function edit($id) {
        // dd($id);
        $college_id = Auth::user()->college_id;
        $module_mast=Modules::pluckUrlAndNameByCollege($college_id);
        $faculty_mast=Faculty::getNameOnUserId($college_id);
        $Role_mast = Role::pluckActiveCodeAndName($college_id);
        $decrypted_id = Crypt::decryptString($id);
        $data=Activity_Restriction::where('id',$decrypted_id)
                                    ->first();
// dd($data);

        return view($this->current_menu.'/edit', [
            'current_menu' => $this->current_menu,
            'college_id' => $college_id,
            'data'=>$data,
            'faculty_mast'=>$faculty_mast,
            'module_mast' => $module_mast,
            'role_mast'=>$Role_mast,
            'decrypted_id'=>$decrypted_id,
            'encrypted_id' => $id
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id) {
        // dd($request);
        $decrypted_id = Crypt::decryptString($id);


        $user_data=Auth::user();
        $college_id=$user_data->college_id;
        $user_id=$user_data->id;
        $role = !empty($request->role)?$request->role:NULL;
        $faculty_id=!empty($request->faculty_id)?$request->faculty_id:null;

        $module_id = !empty($request->module_url)?$request->module_url:NULL;
        $applicable_till=!empty($request->applicable_till)?date('Y-m-d',strtotime($request->applicable_till)):null;

        $allowed_past_days = isset($request->allowed_past_days)?$request->allowed_past_days:NULL;
        $allowed_future_days = isset($request->allowed_future_days)?$request->allowed_future_days:NULL;
        $status = !empty($request->status)?$request->status:NULL;
        $created_at = date('Y-m-d H:i:s');
        $created_by = $user_id;
        
        

      


        $myArr = [
         'college_id'=>$college_id,
         'module_url'=>$module_id,
         'role_id'=>$role,
         'users_id'=>$faculty_id,
         'applicable_till'=>$applicable_till,

         'allowed_past_days'=>$allowed_past_days,
         'allowed_future_days'=>$allowed_future_days,
         'request_date'=>date('Y-m-d H:i:s'),
         
         'status'=>$status,
         'sequence'=>100,
         'created_at' => $created_at,
         'created_by' => $created_by
        ];
// DD($myArr);
        DB::beginTransaction();
        $query = Activity_Restriction::updateDataFromId($decrypted_id, $myArr);
        
        if($query) {
            DB::commit();
            $message = 'Entry Updated Successfuly';
            Session::flash('message', $message);
        }else {
            DB::rollback();
            $message = 'Something Went Wrong';
            Session::flash('error', $message);
        }

        return redirect($this->current_menu);    
    }

       
    

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
   {
    
       $decrypted_id = Crypt::decryptString($id);
       
    
    DB::beginTransaction();
    $delete = Activity_Restriction::findOrFail($decrypted_id);
    $delete->status = 9; // Assuming 1 represents active and 9 represents inactive
    $delete->save();
    DB::commit();
    $message = 'Entry Delete Successfuly';
            Session::flash('message', $message);

    return redirect()->route($this->current_menu.'.index');
   }


   public function extra_access_view() {
        return view($this->current_menu.'/extra_access_view');
   }

   public function extra_access_store(Request $request) {
        $auth_data = Auth::user();
        $users_id = $auth_data->id;
        $college_id = $auth_data->college_id;
        $role_id = $auth_data->role_id;
        
        $previous_days = !empty($request->previous_days)?$request->previous_days:0;
        $till_date = !empty($request->till_date)?date('Y-m-d', strtotime($request->till_date)):NULL;

        $application_date = date('Y-m-d');
        $module_url = 'Attendance';

        $faculty = Faculty::pluckDataFromCollege($college_id, $users_id);
        $faculty_name = array_values($faculty)[0];

        $mail_to = 'vbhagg@gmail.com';

        $myArr = [
            'college_id' => $college_id,
            'module_url' => $module_url,
            'role_id' => $role_id,
            'users_id' => $users_id,
            'allowed_past_days' => $previous_days,
            'allowed_future_days' => 0,
            'request_date' => $application_date,
            'applicable_till' => $till_date,
            'status' => 1,
            'sequence' => 100,
            'updated_at' => NULL,
            'updated_by' => NULL,
            'created_at' => $users_id,
            'created_by' => date('Y-m-d H:i:s')
        ];

        $query = Activity_Restriction::create($myArr);

        if($query) {
            $subject = 'Past Attendance Access - '.$faculty_name;
            $content = $faculty_name." has opened the extra access window for marking attendance for past ".$previous_days." days on ".date('d-M-Y')." till ".date('d-M-Y', strtotime($till_date));
            $blade_file = 'emails.forgot_password';
            $company_id = '';
            $set_credentials = Mailer::set_credentials($college_id, $company_id);
            $response = Mail::to($mail_to)
                            ->send(new CustomMail2($subject, $content, $blade_file));   
            $message = 'Access Granted Successfuly';
            Session::flash('message', $message);
        }
        else {
            $message = 'Something Went Wrong';
            Session::flash('error', $message);
        }
        return redirect('extra_access_view');

   }
  
    

}
