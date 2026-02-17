<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Auth;
use DB;
use Session;
use Crypt;
use App\Http\Controllers\MailController;


use App\Models\User;
use App\Models\User_Profile;
use App\Models\Faculty;


class PasswordController extends Controller
{
    //
    public function change_password(Request $request) {
        // dd($request);
        $current_password = !empty($request->current_password)?$request->current_password:null;
        $new_password = !empty($request->new_password)?$request->new_password:null;
        $confirm_new_password = !empty($request->confirm_new_password)?$request->confirm_new_password:null;

        if($new_password != $confirm_new_password) {
            dd('new and confirm new password do not match');
        }
        else if(empty($current_password)){
            dd('Current Password could not be empty');
        }
        else {
            //check current password is correct or not
            if(Auth::attempt(['email' => Auth::user()->email,'password' => $current_password])){
                // $user=Auth::user();
                $users_id = Auth::user()->id;
                $new_password_hash = Hash::make($new_password);
                $query = DB::table('users')
                            ->where('id', $users_id)
                            ->update([
                                'password' => $new_password_hash,
                                'updated_at' => date('Y-m-d H:i:s')
                            ]);
                if($query) {
                    return redirect('UserProfileMast');
                }
                else {
                    dd('Unable to update password');
                }
            }
            else {
                dd('Current Password is incorrect');
            }

        }
    }


    public function generate_faculty_login(Request $request) {
        // dd($request);
        $name = !empty($request->name)?$request->name:null;
        $faculty_id = !empty($request->faculty_id)?$request->faculty_id:null;
        $college_id = !empty($request->college_id)?$request->college_id:null;
        $dept_id = !empty($request->dept_id)?$request->dept_id:null;
        $email = !empty($request->email)?$request->email:null;
        $mobile = !empty($request->mobile)?$request->mobile:null;
        $checkbox = !empty($request->checkbox)?$request->checkbox:null;
        $role_id = !empty($request->role_id)?$request->role_id:4;   //4 stands for faculty
        // if($checkbox ==1){
        // $role_id = 5;
        // }
        // else{
        // $role_id = 4;

        // }
        if(!empty($mobile) && !empty($email) && !empty($name)) {
            //insert in users 
            $new_password_hash = Hash::make($mobile);
            
            $users_arr = [
                'name' => $name,
                'email' => $mobile,     //login mobile se hoga
                'password' => $new_password_hash,
                'created_at' => date('Y-m-d H:i:s'),
                'role_id' => $role_id,
                'college_id' => $college_id
            ];
            // dd($users_arr);
            DB::beginTransaction();
            $query = User::create($users_arr);
            if($query) {
                $users_id = $query->id;
                //insert in user_profile
                $user_profile_arr = [
                    'users_id' => $users_id,
                    'name' => $name,
                    'contact_no' => $mobile,
                    'email' => $email,
                    'college_id' => $college_id,
                    'course_id' => $dept_id,
                    'status' => 1,
                    'sequence' => 1000,
                    'created_at' => date('Y-m-d H:i:s'),
                    'created_by' => Auth::user()->id
                ];
                $user_profile_query = User_Profile::create($user_profile_arr);
                if($user_profile_query) {
                    $user_profile_id = $user_profile_query->id; 
                    $update_faculty_query = Faculty::where('id', $faculty_id)
                                                    ->update([
                                                        'user_profile_id' => $user_profile_id,
                                                        'users_id' => $users_id
                                                    ]);

                    if($update_faculty_query) {
                        DB::commit();
                    }
                }
                else{
                    DB::rollback();
                }
            }
            else {
                DB::rollback();
            }
                        
                  
        }
        else {
            dd('Mobile, Email and Name all are required feilds');
        }
        return redirect('FacultyMast');
    }


