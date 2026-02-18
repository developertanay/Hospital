<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BloodInv extends Model
{
    use HasFactory;
    protected $table = "blodd_inventory_management";
    protected $guarded = [];

    public static function getDataFromId($id) {
        return BloodInv::where('id', $id)
                    ->first();
                }
    
    public static function updateDataFromId($id, $arr_to_update) {
        return BloodInv::where('id', $id)
                    ->update($arr_to_update);
    }
}
