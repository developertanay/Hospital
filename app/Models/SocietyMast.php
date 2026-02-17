<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;

class SocietyMast extends Model 
{

   
    protected $table = "society_mast";
    protected $guarded = [];

     public static function getAllRecords($college_id='') {
        $data = SocietyMast::where('status','!=',9);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data = $data->get();
        return $final_data;
    }
     public static function pluckActiveCodeAndName($college_id='') {
        $data = SocietyMast::where('status','=',1);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data = $data->pluck('name','id');
        return $final_data;
    }
    public static function getDataforedit($id='') {
        return SocietyMast::where('status','!=',9)
                            ->where('id',$id)
                            ->first();
        
    }
    public static function updateDataFromId($id, $arr_to_update) {
        return SocietyMast::where('id', $id)
                    ->update($arr_to_update);
    }

    public static function getSocietyFromFacultyId($id){
        return SocietyMast::where('faculty_id',$id)
        ->where('status',1)
        ->where('role',4)
        ->pluck('name','id');
    }

}