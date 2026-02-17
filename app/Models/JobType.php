<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobType extends Model
{
    protected $table = "job_type";
    protected $guarded = [];
    public $timestamps=false;

    
    public static function getAllRecords() {
        $data = JobType::where('status','!=',9);
        return $data->get();

    }
    public static function getDataFromId($id) {
        return JobType::where('id', $id)
                    ->first();
    }
    public static function updateDataFromId($id, $arr_to_update) {
        return JobType::where('id', $id)
                    ->update($arr_to_update);
    }

    public static function pluckActiveCodeAndName(){
        return JobType::where('status','=', 1)
                    ->pluck('job_type','id');   
    }
}
