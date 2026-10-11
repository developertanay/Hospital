<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Hospital;
use App\Models\Ambulance;
use App\Models\AmbulanceDriver;
use App\Models\CareBed;

class MobileTestAccountsSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            $password='TestCare@2026';
            $hospital=Hospital::firstOrCreate(['registration_no'=>'APP-TEST-HOSPITAL'],[
                'hospital_name'=>'App Test Hospital','short_name'=>'APP-TEST','type'=>'Test','host'=>'app-test','logo'=>'',
                'state'=>0,'district'=>0,'city'=>0,'address'=>'Test hospital for web and mobile verification','pin_code'=>0,
                'contact_no'=>'0000000000','emergency_no'=>'0000000000','email_id'=>'hospital.test@careroute.test',
                'website'=>'','geo_location'=>'','status'=>1,'created_by'=>0,'updated_by'=>0,'accepting_requests'=>true,
                'latitude'=>28.6139,'longitude'=>77.2090,
            ]);
            $roles=[];
            foreach (['patient'=>'Patient Test','hospital'=>'Hospital Test','driver'=>'Driver Test'] as $type=>$name) {
                $role=DB::table('role_mast')->where('name',$name)->where('college_id',1)->first();
                $roles[$type]=$role ? $role->id : DB::table('role_mast')->insertGetId(['name'=>$name,'college_id'=>1,'status'=>1,'sequence'=>30]);
            }
            $roles['admin']=DB::table('role_mast')->where('name','Admin')->where('status',1)->value('id');
            if (!$roles['admin']) throw new \RuntimeException('An administrator role must exist before provisioning test accounts.');
            foreach (['patient.test@careroute.test'=>'patient','hospital.test@careroute.test'=>'hospital','driver1.test@careroute.test'=>'driver','driver2.test@careroute.test'=>'driver','admin.test@careroute.test'=>'admin'] as $email=>$role) {
                User::firstOrCreate(['email'=>$email],['name'=>ucfirst($role).' Test Account','password'=>Hash::make($password),
                    'care_role'=>$role,'care_enabled'=>true,'role_id'=>$roles[$role],'college_id'=>1,'hospital_id'=>$role==='hospital' ? $hospital->id : null]);
            }
            $category=DB::table('ambulance_category_mast')->where('status',1)->value('id');
            if (!$category) throw new \RuntimeException('Create an active ambulance category first.');
            foreach ([1,2] as $number) {
                $user=User::where('email','driver'.$number.'.test@careroute.test')->firstOrFail();
                $ambulance=Ambulance::firstOrCreate(['vehicle_number'=>'TESTAMB00'.$number],[
                    'ambulance_category_id'=>$category,'operation_type'=>$number===1 ? 'hospital' : 'private',
                    'hospital_id'=>$number===1 ? $hospital->id : null,'driver_name'=>'Test Driver '.$number,'driver_contact'=>'000000000'.$number,'status'=>1,
                ]);
                AmbulanceDriver::firstOrCreate(['user_id'=>$user->id],['id'=>'driver-'.$user->id,'ambulance_id'=>$ambulance->id]);
            }
            $care=DB::table('bedding_type')->where('status',1)->first();
            if (!$care) throw new \RuntimeException('Create an active bed type first.');
            foreach ([1,2,3] as $number) CareBed::firstOrCreate(['id'=>'app-test-bed-'.$number],['hospital_id'=>$hospital->id,'bed_type_id'=>$care->id,'name'=>'Test bed '.$number,'status'=>'Available']);
            $modules=['patient'=>['emergency','emergency/requests','emergency/reservations','emergency/history'],
                'hospital'=>['emergency','emergency/approvals','emergency/beds','emergency/inventory','emergency/blood','emergency/settings','emergency/history'],
                'driver'=>['emergency/dispatch']];
            if (!DB::table('modules')->where('college_id',1)->where('url','emergency/dispatch')->exists()) DB::table('modules')->insert([
                'college_id'=>1,'name'=>'Driver Dispatch','url'=>'emergency/dispatch','icon'=>'bx bx-car','status'=>1,'sequence'=>25,'care_group'=>'Ambulance Operations']);
            foreach ($modules as $type=>$urls) foreach (DB::table('modules')->where('college_id',1)->whereIn('url',$urls)->where('status',1)->get() as $module) {
                DB::table('module_assigning')->updateOrInsert(['college_id'=>1,'role_id'=>$roles[$type],'modules_id'=>$module->id],
                    ['status'=>1,'view_flag'=>1,'create_flag'=>1,'edit_flag'=>1,'upload_flag'=>0,'download_flag'=>0]);
            }
        });
        $this->command->info('Dedicated SQL test accounts created. Existing users/passwords and operational records preserved.');
    }
}
