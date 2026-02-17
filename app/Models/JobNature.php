<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;

class JobNature extends Model 
{

   
    protected $table = "job_nature_mast";
    protected $guarded = [];

     public static function getAllRecords() {
        return JobNature::where('status','!=',9 )
                    ->get();

    }
     public static function getDataFromId($id) {
        return JobNature::where('id', $id)
                    ->first();
    }
 
 public static function updateDataFromId($id, $arr_to_update) {
        return JobNature::where('id', $id)
                    ->update($arr_to_update);
    }
    public static function pluckActiveCodeAndName(){
        return JobNature::where('status','=', 1)
                    ->pluck('job_nature','id');   
    }

}