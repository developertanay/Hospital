<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
   protected $table = "group_mast";
    protected $guarded = [];
    public $timestamps=false;

    public static function getAllRecords($college_id='')
    {
        return Group::where('status',1)->where('college_id',$college_id)->get();

    }
    public static function getDataFromId($decrypted_id='')
    {
        return Group::where('status',1)->where('id',$decrypted_id)->first();

    }

    public static function updateDataFromId($id, $arr_to_update) {
        return Group::where('id', $id)
                    ->update($arr_to_update);
    }
    public static function getGroupFromSubjectCourse($id, $arr_to_update) {
        return Group::where('id', $id)
                    ->update($arr_to_update);
    }
    // public static function pluckActiveCodeAndName($college_id='') {
    //     $data = Group::where('status','!=',9);
    //     if(!empty($college_id)) {
    //         $data->where('college_id', $college_id);
    //     }
    //     $final_data = $data->pluck('name','id');
    //     return $final_data;
    // }
}
