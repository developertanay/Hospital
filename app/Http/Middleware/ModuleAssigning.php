<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Auth;
use App\Models\Modules;

class ModuleAssigning extends Middleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle($request, Closure $next, ...$auth)
    {
        // return $next($request);
        if(!empty(Auth::user())) {
            $auth_data = Auth::user();
            $college_id = $auth_data->college_id;
            $role_id = $auth_data->role_id;
            $company_id = $auth_data->company_id;
            $register_type = $auth_data->register_type;

            // $server_request_uri = $_SERVER['REQUEST_URI'];
            // $server_request_uri_arr = explode('/', $server_request_uri);
            // $indexes_url=$server_request_uri_arr[count($server_request_uri_arr)-1];
            // dd($server_request_uri, $server_request_uri_arr,$indexes_url);

            $pathInfoDetails = $request->getPathInfo();
            $break_get_details =  explode('/', $pathInfoDetails);
                // abort(400);
            if (empty($break_get_details)) {
                // dd();
            }
            else{
                $url = $break_get_details[1];
                if($url=="") {
                    return redirect('dashboard');
                }
                else {
                    // dd($pathInfoDetails, $break_get_details);
                    $access = Modules::check_access($college_id, $role_id, $url, $register_type);
                    if($access) {
                        // dd(1);
                        return $next($request);
                    }
                    else {
                        dd('You are not authorized to view this page');
                    }
                }

            }

        }
        else {
            Auth::logout();
            return redirect('login');
        }
    }
}
