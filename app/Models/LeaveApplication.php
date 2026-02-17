<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveApplication extends Model
{
    use HasFactory;
    protected $table ="leave_application";
    protected $guarded = [];
    public $timestamps = false;

    public static function getDataForUser($college_id, $users_id='', $status='') 
    {

        $data = LeaveApplication::where('college_id', $college_id);
                                

        if(!empty($status)) {
            $data->where('status',$status);
        }
        if(!empty($users_id)) {
            $data->where('applicant', $users_id);
        }
        $final_data = $data->get();
        return $final_data;
    }


    public static function updateData($college_id, $id, $update_arr) {
        return LeaveApplication::where('id', $id)
                                ->where('college_id', $college_id)
                                ->update($update_arr);
    }

    public static function getDataById($id){

        return LeaveApplication::where('id',$id)
                                    ->first();
    }
    public static function getDataForAllUser($college_id, $status='') 
    {

        $data = LeaveApplication::where('college_id', $college_id);
                                

        if(!empty($status)) {
            $data->where('status',$status);
        }
        
        $final_data = $data->get();
        return $final_data;
    }



}
