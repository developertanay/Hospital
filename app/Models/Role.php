<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;

class Role extends Model 
{

   
    protected $table = "role_mast";
    protected $guarded = [];

    public static function getAllRecords($college_id='') {
        $data = Role::where('status',1);
                    // ->where('college_id',NULL);

        if(!empty($college_id)) {
            $data->where('college_id',$college_id);
        }

        return $data->get();

    }

public static function pluckActiveCodeAndName($college_id='') {
        $data = Role::where('status',1)
                    ->where('college_id', NULL)
                    ->where('company_id', NULL);
        if(!empty($college_id)){
            $data->orWhere('college_id',$college_id);
        }
        $final_data=$data->pluck('name','id');
        return $final_data;
    }

public static function getDataFromId($id) {
        return Role::where('id', $id)
                    ->first();
                }

public static function updateDataFromId($id, $arr_to_update) {
        return Role::where('id', $id)
                    ->update($arr_to_update);
    }
    public static function pluckNameFromId($id) {
        return Role::where('id', $id)
                    ->pluck('name','id');
                }

}