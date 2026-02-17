<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BedInven extends Model
{
    use HasFactory;
    protected $table = "bed_inventory_management";
    protected $guarded = [];

    public static function getDataFromId($id) {
        return BedInven::where('id', $id)
                    ->first();
                }
    
    public static function updateDataFromId($id, $arr_to_update) {
        return BedInven::where('id', $id)
                    ->update($arr_to_update);
    }
}
