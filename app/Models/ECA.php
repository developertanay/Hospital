<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ECA extends Model
{
    protected $table = "eca_registered_students";
    protected $guarded = [];

     public static function getAllRecords($college_id='') {
        $data = ECA::where('status','!=',9);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data = $data->get();
        return $final_data;
    }

    public static function getDataByStudent($college_id='',$id){
        $data = ECA::where('status','=',1)->where('user_id',$id);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data = $data->get();
        return $final_data;
    }

     public static function pluckActiveCodeAndName($college_id='',$society_id) {
        $data = ECA::where('status','=',1)->where('society_id',$society_id);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data = $data->pluck('user_id','id');
        return $final_data;
    }
    public static function getDataforedit($id='') {
        return ECA::where('status','!=',9)
                            ->where('id',$id)
                            ->first();
        
    }
    public static function updateDataFromId($id, $arr_to_update) {
        return ECA::where('id', $id)
                    ->update($arr_to_update);
    }
}
