<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;

use Illuminate\Database\Eloquent\Factories\HasFactory;

class SocietyDesignation extends Model
{
    protected $table = "society_designation_mast";
    protected $guarded = [];

    public static function getAllRecords($college_id) {
        $data = SocietyDesignation::where('status', '!=',9);
        if(!empty($college_id)){

            $data->where('college_id' ,$college_id);
        
        }
        $final_data = $data->get();
        return $final_data;
    }

    // public static function pluckActiveCodeAndName($designation='') {
        
    // }
    
    public static function pluckCodeAndName($designation='') {
        $data = SocietyDesignation::where('status', '!=', 9);
        if(!empty($designation)) {
            $data->where('id', $designation);
        }
        $final_data = $data->pluck('society', 'id');
        return $final_data;
    }

    public static function pluckActiveCodeAndName($college_id='') {
        $data = SocietyDesignation::where('status', 1);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data = $data->orderBy('name', 'asc')
                            ->pluck('name', 'id');

        return $final_data;
       
    }

    public static function getDataFromId($id) {
        return SocietyDesignation::where('id', $id)
                        ->first();
    }
    

    public static function updateDataFromId($id, $arr_to_update) {
        return SocietyDesignation::where('id', $id)
                    ->update($arr_to_update);
    }
}
