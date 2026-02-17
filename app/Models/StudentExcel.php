<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StudentExcel extends Model
{
    protected $table = "student_excel_csv";
    protected $guarded = [];
    public $timestamps=false;
    public static function getAllRecords() {
        return StudentExcel::get();
    }
    public static function getDataCollege($college_id = '') {
        return StudentExcel::where('college_id',$college_id)->get();
    }
    

}
