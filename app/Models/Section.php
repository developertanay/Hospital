<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $table = "section_mast";
    protected $guarded = [];
    public $timestamps=false;

    public static function getAllRecords($college_id='')

    {
        return Section::where('status',1)->where('college_id',$college_id)->get();

    }
    public static function getDataFromId($decrypted_id='')
    {
        return Section::where('status',1)->where('id',$decrypted_id)->first();

    }

    public static function updateDataFromId($id, $arr_to_update) {
        return Section::where('id', $id)
                    ->update($arr_to_update);
    }
    public static function getSectionFromSubjectCourse($id, $arr_to_update) {
        return Section::where('id', $id)
                    ->update($arr_to_update);
    }
    //ye commented tha uncomment by rahul
    public static function pluckActiveCodeAndName($college_id='') {
        $data = Section::where('status','!=',9);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data = $data->pluck('name','id');
        return $final_data;
    }


    

}
