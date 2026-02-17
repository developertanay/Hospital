<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpdateMajorPaper extends Model
{
    protected $table = "major_paper_mapping";
    protected $guarded = [];
    public $timestamps=false;

    public static function getAllRecords()
    {
        return UpdateMajorPaper::where('status',1)->get();

    }
    public static function updateDataFromId($id, $arr_to_update) {
        return UpdateMajorPaper::where('id', $id)
                    ->update($arr_to_update);
    }
    public static function getDataFromId($decrypted_id='')
    {
        return UpdateMajorPaper::where('status',1)->where('id',$decrypted_id)->first();

    }
}
