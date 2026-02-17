<?php

namespace App\Http\Controllers;

use App\Models\User_Profile;

use Illuminate\Http\Request;
use Auth;
use Session;

class AppLoginController extends Controller
{
    //
    public function login(Request $request) {
        // dd($request);
        $username = !empty($request->username)?$request->username:NULL;
        $password = !empty($request->password)?$request->password:NULL;
        $current_date = date('Y-m-d');
        // dd($username, $password);
        // if(empty($username) || empty($password)) {
        //     dd('DATA NHI BHEJA');
        // }
        if(Auth::attempt(['email' => $username,'password' => $password])){

            $login_source = 'app';
            Session::forget('login_source');
            Session::put('login_source', $login_source);
            // dd()
        // dd(Auth::attempt(['email' => $username,'password' => $password]));

            $user=Auth::user();
            return redirect('UserProfileMast');
            // dd($college_id);
           
            // if($college_id==44) {
            // }
            // else {
            //     dd('UNHEALTHY APP LOGIN 1');       
            // }
        }
        else {
            dd('UNHEALTHY APP LOGIN 2');
        }
    }
}
 