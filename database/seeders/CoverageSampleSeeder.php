<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use App\Models\CoverageOrganisation;
use App\Models\HospitalCoverage;
use App\Models\Hospital;
class CoverageSampleSeeder extends Seeder {
    public function run(){
        // Provision only the new administration modules through stored role assignments.
        foreach(\Illuminate\Support\Facades\DB::table('users')->where('care_role','admin')->where('care_enabled',1)->get() as $admin){
            foreach(['CoverageOrganisation','HospitalCoverage'] as $url){
                $module=\Illuminate\Support\Facades\DB::table('modules')->where('college_id',$admin->college_id)->where('url',$url)->first();
                if($module && !\Illuminate\Support\Facades\DB::table('module_assigning')->where('modules_id',$module->id)->where('role_id',$admin->role_id)->where('college_id',$admin->college_id)->exists())\Illuminate\Support\Facades\DB::table('module_assigning')->insert(['modules_id'=>$module->id,'role_id'=>$admin->role_id,'college_id'=>$admin->college_id,'status'=>1,'view_flag'=>1]);
            }
        }
        $hospital=Hospital::where('registration_no','APP-TEST-HOSPITAL')->first();
        foreach(['Insurance','TPA','Employer','Scheme'] as $kind){$o=CoverageOrganisation::firstOrCreate(['name'=>'Demo '.$kind.' Organisation'],['kind'=>$kind,'status'=>1]);if($hospital)HospitalCoverage::firstOrCreate(['hospital_id'=>$hospital->id,'organisation_id'=>$o->id],['status'=>1,'settlement_mode'=>in_array($kind,['Insurance','TPA'])?'Cashless':'Panel']);}
        $this->command->info('Labelled demo organisations and App Test Hospital mappings created. Existing records preserved.');
    }
}
