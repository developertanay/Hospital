<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Hospital extends Model
{
    use HasFactory;
    protected $table = "hospital_mast";
    protected $guarded = [];

    public static function getDataFromId($id) {
        return Hospital::where('id', $id)
                    ->first();
                }
    
    public static function updateDataFromId($id, $arr_to_update) {
        return Hospital::where('id', $id)
                    ->update($arr_to_update);
    }
    public static function pluckActiveCodeAndName($college='') {
        $data = Hospital::where('status', 1);
        if(!empty($college)) {
            $data->where('id', $college);
        }
        $final_data = $data->orderBy('hospital_name', 'asc')
                            ->pluck('hospital_name', 'id');

        return $final_data;
       
    }
}
    