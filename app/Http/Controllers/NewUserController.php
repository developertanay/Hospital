<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Auth;
use App\Models\Gender;
use App\Models\Category;
use App\Models\State;
use App\Models\Role;
use App\Models\CollegeDeptMapping;
use App\Models\Department;
use App\Models\User;
use Crypt;
use Session;
use Hash;
use DB;
use App\Models\User_Profile;
class NewUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
     public function __construct() {
        $this->current_menu = 'NewUser';
    }
    public function index(Request $request)
    {
        // dd($request);


        $user_data=Auth::user();
        $college_id=$user_data->college_id;

        $role_mast=Role::pluckActiveCodeAndName($college_id);

        $name=!empty($request->name)?$request->name:'';
        $contact_no=!empty($request->contact_no)?$request->contact_no:'';
        $role=!empty($request->role)?$request->role:'';
        $submit=!empty($request->submit)?$request->submit:'';
        // dd(!empty($submit));
        if(!empty($submit)){
            if(!empty($name) || !empty($contact_no) || !empty($role)){
                

                $data=User_Profile::getUserfromNamePhoneRole($college_id,$name,$contact_no,$role);
            }        
            else
           {
                $message = 'Please select Any Field';
                Session::flash('message', $message);
                return view($this->current_menu.'/index', [
                    'current_menu'=>$this->current_menu,
                    'role_mast'=>$role_mast,
                    'data'=>[],
                    'role'=>$role
                ]);
            }
        }
        
    // dd($data);
        // dd($data);
        

   
        
        return view($this->current_menu.'/index', [
            'current_menu'=>$this->current_menu,
            'role_mast'=>$role_mast,
            'data'=>!empty($data)?$data:[],
            'role'=>$role
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $college_id = Auth::user()->college_id;
        $gender_mast =Gender::pluckCodeAndName($college_id);
        $category_mast =Category::pluckActiveCodeAndName($college_id);
        $state = State::pluckCodeAndName();
        // $department_mast = CollegeDeptMapping::pluckDeptandFromCollegId($college_id);
        // $department_id  = Department::whereIn('id',$department_mast)->pluck('department_name','id');
        $role_id = Role::pluckActiveCodeAndName($college_id);
         return view($this->current_menu.'/create', [
            'current_menu'=>$this->current_menu,
            'gender_mast'=>$gender_mast,
            'category_mast'=>$category_mast,
            'state'=>$state,
            'role_id'=>$role_id,
            // 'department_id'=>$department_id,
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
        //
        // dd($request);
        $user_data=Auth::user();
        $college_id=$user_data->college_id;
        $user_id=$user_data->id;

        $name=!empty($request->name)?$request->name:'';
        $father_name=!empty($request->father_name)?$request->father_name:'';
        $mother_name=!empty($request->mother_name)?$request->mother_name:'';
        $date_of_birth=!empty($request->date_of_birth)?date('Y-m-d', strtotime($request->date_of_birth)):NULL;
        $alternate_no=!empty($request->alternate_no)?$request->alternate_no:NULL;
        $contact_no=!empty($request->contact_no)?$request->contact_no:NULL;
        $email=!empty($request->email)?$request->email:'';
        $current_state=!empty($request->current_state)?$request->current_state:NULL;
        $permanent_state=!empty($request->permanent_state)?$request->permanent_state:NULL;
        $gender=!empty($request->gender)?$request->gender:NULL;
        $category=!empty($request->category)?$request->category:NULL;
        $department=!empty($request->department)?$request->department:NULL;
        $current_address=!empty($request->current_address)?$request->current_address:NULL;
        $current_pincode=!empty($request->current_pincode)?$request->current_pincode:NULL;
        $address_same=!empty($request->address_same)?$request->address_same:0;
        $permanent_address=!empty($request->permanent_address)?$request->permanent_address:NULL;
        $permanent_pincode=!empty($request->permanent_pincode)?$request->permanent_pincode:NULL;
        $joining_year=!empty($request->joining_year)?$request->joining_year:NULL;
        $leaving_year=!empty($request->leaving_year)?$request->leaving_year:NULL;
        $role=!empty($request->role)?$request->role:NULL;
        $status=!empty($request->status)?$request->status:NULL;
        $file=!empty($request->image)?$request->image:'';
        if ($request->hasFile('image')) {
            $extension = $file->getClientOriginalExtension();
            $fileName = date('YmdHis').rand(10,99).'.'.$extension;
                
            $destinationPath_profile = public_path('/images/userprofile');
            $pathForDB_profile = 'images/userprofile/'.$fileName;
            
            $file->move($destinationPath_profile, $fileName);
        }
        else {
            $pathForDB_profile = NULL;
        }


        
       $user_arr=[

        'name'=>$name,
        'email'=>$contact_no,
        'email_verified_at'=>NULL,
        'password' => Hash::make($contact_no),
        'role_id' =>$role,
        'college_id' =>$college_id,
        'created_at'=>date('Y-m-d H:i:s'),
        
       ];

       DB::beginTransaction();

       $query=User::create($user_arr);
       $users_id=$query->id;
       

       $user_profile_arr=[
        'users_id'=>$users_id,
        'name'=>$name,
        'father_name'=>$father_name,
        'mother_name'=>$mother_name,
        'contact_no'=>$contact_no,
        // 'parent_contact_no'=>
        'dob'=>$date_of_birth,
        'photo'=>$pathForDB_profile,
        'gender_id'=>$gender,
        'category_id'=>$category,
        'email'=>$email,
        'current_address'=>$current_address,
        'current_state_id'=>$current_state,
        'current_pincode'=>$current_pincode,
        'address_same_flag'=>$address_same,
        'permanent_address'=>$permanent_address,
        'permanent_state_id'=>$permanent_state,
        'permanent_pincode'=>$permanent_pincode,
        'enrolment_no'=>NULL,
        'roll_no'=>NULL,
        'cuet_score'=>NULL,
        'college_id'=>$college_id,
        'course_id'=>NULL,
        // 'semester'=>
        // 'academic_year'=>
        // 'session_duration'=>
        'admission_year'=>$joining_year,
        'passout_year'=>$leaving_year,
        'status'=>1,        
        'created_at'=>date('Y-m-d'),
        'created_by'=>$user_id

       ];
    //    dd($user_profile_arr);
    $query2=User_Profile::create($user_profile_arr);
            
            if($query && $query2){
                DB::commit();
                $message = 'Entry Saved Successfuly';
                Session::flash('message', $message);
                }  else {
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
         // dd($request);
         $user_data=Auth::user();
         $college_id=$user_data->college_id;
         
        $decrypted_id=Crypt::DecryptString($id);
        

        $gender_mast =Gender::pluckCodeAndName($college_id);
        $category_mast =Category::pluckActiveCodeAndName($college_id);
        $state = State::pluckCodeAndName();
        $department_mast = CollegeDeptMapping::pluckDeptandFromCollegId($college_id);
        $department_id  = Department::whereIn('id',$department_mast)->pluck('department_name','id');
        $role_id = Role::pluckActiveCodeAndName($college_id);
        

        $data=User_Profile::pluckFirstByIdandCollege($college_id,$decrypted_id);
                             

                            //  dd($data);

            return view($this->current_menu.'/edit', [
                'current_menu'=>$this->current_menu,
                'data'=>$data,
                'id'=>$id,    
                'gender_mast'=>$gender_mast,
                'category_mast'=>$category_mast,
                'state'=>$state,
                'role_id'=>$role_id,
                'department_id'=>$department_id,
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
        $decrypted_id=Crypt::DecryptString($id);

        $user_data=Auth::user();
        $college_id=$user_data->college_id;
        $user_id=$user_data->id;

        $name=!empty($request->name)?$request->name:'';
        $father_name=!empty($request->father_name)?$request->father_name:'';
        $mother_name=!empty($request->mother_name)?$request->mother_name:'';
        $date_of_birth=!empty($request->date_of_birth)?date('Y-m-d', strtotime($request->date_of_birth)):NULL;
        $alternate_no=!empty($request->alternate_no)?$request->alternate_no:NULL;
        $contact_no=!empty($request->contact_no)?$request->contact_no:NULL;
        $email=!empty($request->email)?$request->email:'';
        $current_state=!empty($request->current_state)?$request->current_state:NULL;
        $permanent_state=!empty($request->permanent_state)?$request->permanent_state:NULL;
        $gender=!empty($request->gender)?$request->gender:NULL;
        $category=!empty($request->category)?$request->category:NULL;
        $department=!empty($request->department)?$request->department:NULL;
        $current_address=!empty($request->current_address)?$request->current_address:NULL;
        $current_pincode=!empty($request->current_pincode)?$request->current_pincode:NULL;
        $address_same=!empty($request->address_same)?$request->address_same:0;
        $permanent_address=!empty($request->permanent_address)?$request->permanent_address:NULL;
        $permanent_pincode=!empty($request->permanent_pincode)?$request->permanent_pincode:NULL;
        $joining_year=!empty($request->joining_year)?$request->joining_year:NULL;
        $leaving_year=!empty($request->leaving_year)?$request->leaving_year:NULL;
        $role=!empty($request->role)?$request->role:NULL;
        $status=!empty($request->status)?$request->status:NULL;
        $file=!empty($request->image)?$request->image:'';
        if ($request->hasFile('image')) {
            $extension = $file->getClientOriginalExtension();
            $fileName = date('YmdHis').rand(10,99).'.'.$extension;
                
            $destinationPath_profile = public_path('/images/userprofile');
            $pathForDB_profile = 'images/userprofile/'.$fileName;
            
            $file->move($destinationPath_profile, $fileName);
        }
        else {
            $pathForDB_profile = NULL;
        }


        $user_profile_arr=[
            
            'name'=>$name,
            'father_name'=>$father_name,
            'mother_name'=>$mother_name,
            'contact_no'=>$contact_no,
            // 'parent_contact_no'=>
            'dob'=>$date_of_birth,
            'photo'=>$pathForDB_profile,

            'gender_id'=>$gender,
            'category_id'=>$category,
            'email'=>$email,
            'current_address'=>$current_address,
            'current_state_id'=>$current_state,
            'current_pincode'=>$current_pincode,
            'address_same_flag'=>$address_same,
            'permanent_address'=>$permanent_address,
            'permanent_state_id'=>$permanent_state,
            'permanent_pincode'=>$permanent_pincode,
            'enrolment_no'=>NULL,
            'roll_no'=>NULL,
            'cuet_score'=>NULL,
            'college_id'=>$college_id,
            'course_id'=>NULL,
            // 'semester'=>
            // 'academic_year'=>
            // 'session_duration'=>
            'admission_year'=>$joining_year,
            'passout_year'=>$leaving_year,
            'status'=>1,        
            'updated_at'=>date('Y-m-d'),
            'updated_by'=>$user_id
    
           ];
        //    dd($user_profile_arr);
        $query2=User_Profile::where('id',$decrypted_id)
                            ->update($user_profile_arr);
        $message = 'Entry Updated Succesfully';
        Session::flash('message', $message);
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
        // $decrypted_id=Crypt::decryptString($id);
        // $delete=User_Profile::where('id',$decrypted_id)
        //                     ->delete();
            return redirect($this->current_menu);
    }

    public function user_update(Request $request){


        // dd($request);

          $role_id=!empty($request->role_id)?$request->role_id:'';
          $user_id=!empty($request->user_id)?$request->user_id:'';
          $find=!empty($request->find)?$request->find:0;
          $user_data=Auth::user();
          $college_id=$user_data->college_id;
          $role_mast=Role::pluckActiveCodeAndName($college_id);
          $users=User_Profile::getuser($college_id);
        //   dd($find);
          if($find){
          $user = DB::table('users')
          ->where('id',$user_id)
          ->update(['role_id' => $role_id]);
          if($user){
          $message = 'Entry Updated Succesfully';
          Session::flash('message', $message);}
        else{
            $message = 'Something Went Wrong';
            Session::flash('error', $message);
        }}
       return view($this->current_menu.'/user_update',[
        'current_menu'=>$this->current_menu,
        'users'=>$users,
        'role_id'=>$role_id,
        'user_id'=>$user_id,
        'role_mast'=>$role_mast,
        'college_id'=>$college_id

       ]);
    }
    
}
