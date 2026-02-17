<?php

namespace App\Models;

// use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmployeeCount extends Model
{
    // use HasFactory;
    protected $table = "employee_count";
    protected $guarded = [];

    public static function pluckActiveData() {
        return EmployeeCount::where('status',1)
                            ->orderBy('sequence', 'asc')
                            ->pluck('name', 'id');
    }
}
