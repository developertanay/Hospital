<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveTypeTransaction extends Model
{
    protected $table = "leave_type_transaction";
    use HasFactory;

    public static function getData($college_id) {
        return LeaveTypeTransaction::where('college_id', $college_id)
                                    ->whereIn('status', [1,2])
                                    ->orderBy('group_id','asc')
                                    ->orderBy('leave_type','asc')
                                    ->get();
    }

    public static function getActiveDataFromGroupId($college_id,$group_id) {
        return LeaveTypeTransaction::where('college_id', $college_id)
                                ->where('group_id', $group_id)
                                ->where('status', 1)
                                ->get();
    }

    public static function getAssignedGroups($college_id) {
        return LeaveTypeTransaction::where('college_id', $college_id)
                                ->where('status', 1)
                                // ->pluck('group_id')
                                ->distinct('group_id')
                                ->pluck('group_id')
                                ->toArray();   
    }

    public static function getRow($college_id, $group_id, $leave_type_id) {
        return LeaveTypeTransaction::where('college_id', $college_id)
                                ->where('group_id', $group_id)
                                ->where('leave_type', $leave_type_id)
                                ->where('status', 1)
                                ->first();
    }

}
