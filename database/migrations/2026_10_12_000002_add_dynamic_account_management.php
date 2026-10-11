<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class AddDynamicAccountManagement extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) { $table->boolean('care_enabled')->default(true); });
        DB::statement('ALTER TABLE hospital_mast MODIFY contact_no VARCHAR(40) NOT NULL');
        DB::statement('ALTER TABLE hospital_mast MODIFY emergency_no VARCHAR(40) NOT NULL');
        $module = DB::table('modules')->where('url', 'Accounts')->first();
        $id = $module ? $module->id : DB::table('modules')->insertGetId(['name'=>'Accounts & Hospital Access','url'=>'Accounts','college_id'=>1,'parent_id'=>null,'icon'=>'bx bx-user','status'=>1,'sequence'=>10,'care_roles'=>json_encode(['admin']),'care_group'=>'Administration','created_at'=>now()]);
        foreach (DB::table('users')->where('care_role', 'admin')->get() as $user) DB::table('module_assigning')->updateOrInsert(['college_id'=>$user->college_id ?? 1,'role_id'=>$user->role_id,'modules_id'=>$id], ['status'=>1,'view_flag'=>1,'create_flag'=>1,'edit_flag'=>1,'upload_flag'=>0,'download_flag'=>1]);
    }
    public function down() { Schema::table('users', function (Blueprint $table) { $table->dropColumn('care_enabled'); }); }
}
