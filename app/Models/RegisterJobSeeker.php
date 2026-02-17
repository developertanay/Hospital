<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;
// use DB;

class RegisterJobSeeker extends Model 
{

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $table = "candidate_profile";
    protected $guarded = [];
    
}


