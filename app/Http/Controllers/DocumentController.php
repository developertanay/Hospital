<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Documents;

use DB;
use Auth;
use Crypt;
use Session;


class DocumentController extends Controller
{
   
    public function __construct() {
        $this->current_menu = 'DocumentMast';
      }


    public function index()
    {
        $data=Documents::getAllRecords();
       return view($this->current_menu.'/index',[
            'data' => $data,
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
        $document_name=!empty($request->name)?$request->name:NULL;
        $status = !empty($request->status)?$request->status:1;
        $updated_at = NULL;
        $updated_by = NULL;
        $created_at = date('Y-m-d H:i:s');
        $created_by = Auth::user()->id;

         $myArr = [
            'name'=>$document_name,
            'status'=>$status,
            'updated_at'=>$updated_at,
            'updated_by'=>$updated_by,
            'created_at'=>$created_at,
            'created_by'=>$created_by,

        ];


        DB::beginTransaction();
        $query = Documents::create($myArr);
        
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
        
              $data =Documents::getDataFromId($decrypted_id);
              $encrypted_id=$id;

              return view($this->current_menu.'/edit', [
                    'data'=>$data,
                    'current_menu' => $this->current_menu,
                    'encrypted_id'=>$encrypted_id,
                ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update( $id,Request $request)
    {
        $decrypted_id = Crypt::decryptString($id);
        $document_name=!empty($request->name)?$request->name:NULL;
        $status = !empty($request->status)?$request->status:1;
        $updated_at = date('Y-m-d H:i:s');
        $updated_by =Auth::user()->id;
        $created_at = NULL;
        $created_by = NULL;

        $myArr = [
            'name'=>$document_name,
            'status'=>$status,
            'updated_at'=>$updated_at,
            'updated_by'=>$updated_by,
            'created_at'=>$created_at,
            'created_by'=>$created_by,

        ];


        DB::beginTransaction();
        $query = Documents::updateDataFromId($decrypted_id, $myArr);
        
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
         $decrypted_id = Crypt::decryptString($id);
    
    DB::beginTransaction();
    $department = Documents::findOrFail($decrypted_id);
    $department->status = 9; // Assuming 1 represents active and 9 represents inactive
    $department->save();
    DB::commit();

    return redirect()->route($this->current_menu.'.index');
    }
}
