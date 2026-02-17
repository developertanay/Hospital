<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;

class SocietyCategory extends Model 
{

   
    protected $table = "society_category_mast";
    protected $guarded = [];

     public static function pluckActiveCodeAndName($college_id='') {
        $data = SocietyCategory::where('status','=',1);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data = $data->pluck('name','id');
        return $final_data;
    }
    public static function updateDataFromId($id, $arr_to_update) {
        return SocietyCategory::where('id', $id)
                    ->update($arr_to_update);
    }
    public static function getDataforedit($id='') {
        return SocietyCategory::where('status','!=',9)
                            ->where('id',$id)
                            ->first();
        
    }

}