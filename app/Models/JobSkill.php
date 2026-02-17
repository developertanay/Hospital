<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobSkill extends Model
{
  protected $table='job_skill';
  protected $guarded=[];
  public $timestamps=false;
  
    
    public static function getAllRecords() {
        $data = JobSkill::where('status','!=',9);
        return $data->get();

    }
    public static function getDataFromId($id) {
        return JobSkill::where('id', $id)
                    ->first();
    }
    public static function updateDataFromId($id, $arr_to_update) {
        return JobSkill::where('id', $id)
                    ->update($arr_to_update);
    }

    public static function pluckActiveCodeAndName(){
        return JobSkill::whereIn('status',[1,5])
                    ->pluck('name','id');   
    }
     public static function pluckCodeAndName(){
        return JobSkill::where('status',1)
                    ->pluck('name','id');   
    }
}
