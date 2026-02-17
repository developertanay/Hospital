<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RollNo extends Model
{
    protected $table = "college_roll_no_excel_log";
    protected $guarded = [];
    public $timestamps=false;

    public static function getAllRecords() {
        return RollNo::get();
    }
}
