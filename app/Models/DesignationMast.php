<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DesignationMast extends Model
{
    use HasFactory;
    protected $table = 'designation_mast';
    protected $guarded = [];

    public static function pluckData($college_id) 
    {
        return DesignationMast::where('college_id', $college_id)
                        ->where('status',1)
                        ->orderBy('sequence', 'asc')
                        ->pluck('name', 'id');
                        
    }
}
