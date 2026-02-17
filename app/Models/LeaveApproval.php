<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveApproval extends Model
{
    // use HasFactory;
    protected $table ="leave_application_approval";
    protected $guarded = [];
    public $timestamps = false;

    public static function getEmployeesOnLeave($college_id, $leave_date) {
        return LeaveApproval::join('leave_application', 'leave_application_approval.leave_application_id', 'leave_application.id')
                            ->select('leave_application.applicant', 'leave_application_approval.leave_type_id', 'leave_application_approval.from_date', 'leave_application_approval.to_date', 'leave_application_approval.status', 'leave_application_approval.leave_count')
                            ->where('leave_application_approval.college_id',$college_id)
                            ->where('leave_application_approval.status', 1)
                            ->where('leave_application_approval.from_date', '<=', $leave_date)
                            ->where('leave_application_approval.to_date', '>=', $leave_date)
                            ->get();
    }
}
