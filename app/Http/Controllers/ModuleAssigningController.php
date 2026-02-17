<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Role;
use App\Models\Hospital;
use App\Models\Modules;

use DB;
use Auth;
use Crypt;
use Session;

class ModuleAssigningController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function __construct() {
        $this->current_menu = 'ModuleAssigning';
      }

    public function index()
    {
        $college_id=Auth::user()->college_id;
        $college_mast=Hospital::pluckActiveCodeAndName($college_id);
        $role_mast= Role::pluckActiveCodeAndName($college_id);
        // dd($role_mast);
         return view($this->current_menu.'/index',[
            'college_mast'=>$college_mast,
            'role_mast'=>$role_mast,
            'current_menu' => $this->current_menu,
                ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        // dd($request);
        $data=$request;
        $role_id=!empty($request->role_id)?$request->role_id:null;
        $college_id=Auth::user()->college_id;
        $college_mast=Hospital::pluckActiveCodeAndName($college_id);
        $role_mast= Role::pluckActiveCodeAndName($college_id);
        $parent_arr=Modules::pluckActiveParent($college_id);
        $module_full_data=Modules::pluckActiveChilds($college_id);
        foreach($module_full_data as $key =>$value){
            $child_arr[$value->parent_id][]=$value;
        }
        

        $already_assigned_module_data=DB::table('module_assigning')->where('college_id',$college_id)->where('role_id',$role_id)->pluck('modules_id')->toArray();
        // dd($already_assigned_module_data);
        // dd($parent_arr,$child_arr);
        // ARR[P][C][SC][SSC] = sssc
        // dd($module_arr);
        // dd($module_data);
         return view($this->current_menu.'/create',[
            'data'=>$data,
            'college_mast'=>$college_mast,
            'role_mast'=>$role_mast,
            'parent_arr'=>$parent_arr,
            'child_arr'=>$child_arr,
            'already_assigned_module_data'=>$already_assigned_module_data,
            'current_menu' => $this->current_menu,
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
        $college_id=!empty($request->college_id)?$request->college_id:NULL;
        $role_id=!empty($request->role_id)?$request->role_id:NULL;
        $modules_arr=!empty($request->child_arr)?$request->child_arr:[];
        $status = !empty($request->status)?$request->status:1;
        $updated_at = NULL;
        $updated_by = NULL;
        $created_at = date('Y-m-d H:i:s');
        $created_by = Auth::user()->id;

        $existingArray=DB::table('module_assigning')
                          ->where('college_id',$college_id)
                          ->where('role_id',$role_id)
                          ->delete();
        // dd($existingArray);  
        if($existingArray != 0){
            $created_at = NULL;
            $created_by = NULL;
            $updated_at=date('Y-m-d H:i:s');
            $updated_by=Auth::user()->id;
        }   
        $final_insert_arr=[];                     
        foreach($modules_arr as $module_key =>$module_value){
        $insert_arr =[
            'college_id'=>$college_id,
            'role_id'=>$role_id,
            'modules_id'=>$module_key,
            'view_flag'=>1,
            'create_flag'=>1,
            'edit_flag'=>1,
            'download_flag'=>1,
            'upload_flag'=>1,
            'status'=>$status,
            'updated_at'=>$updated_at,
            'updated_by'=>$updated_by,
            'created_at'=>$created_at,
            'created_by'=>$created_by,

        ];
        // dd($insert_arr);
            $insert_query=DB::table('module_assigning')->insert($insert_arr);
            if(!empty($module_value)){
                foreach($module_value as $key => $value)
                {
                $child_arr =[
                'college_id'=>$college_id,
                'role_id'=>$role_id,
                'modules_id'=>$value,
                'view_flag'=>1,
                'create_flag'=>1,
                'edit_flag'=>1,
                'download_flag'=>1,
                'upload_flag'=>1,
                'status'=>$status,
                'updated_at'=>$updated_at,
                'updated_by'=>$updated_by,
                'created_at'=>$created_at,
                'created_by'=>$created_by,
                    ];

                $final_insert_arr[]=$child_arr;
                }
            }

        }

        // dd($final_insert_arr);

        DB::beginTransaction();
                $insert_query = DB::table('module_assigning')->insert($final_insert_arr);
                if($insert_query) {
                    DB::commit();
                    $message = 'Module Assigned Successfuly';
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
    public function edit($id)
    {
        //
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
        //
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
