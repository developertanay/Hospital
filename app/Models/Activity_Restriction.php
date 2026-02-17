<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity_Restriction extends Model
{
    protected $table = "user_activity_restriction";
    protected $guarded = [];
    use HasFactory;

    public static function getAllData($college_id)
    {
        return Activity_Restriction::where('status', 1)
            ->where('college_id', $college_id)
            ->get();
    }
    public static function updateDataFromId($id, $arr_to_update) {
        return Activity_Restriction::where('id', $id)
                    ->update($arr_to_update);
    }

   
}