    public function autogenerate_faculty_login(Request $request) {
        dd($request, Auth::user()->college_id);
        $college_id_cus = Auth::user()->college_id;
        $faculty_data_db = DB::table('faculty_mast')
                            ->where('college_id', $college_id_cus)
                            ->where('users_id' , NULL)
                            ->where('user_profile_id' , NULL)
                            ->get();

        foreach($faculty_data_db as $faculty_data_db_key => $faculty_data_db_value) {
            $firstname = !empty($faculty_data_db_value->firstname)?$faculty_data_db_value->firstname:null;
            $lastname = !empty($faculty_data_db_value->lastname)?$faculty_data_db_value->lastname:null;
            $name = $firstname.' '.$lastname;
            $name = trim($name);
            $faculty_id = !empty($faculty_data_db_value->id)?$faculty_data_db_value->id:null;
            $college_id = !empty($faculty_data_db_value->college_id)?$faculty_data_db_value->college_id:null;
            $dept_id = !empty($faculty_data_db_value->department_id)?$faculty_data_db_value->department_id:null;
            $email = !empty($faculty_data_db_value->email_id)?$faculty_data_db_value->email_id:null;
            $mobile = !empty($faculty_data_db_value->whatsapp_no)?$faculty_data_db_value->whatsapp_no:null;
            
            // $checkbox = !empty($faculty_data_db_value->checkbox)?$faculty_data_db_value->checkbox:null;
            // if($checkbox ==1){
            // $role_id = 5;
            // }
            // else{
            $role_id = 4;

            // }
            // dd($name,$faculty_id,$college_id,$dept_id,$email,$mobile);

            if(!empty($mobile) && !empty($email) && !empty($name)) {
                //insert in users 
                $new_password_hash = Hash::make($mobile);
                
                $users_arr = [
                    'name' => $name,
                    'email' => $mobile,     //login mobile se hoga
                    'password' => $new_password_hash,
                    'created_at' => date('Y-m-d H:i:s'),
                    'role_id' => $role_id,
                    'college_id' => $college_id
                ];
                // dd($users_arr);
                DB::beginTransaction();
                $query = User::create($users_arr);
                if($query) {
                    $users_id = $query->id;
                    //insert in user_profile
                    $user_profile_arr = [
                        'users_id' => $users_id,
                        'name' => $name,
                        'contact_no' => $mobile,
                        'email' => $email,
                        'college_id' => $college_id,
                        'course_id' => $dept_id,
                        'status' => 1,
                        'sequence' => 1000,
                        'created_at' => date('Y-m-d H:i:s'),
                        'created_by' => Auth::user()->id
                    ];
                    $user_profile_query = User_Profile::create($user_profile_arr);
                    if($user_profile_query) {
                        $user_profile_id = $user_profile_query->id; 
                        $update_faculty_query = Faculty::where('id', $faculty_id)
                                                        ->update([
                                                            'user_profile_id' => $user_profile_id,
                                                            'users_id' => $users_id
                                                        ]);

                        if($update_faculty_query) {
                            DB::commit();
                        }
                    }
                    else{
                        DB::rollback();
                    }
                }
                else {
                    DB::rollback();
                }
                            
                      
            }
            else {
                dd('Mobile, Email and Name all are required feilds');
            }

        }
        dd('aagya bahar');

        // return redirect('FacultyMast');
    }


