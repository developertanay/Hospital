<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class CareRouteDemoSeeder extends Seeder
{
    public function run()
    {
        DB::transaction(function () {
            $now=now(); $roles=['admin'=>2];
            foreach (['hospital'=>'Hospital Operator','patient'=>'Patient Requester','driver'=>'Ambulance Driver'] as $key=>$name) {
                $id=DB::table('role_mast')->where('name',$name)->where('college_id',1)->value('id');
                if (!$id) $id=DB::table('role_mast')->insertGetId(['name'=>$name,'college_id'=>1,'status'=>1,'sequence'=>20,'created_at'=>$now]);
                $roles[$key]=$id;
            }
            foreach (config('demo.accounts') as $email=>$account) {
                // Never overwrite an existing password on rerun.
                if (!DB::table('users')->where('email',$email)->exists()) DB::table('users')->insert([
                    'name'=>$account['name'],'email'=>$email,'password'=>Hash::make('CareRoute@2026'),
                    'college_id'=>1,'company_id'=>null,'register_type'=>null,'role_id'=>$roles[$account['role']],
                    'created_at'=>$now,'updated_at'=>$now,'email_verified_at'=>$now,
                ]);
            }
            $groups=[];
            foreach (config('demo.modules') as $module) {
                $group=$module['group'];
                if (!isset($groups[$group])) {
                    $id=DB::table('modules')->where('college_id',1)->whereNull('parent_id')->where('name',$group)->value('id');
                    if (!$id) $id=DB::table('modules')->insertGetId(['college_id'=>1,'parent_id'=>null,'name'=>$group,'url'=>'#','icon'=>'bx bx-grid-alt','status'=>1,'sequence'=>1,'created_at'=>$now]);
                    $groups[$group]=$id;
                }
                $id=DB::table('modules')->where('college_id',1)->where('url',$module['url'])->value('id');
                if (!$id) $id=DB::table('modules')->insertGetId(['college_id'=>1,'parent_id'=>$groups[$group],'name'=>$module['name'],'url'=>$module['url'],'icon'=>'bx bx-chevron-right','status'=>1,'sequence'=>10,'created_at'=>$now]);
                foreach ($module['roles'] as $role) {
                    foreach ([$id,$groups[$group]] as $moduleId) $this->assign($roles[$role],$moduleId,$role==='admin',$now);
                }
            }
            // Preserve existing assignments; add every stored module to Admin and Super Admin.
            foreach (DB::table('modules')->where('college_id',1)->pluck('id') as $id) foreach ([1,2] as $role) $this->assign($role,$id,true,$now);
        });
        $this->command->info('CareRoute demo accounts and module assignments seeded. Password: CareRoute@2026 (new accounts only).');
    }

    private function assign($role,$module,$admin,$now)
    {
        DB::table('module_assigning')->updateOrInsert(['college_id'=>1,'role_id'=>$role,'modules_id'=>$module],
            ['status'=>1,'view_flag'=>1,'create_flag'=>$admin?1:0,'edit_flag'=>$admin?1:0,'upload_flag'=>$admin?1:0,'download_flag'=>1,'updated_at'=>$now]);
    }
}
