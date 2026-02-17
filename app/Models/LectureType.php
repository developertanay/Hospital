<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LectureType extends Model
{
     protected $table = "lecture_type_mast";
    protected $guarded = [];

    public static function getAllRecords($college_id='') {
        $data = LectureType::where('status','!=',9);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data = $data->get();
        return $final_data;
    }
     public static function pluckActiveCodeAndName($college_id='') {
        $data = LectureType::where('status','!=',9);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data = $data->pluck('name','id');
        return $final_data;
    }
    public static function pluckCodeAndName($college_id='') {
        $data = LectureType::where('status','!=',9);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data = $data->pluck('code','id');
        return $final_data;
    }
     public static function getDataFromId($id) {
        return LectureType::where('id', $id)
                    ->first();
    }
    public static function updateDataFromId($id, $arr_to_update) {
        return LectureType::where('id', $id)
                    ->update($arr_to_update);
    }
    public static function getOverlapStatus($college_id='',$lecture_type=''){
        if(!empty($lecture_type)){
            return LectureType::where('status',1)
                            ->where('college_id', $college_id)
                            ->where('id',$lecture_type)
                            ->pluck('overlap')->first(); 
        }

    }
}
