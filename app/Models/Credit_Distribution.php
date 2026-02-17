<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Credit_Distribution extends Model
{
    use HasFactory;
    
    protected $table="credit_distribution_mast";

    public static function getCreditsAndlecturetype($subject_id=''){

        return Credit_Distribution::where('subject_mast_id',$subject_id)
                                    ->pluck('credits','lecture_type_id');
    }
}
