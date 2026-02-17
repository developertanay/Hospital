<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SectionAllotment extends Model
{
    protected $table = "section_allotment_mast";
    protected $guarded = [];
    public $timestamps=false;

    public static function getAllRecords($created_by,$created_at)
    {
        return SectionAllotment::where('status',1)
                                ->where('created_by',$created_by)
                                ->where('created_at',$created_at)
                                ->get();

    }public static function getAllData($college_id)
    {
        return SectionAllotment::where('status',1)
                                ->where('college_id',$college_id)
                                ->get();

    }
    public static function getAllotedSections($academic_year,$college_id,$semester,$subject_id)
    {
        return SectionAllotment::where('academic_year',$academic_year)
                                ->where('college_id',$college_id)
                                ->where('subject_id',$subject_id)
                                ->where('semester',$semester)
                                ->where('status',1)
                                ->pluck('section','course_id');

    }
     public static function getDataFromId($decrypted_id='')
    {
        return SectionAllotment::where('status',1)->where('id',$decrypted_id)->first();

    }

    public static function updateDataFromId($id, $arr_to_update) {
        return SectionAllotment::where('id', $id)
                    ->update($arr_to_update);
    }
}
