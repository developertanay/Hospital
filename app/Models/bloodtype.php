<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class bloodtype extends Model
{
     use HasFactory;
    protected $table = "blood_type";
    protected $guarded = [];

    public static function getDataFromId($id) {
        return bloodtype::where('id', $id)
                    ->first();
                }
    
    public static function updateDataFromId($id, $arr_to_update) {
        return bloodtype::where('id', $id)
                    ->update($arr_to_update);
    }

}
