<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabSession extends Model
{
    use HasFactory;

    protected $table = "lab_session";
    protected $guarded=[];

    public static function getAllRecords()
    {
        return LabSession::where('status',1)
        ->get();
    }

    public static function getDataById($id){
        return LabSession::where('id',$id)
        ->first();
    }

    public static function getDataFromCollegeId($college_id){
        return LabSession::where('college_id',$college_id)
        ->whereIn('status',[1,2])
        ->get();
    }

    public static function updateFromId($id,$arr_to_update){
        return LabSession::where('id',$id)
        ->update($arr_to_update);
    }

}
