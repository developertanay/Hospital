<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HourMast extends Model
{
    protected $table = "hour_mast";
    protected $guarded = [];

    public static function getAllRecords($college_id='') {
        $data = HourMast::where('status','!=',9);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data = $data->get();
        return $final_data;
    }

    

    public static function getDataFromId($id) {
        return HourMast::where('id', $id)
                    ->first();
    }

    public static function getDataFromCollegeId($college_id) {
        return HourMast::where('college_id', $college_id)
                        ->where('status',1)
                        ->get();
    }

    public static function updateDataFromId($id, $arr_to_update) {
        return HourMast::where('id', $id)
                    ->update($arr_to_update);
    }

    public static function getActiveHours($college_id) {
        return HourMast::where('college_id', $college_id)
                        ->where('is_break', 0)
                        ->where('status',1)
                        ->pluck('end_time', 'start_time'); 
    }
}
