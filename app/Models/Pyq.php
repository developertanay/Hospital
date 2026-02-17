<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PYQ extends Model
{
    protected $table = "pyq_mast";
    protected $guarded = [];

     public static function getAllRecords()
    {
        return PYQ::whereIn('status',[1,2])->get();

    }
    public static function updateDataFromId($id, $arr_to_update) {
        return PYQ::where('id', $id)
                    ->update($arr_to_update);
    }
}
