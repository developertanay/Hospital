<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SocietyType extends Model
{
    protected $table = "society_type_mast";
    protected $guarded = [];
    public $timestamps= false;

   public static function pluckActiveCodeAndName($college_id='') {
        $data = SocietyType::where('status','=',1);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data = $data->pluck('name','id');
        return $final_data;
    }
    public static function updateDataFromId($id, $arr_to_update) {
        return SocietyType::where('id', $id)
                    ->update($arr_to_update);
    }
    public static function getDataforedit($id='') {
        return SocietyType::where('status','!=',9)
                            ->where('id',$id)
                            ->first();
        
    }
}