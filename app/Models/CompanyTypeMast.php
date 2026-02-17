<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CompanyTypeMast extends Model
{
  protected $table='company_type_mast';
  protected $guarded=[];
  public $timestamps=false;
 
 public static function getAllRecords() {
        $data = CompanyTypeMast::where('status','!=',9);
        return $data->get();

    }
    public static function updateDataFromId($id, $arr_to_update) {
        return CompanyTypeMast::where('id', $id)
                    ->update($arr_to_update);
    }

    public static function pluckActiveData() {
        return CompanyTypeMast::where('status',1)
                            ->orderBy('sequence', 'asc')
                            ->orderBy('name', 'asc')
                            ->pluck('name', 'id');
    }

}
