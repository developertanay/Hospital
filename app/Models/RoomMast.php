<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RoomMast extends Model
{
    protected $table = "room_mast";
    protected $guarded = [];

    public static function getAllRecords($college_id='') {
        $data = RoomMast::where('status','!=',9);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data = $data->get();
        return $final_data;
    }
     public static function pluckActiveCodeAndName($college_id='') {
        $data = RoomMast::where('status',1);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data = $data->pluck('name','id');
        return $final_data;
    }
     public static function getDataFromId($id) {
        return RoomMast::where('id', $id)
                    ->first();
    }
    public static function updateDataFromId($id, $arr_to_update) {
        return RoomMast::where('id', $id)
                    ->update($arr_to_update);
    }
}
