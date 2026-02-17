<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;
// use DB;

class CollegeDeptMapping extends Model 
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "college_dept_mapping";
    protected $guarded = [];
    
    public static function getAllRecords($college_id='') {
        $data = CollegeDeptMapping::where('status','!=',9);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        
        $final_data = $data->get();
        return $final_data;
    }

    public static function pluckDeptFromCollegId($college_id=''){
            return CollegeDeptMapping::where('status', 1)
                                      ->where('college_id',$college_id)
                                      ->pluck('dept_id');

    }
     public static function pluckDeptandFromCollegId($college_id=''){
            $data = CollegeDeptMapping::where('status',1);
            if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
         $final_data = $data->pluck('dept_id','id');
        return $final_data;
                                     
    }   

    public static function pluckFilteredDeptandFromCollegId($college_id='',$department_id=''){
            $data = CollegeDeptMapping::where('status',1);
            if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        if(!empty($department_id)) {
            $data->where('dept_id', $department_id);
        }
         $final_data = $data->pluck('dept_id','id');
        return $final_data;
                                     
    }   
}
?>