<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class LeaveBalance extends Model
{
    use HasFactory;
    protected $table="leave_stock";
    protected $guarded = [];
    public $timestamps = false;

    public static function getStock($college_id, $acc_year, $users_id) {
        return LeaveBalance::where('college_id', $college_id)
                            ->where('acc_year', $acc_year)
                            ->where('status',1)
                            ->where('users_id', $users_id)
                            ->get();
    }


    public static function updateStock($college_id, $acc_year, $users_id, $leave_type_id, $update_arr) {
        return LeaveBalance::where('college_id', $college_id)
                            ->where('acc_year', $acc_year)
                            ->where('users_id', $users_id)
                            ->where('status',1)
                            ->where('leave_type_id', $leave_type_id)
                            ->update($update_arr);
    } 

    public static function getAllStock($college_id, $current_year) {
        return LeaveBalance::where('college_id', $college_id)
                            ->where('acc_year', $current_year)
                            ->where('status',1)
                            ->get();   
    }   

   
}
