<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;
// use DB;

class FeeType extends Model 
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "fee_type_mast";
    protected $guarded = [];
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];

    public static function getAllRecords($college_id='') {
       $data = FeeType::where('status','!=',9);
        // if(!empty($college_id)) {
        //     $data->where('college_id', $college_id);
        // }
        $final_data = $data->orderBy('name', 'asc')->get();
        return $final_data;
    }

    public static function pluckCodeAndName($college_id='') {
        $data = FeeType::where('status', 1);
        // if(!empty($college_id)) {
        //     $data->where('college_id', $college_id);
        // }
        $final_data = $data->pluck('name', 'id');
        return $final_data;                    
    }

    public static function pluckActiveCodeAndName($college_id) {
        return FeeType::whereNotIn('status', [9,2])
                    ->where('college_id', $college_id)
                    ->pluck('name', 'id');
    }

    public static function getDataFromId($id) {
        return FeeType::where('id', $id)
                    ->first();
    }

    public static function updateDataFromId($id, $arr_to_update) {
        return FeeType::where('id', $id)
                    ->update($arr_to_update);
    }



}