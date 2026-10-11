<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class EnableHospitalMasterCrud extends Migration
{
    public function up()
    {
        DB::transaction(function () {
            foreach (DB::table('users')->where('care_role','admin')->get() as $user) {
                foreach (['Department'=>'Department Master','AmbulanceCategory'=>'Ambulance Categories','HospitalDepartment'=>'Hospital Departments'] as $url=>$name) {
                    $module = DB::table('modules')->where('college_id',$user->college_id)->where('url',$url)->first();
                    if (!$module) continue;
                    DB::table('modules')->where('id',$module->id)->update(['name'=>$name,'status'=>1,'care_group'=>'Masters','updated_at'=>now(),'updated_by'=>$user->id]);
                    DB::table('module_assigning')->updateOrInsert(
                        ['college_id'=>$user->college_id,'role_id'=>$user->role_id,'modules_id'=>$module->id],
                        ['status'=>1,'view_flag'=>1,'create_flag'=>1,'edit_flag'=>1,'upload_flag'=>0,'download_flag'=>1,'updated_at'=>now(),'updated_by'=>$user->id]
                    );
                }
            }
        });
    }
    public function down()
    {
        // Keep admin selections and module records; they remain editable in Role Assignments.
    }
}
