<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;
// use DB;

class DesignationAssign extends Model
{
    protected $table = "designation_assignment";
    protected $guarded = [];

    public static function getAllRecords($college_id) {
        $data = DesignationAssign::where('status', '!=', 9);
        if(!empty($college_id)){

            $data->where('college_id', $college_id);
        }
        $final_data = $data->get();
        return $final_data;
    }

    public static function pluckActiveCodeAndName($college='') {
        $data = DesignationAssign::where('status', 1);
        if(!empty($college)) {
            $data->where('id', $college_id);
        }
        $final_data = $data;

        return $final_data;
       
    }

    public static function pluckCodeAndName($college='') {
        // dd($college);
    $data = DesignationAssign::where('status','!=', 9);
    if(!empty($college)) {
        $data->where('id', $college);
    }
    $final_data = $data;

    return $final_data;
}

public static function getDataFromId($id) {
    return DesignationAssign::where('id', $id)
                ->first();
}

public static function updateDataFromId($id, $arr_to_update) {
    return DesignationAssign::where('id', $id)
                ->update($arr_to_update);
}

    
}
