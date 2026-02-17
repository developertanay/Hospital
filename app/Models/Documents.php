<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;
// use DB;

use Illuminate\Database\Eloquent\Factories\HasFactory;


class Documents extends Model
{
     protected $table = "document_mast";
    protected $guarded = [];
   public static function getAllRecords() {
        return Documents::where('status', '!=',9)
                    ->orderBy('name', 'asc')
                    ->get();
                }

     public static function pluckCodeAndName() {
        return Documents::where('status','!=', 9)
                    ->pluck('name', 'id');
    }

    public static function getDataFromId($id) {
        return Documents::where('id', $id)
                    ->first();
    }

     public static function updateDataFromId($id, $arr_to_update) {
        return Documents::where('id', $id)
                    ->update($arr_to_update);
    }


 }

      ?>