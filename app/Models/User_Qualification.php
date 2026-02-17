<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;
// use DB;

use Illuminate\Database\Eloquent\Factories\HasFactory;


class User_Qualification extends Model
{
     protected $table = "user_qualification";
    protected $guarded = [];
   public static function getAllRecords() {
        return User_Qualification::where('status', '!=',9)
                    ->orderBy('qualification_id', 'asc')
                    ->get();
                }
     public static function pluckCodeAndName() {
        return User_Qualification::where('status','!=', 9)
                    ->pluck('qualification_id', 'id');
    }           

     }
?>     