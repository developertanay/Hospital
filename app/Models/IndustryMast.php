<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IndustryMast extends Model
{
    protected $table = "industry_mast";
    protected $guarded = [];

    public static function getAllData()
    {
        return IndustryMast::whereIn('status', [1,2])
            ->get();
    }
    public static function updateDataFromId($id, $arr_to_update) {
        return IndustryMast::where('id', $id)
                    ->update($arr_to_update);
    }

    public static function pluckCodeAndName() {
        return IndustryMast::where('status','!=', 9)
                    ->orderBy('name', 'asc')
                    ->pluck('name', 'id');
    }

    public static function pluckActiveData() {
        return IndustryMast::where('status',1)
                            ->orderBy('sequence', 'asc')
                            ->orderBy('name', 'asc')
                            ->pluck('name', 'id');   
    }


}
