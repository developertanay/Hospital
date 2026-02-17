<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerkMast extends Model
{
    protected $table='perk_mast';
    protected $guarded=[];
    public $timestamps=false;

    public static function getAllRecords($company_id='') {
        $data = PerkMast::where('status','!=',9);
        if(!empty($company_id)) {
            $data->where('company_id', $company_id);
        }
        
        $final_data = $data->get();
        return $final_data;
    }

    public static function getDataFromId($id) {
        return PerkMast::where('id', $id)
                    ->first();
                }

    public static function updateDataFromId($id, $arr_to_update) {
      return PerkMast::where('id', $id)
                  ->update($arr_to_update);
  }
}
