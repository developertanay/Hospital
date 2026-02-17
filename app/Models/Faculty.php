<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;
use DB;

class Faculty extends Model 
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "faculty_mast";
    protected $guarded = [];
    

    public static function getAllRecords($college_id='', $department_id='',$teaching_flag='') {
        $data = Faculty::where('status','!=',9);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        if(!empty($teaching_flag)) {
            $data->where('teaching_flag', $teaching_flag);
        }
        if(!empty($department_id)) {
            $data->where('department_id', $department_id);
        }

        $final_data = $data->get();
        // dd($college_id,$department_id,$final_data);
        return $final_data;
    }

    public static function getDataFromId($id) {
        return Faculty::where('id', $id)
                    ->first();
    }

    public static function updateDataFromId($id, $arr_to_update) {
        return Faculty::where('id', $id)
                    ->update($arr_to_update);
    }

    
    public static function pluckCodeAndName() {
        return Faculty::where('status','!=', 9)
                    ->pluck('firstname', 'id');
    }
    public static function getFacultyFullName() {
        $data=Faculty::where('status','!=', 9)
                     ->get();
            foreach($data as $key => $value)
            {
                $faculty_name[$value->id]=$value->firstname.' '.$value->lastname;
            } 
          return $faculty_name;           
    }
    public static function getFacultyFromUserProfileId($college_id='',$user_profile_id='') {
        
        $data=Faculty::where('status', 1)
                      ->where('college_id',$college_id);
        
        if(!empty($user_profile_id)){

            $data->where('user_profile_id',$user_profile_id);
        }
                      
                        
        $final_data=$data->get();
                    //  dd($final_data);
                     $faculty_name=[];
        foreach($final_data as $key => $value)
        {
            $faculty_name[$value->id]=$value->firstname.' '.$value->lastname;
        } 
        return $faculty_name;           
    }
    
    public static function getData($faculty_arr) {
        return Faculty::whereIn('id', $faculty_arr)
                        ->get();
    }

    public static function getFacultyFromDept($dept_id='')
    {
        $data=Faculty::where('status','!=', 9);
        if(!empty($dept_id)){
            $data->where('department_id',$dept_id);
        }
        $final_data=$data->get();
            foreach($final_data as $key => $value)
            {
                $faculty_name[$value->id]=$value->firstname.' '.$value->lastname;
            } 
          return $faculty_name; 
    }

    public static function getFacultyFromUserId($user_id=''){
        // $user_profile_id= DB::table('user_profile')->where('status',1)->where('users_id',$user_id)->pluck('id')->first();
        // $faculty=Faculty::where('status',1)->where('user_profile_id',$user_profile_id)->pluck('id')->first();

        $data = Faculty::where('id', $user_id)->first();
        // dd($data);
        $faculty_user_profile_id = $data->user_profile_id;

        return $faculty_user_profile_id;
    } 
     public static function getFacultyFullNameByCollege($college_id,$faculty_id='') {
        $data = Faculty::where('status','!=', 9)
                        ->where('college_id',$college_id);
        if($faculty_id){
            $final_data=$data->where('id',$faculty_id)->get();
        }
        else {
            $final_data= $data->get();
        }
                        
        foreach($final_data as $key => $value) {
            $faculty_name[$value->id]=$value->firstname.' '.$value->lastname;
        } 
        return $faculty_name;           
    }

    public static function getFacultyFullNameByUserProfile($college_id = '',$user_profile_id='')
    {

        $data=Faculty::where('status','!=', 9)->where('college_id',$college_id);
                     
        if($user_profile_id){
            $final_data=$data->where('user_profile_id',$user_profile_id)->get();
        }
        else
        {
            $final_data=$data->get();
        }
        
                     
            foreach($final_data as $key => $value)
            
            {
              $faculty_name[$value->user_profile_id]=$value->firstname.' '.$value->lastname;
            } 

          return $faculty_name;           
    }

    public static function faculty_count_of_college($college_id) {
        $data = Faculty::where('college_id', $college_id)
                        ->where('teaching_flag',1)
                        ->where('status',1)
                        ->count();
        return $data;
    }

    public static function getDataFromUsersId($college_id, $users_id) {
        $data = Faculty::where('college_id', $college_id)
                        ->where('users_id', $users_id)
                        ->where('status',1)
                        ->get();
        $fac_arr = [];
        foreach($data as $key => $value) {
            $fac_arr[$value->user_profile_id]=$value->firstname.' '.$value->lastname;
        } 
        return $fac_arr;
    }



    public static function getUserProfileIdByFacultyId($id){


         $data =Faculty::where('id',$id)->where('status',1)->first();
         return !empty($data->user_profile_id)?$data->user_profile_id:NULL;
        }


    
    public static function getFaculty($college_id='',$user_id=''){
        $data = Faculty::where('users_id', $user_id)
                    ->where('college_id',$college_id)
                    ->first();
        return $data->id;     
    }

    public static function getDataFromUsersId2($college_id='',$user_id=''){
    
        $data = Faculty::where('college_id',$college_id)
                    ->where('users_id', $user_id)
                    ->first();
        
            return $data;     
    }

    public static function getNameOnUserId($college_id){
    
        $data = Faculty::where('college_id',$college_id)
                    ->get();
        

        $faculty_name=[];
        foreach($data as $key => $value)
            {
                $faculty_name[$value->users_id]=$value->firstname.' '.$value->lastname;
            }
            
        return $faculty_name;

                
    }

    public static function pluckDataFromCollege($college_id, $users_id='') {
        $data = Faculty::where('college_id',$college_id)
                        ->where('status', '!=', 9);

        if(!empty($users_id)) {
            $data->where('users_id', $users_id);
        }
        $final_data = $data->get();

        $faculty_arr=[];
        foreach($final_data as $key => $value) {
            $faculty_arr[$value->id]=$value->firstname.' '.$value->lastname;
        } 
        return $faculty_arr;
    }


    public static function is_faculty($college_id,$users_id){
        
        $data= Faculty::where('college_id',$college_id)
                        ->where('status', '!=', 9)
                        ->where('users_id',$users_id)
                        ->first();
        return !empty($data)?$data:null;


    }

    public static function getFacultyName($college_id) {
        $data=Faculty::where('status','!=', 9)
                       ->where('college_id',$college_id) 
                       ->get();
            foreach($data as $key => $value)
            {
                $faculty_name[$value->id]=$value->firstname.' '.$value->lastname;
            } 
          return $faculty_name;           
    }

    public static function  pluckNameAndUsersId($college_id) {
        $data = Faculty::selectRaw("CONCAT_WS(' ',trim(firstname),trim(lastname)) as name, users_id")
                        ->where('college_id', $college_id)
                        ->where('users_id', '!=', NULL)
                        ->where('status', 1)
                        ->orderBy('name', 'asc')
                        ->pluck('name', 'users_id');
        return $data;
    }
    public static function pluckNameAndUsersdeptId($college_id,$dept_id) {
        // dd($dept_id,1,$dept_id === "0");
        $data = Faculty::selectRaw("CONCAT_WS(' ',trim(firstname),trim(lastname)) as name, users_id")
                        ->where('college_id', $college_id)
                        ->where('users_id', '!=', NULL)
                        ->where('status', 1)
                        ->orderBy('name', 'asc');

        if($dept_id === '0'){
            $data->where('role_id', '=',5);
        }
        else if(!empty($dept_id)){
            // dd(1);
            $data->where('department_id', $dept_id);
        }
        $final_data=$data->pluck('name', 'users_id');
        return $final_data;
    }

    public static function pluckNameAndFacultyId($college_id) {
        $data = Faculty::selectRaw("CONCAT_WS(' ',trim(firstname),trim(lastname)) as name, id")
                        ->where('college_id', $college_id)
                        ->where('role_id', 4)
                        ->where('status', 1)
                        ->orderBy('name', 'asc')
                        ->pluck('name', 'id');
        return $data;
    }
      
}