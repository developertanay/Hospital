<?php
namespace App\Http\Controllers\API;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Hash;
use Validator;
use DB;
use Mail;
use App\Mail\CustomMail2;


class LoginController extends Controller {
      
    public function login(Request $request) {
        $validator=Validator::make($request->all(),[
            'username' => 'required',
            'password' => 'required',         
        ]);
        if($validator->fails()) {
            return response()->json(['response'=>FALSE,'message'=>'Validation Error!','Error'=>$validator->errors()],200);
        }
        $username = $request->username;
        $password = $request->password;
        $current_date = date('Y-m-d');

        if(Auth::attempt(['email' => $username,'password' => $password])){
            $user=Auth::user();
            $college_id = $user->college_id;
            if($college_id==44) {
                $return_data = self::check_on_server2($username, $password);
                if($return_data['status']) {
                    $url = $return_data['url'];
                    return response()->json(['response'=>TRUE,'url'=>$url]);
                }
                else {
                    return response()->json(['response'=>FALSE,'message'=>'Invalid Username or Password','Error'=>'Invalid Username or Password'],200);
                }                

            }
            else {
                $college_data = DB::table('college_mast')->where('id', $college_id)->first();
                $host = !empty($college_data->host)?$college_data->host:NULL;
                if(!empty($host)) {
                    $url = $host.'.msell.in/public/api_login';
                    $new_url = $url.'?username='.$username.'&password='.$password;
                    return response()->json(['response'=>TRUE,'url'=>$new_url]);
                }
                else {
                    return response()->json(['response'=>FALSE,'message'=>'College Host Not Defined','Error'=>'College Host Not Defined'],200);
                }
       
            }
        }
        else {
            //check on server 2
            $return_data = self::check_on_server2($username, $password);
            // dd($return_data);

            if($return_data['status']) {
                $url = $return_data['url'];
                return response()->json(['response'=>TRUE,'url'=>$url]);
            }
            else {
                return response()->json(['response'=>FALSE,'message'=>'Invalid Username or Password','Error'=>'Invalid Username or Password'],200);

            }


        }



    }

    public function check_on_server2($username, $password) {
        // dd(1);
        $url = 'https://erp.ramanujancollege.ac.in/api/check_login_creds';
        $httpMethod ="POST";
        // $header = $curl_header;
        $header = array(
                'Content-Type:application/json'
            );
        $body_arr['username'] = $username;
        $body_arr['password'] = $password;
        $body_json['response'] = json_encode($body_arr);
        // dd($body_json);
        // $post_body_str = 'username='.$username.'&password='.$password;
        $post_body_str = http_build_query(array('username' => $username, 'password' => $password));
        // dd($post_body_str);
        // $curl = curl_init();

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body_json);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        $response_decode = json_decode($response);
        curl_close($ch);
        // dd('printing response in call',$response_decode); die;
        if($response_decode->response) {
            $return_arr['status'] = $response_decode->response;
            $url = $response_decode->url;
            $new_url = $url.'?username='.$username.'&password='.$password;
            $return_arr['url'] = $new_url;
        }
        else {
            $return_arr['status'] = $response_decode->response;
        }
        return $return_arr;

