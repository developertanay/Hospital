<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GridExport extends Model
{
    protected $table = "grid_export";
    protected $guarded = [];
    public $timestamps = false;

    public static function getAllRecords(){
        return GridExport::where('status','!=',9)
        ->get();
    }
    public static function pluckActiveCodeAndName(){
        return GridExport::where('status', [9,2])
                    ->pluck('', 'id');
    }

}
