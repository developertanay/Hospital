<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveSummary extends Model
{
    use HasFactory;
    protected $table='leave_summary';


    public static function getData($college_id, $faculty_id) {
        return LeaveSummary::where('college_id', $college_id)
                            ->where('user_id' , $faculty_id)
                            // ->groupBy('leave_type')
                            ->pluck('balance' , 'leave_type');
    }
}
