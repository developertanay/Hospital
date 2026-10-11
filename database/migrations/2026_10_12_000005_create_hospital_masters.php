<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateHospitalMasters extends Migration
{
    public function up()
    {
        if (!Schema::hasTable('department_mast')) Schema::create('department_mast', function (Blueprint $table) {
            $table->increments('id');
            $table->string('department_name', 100)->unique();
            $table->integer('college_id')->nullable();
            $table->integer('university_id')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
        });
        Schema::create('ambulance_category_mast', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->unique();
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
        });
        Schema::create('hospital_department_mapping', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('hospital_id');
            $table->unsignedInteger('department_id');
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->unique(['hospital_id', 'department_id']);
            $table->foreign('hospital_id')->references('id')->on('hospital_mast');
            $table->foreign('department_id')->references('id')->on('department_mast');
        });
        foreach (DB::table('users')->where('care_role', 'admin')->get()->unique('college_id') as $user) {
            foreach (['Department'=>'Department Master', 'AmbulanceCategory'=>'Ambulance Categories', 'HospitalDepartment'=>'Hospital Departments'] as $url=>$name) {
                if (!DB::table('modules')->where('college_id',$user->college_id)->where('url',$url)->exists()) {
                    DB::table('modules')->insert(['college_id'=>$user->college_id,'name'=>$name,'url'=>$url,'icon'=>'bx bx-list-ul','status'=>1,'sequence'=>20,'care_group'=>'Masters','created_at'=>now()]);
                }
            }
        }
        // Register modules only. Administrators choose their role assignments.
    }
    public function down()
    {
        Schema::dropIfExists('hospital_department_mapping');
        Schema::dropIfExists('ambulance_category_mast');
        // Preserve the shared legacy department master and its records.
    }
}
