<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use Crypt;
use Auth;

use App\Models\Mailer;
use App\Mail\CustomMail2;
use Mail;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    // protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    // public function register(Request $request) {
    //     dd('inside register function of registration controller');
    // }


    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        // dd($data,'validate');
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'max:255', 'unique:users'],
            'password' => ['required', 'string'],
            // 'email' => ['required', 'string', 'max:255'],
            // 'password' => ['required', 'string', 'min:8', 'confirmed'],
            // 'password' => ['required', 'string', 'min:1'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        //set credentials
        // $auth_data = Auth::user();
        // $college_id = !empty($auth_data)?$auth_data->college_id:NULL;
        // $company_id = !empty($auth_data)?$auth_data->company_id:NULL;

        // dd('UNDER PRODUCTION', $data);
        $college_id = NULL;
        $company_id = NULL;

        $name = !empty($data['name'])?$data['name']:NULL;
        $email = !empty($data['email'])?$data['email']:NULL;
        $password = !empty($data['password'])?Hash::make($data['password']):NULL;
        $register_type = !empty($data['register_type'])?$data['register_type']:NULL;
        $insert_arr = [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'register_type' => $register_type
        ];

        $create_user_query = User::create($insert_arr);

        if($create_user_query) {
            $users_id = $create_user_query->id;
        }
        else {
            dd('Unable to Create User at the present moment');
        }

        // $random_number = rand(100000,999999);

        // //store this otp in users table
        // $otp_store = User::where('id', $users_id)
        //                 ->update(['remember_token'=>$random_number]);

        // if(!$otp_store) {
        //     dd('Unable to generate OTP at the present moment, Please try again later');
        // }

        // // $bcc_mail_id = ['vbhagg@gmail.com'];
        // // $user_email_id = $email;
        // $user_email_id = 'vbhagg@gmail.com';
        // $subject = 'New User Registration';
        // $content = "Greetings of the day. Please use ".$random_number." as your OTP to proceed.";
        // // $user_email_id = 'abc@gmail.com';
        // $blade_file = 'emails.forgot_password';

        // $set_credentials = Mailer::set_credentials($college_id, $company_id);

        // $response = Mail::to($user_email_id)
        //                 // ->bcc($bcc_mail_id)
        //                 ->send(new CustomMail2($subject, $content, $blade_file));

        // dd($response, $random_number);
        // return view('verify_otp');
        return $create_user_query;


        // return User::create([
        //     'name' => $data['name'],
        //     'email' => $data['email'],
        //     'password' => Hash::make($data['password']),
        // ]);
    }
}
