<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class nationality extends Model
{
    protected $table = "_nationality";
    protected $guarded = [];
    public $timestamps= false;

    public static function updateformdata($myarr,$id)
    {
       return nationality::where('id',$id)->update($myarr);
    }
    
}