<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\bloodtype;
use DB;
use Auth;
use Session;
use Crypt;

class Bloodtypecontroller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct() {
        $this->current_menu = 'BloodType';
      }
    public function index()
    {
        $data = bloodtype::where('status', '!=',9)->get();
        return view($this->current_menu.'/index',[
            'data' =>$data,
            'current_menu' => $this->current_menu,
                ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view($this->current_menu.'/create', [
            // 'module'=> $module,
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
       $name=!empty($request->blood)?$request->blood:NULL;
        $status = !empty($request->status)?$request->status:1;
        $updated_at = date('Y-m-d H:i:s');
        $updated_by = Auth::user()->id;
        $created_at = date('Y-m-d H:i:s');
        $created_by = Auth::user()->id;


        $myArr = [
            // 'college_id'=>$college_id,
            'blood_type'=>$name,
            'status'=>$status,
            'updated_at'=>$updated_at,
            'updated_by'=>$updated_by,
            'created_at'=>$created_at,
            'created_by'=>$created_by,

        ];
        // dd($myArr);

        DB::beginTransaction();
        $query = bloodtype::create($myArr);
        
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
    public function edit($id)
    {
        $decrypted_id = Crypt::decryptString($id);
        $data =bloodtype::getDataFromId($decrypted_id);
        // $module =Module::pluckActiveParent();
        // dd($data);
        
        return view($this->current_menu.'/edit', [
            'data'=>$data,
            'current_menu' => $this->current_menu,
            'encrypted_id'=>$id,
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
        $decrypted_id = Crypt::decryptString($id);
        $name = !empty($request->blood)?$request->blood:NULL;
        $status = !empty($request->status)?$request->status:1;
        $sequence = !empty($request->sequence)?$request->sequence:1;
        $updated_at = date('Y-m-d H:i:s');
        $updated_by =Auth::user()->id;

        $myArr = [
            'blood_type'=>$name,
            'status'=>$status,
            // 'sequence' => $sequence,
            'updated_at'=>$updated_at,
            'updated_by'=>$updated_by
        ];
        // dd($myArr);

        DB::beginTransaction();
        $query = bloodtype::updateDataFromId($decrypted_id, $myArr);
        
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
        DB::beginTransaction();
    $department = bloodtype::findOrFail($decrypted_id);
    $department->status = 9; // Assuming 1 represents active and 9 represents deleted
    $department->save();
    DB::commit();

    return redirect()->route($this->current_menu.'.index');
    }
}
