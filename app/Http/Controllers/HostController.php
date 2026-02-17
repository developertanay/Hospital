<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use DB;
use Auth;
use Session;
use App\Models\College;
use App\Models\RoleMast;
use App\Models\User;
use App\Models\Modules;
class HostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct() {
        $this->current_menu = 'Host';
      }
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $college_mast = College::where('host',Null)->pluck('college_name','id');
        // dd($college_mast);
        return view($this->current_menu.'/create', [
                    'current_menu' => $this->current_menu,
                    'college_mast' => $college_mast,
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
        $college_old = '33';
        // dd($college);
        $college_id = !empty($request->college_id)?$request->college_id:NULL;
        $college_host_name = !empty($request->host_name)?$request->host_name:NULL;
        $created_at = date('Y-m-d H:i:s');
        $created_by = Auth::user()->id;
        $status = !empty($request->status)?$request->status:1;
        $new_password = 'mSELL@admin0';
        $new_password_hash = Hash::make($new_password);
        // dd($new_password,$new_password_hash);
        $parent_arr=Modules::getActiveParent($college_old);
        $module_full_data=Modules::pluckActiveChilds($college_old);
        foreach($module_full_data as $key =>$value){
            $child_arr[$value->parent_id][]=$value;
        }
        // dd($parent_arr,$child_arr);
        DB::beginTransaction();
        $college = College::findOrFail($college_id);
        $college->host = $college_host_name; // Assuming 1 represents active and 9 represents inactive
        $college->save();

        ////create Admin Role for new college
        // $role = [
        //    'college_id' => $college_id,
        //    'name'=>'Admin',
        //    'status'=>$status,
        //    'created_at' => $created_at,
        //    'created_by' => $created_by
        // ];
        // $query = RoleMast::create($role);
        $role_id = 2;

        // $role_id = RoleMast::where('college_id',$college_id)->first('id');

// dd($query,$role_id);
        ////create Admin User for new college
        $admin_create = [
            'name'=> 'Admin Test',
           'email' => 'admin@'.$college_host_name,
           'password' => $new_password_hash,
           'created_at' => $created_at,
           'role_id' => $role_id,
           'college_id' => $college_id,
        ];
        // dd($admin_create);
        $lalu = User::create($admin_create);
        // dd($lalu);
        $new_modules = [];

        foreach ($parent_arr as $key => $value) {
           $parent_insert_arr=[
                'college_id'=>$college_id,
                'name'=>$value->name,
                'url'=>$value->url,
                'icon'=>$value->icon,
                'sequence'=>$value->sequence,
                'status'=>$value->status,
                'created_at'=>$created_at,
                'created_by'=>$created_by,
           ];
           $parent_query=Modules::create($parent_insert_arr);
           $new_child_arr=!empty($child_arr[$value->id])?$child_arr[$value->id]:'';
           if(!empty($new_child_arr)){
           foreach($new_child_arr as $key2 =>$value2 ){
                $child_insert_arr=[
                'college_id'=>$college_id,
                'parent_id' =>$parent_query->id,
                'name'=>$value2->name,
                'url'=>$value2->url,
                'icon'=>$value2->icon,
                'sequence'=>$value2->sequence,
                'status'=>$value2->status,
                'created_at'=>$created_at,
                'created_by'=>$created_by,
                ];
           $new_child_insert_arr[]=$child_insert_arr;
           }
           }
        }
        $child_query=Modules::insert($new_child_insert_arr);
        // dd($child_query);



        $modules_assigning = Modules::where('college_id',$college_id)->pluck('name','id');
        foreach ($modules_assigning as $key => $value) {
           $insert_arr2=[
                'college_id'=>$college_id,
                'role_id'=>$role_id,
                'modules_id'=>$key,
                'status'=>$status,
                'created_at'=>$created_at,
                'created_by'=>$created_by,
           ];
           $new_modules_assigning[]=$insert_arr2;
        }
        $peelu = DB::table('module_assigning')->insert($new_modules_assigning);
        // dd($peelu);
        if($parent_query && $child_query && $lalu && $peelu){
        DB::commit();
            $message = 'Entry Saved Successfuly';
            Session::flash('message', $message);
        }
        else {
            DB::rollback();
            $message = 'Something Went Wrong';
            Session::flash('error', $message);

        }
        return redirect($this->current_menu.'/create');

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
