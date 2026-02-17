<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('login', 'API\LoginController@login');
Route::post('check_login_creds', 'API\LoginController@check_login_creds');
Route::post('sendOtp', 'API\LoginController@sendOtp');
Route::post('sendOtpServer2', 'API\LoginController@sendOtpServer2');
Route::post('checkOtp', 'API\LoginController@checkOtp');
Route::post('checkOtpServer2', 'API\LoginController@checkOtpServer2');

Route::post('updatePassword', 'API\LoginController@updatePassword');
Route::post('updatePasswordServer2', 'API\LoginController@updatePasswordServer2');