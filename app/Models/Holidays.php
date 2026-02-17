<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Holidays extends Model
{
    protected $table = "holidays";
    protected $guarded = [];
    public static function updateDataFromId($id, $arr_to_update) {
        return Holidays::where('id', $id)
                    ->update($arr_to_update);
                }
}
 