        // $response2_arr = json_decode($response2);
    }

    public  function check_login_creds(Request $request) {
        $response = json_decode($request->response);
        // print_r($response);
        
        // return response()->json([ 'response' =>True,'Sync_status'=>$response,'Sync_date_time'=>date('Y-m-d H:i:s')]);

        $username = $response->username;
        $password = $response->password;

        if(Auth::attempt(['email' => $username,'password' => $password])){
            $user=Auth::user();
            $college_id = $user->college_id;
            return response()->json(['response'=>TRUE,'url'=>'erp.ramanujancollege.ac.in/api_login']);
        }
        else {
            return response()->json(['response'=>FALSE,'message'=>'Invalid Credentials','Error'=>'Invalid Credentials'],200);
        }

    }

    public function sendOtp(Request $request) {
        $validator=Validator::make($request->all(),[
            'username' => 'required',         
        ]);
        if($validator->fails()) {
            return response()->json(['response'=>FALSE,'message'=>'Validation Error!','Error'=>$validator->errors()],200);
        }
        $username = $request->username;
        
        //check in users
        $users_data = DB::table('users')->where('email', $username)->where('college_id', '!=', 44)->first();
        if(!empty($users_data)) {
            //user_found on primary server, get user_profile and send email to the registered email id
            $users_id = $users_data->id;
            $user_profile_data = DB::table('user_profile')
                                    ->where('users_id', $users_id)
                                    ->first();
            if(!empty($user_profile_data)) {
                $email_id = !empty($user_profile_data->email)?$user_profile_data->email:NULL;
                if(!empty($email_id)) {
                    //sent otp on this mail

                    $random_number = rand(100000,999999);
                    // dd($random_number);

                    $update_otp = DB::table('users')
                                    ->where('id', $users_id)
                                    ->update([
                                        'remember_token' => $random_number
                                    ]);

                    if($update_otp) {
                        $user_email_id = $email_id;
                        $bcc_mail_id = ['vbhagg@gmail.com','bypriyanka.iimt@gmail.com'];
                        $subject = 'Password Change Request';
                        $content = "A Request has been initiated for password change from application. Please use ".$random_number." as your OTP to proceed.";
                        // $user_email_id = 'abc@gmail.com';
                        $blade_file = 'emails.forgot_password';
                        $response = Mail::to($user_email_id)
                                        ->bcc($bcc_mail_id)
                                            ->send(new CustomMail2($subject, $content, $blade_file));
                        return response()->json(['response'=>TRUE,'message'=>'OTP has been sent on your registered email id', 'server_id'=>1, 'users_id'=>$users_id]);
                    }
                    else {
                        return response()->json(['response'=>FALSE,'message'=>'Unable to Generate OTP','Error'=>'Unable to Generate OTP'],200);
                    }


                }
                else {
                    return response()->json(['response'=>FALSE,'message'=>'No Email Id Found, Contact Admin','Error'=>'No Email Id Found, Contact Admin'],200);    
                }
            }
            else {
                return response()->json(['response'=>FALSE,'message'=>'User Profile Not Found','Error'=>'User Profile Not Found'],200);
            }

        }
        else {
            //check on server 2
            $url = 'https://erp.ramanujancollege.ac.in/api/sendOtpServer2';
            $httpMethod ="POST";
            // $header = $curl_header;
            $header = array(
                    'Content-Type:application/json'
                );
            $body_arr['username'] = $username;
            $body_json['response'] = json_encode($body_arr);
            // dd($body_json);
            // $post_body_str = 'username='.$username.'&password='.$password;
            $post_body_str = http_build_query(array('username' => $username));
            // dd($post_body_str);
            // $curl = curl_init();

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $body_json);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            $response_decode = json_decode($response);
            curl_close($ch);
            // dd('printing response in call',$response_decode); die();
            if($response_decode->response) {
                $server_id = 2;
                $message = $response_decode->message;
                $users_id = $response_decode->users_id;
                // 'users_id'=>$users_id
                return response()->json(['response'=>TRUE,'message'=>$message, 'server_id'=>$server_id, 'users_id'=>$users_id]);
            }

            else {    
                $message = $response_decode->message;
                return response()->json(['response'=>FALSE,'message'=>$message,'Error'=>$message],200);
            }
            
        }

    }


    public function sendOtpServer2(Request $request) {
        $response = json_decode($request->response);
        // print_r($response);
        
        // return response()->json([ 'response' =>True,'Sync_status'=>$response,'Sync_date_time'=>date('Y-m-d H:i:s')]);

        $username = $response->username;
        $users_data = DB::table('users')->where('email', $username)->where('college_id',44)->first();
        if(!empty($users_data)) {
            //user_found on primary server, get user_profile and send email to the registered email id
            $users_id = $users_data->id;
            $user_profile_data = DB::table('user_profile')
                                    ->where('users_id', $users_id)
                                    ->first();
            if(!empty($user_profile_data)) {
                $email_id = !empty($user_profile_data->email)?$user_profile_data->email:NULL;
                if(!empty($email_id)) {
                    //sent otp on this mail

                    $random_number = rand(100000,999999);
                    // dd($random_number);

                    $update_otp = DB::table('users')
                                    ->where('id', $users_id)
                                    ->update([
                                        'remember_token' => $random_number
                                    ]);

                    if($update_otp) {
                        $user_email_id = $email_id;
                        $bcc_mail_id = ['vbhagg@gmail.com', 'bypriyanka.iimt@gmail.com'];
                        $subject = 'Password Change Request';
                        $content = "A Request has been initiated for password change from application. Please use ".$random_number." as your OTP to proceed.";
                        // $user_email_id = 'abc@gmail.com';
                        $blade_file = 'emails.forgot_password';
                        // dd('reached_here'); die();
                        $response = Mail::to($user_email_id)
                                        ->bcc($bcc_mail_id)
                                            ->send(new CustomMail2($subject, $content, $blade_file));
                        return response()->json(['response'=>TRUE,'message'=>'OTP has been sent on your registered email id', 'server_id'=>2, 'users_id'=>$users_id]);
                    }
                    else {
                        return response()->json(['response'=>FALSE,'message'=>'Unable to Generate OTP','Error'=>'Unable to Generate OTP'],200);
                    }


                }
                else {
                    return response()->json(['response'=>FALSE,'message'=>'No Email Id Found, Contact Admin','Error'=>'No Email Id Found, Contact Admin'],200);    
                }
            }
            else {
                return response()->json(['response'=>FALSE,'message'=>'User Profile Not Found','Error'=>'User Profile Not Found'],200);
            }

        }
        else {
            return response()->json(['response'=>FALSE,'message'=>'Invalid User Id Entered','Error'=>'Invalid User Id Entered'],200);
        }

    }



    public function checkOtp(Request $request) {
         $validator=Validator::make($request->all(),[
            'users_id' => 'required',         
            'server_id' => 'required',
            'given_otp' => 'required'
        ]);
        if($validator->fails()) {
            return response()->json(['response'=>FALSE,'message'=>'Validation Error!','Error'=>$validator->errors()],200);
        }

        $server_id = !empty($request->server_id)?$request->server_id:NULL;
        $users_id = !empty($request->users_id)?$request->users_id:NULL;
        $given_otp = !empty($request->given_otp)?$request->given_otp:NULL;

        if(!empty($server_id)) {
            if($server_id==1) {
                //call msell
                $check_otp_from_users = DB::table('users')
                                            ->where('id', $users_id)
                                            ->where('remember_token', $given_otp)
                                            ->first();
                if(!empty($check_otp_from_users)) {
                    return response()->json(['response'=>TRUE,'message'=>'OTP Matched']);
                }
                else {
                    return response()->json(['response'=>FALSE,'message'=>'Invalid Otp, Please Re-enter','Error'=>'Invalid Otp, Please Re-enter'],200);
                }
            }
            else if($server_id==2) {
                //call erp
                $url = 'https://erp.ramanujancollege.ac.in/api/checkOtpServer2';
                $httpMethod ="POST";
                // $header = $curl_header;
                $header = array(
                        'Content-Type:application/json'
                    );
                $body_arr['users_id'] = $users_id;
                $body_arr['server_id'] = $server_id;
                $body_arr['given_otp'] = $given_otp;
                $body_json['response'] = json_encode($body_arr);
                // dd($body_json);
                // $post_body_str = 'username='.$username.'&password='.$password;
                // $post_body_str = http_build_query(array('username' => $username));
                // dd($post_body_str);
                // $curl = curl_init();

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body_json);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                $response_decode = json_decode($response);
                curl_close($ch);
                // dd('printing response in call',$response_decode); die;
                if($response_decode->response) {
                    $message = $response_decode->message;
                    return response()->json(['response'=>TRUE,'message'=>$message]);
                }

                else {    
                    $message = $response_decode->message;
                    return response()->json(['response'=>FALSE,'message'=>$message,'Error'=>$message],200);
                }
            }
            else {
                return response()->json(['response'=>FALSE,'message'=>'Invalid Server Key','Error'=>'Invalid Server Key'],200);    
            }
        }
        else {
            return response()->json(['response'=>FALSE,'message'=>'Server Key Required','Error'=>'Server Key Required'],200);
        }



    }


    public function checkOtpServer2(Request $request) {
        $response = json_decode($request->response);
        // print_r($response);
        
        // return response()->json([ 'response' =>True,'Sync_status'=>$response,'Sync_date_time'=>date('Y-m-d H:i:s')]);

        $users_id = $response->users_id;
        $server_id = $response->server_id;
        $given_otp = $response->given_otp;
        $users_data = DB::table('users')
                        ->where('college_id',44)
                        ->where('id', $users_id)
                        // ->where('remember_token', $given_otp)
                        ->first();
        
        if(!empty($users_data)) {
            //user present with college id 44
            $match = ($users_data->remember_token == $given_otp)?true:false;
            if($match) {
                return response()->json(['response'=>TRUE,'message'=>'OTP Matched']);
            }
            else {
                return response()->json(['response'=>FALSE,'message'=>'Invalid Otp, Please Re-enter','Error'=>'Invalid Otp, Please Re-enter'],200);
            }
        }
        else {
            return response()->json(['response'=>FALSE,'message'=>'Invalid User, Contact Admin','Error'=>'Invalid User, Contact Admin'],200);
        }

    }




    public function updatePassword(Request $request) {
         $validator=Validator::make($request->all(),[
            'users_id' => 'required',         
            'server_id' => 'required',
            'password' => 'required'
        ]);
        if($validator->fails()) {
            return response()->json(['response'=>FALSE,'message'=>'Validation Error!','Error'=>$validator->errors()],200);
        }

        $server_id = !empty($request->server_id)?$request->server_id:NULL;
        $users_id = !empty($request->users_id)?$request->users_id:NULL;
        $password = !empty($request->password)?$request->password:NULL;

        $new_password_hash = Hash::make($password);

        if(!empty($server_id)) {
            $update_arr = [
                'password' => $new_password_hash,
                'updated_at' => date('Y-m-d H:i:s')
            ];

            if($server_id==1) {
                //call msell
                $update_password_query = DB::table('users')
                                            ->where('id', $users_id)
                                            ->update($update_arr);
                if(!empty($update_password_query)) {
                    return response()->json(['response'=>TRUE,'message'=>'Password Updated Successfully']);
                }
                else {
                    return response()->json(['response'=>FALSE,'message'=>'Unable to Update Password','Error'=>'Unable to Update Password'],200);
                }
            }
            else if($server_id==2) {
                //call erp
                $url = 'https://erp.ramanujancollege.ac.in/api/updatePasswordServer2';
                $httpMethod ="POST";
                // $header = $curl_header;
                $header = array(
                        'Content-Type:application/json'
                    );
                $body_arr['users_id'] = $users_id;
                $body_arr['server_id'] = $server_id;
                $body_arr['password'] = $password;
                $body_json['response'] = json_encode($body_arr);
                // dd($body_json);
                // $post_body_str = 'username='.$username.'&password='.$password;
                // $post_body_str = http_build_query(array('username' => $username));
                // dd($post_body_str);
                // $curl = curl_init();

                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $body_json);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $response = curl_exec($ch);
                $response_decode = json_decode($response);
                curl_close($ch);
                // dd('printing response in call',$response_decode); die;
                if($response_decode->response) {
                    $message = $response_decode->message;
                    return response()->json(['response'=>TRUE,'message'=>$message]);
                }

                else {    
                    $message = $response_decode->message;
                    return response()->json(['response'=>FALSE,'message'=>$message,'Error'=>$message],200);
                }
            }
            else {
                return response()->json(['response'=>FALSE,'message'=>'Invalid Server Key','Error'=>'Invalid Server Key'],200);    
            }
        }
        else {
            return response()->json(['response'=>FALSE,'message'=>'Server Key Required','Error'=>'Server Key Required'],200);
        }



    }


    public function updatePasswordServer2(Request $request) {
        $response = json_decode($request->response);
        // print_r($response);
        
        // return response()->json([ 'response' =>True,'Sync_status'=>$response,'Sync_date_time'=>date('Y-m-d H:i:s')]);

        $users_id = $response->users_id;
        $server_id = $response->server_id;
        $password = $response->password;

        $new_password_hash = Hash::make($password);

        $update_arr = [
            'password' => $new_password_hash,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $update_password_query = DB::table('users')
                        ->where('college_id',44)
                        ->where('id', $users_id)
                        ->update($update_arr);
        
        if($update_password_query) {
            //user present with college id 44
                return response()->json(['response'=>TRUE,'message'=>'Password Updated Successfully']);
        }
        else {
            return response()->json(['response'=>FALSE,'message'=>'Unable to Update Password','Error'=>'Unable to Update Password'],200);
        }

    }



    function cleanSpecialChar($string) {
       return preg_replace('/[^A-Za-z0-9\s:\/"{},[]]/', '', $string); // Removes special chars.
    }



}