    public function reset_password(Request $request) {
        // dd($request);
        $college_id = !empty($request->college_id)?$request->college_id:NULL;
        $login_id = !empty($request->login_id)?$request->login_id:NULL;
        $page = !empty($request->page)?$request->page:NULL;

        if(empty($college_id) || empty($login_id)) {
            dd('Insufficient variables passed');
        }

        $created_at = date('Y-m-d H:i:s');
        $created_by = Auth::user()->id;

        //get data from users table
        $users_data = DB::table('users')
                            ->where('email', $login_id)
                            ->first();
        // dd($users_data,$login_id);
        if(empty($users_data)) {
            dd('User Credential Does Not Exist');
        }
        else {
            $users_id = $users_data->id;
            if($page=='faculty') {
                $new_password = $login_id;
            }
            else if($page=='student') {
                $new_password = 'Pass@123';
            }
            else {
                $new_password = $login_id;
            }
            $new_password_hash = Hash::make($new_password);

            $myArr = [
                'college_id' => $college_id,
                'users_id' => $users_id,
                'new_password_str' => $new_password,
                'created_at' => $created_at,
                'created_by' => $created_by
            ];

            
            $users_update_arr = [
                'password' => $new_password_hash,
                'updated_at' => date('Y-m-d H:i:s'),
            ];

            DB::beginTransaction();
            $update_query = DB::table('users')
                                ->where('id', $users_id)
                                ->update($users_update_arr);

            $insert_query = DB::table('reset_password')
                                ->insert($myArr);

            if($update_query && $insert_query) {
                DB::commit();
                Session::flash('message', 'Password Reset Successfully');
                Session::flash('alert-class', 'alert-success');
            }
            else {
                DB::rollback();
                Session::flash('message', 'Unable to Update Password!');
                Session::flash('alert-class', 'alert-danger');
            }
            
            if($page=='faculty') {
                return redirect('FacultyMast');
            }
            else if($page=='student') {
                return redirect('Student');
            }
            else {
                return redirect('UserProfileMast');
            }
        }

    }

    public function forgot_password_submit(Request $request) {
        // dd($request);
        $login_id = !empty($request->user_id)?$request->user_id:NULL;
        $data['code'] = 200;
        if($login_id == NULL) {
            $data['alert_message'] = 'PLEASE ENTER USER ID';
        }
        else {
            //check if the login id exists 
            $users_data = DB::table('users')
                            ->where('email', $login_id)
                            ->first();

            if(empty($users_data)) {
                $data['alert_message'] = 'Entered User Id does not exist in our records';
            }
            else {
                //record found in users table
                //get the associated email id from user_profile
                $users_id = $users_data->id;
                $college_id = !empty($users_data->college_id)?$users_data->college_id:NULL;
                $company_id = !empty($users_data->company_id)?$users_data->company_id:NULL;

                $user_profile_data = DB::table('user_profile')
                                        ->where('users_id', $users_id)
                                        ->first();
                if(empty($user_profile_data)) {
                    $data['alert_message'] = 'Your Profile Does Not Exist, Contact Admin';
                }
                else {
                    $email = $user_profile_data->email;
                    if(empty($email)) {
                        $data['alert_message'] = 'Your Email Id Does Not Exist, Contact Admin';
                    }
                    else {
                        //send code on email
                        // dd($email);
                        $token = self::generateRandomString();
                        // dd($token);
                        $MailController = app(MailController::class);
                        $result = $MailController->forgot_password_mail($email, $token, $college_id, $company_id);
                        // dd($result);
                        if($result) {
                            //mail sent successfuly
                            $data['token'] = $token;
                            $users_update = DB::table('users')
                                                ->where('id', $users_id)
                                                ->update([
                                                    'remember_token' => $token
                                                ]);
                            if($users_update) {

                            }
                            else {
                                $data['alert_message'] = 'Security Token Fault, Please Try Again Later';    
                            }

                        }
                        else {
                            $data['alert_message'] = 'Something Went Wrong';
                        }
                    }
                }
            }
        }
        return json_encode($data);
    }

