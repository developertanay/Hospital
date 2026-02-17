<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Leavetype extends Model
{
    protected $table ="leave_types";
    
    public static function getActiveDataFromGroup($college_id, $group_id) {
        return LeaveTypeMast::join('leave_type_transaction', 'leave_type_transaction.leave_type','leave_type_mast.id')
                                    ->select('leave_type_mast.*')
                                    ->where('leave_type_transaction.college_id', $college_id)
                                    ->where('leave_type_mast.college_id', $college_id)
                                    ->where('leave_type_transaction.group_id', $group_id)
                                    ->where('leave_type_transaction.status', 1)
                                    ->where('leave_type_mast.status', 1)
                                    ->get();
    }
    public static function pluckActiveIdAndName($college_id) {
        return LeaveTypeMast::where('college_id', $college_id)
                                ->where('status', 1)
                                ->pluck('description', 'id');
    }
}
