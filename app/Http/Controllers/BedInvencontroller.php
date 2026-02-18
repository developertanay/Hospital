<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BedInven;
use App\Models\bedtype;
use DB;
use Auth;
use Session;
use Crypt;


class BedInvencontroller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
   public function __construct() {
        $this->current_menu = 'BedInven';
      }

    public function index()
    {
        $data = BedInven::where('status', '!=',9)->get();
        $final = bedtype::where('status', '!=',9)->pluck( 'name','id');
        // dd($final,$data);
        return view($this->current_menu.'/index',[
            'data' =>$data,
            'final' =>$final,
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
        $data = DB::table('bedding_type')->where('status', '!=',9)->pluck('name', 'id');
        return view($this->current_menu.'/create', [
            'data'=> $data,
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
       $name=!empty($request->bed)?$request->bed:NULL;
       $data=!empty($request->type)?$request->type:NULL;
        $status = !empty($request->status)?$request->status:1;
        $updated_at = date('Y-m-d H:i:s');
        $updated_by = Auth::user()->id;
        $created_at = date('Y-m-d H:i:s');
        $created_by = Auth::user()->id;


        $myArr = [
            // 'college_id'=>$college_id,
            'bed_id'=>$data,
            'count'=>$name,
            'status'=>$status,
            'updated_at'=>$updated_at,
            'updated_by'=>$updated_by,
            'created_at'=>$created_at,
            'created_by'=>$created_by,

        ];
        // dd($myArr);

        DB::beginTransaction();
        $query = BedInven::create($myArr);
        
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
        $data =BedInven::getDataFromId($decrypted_id);
        $blood = DB::table('bedding_type')->where('status', '!=',9)->pluck('name', 'id');

        // $module =Module::pluckActiveParent();
        // dd($data);
        
        return view($this->current_menu.'/edit', [
            'data'=>$data,
            'blood'=>$blood,
            'current_menu' => $this->current_menu,
            'id'=>$id,
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
         $name=!empty($request->bed)?$request->bed:NULL;
       $data=!empty($request->type)?$request->type:NULL;
        $status = !empty($request->status)?$request->status:1;
        $updated_at = date('Y-m-d H:i:s');
        $updated_by = Auth::user()->id;
        $created_at = date('Y-m-d H:i:s');
        $created_by = Auth::user()->id;


        $myArr = [
            // 'college_id'=>$college_id,
            'bed_id'=>$data,
            'count'=>$name,
            'status'=>$status,
            'updated_at'=>$updated_at,
            'updated_by'=>$updated_by,
            'created_at'=>$created_at,
            'created_by'=>$created_by,

        ];
        // dd($myArr);

        DB::beginTransaction();
        $query = BedInven::updateDataFromId($decrypted_id, $myArr);
        
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
    $department = BedInven::findOrFail($decrypted_id);
    $department->status = 9; // Assuming 1 represents active and 9 represents deleted
    $department->save();
    DB::commit();

    return redirect()->route($this->current_menu.'.index');
    }}
