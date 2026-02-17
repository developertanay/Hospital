<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;

class Category extends Model 
{

   
    protected $table = "category_mast";
    protected $guarded = [];

    public static function getAllRecords($college_id='',$category_id='') {
        $data =Category::where('status','!=',9);
        if(!empty($college_id))
        {
            $data->where('college_id',$college_id);
        }
        if(!empty($category_id))
        {
            $data->where('id',$category_id);
        }
        $final_data = $data->get();
        return $final_data; 

    }

    public static function pluckActiveCodeAndName($college_id='') {
        $data =Category::where('status','!=',9);
        if(!empty($college_id))
        {
            $data->where('college_id',$college_id);
        }
        $final_data = $data->pluck('name','id');
        return $final_data; 
                        
    }

    public static function getDataFromId($id) {
        return Category::where('id', $id)
                    ->first();
                }

    public static function updateDataFromId($id, $arr_to_update) {
        return Category::where('id', $id)
                    ->update($arr_to_update);
    }
    public static function getcategory($college_id = '') {
        $data =Category::where('status','!=',9);
        if(!empty($college_id))
        {
            $data->where('college_id',$college_id);
        }
        $final_data = $data->get();
        return $final_data; 

}

}