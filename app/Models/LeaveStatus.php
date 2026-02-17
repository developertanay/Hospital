<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveStatus extends Model
{
    use HasFactory;
    protected $table ="leave_status_mast";

    public static function pluckData($status_arr) {
        return LeaveStatus::whereIn('status', $status_arr)
                            ->orderBy('sequence', 'asc')
                            ->pluck('name', 'id'); 
    }
}
