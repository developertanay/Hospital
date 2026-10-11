<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
class AmbulanceDemoSeeder extends Seeder {
    public function run() {
        DB::transaction(function () {
            $role=DB::table('role_mast')->where('name','Ambulance Driver')->where('college_id',1)->value('id');
            if (!$role) $role=DB::table('role_mast')->insertGetId(['name'=>'Ambulance Driver','college_id'=>1,'status'=>1,'sequence'=>20]);
            foreach (config('ambulance.drivers') as $email=>$driver) {
                if (DB::table('users')->where('email',$email)->exists()) continue;
                DB::table('users')->insert(['name'=>$driver['name'],'email'=>$email,'password'=>Hash::make('CareRoute@2026'),
                    'care_role'=>'driver','care_enabled'=>true,'hospital_id'=>null,'college_id'=>1,'role_id'=>$role,'created_at'=>now(),'updated_at'=>now()]);
            }
        });
        $this->command->info('Dispatch demo drivers provisioned; existing accounts/passwords and hospital inventory preserved.');
    }
}
