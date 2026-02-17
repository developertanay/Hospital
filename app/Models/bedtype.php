<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class bedtype extends Model
{
    use HasFactory;
    protected $table = "bedding_type";
    protected $guarded = [];

    public static function getDataFromId($id) {
        return bedtype::where('id', $id)
                    ->first();
                }
    
    public static function updateDataFromId($id, $arr_to_update) {
        return bedtype::where('id', $id)
                    ->update($arr_to_update);
    }
}
