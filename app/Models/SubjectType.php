<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;
use DB;

class SubjectType extends Model 
{

   
    protected $table = "subject_type_mast";
    protected $guarded = [];

    public static function getAllRecords($college_id='') {
        $data = SubjectType::where('status','!=',9);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data = $data->get();
        return $final_data;
    }

public static function getDataFromId($id) {
        return SubjectType::where('id', $id)
                    ->first();
                }

public static function updateDataFromId($id, $arr_to_update) {
        return SubjectType::where('id', $id)
                    ->update($arr_to_update);
    }
public static function pluckActiveCodeAndName($college_id='') {
        $data = SubjectType::where('status',1);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data = $data->pluck('subject_full_name','id');
        return $final_data;
    }

    public static function pluckCodeAndName() {
        return SubjectType::where('status','!=',9)
                    ->orderBy('sequence', 'asc')
                    ->pluck('subject_full_name','id');
    }

    public static function pluckActiveData($college_id='') {
        $data = SubjectType::where('status',1);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data = $data->orderBy('sequence', 'asc')
                           ->pluck('subject_short_name','id');
        return $final_data;
        // return SubjectType::where('status', 1)
        //             ->orderBy('sequence', 'asc')
        //             ->pluck('subject_short_name','id');
        //                 // ->pluck(DB::raw("CONCAT_WS(subject_short_name, ' ', subject_full_name) as subject_full_name"),'id');
    }


    public static function pluckDefualtSelection(){
        return SubjectType::orderBy('sequence', 'asc')
                    ->pluck('default_view','id');
    }
    public static function pluckSubjectTypeFromCollege($college_id){
        return SubjectType::where('college_id',$college_id)
                            ->where('status',1)
                           ->pluck('subject_short_name','id');
    }
    public static function pluckDefualtSelectedOnly($college_id) {
        return SubjectType::where('college_id', $college_id)
                            ->where('default_view', 1)
                            ->where('status',1)
                            ->pluck('id')
                            ->toArray();
    }

    public static function pluckSpecificSubjectTypes($subject_type_id_arr){
        return SubjectType::whereIn('id', $subject_type_id_arr)
                    ->orderBy('sequence', 'asc')
                    ->pluck('subject_short_name', 'id');
    }

    public static function pluckDefaultMapping($college_id='') {
        $data = SubjectType::where('status',1);
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data = $data->orderBy('sequence', 'asc')
                           ->pluck('default_map','id');
        return $final_data;
        // return SubjectType::orderBy('sequence', 'asc')
        //             ->pluck('default_map','id');   
    }

    public static function pluckActiveCodeAndNameNonDefaultView($college_id) {
        return SubjectType::where('college_id', $college_id)
                            ->where('default_view', 0)
                            ->orderBy('sequence', 'asc')
                            ->pluck('subject_full_name', 'id');   
    }

     public static function pluckActiveCodeAndNameDefaultView($college_id) {
        return SubjectType::where('college_id', $college_id)
                            ->where('default_view', 1)
                            ->orderBy('sequence', 'asc')
                            ->pluck('id')->first();   
    }
    public static function pluckActiveCodeAndNameExceptDSC($college_id='') {
        $data = SubjectType::where('status',1)->where('subject_short_name','!=','DSC');
        if(!empty($college_id)) {
            $data->where('college_id', $college_id);
        }
        $final_data = $data->pluck('subject_full_name','id');
        return $final_data;
    }
}