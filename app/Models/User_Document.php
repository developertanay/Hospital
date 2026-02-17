<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;
// use DB;

use Illuminate\Database\Eloquent\Factories\HasFactory;


class User_Document extends Model
{
     protected $table = "user_documents";
     protected $guarded = [];
     public static function getAllRecords() {
        return User_Document::where('status', '!=',9)
                    ->orderBy('document_id', 'asc')
                    ->get();
                }

     public static function pluckCodeAndName() {
        return User_Document::where('status','!=', 9)
                    ->pluck('document_id', 'id');
    }          

     }
?>     