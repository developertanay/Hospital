<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $table = "company_mast";
    protected $guarded = [];

    public static function getAllData()
    {
        return Company::whereIn('status', [1,2])
            ->get();
    }
    public static function updateDataFromId($id, $arr_to_update) {
        return Company::where('id', $id)
                    ->update($arr_to_update);
    }


    public static function getDataFromCompanyId($company_id_arr) {
        return Company::whereIn('id', $company_id_arr)
                    ->where('status',1)
                    ->get();
    }
    public static function pluckActiveCodeAndName(){
        return Company::where('status','=', 1)
                    ->pluck('name','id');   
    }
}