    function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    function generate_password(Request $request) {
        $login_id = !empty($request->user_id)?$request->user_id:NULL;
        $user_token = !empty($request->user_token)?$request->user_token:NULL;
        $new_p1 = !empty($request->new_p1)?$request->new_p1:NULL;
        $new_p2 = !empty($request->new_p2)?$request->new_p2:NULL;
        $data['code'] = 200;
        if($login_id==NULL || $user_token==NULL || $new_p1==NULL || $new_p2==NULL) {
            // dd($login_id, $user_token, $new_p1, $new_p2);
            $data['alert_message'] = 'Requirements Not Fulfilled';
            // $data['alert_message'] = $login_id.'-'.$user_token.'-'.$new_p1.'-'.$new_p2;
        }
        else {
            $new_password_hash = Hash::make($new_p1);
            $query = DB::table('users')
                        ->where('email', $login_id)
                        ->where('remember_token', $user_token)
                        ->update([
                            'password' => $new_password_hash,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
            if($query) {
                $data['success'] = 'Password Updated Successfully. Please Login to Continue.';
            }
            else {
                $data['alert_message'] = 'Authentication Failure.';
            }
        }
        return json_encode($data);
    }


    public function verify_otp_submit(Request $request) {
        // dd($request);
        $otp = !empty($request->otp)?$request->otp:NULL;
        $data['code'] = 200;
        if($otp == NULL) {
            $data['alert_message'] = 'PLEASE ENTER OTP';
        }
        else {
            
            $session_data = Session::get('user_details');
            $email = !empty($session_data[0]['email'])?$session_data[0]['email']:NULL;
            $users_id = !empty($session_data[0]['users_id'])?$session_data[0]['users_id']:NULL;
            $college_id = !empty($session_data[0]['college_id'])?$session_data[0]['college_id']:NULL;
            $role_id = !empty($session_data[0]['role_id'])?$session_data[0]['role_id']:NULL;
            $password = !empty($session_data[0]['password'])?$session_data[0]['password']:NULL;
            if(!empty($password)) {
                $original_password = Crypt::decryptString($password);
            }
            else {
                $original_password = ' ';   //in case of company register, this would be applicable to make the email and password same condition false
            }

            $users_data = DB::table('users')
                                ->where('id', $users_id)
                                ->first();
            $original_otp = $users_data->remember_token;

            if($otp==$original_otp) {
                // $host = $_SERVER['HTTP_HOST'];
                // dd($host);

                //check if password is changed or not 
                    //if changed, then redirect to /
                    if($email == $original_password) {
                        $data['url'] = 'cpview';
                    }
                    else {
                        // $auth_attempt = Auth::attempt([
                        //             'email' => $email,
                        //             'password' => $original_password,
                        //             'college_id' => $college_id
                        //         ]);

                        $datetime = date('Y-m-d H:i:s');
                        $users_data = DB::table('users')
                                        ->where('id', $users_id)
                                        ->update(['email_verified_at'=> $datetime]);

                        $auth_attempt = Auth::loginUsingId($users_id);
                        $data['url'] = '/';
                    }
                    //if not then redirect to change password change


                return json_encode($data);
            }
            else {
                $data['alert_message'] = 'Incorrect OTP Entered, Please Retry.';
                return json_encode($data);
            }
        }
    }

    public function change_password_at_login(Request $request) {
        $new_password = $request->new_password;
        $old_password = $request->old_password;

        $session_data = Session::get('user_details');
        $email = $session_data[0]['email'];
        $users_id = $session_data[0]['users_id'];
        $college_id = $session_data[0]['college_id'];
        $role_id = $session_data[0]['role_id'];
        $password = $session_data[0]['password'];
        $original_password = Crypt::decryptString($password);
        $data['code'] = 200;
        
        if($old_password != $original_password) {
            $data['alert_message'] = 'Incorrect Old Password Entered.';
        }
        else {
            $new_password_hash = Hash::make($new_password);
            
            $query = DB::table('users')
                        ->where('id', $users_id)
                        ->update([
                            'password' => $new_password_hash,
                            'updated_at' => date('Y-m-d H:i:s')
                        ]);
            if($query) {
                $auth_attempt = Auth::attempt([
                                        'email' => $email,
                                        'password' => $new_password,
                                        'college_id' => $college_id
                                    ]);
                $data['url'] = '/';
                Session::flash('message', 'Password Updated Successfully');
                Session::flash('alert-class', 'alert-success');
            }
            else {
                $data['alert_message'] = 'Nothing Updated';   
            }

        }

        return json_encode($data);
    }

    public function verify_otp_registration(Request $request) {
        
    }
}
