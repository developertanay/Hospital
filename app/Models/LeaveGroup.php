<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveGroup extends Model
{
    protected $table ="groups";
    use HasFactory;

    public static function pluckActiveIdAndName($college_id) {
        return LeaveGroup::where('college_id', $college_id)
                        ->where('status', 1)
                        ->pluck('name', 'id');
    }
}
