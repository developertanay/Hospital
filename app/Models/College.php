<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;
// use DB;

use Illuminate\Database\Eloquent\Factories\HasFactory;


class College extends Model
{
     protected $table = "college_mast";
    protected $guarded = [];
   public static function getAllRecords($college_id) {
       $data= College::where('status','!=',9);
        if(!empty($college_id)){

            $data->where('id',$college_id);
        }
        $final_data = $data->get();
        return $final_data;  
                }

    public static function pluckActiveCodeAndName($college='') {
        $data = College::where('status', 1);
        if(!empty($college)) {
            $data->where('id', $college);
        }
        $final_data = $data->orderBy('college_name', 'asc')
                            ->pluck('college_name', 'id');

        return $final_data;
       
    }

    public static function pluckCodeAndName($college='') {
            // dd($college);
        $data = College::where('status','!=', 9);
        if(!empty($college)) {
            $data->where('id', $college);
        }
        $final_data = $data->orderBy('college_name', 'asc')
                            ->pluck('college_name', 'id');
        return $final_data;
    }

    public static function getDataFromId($id) {
        return College::where('id', $id)
                    ->first();
    }

    public static function updateDataFromId($id, $arr_to_update) {
        return College::where('id', $id)
                    ->update($arr_to_update);
    }


}
   