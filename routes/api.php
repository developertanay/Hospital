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

Route::prefix('mobile')->group(function () {
    Route::get('health', [App\Http\Controllers\API\MobileController::class,'health']);
    Route::post('login', [App\Http\Controllers\API\MobileController::class,'login'])->middleware('throttle:mobile-login');
    Route::post('register', [App\Http\Controllers\API\MobileController::class,'register'])->middleware('throttle:mobile-register');
    Route::middleware(['auth:sanctum',App\Http\Middleware\MobileToken::class])->group(function () {
        Route::get('state', [App\Http\Controllers\EmergencyController::class,'state']);
        Route::post('action', [App\Http\Controllers\EmergencyController::class,'mutate']);
        Route::get('sos/state', [App\Http\Controllers\API\AmbulanceController::class,'state']);
        Route::post('sos/action', [App\Http\Controllers\API\AmbulanceController::class,'action']);
        Route::get('portfolio',[App\Http\Controllers\API\PatientPortfolioController::class,'index']);
        Route::post('coverage/review',[App\Http\Controllers\API\PatientPortfolioController::class,'review']);
        Route::get('coverages/{id}/card',[App\Http\Controllers\API\PatientPortfolioController::class,'card']);
        Route::apiResource('dependents','API\PatientPortfolioController')->except(['show','index']);
        Route::apiResource('coverages','API\PatientPortfolioController')->except(['show','index']);
        Route::post('logout', [App\Http\Controllers\API\MobileController::class,'logout']);
    });
});
