<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Hospital;
use DB;
use Auth;
use Session;
use Crypt;


class Hospitalcontroller extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function __construct() {
        $this->current_menu = 'Hospital';
      }
    public function index()
    {
        $data = Hospital::where('status', '!=',9)->get();
        
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
        $state = DB::table('state_mast')->where('status', '!=',9)->pluck('state_name', 'id');
        $city = DB::table('city_mast')->where('status', '!=',9)->pluck('city_name', 'id');
        // dd($data);
        return view($this->current_menu.'/create', [
            'state'=> $state,
            'city'=> $city,
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
       
        try {
                    
            $file = !empty($request->file('image'))?$request->file('image'):null;

        if(!empty($file)) {
            $extension = $file->getClientOriginalExtension();
            $fileName = date('YmdHis').rand(10,99).'.'.$extension;
                
            $destinationPath_profile = public_path('/images/userprofile');
            $pathForDB_profile = 'public/images/userprofile/'.$fileName;
            
            $file->move($destinationPath_profile, $fileName);
        }
        else {
            $pathForDB_profile = NULL;
        }
            // dd($request);
            // Map form fields to database fields
        
        $myArr =[
            'hospital_name' => $request->name,
            'short_name' => $request->short_name,
            'registration_no' => $request->Registration,
            'type' => $request->type,
            'host' => $request->host,
            'contact_no' => $request->contact_no,
            'emergency_no' => $request->alternate_no,
            'email_id' => $request->email,
            'logo' => $pathForDB_profile,
            'geo_location' => $request->geo_location,
            'address' => $request->address,
            'state' => $request->current_state,
            'pin_code' => $request->pincode,
            'website' => $request->url,
            'district' => 0, // Default request if not provided
            'city' => 0, 
            'status' => $request->status,
            'updated_at' => now(),
            'updated_by' => auth()->id() ?? 1,
            'created_at' => now(),
            'created_by' => auth()->id() ?? 1
            ];
            // dd($myArr);
        
            $hospital = Hospital::create($myArr);
            
            return redirect()->route($this->current_menu . '.index')
                             ->with('success', 'Hospital created successfully!');
                             
        } catch (\Exception $e) {
            return redirect()->back()
                             ->with('error', 'Failed to create hospital: ' . $e->getMessage())
                             ->withInput();
        }
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
        $data =Hospital::getDataFromId($decrypted_id);
        $state = DB::table('state_mast')->where('status', '!=',9)->pluck('state_name', 'id');

        // $module =Module::pluckActiveParent();
        // dd($data);
        
        return view($this->current_menu.'/edit', [
            'data'=>$data,
            'state'=>$state,
            'current_menu' => $this->current_menu,
            'encid'=>$id,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    /**
 * Update the specified hospital in storage.
 */
public function update(Request $request, $id)
{
    $decrypted_id = Crypt::decryptString($id);
     try {
                    
            $file = !empty($request->file('image'))?$request->file('image'):null;

        if(!empty($file)) {
            $extension = $file->getClientOriginalExtension();
            $fileName = date('YmdHis').rand(10,99).'.'.$extension;
                
            $destinationPath_profile = public_path('/images/userprofile');
            $pathForDB_profile = 'public/images/userprofile/'.$fileName;
            
            $file->move($destinationPath_profile, $fileName);
        }
        else {
            $pathForDB_profile = NULL;
        }
            // dd($request);
            // Map form fields to database fields
        
        $myArr =[
            'hospital_name' => $request->name,
            'short_name' => $request->short_name,
            'registration_no' => $request->Registration,
            'type' => $request->type,
            'host' => $request->host,
            'contact_no' => $request->contact_no,
            'emergency_no' => $request->alternate_no,
            'email_id' => $request->email,
            'logo' => $pathForDB_profile,
            'geo_location' => $request->geo_location,
            'address' => $request->address,
            'state' => $request->current_state,
            'pin_code' => $request->pincode,
            'website' => $request->url,
            'district' => 0, // Default request if not provided
            'city' => 0, 
            'status' => $request->status,
            'updated_at' => now(),
            'updated_by' => auth()->id() ?? 1,
            'created_at' => now(),
            'created_by' => auth()->id() ?? 1
            ];
            // dd($myArr);
        
            $hospital = Hospital::updateDataFromId($decrypted_id, $myArr);
            
            return redirect()->route($this->current_menu . '.index')
                             ->with('success', 'Hospital created successfully!');
                             
        } catch (\Exception $e) {
            return redirect()->back()
                             ->with('error', 'Failed to create hospital: ' . $e->getMessage())
                             ->withInput();
        }
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
        $department = Hospital::findOrFail($decrypted_id);
        $department->status = 9; // Assuming 1 represents active and 9 represents deleted
        $department->save();
        DB::commit();

        return redirect()->route($this->current_menu.'.index');
    }
}
