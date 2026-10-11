<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateDynamicHospitalWorkflow extends Migration
{
    public function up()
    {
        Schema::table('hospital_mast', function (Blueprint $table) {
            $table->boolean('accepting_requests')->default(true);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
        });
        Schema::table('users', function (Blueprint $table) {
            $table->string('care_role', 20)->nullable();
            $table->integer('hospital_id')->nullable()->index();
        });
        Schema::table('blodd_inventory_management', function (Blueprint $table) {
            $table->integer('hospital_id')->nullable()->index();
        });
        Schema::table('modules', function (Blueprint $table) {
            $table->text('care_roles')->nullable();
            $table->string('care_group', 50)->nullable();
        });
        Schema::create('care_beds', function (Blueprint $table) {
            $table->string('id', 40)->primary();
            $table->integer('hospital_id')->index();
            $table->integer('bed_type_id')->index();
            $table->string('name', 60);
            $table->string('status', 20)->default('Available');
            $table->string('request_id', 40)->nullable()->unique();
        });
        Schema::create('care_requests', function (Blueprint $table) {
            $table->string('id', 40)->primary();
            $table->unsignedBigInteger('owner_id')->index();
            $table->string('patient', 80);
            $table->string('location', 120);
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('care', 60);
            $table->string('severity', 20);
            $table->boolean('verified')->default(false);
            $table->string('status', 20)->default('New');
            $table->integer('hospital_id')->nullable()->index();
            $table->string('bed_id', 40)->nullable();
            $table->unsignedBigInteger('created_at');
            $table->unsignedBigInteger('expires_at')->nullable();
        });
        Schema::create('care_events', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('request_id', 40)->nullable()->index();
            $table->integer('hospital_id')->nullable()->index();
            $table->text('message');
            $table->unsignedBigInteger('at');
        });
        Schema::create('care_workflow_lock', function (Blueprint $table) {
            $table->integer('id')->primary();
        });
        DB::table('care_workflow_lock')->insert(['id'=>1]);

        // Roles come from existing role records, never from fixed login emails.
        foreach (DB::table('role_mast')->get() as $role) {
            $name = strtolower(trim($role->name));
            $careRole = null;
            if (in_array($name, ['admin', 'super admin'])) $careRole = 'admin';
            if ($name === 'hospital operator') $careRole = 'hospital';
            if ($name === 'patient requester') $careRole = 'patient';
            if ($careRole) DB::table('users')->where('role_id', $role->id)->update(['care_role'=>$careRole]);
        }
        // Existing inventories/accounts are preserved. Hospital ownership must
        // be assigned explicitly; old college IDs are not hospital identities.
        foreach (config('workflow.modules') as $module) {
            $row = DB::table('modules')->where('url', $module['url'])->first();
            if (!$row) {
                $id = DB::table('modules')->insertGetId(['name'=>$module['name'], 'url'=>$module['url'], 'college_id'=>1, 'parent_id'=>null, 'icon'=>'bx bx-grid-alt', 'status'=>1, 'sequence'=>10, 'created_at'=>now()]);
            } else $id = $row->id;
            DB::table('modules')->where('id', $id)->update(['care_roles'=>json_encode($module['roles']), 'care_group'=>$module['group']]);
            foreach (DB::table('users')->whereIn('care_role', $module['roles'])->get() as $user) {
                DB::table('module_assigning')->updateOrInsert(['college_id'=>$user->college_id ?? 1, 'role_id'=>$user->role_id, 'modules_id'=>$id], ['status'=>1, 'view_flag'=>1, 'create_flag'=>1, 'edit_flag'=>1, 'upload_flag'=>0, 'download_flag'=>1]);
            }
        }
    }

    public function down()
    {
        Schema::dropIfExists('care_events');
        Schema::dropIfExists('care_requests');
        Schema::dropIfExists('care_beds');
        Schema::dropIfExists('care_workflow_lock');
        Schema::table('modules', function (Blueprint $table) { $table->dropColumn(['care_roles','care_group']); });
        Schema::table('users', function (Blueprint $table) { $table->dropColumn(['care_role','hospital_id']); });
        Schema::table('hospital_mast', function (Blueprint $table) { $table->dropColumn(['accepting_requests','latitude','longitude']); });
        Schema::table('blodd_inventory_management', function (Blueprint $table) { $table->dropColumn('hospital_id'); });
    }
}
