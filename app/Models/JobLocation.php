<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;

class JobLocation extends Model 
{

   
    protected $table = "job_location_mast";
    protected $guarded = [];

     public static function getAllRecords() {
        return JobLocation::where('status','!=',9 )
                    ->get();

    }
     public static function getDataFromId($id) {
        return JobLocation::where('id', $id)
                    ->first();
    }
 
    public static function updateDataFromId($id, $arr_to_update) {
        return JobLocation::where('id', $id)
                    ->update($arr_to_update);
    }

    public static function pluckActiveData(){
        return JobLocation::where('status', 1)
                    ->pluck('job_location_type','id');
    }
}