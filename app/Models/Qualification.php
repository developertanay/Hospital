<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;
// use DB;

use Illuminate\Database\Eloquent\Factories\HasFactory;


class Qualification extends Model
{
     protected $table = "qualification_mast";
    protected $guarded = [];
   public static function getAllRecords() {
        return Qualification::where('status', '!=',9)
                    ->orderBy('name', 'asc')
                    ->get();
                }
    public static function pluckActiveCodeAndName() {
        return Qualification::where('status', 1)
                    ->pluck('name', 'id');
    }
      public static function pluckCodeAndName() {
        return Qualification::where('status','!=', 9)
                    ->pluck('name', 'id');
    }

    public static function getDataFromId($id) {
        return Qualification::where('id', $id)
                    ->first();
    }

     public static function updateDataFromId($id, $arr_to_update) {
        return Qualification::where('id', $id)
                    ->update($arr_to_update);
    }          

     }
?>     