<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;

class JobSchedule extends Model 
{

   
    protected $table = "job_schedule_mast";
    protected $guarded = [];

     public static function getAllRecords() {
        return JobSchedule::where('status','!=',9 )
                    ->get();

    }
     public static function getDataFromId($id) {
        return JobSchedule::where('id', $id)
                    ->first();
    }
 
 public static function updateDataFromId($id, $arr_to_update) {
        return JobSchedule::where('id', $id)
                    ->update($arr_to_update);
    }
     public static function pluckActiveCodeAndName(){
        return JobSchedule::where('status','=', 1)
                    ->pluck('name','id');   
    }
}