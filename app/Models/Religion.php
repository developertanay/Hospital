<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;


class Religion extends Model{
    use HasFactory;
    protected $table = 'religion';
    protected $guarded =[];
    public static function getAllRecords() {
        return Religion::where('status',1)
                    ->get();
    }
    public static function updateDataFromId($id, $myarr){
        // dd($id,$myarr);
        return Religion::where('id',$id)
                     ->update($myarr);
     }
}
