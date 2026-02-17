<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SessionDuration extends Model
{
    use HasFactory;
    protected $table=('session_duration');

    public static function getData($college_id)
    {
         return SessionDuration::where('college_id',$college_id)
                                ->where('status',1)
                                ->get();
    }

    public static function pluckdata($college_id,$session) {
         return SessionDuration::where('college_id',$college_id)
                                ->where('id','!=',$session)
                                // ->where('start_year', '>=', $currentYear)
                                ->where('status',1)
                                ->pluck('name','id');
    }

     public static function pluckDataFromCollege($college_id) {
          return SessionDuration::where('college_id',$college_id)
                                ->where('status',1)
                                ->orderBy('currently_running','DESC')
                                ->pluck('name','id');
     }

     public static function pluckCurrentlyRunningSession($college_id) {
          return SessionDuration::where('college_id',$college_id)
                                ->where('status',1)
                                ->where('currently_running',1)
                                ->pluck('name','id');
     }

     public static function pluckCurrentlyRunningSessionId($college_id) {
          return SessionDuration::where('college_id',$college_id)
                                ->where('status',1)
                                ->where('currently_running',1)
                                ->pluck('id')
                                ->first();
     }
}
