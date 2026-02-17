<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Course_Subject_Mapping;
use App\Models\College;
use App\Models\Subject;
use App\Models\Course;
use App\Models\Permission;
use App\Models\SubjectType;
use App\Models\User_Profile;
use Auth;
use DB;
use Crypt;
use Session;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct() {
        $this->current_menu = 'Permission';
    }
    public function index()
    {
        $data = Permission::getAllRecords();
        // dd($data);
        $college_mast = College::pluckActiveCodeAndName();
        $course_mast = Course::pluckActiveCodeAndName();
        // dd($data);
        return view($this->current_menu.'/index', [
            'data' => $data,
            'current_menu' => $this->current_menu,
            'college_mast' => $college_mast,
            // 'subject_mast' => $subject_mast,
            // 'subject_type_mast' => $subject_type_mast,
                   
            'course_mast' => $course_mast,


        ]);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $role_id = Auth::user()->role_id;
        if($role_id == 4) {   //faculty
            $users_id = Auth::user()->id;
            $user_profile_data = User_Profile::getDataFromUsersId($users_id);
            if(empty($user_profile_data)) {
                dd('User Profile not created for the logged in user');
            }
            // dd($users_id, $user_profile_data);
            $college_to_select = $user_profile_data->college_id;
            $college_arr = College::pluckActiveCodeAndName($college_to_select);
        }
        else {
            $college_arr = College::pluckActiveCodeAndName();
        // dd($college_arr);
        }
       // dd($college_arr);   
        foreach ($college_arr as $key =>$value) {
            $college_ids[] =$key;
        }
        // dd($college_ids);
        // $data = UserSubjectMapping::getAllRecords($college_ids, $course, $semester, $subject_type);
        // dd($data);
        // dd($college_arr);
        $college_mast = College::pluckActiveCodeAndName();
        // $name_id = User_Profile::pluckCodeAndName();
        $course_arr = Course::pluckActiveCodeAndName();
        
        $course_mast = Course::whereIn('college_id',$college_ids)->where('status',1)->pluck('name','id');
        // dd($name_id);
        return view($this->current_menu.'/create', [
            'current_menu' => $this->current_menu,
            'college_arr' => $college_arr,
            'college_mast' => $college_mast,
            'college_ids' => $college_ids,
            'course_arr' => $course_arr,
            'course_mast' => $course_mast,
            
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
        $college_id = !empty($request->college_id)?$request->college_id: NULL;
        $course_id = !empty($request->course_id)?$request->course_id: NULL;
        $module_id = !empty($request->module_id)?$request->module_id: NULL;
        $semesters = !empty($request->semester)?$request->semester: NULL;
        $status = !empty($request->status)?$request->status: 1;
        $updated_at = NULL;
        $updated_by = NULL;
        $created_at = date('Y-m-d H:i:s');
        $created_by = Auth::user()->id;

        $myArr=[
                    'college_id' => $college_id,
                    'module' => $module_id,
                    'course_id' => $course_id,
                    'semesters' => $semesters,
                    'status' => $status,
                    'updated_at' => $updated_at,
                    'updated_by' => $updated_by,
                    'created_at' => $created_at,
                    'created_by' => $created_by
                ];
        DB::beginTransaction();
                            // dd($myArr);                
        $query = Permission::create($myArr);
        
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
    public function edit($encid)
{

    $decrypted_id = Crypt::decrypt($encid);
    $data = DB::table('permission')->where('id', $decrypted_id)->first();
    
    $semester_obj = Course::getSemesterFromCourseId($data->course_id);
    $course_arr = Course::pluckActiveCodeAndName();

    
     $role_id = Auth::user()->role_id;
    if($role_id == 4) {   //faculty
        $users_id = Auth::user()->id;
        $user_profile_data = User_Profile::getDataFromUsersId($users_id);
        if(empty($user_profile_data)) {
            dd('User Profile not created for the logged in user');
        }
        // dd($users_id, $user_profile_data);
        $college_to_select = $user_profile_data->college_id;
        $college_arr = College::pluckActiveCodeAndName($college_to_select);
    }
    else {
        $college_arr = College::pluckActiveCodeAndName();
    // dd($college_arr);
    }
   // dd($college_arr);   
    foreach ($college_arr as $key =>$value) {
        $college_ids[] =$key;
    }

    
    $sem = Course::getSemesterFromCourseId($data->course_id);
    // dd($data);
    
    return view($this->current_menu.'/edit', [
        'current_menu' => $this->current_menu,
        'college_arr' => $college_arr,
        'college_ids' => $college_ids,
        'sem' => $sem,
        'course_arr' => $course_arr,
        'encrypted_id' => $decrypted_id,
        'data' => $data,
        'semester_obj' => $semester_obj,
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
        // dd($request);
        $decrypted_id = $id;
        // dd($id);
        $module_id = !empty($request->module_id)?$request->module_id: NULL;
        $college_id = !empty($request->college_id)?$request->college_id: NULL;
        $course_id = !empty($request->course_id)?$request->course_id: NULL;
        $semesters = !empty($request->semesters)?$request->semesters: NULL;
        $status = !empty($request->status)?$request->status: 1;
        $updated_at = NULL;
        $updated_by = NULL;
        $created_at = date('Y-m-d H:i:s');
        $created_by = Auth::user()->id;

        $myArr=[
                    'college_id' => $college_id,
                    'module' => $module_id,
                    'course_id' => $course_id,
                    'semesters' => $semesters,
                    'status' => $status,
                    'updated_at' => $updated_at,
                    'updated_by' => $updated_by,
                    'created_at' => $created_at,
                    'created_by' => $created_by
                ];
        DB::beginTransaction();
        $delete_query =DB::table('permission')->where('id',$decrypted_id)->delete();                                   
        $query = Permission::create($myArr);
        
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
