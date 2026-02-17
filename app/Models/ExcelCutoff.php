<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExcelCutoff extends Model
{
    use HasFactory;
    protected $table = "cutoff_excel_data";
    protected $guarded = [];
      public $timestamps=false;

    public static function getAllRecords($college_id,$course_id)
    {
        $data = ExcelCutoff::where('id','!=',null);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        if(!empty($course_id)) {
            $data->where('course_id', $course_id);
        }

        $final_data = $data->get();
        return $final_data;
        // return ExcelCutoff::get();
    }
    public static function getDataFromId($id) {
        return ExcelCutoff::where('id', $id)
                    ->first();
    }
    public static function updateDataFromId($id, $arr_to_update) {
        return ExcelCutoff::where('id', $id)
                    ->update($arr_to_update);
    }
}
