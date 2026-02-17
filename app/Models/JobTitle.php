<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobTitle extends Model
{
  protected $table = "job_title";
    protected $guarded = [];
    public $timestamps=false;

    
    public static function getAllRecords() {
        $data = JobTitle::where('status','!=',9);
        return $data->get();

    }
    public static function getRecordsForTitle() {
        return JobTitle::whereIn('status', [1,5])
                    ->pluck('name','id'); 
    }
    public static function getDataFromId($id) {
        return JobTitle::where('id', $id)
                    ->first();
    }
    public static function updateDataFromId($id, $arr_to_update) {
        return JobTitle::where('id', $id)
                    ->update($arr_to_update);
    }
    public static function pluckActiveCodeAndName(){
        return JobTitle::where('status','!=', 9)
                    ->pluck('name','id');   
    }
}
