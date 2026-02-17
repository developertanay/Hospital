<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Auth;
use Session;

class Jobdesk extends Model 
{

   
    protected $table = "job_internship_mast";
    protected $guarded = [];

     public static function getAllRecords($company_id) {
        return Jobdesk::where('company_id',$company_id )
                    ->where('status','!=',9 )
                    ->get();
    }

    public static function getDataFromId($id) {
        return Jobdesk::where('id', $id)
                    ->first();
    }
 
    public static function updateDataFromId($id, $arr_to_update) {
        return Jobdesk::where('id', $id)
                    ->update($arr_to_update);
    }

    public static function getCustomRecords($show_public_jobs_flag, $show_college_specific_jobs_flag, $college_id, $previously_applied_jobs_id_arr) {
        //show_public_jobs_flag is always 1 kyuki publi toh dikhaani hi hai

        $exceptional_company_id_arr = [];
        
        $status_arr = [1,2];

        if($show_college_specific_jobs_flag) {
            //get company assigned specifically to colleges
            if(!empty($college_id)) {
                //get all companies that are not a part of this college(means a part of another college and not public)
                $exceptional_company_id_arr = Company_College_Mapping::where('college_id', '!=', $college_id)->pluck('company_id')->toArray();
            }
        }

        $data = Jobdesk::where('title','!=', NULL); //dont remove this as it handles the where, orwhere of below conditions
        
        if(count($exceptional_company_id_arr)>0) {
            $data->where(function ($query) use ($exceptional_company_id_arr) {
                        $query->whereNotIn('company_id', $company_id)
                            ->where('status',1);
                    });
        }
        if(count($previously_applied_jobs_id_arr)>0) {
            $data->orWhere(function ($query) use ($previously_applied_jobs_id_arr) {
                        $query->whereIn('id', $previously_applied_jobs_id_arr)
                             ->whereIn('status', [1,2]);
                    });
        }

        $final_data = $data->get();
        
        return $final_data;
    }

    public static function getAppliedJobData($job_ids,$users_id){
        $data= Jobdesk::join('job_application','job_internship_mast.id','job_application.job_id')->select('job_internship_mast.*','job_application.status as job_status','job_application.noc_letter_id as noc_letter_id','job_application.noc_status as noc_status');
        if(count($job_ids)>0)
        {
            $data->whereIn('job_internship_mast.id',$job_ids);
        }
        if(!empty($users_id)){
            $data->where('job_application.users_id',$users_id);

        }

        $final_data=$data->get();
        return $final_data;

    }
    public static function getAppliedJobDatabyId($job_id,$users_id){
        $data= Jobdesk::join('job_application','job_internship_mast.id','job_application.job_id')->select('job_internship_mast.*','job_application.status as job_status','job_application.noc_letter_id as noc_letter_id','job_application.noc_status as noc_status');

        if(!empty($job_id))
        {
            $data->where('job_internship_mast.id',$job_id);
        }
        if(!empty($users_id)){
            $data->where('job_application.users_id',$users_id);

        }
        
        $final_data=$data->get()->first();
        return $final_data;

    }
    public static function getAppliedInternshipData($internship_ids,$users_id){
        $data= Jobdesk::join('job_application','job_internship_mast.id','job_application.job_id')->select('job_internship_mast.*','job_application.status as job_status','job_application.noc_letter_id as noc_letter_id','job_application.noc_status as noc_status');

        if(count($internship_ids)>0)
        {
            $data->whereIn('job_internship_mast.id',$internship_ids);
        }
        if(!empty($users_id)){
            $data->where('job_application.users_id',$users_id);

        }

        $final_data=$data->where('job_application.job_type_id',2)->get();
        return $final_data;

    }
    public static function getAppliedInternshipDatabyId($job_id,$users_id){
        $data= Jobdesk::join('job_application','job_internship_mast.id','job_application.job_id')->select('job_internship_mast.*','job_application.status as job_status','job_application.noc_letter_id as noc_letter_id','job_application.noc_status as noc_status');

        if(!empty($job_id))
        {
            $data->where('job_internship_mast.id',$job_id);
        }
        if(!empty($users_id)){
            $data->where('job_application.users_id',$users_id);

        }
        
        $final_data=$data->get()->first();
        return $final_data;

    }

}