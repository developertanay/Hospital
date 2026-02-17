<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;
// use DB;

use Illuminate\Database\Eloquent\Factories\HasFactory;


class User_Subject_Mapping extends Model
{
     protected $table = "user_subject_mapping";
     protected $guarded = [];
     public static function getAllRecords() {
        return User_Subject_Mapping::where('status', '!=',9)
                    ->orderBy('user_profile_id', 'asc')
                    ->get();
                }
     public static function pluckCodeAndName() {
        return User_Subject_Mapping::where('status','!=', 9)
                    ->pluck('user_profile_id', 'id');
    }
    
    public static function getAllDataOnCondition($college_id='',$academic_year='',$semester='') {
            $data=User_Subject_Mapping::where('status','!=', 9);
                    if($college_id){
                     $data->where('college_id',$college_id);
                    }
                    if($college_id){
                     $data->where('academic_year',$academic_year);
                     }
                    if($college_id){
                     $data->where('semester',$semester);
                        }
                  $final_data=$data->get();
      return $final_data;
                  
  }    
    public static function  delete_data($college_id='',$academic_year='',$semester='') {
            $data=User_Subject_Mapping::where('status','!=', 9);
                     if($college_id){
                      $data->where('college_id',$college_id);
                     }
                     if($college_id){
                        $data->where('academic_year',$academic_year);
                        }
                     if($college_id){
                        $data->where('semester',$semester);
                           }
                 $final_data=$data->delete();
      return $final_data;
               
   }    

     }
?>     