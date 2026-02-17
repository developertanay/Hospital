<?php

use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
            Route::resource('JobApplication', 'JobApplicationController');
            
    Route::get('forgot_password', function () {
        return view('forgot_password');
    });

    Route::post('forgot_password_submit', 'PasswordController@forgot_password_submit')->name('forgot_password_submit');
    Route::post('verify_otp_submit', 'PasswordController@verify_otp_submit')->name('verify_otp_submit');

    
    Route::post('generate_password', 'PasswordController@generate_password')->name('generate_password');


    Route::get('feedback', 'FeedbackController@index');
    Route::post('submit_feedback', 'FeedbackController@store');


    Route::get('api_login', 'AppLoginController@login');

    Route::get('cpview', function() {
        return view('cpview');  //change password view
    });

    Route::post('change_password_at_login', 'PasswordController@change_password_at_login')->name('change_password_at_login');
    
    // Route::get('company_register', function() {
    //     return view('company_register');
    // });
    Route::post('register_new_company', 'CompanyMastController@register_new_company')->name('register_new_company');

    Auth::routes();

    Route::resource('CollegeMaster', 'CollegeMasterController');
    Route::get('course_student_subject', 'ReportController@course_student_subject');
    Route::get('course_student_subject_copy', 'ReportController@course_student_subject_copy');
    Route::post('course_student_subject/reject_dsc_subject', 'ReportController@reject_dsc_subject');
    Route::get('studentwise_attendance_report', 'ReportController@studentwise_attendance_report');
    // Route::get('studentwise_attendance_report2', 'ReportController@studentwise_attendance_report2');
    Route::get('studentwise_attendance_report_percentage', 'ReportController@studentwise_attendance_report_percentage');
    Route::group(['middleware' => ['auth']], function () {

        Route::resource('UserProfileMast', 'UserProfileController');
        Route::get('dashboard1', 'HomeController@dashboard');
        Route::get('dashboard', 'HomeController@index');

            Route::get('/home1212', [App\Http\Controllers\HomeController::class, 'index'])->name('home');
            Route::get('home', function () {
                return redirect('dashboard');
            });


            Route::get('defected_timetable', 'TimeTableReportController@defected_timetable');
            Route::post('update_student_details', 'StudentController@update_student_details');
            Route::resource('ModuleAssigning', 'ModuleAssigningController');
            Route::get('assign_modules', 'ScriptController@assign_modules');
            Route::resource('Module', 'ModuleController');
            Route::resource('ModuleMaster', 'ModuleMasterController');
            Route::resource('Permission', 'PermissionController');
            Route::resource('NewUser', 'NewUserController');
            Route::resource('RegisterUser', 'RegisterUserController');
            Route::resource('Mailer', 'MailController');
            Route::resource('Editor','EditorController');
            Route::resource('CkEditor','CkEditorController');




        Route::group(['middleware' => ['module_assigning']], function () {

            Route::get('/', function () {
                return redirect('dashboard');
            });

            
            
            
            
        });
                
            
        
            
            Route::get('stepform', function() {
                return view('Student.stepform');
            });
            
            Route::post('change_password', 'PasswordController@change_password');
            Route::post('reset_password', 'PasswordController@reset_password');

            Route::post('generate_faculty_login', 'PasswordController@generate_faculty_login');
            Route::get('autogenerate_faculty_login', 'PasswordController@autogenerate_faculty_login');

            Route::post('update_basic_details', 'UserProfileController@update_basic_details')->name('update_basic_details');
            Route::post('update_documents', 'UserProfileController@update_documents')->name('update_documents');
            Route::post('updateprofilestep1/{id}' ,  'UserProfileController@update_step1');

            Route::post('update_summary_profile', 'UserProfileController@update_summary_profile');

            
            Route::post('ExcelCutoff/importData', 'ExcelCutoffController@importData')->name('ExcelFile.importData');
            Route::get('ExcelFile', 'ExcelCutoffController@index')->name('ExcelFile.index');
            
            Route::get('/get-selected-subjects', 'AjaxController@getSelectedSubjects');

            Route::post('getSections', 'AjaxController@getSections')->name('getSections');
            Route::post('getSubjects', 'AjaxController@getSubjects')->name('getSubjects');
            Route::post('getSubjects2', 'AjaxController@getSubjects2')->name('getSubjects2');
            Route::post('gridexport/importData', 'GridExportController@importData')->name('gridexport.importData');
            Route::post('/send-emails', 'MailController@sendEmails')->name('send.emails');
            Route::post('download_csv', 'CsvController@download_csv')->name('download_csv');
            Route::post('admissioncancel/importData', 'AdmissionCancelController@importData')->name('admissioncancel.importData');
            
            Route::post('rollno/importData', 'RollNoController@importData')->name('rollno.importData');
            Route::post('getstudentsbyCollegeCourseSemester', 'AjaxController@getstudentsbyCollegeCourseSemester')->name('getstudentsbyCollegeCourseSemester');
            Route::post('getstudentsbyCollegeCourseSemester2', 'AjaxController@getstudentsbyCollegeCourseSemester2')->name('getstudentsbyCollegeCourseSemester2');
            Route::post('getSubjectFromSubjectType', 'AjaxController@getSubjectFromSubjectType')->name('getSubjectFromSubjectType');
            Route::post('getStudents', 'AjaxController@getStudents')->name('getStudents');
            Route::post('updateStudents', 'AjaxController@updateStudents')->name('updateStudents');
            Route::post('getGroups', 'AjaxController@getGroups')->name('getGroups');
            Route::get('format_mail', 'MailController@ckeditor_index');
            Route::get('s123', 'MailController@update');
            Route::post('send_mail_to_students', 'MailController@send_mail_to_students');
            
    });

Route::get('mail1', 'CronController@mail1');
Route::get('privacyPolicy', function() {
    // dd('Policy here');
    return view('privacyPolicy');
});

Route::get('user_delete_request', function() {
    return view('user_delete_request');
});



Route::get('server_vars', function() {
    $var_arr['max_input_time'] = ini_get('max_input_time');
    $var_arr['max_input_vars'] = ini_get('max_input_vars');
    $var_arr['memory_limit'] = ini_get('memory_limit');
    $var_arr['post_max_size'] = ini_get('post_max_size');
    $var_arr['upload_max_filesize'] = ini_get('upload_max_filesize');
    $var_arr['max_file_uploads'] = ini_get('max_file_uploads');

    dd($var_arr, $_SERVER);
});


Route::get('test_mail', 'MailController@test_mail');


Route::get('compress', 'ScriptController@compress_image');
Route::get('stepform', function() {
    return view('Stepform/create');
});
Route::get('downloadLetterPdf/{id}', 'RequestController@downloadLetterPdf');
