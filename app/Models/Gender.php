<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;
// use DB;

class Gender extends Model 
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "gender_mast";
    protected $guarded = [];
    // protected $fillable = [
    //     'name',
    //     'email',
    //     'password',
    // ];

    public static function getAllRecords($college_id='') {
       $data = Gender::where('status','!=',9);
        // if(!empty($college_id)) {
        //     $data->where('college_id', $college_id);
        // }
        $final_data = $data->orderBy('name', 'asc')->get();
        return $final_data;
    }

    public static function pluckCodeAndName($college_id='') {
        $data = Gender::where('status', 1);
        // if(!empty($college_id)) {
        //     $data->where('college_id', $college_id);
        // }
        $final_data = $data->pluck('name', 'id');
        return $final_data;                    
    }

    public static function pluckActiveCodeAndName($college_id='') {
        return Gender::whereNotIn('status', [9,2])
                    // ->where('college_id', $college_id)
                    ->pluck('name', 'id');
    }

    public static function getDataFromId($id) {
        return Gender::where('id', $id)
                    ->first();
    }

    public static function updateDataFromId($id, $arr_to_update) {
        return Gender::where('id', $id)
                    ->update($arr_to_update);
    }
    public static function getgender($college_id = '') {
        return Gender::where('status','!=',9)
                    // ->where('college_id',$college_id)
                    ->get();
    }
    public static function pluckGender() {
        return Gender::whereNotIn('status', [9,2])
                    ->pluck('name', 'id');
    }



}