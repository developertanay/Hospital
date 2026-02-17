<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;

class Subject extends Model 
{

   
    protected $table = "subject_mast";
    protected $guarded = [];

    public static function getAllRecords($credit='', $subject_type='',$college_id='') {
        $data = Subject::where('status','!=',9)->where('college_id',$college_id);
        if(!empty($subject_type)) {
            $data->where('subject_type', $subject_type);
        }
        if(!empty($credit)) {
            $data->where('credit', $credit);
        }

        $final_data = $data->get();
        return $final_data;
    }

    public static function getDataFromId($id) {
        return Subject::where('id', $id)
                    ->first();
    }

    public static function updateDataFromId($id, $arr_to_update) {
        return Subject::where('id', $id)
                    ->update($arr_to_update);
    }

    public static function getAllActiveRecords($college_id='') {
        $data = Subject::where('status',1);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data=$data->get();
        return $final_data;
    }
    public static function pluckActiveCodeAndName($college_id='',$subject_type = '') {
        $data = Subject::whereNotIn('status', [9,2]);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        if(!empty($subject_type)) {
            $data->where('subject_type', $subject_type);
        }
        $final_data = $data->pluck('subject_name', 'id')->toArray();
        return $final_data;
    }

    public static function getFilteredDataFromId($college_id,$already_studied_subjects) {
        return Subject::where('status','!=',9)
                        ->where('college_id',$college_id)
                        ->whereNotIn('id', $already_studied_subjects)
                        ->get();

    }

    public static function pluckCodeAndName($college_id){

        return Subject::where('status',1)
                        ->where('college_id',$college_id)
                        ->pluck('subject_name','id');
    }

    public static function pluckTypeAndSub($college_id=''){

        return Subject::where('status',1)
                        ->where('college_id',$college_id)
                        ->pluck('subject_type','id');
    }
    public static function pluckUpcCode(){
        return Subject::where('status',1)
                      ->pluck('unique_paper_code','id');
    }
    public static function pluckSubjectforUpdate($college_id='',$subject_type = '') {
        $data = Subject::whereNotIn('status', [9,2]);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        if(!empty($subject_type)) {
            $data->where('subject_type', $subject_type);
        }
        $final_data = $data->pluck('subject_name', 'id')->toArray();
        return $final_data;
    }

//     public static function getGE() {
//         return Subject::where('subject_type','=',2)
//                     ->pluck('subject_name','id');
//     }
//     public static function getSEC() {
//         return Subject::where('subject_type','=',1)
//                     ->pluck('subject_name','id');
//     }
//     public static function getAECC() {
//         return Subject::where('subject_type','=',4)
//                     ->pluck('subject_name','id');
//     }
//     public static function getDSC() {
//         return Subject::where('subject_type','=',3)
//                     ->pluck('subject_name','id');
//     }
//     public static function getCORE() {
//         return Subject::where('subject_type','=',5)
//                     ->pluck('subject_name','id');
//     }
//     public static function getVAC() {
//         return Subject::where('subject_type','=',6)
//                     ->pluck('subject_name','id');
//     }

// public static function pluckSEC() {
//         return Subject::where('subject_type','=',1)
//                     ->orderBy('subject_name', 'asc')
//                     ->pluck('subject_name', 'id');
//     }    

}