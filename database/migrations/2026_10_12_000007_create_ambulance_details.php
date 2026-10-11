<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateAmbulanceDetails extends Migration
{
    public function up()
    {
        Schema::create('ambulance_details', function (Blueprint $table) {
            $table->increments('id');
            $table->string('vehicle_number',30)->unique();
            $table->unsignedInteger('ambulance_category_id');
            $table->string('operation_type',20);
            $table->integer('hospital_id')->nullable();
            $table->string('operator_name',100)->nullable();
            $table->string('operator_contact',30)->nullable();
            $table->string('doctor_name',100)->nullable();
            $table->string('doctor_contact',30)->nullable();
            $table->string('driver_name',100);
            $table->string('driver_contact',30);
            $table->string('photo_path')->nullable();
            $table->string('doctor_photo_path')->nullable();
            $table->string('driver_photo_path')->nullable();
            $table->integer('status')->default(1);
            $table->timestamps();
            $table->integer('created_by')->nullable();
            $table->integer('updated_by')->nullable();
            $table->foreign('ambulance_category_id')->references('id')->on('ambulance_category_mast');
            $table->foreign('hospital_id')->references('id')->on('hospital_mast');
        });
        foreach (DB::table('users')->where('care_role','admin')->get() as $user) {
            $module = DB::table('modules')->where('college_id',$user->college_id)->where('url','Ambulance')->first();
            $id = $module ? $module->id : DB::table('modules')->insertGetId([
                'college_id'=>$user->college_id,'name'=>'Ambulance Details','url'=>'Ambulance','icon'=>'bx bx-car',
                'status'=>1,'sequence'=>21,'care_group'=>'Masters','created_at'=>now(),
            ]);
            DB::table('module_assigning')->updateOrInsert(
                ['college_id'=>$user->college_id,'role_id'=>$user->role_id,'modules_id'=>$id],
                ['status'=>1,'view_flag'=>1,'create_flag'=>1,'edit_flag'=>1,'upload_flag'=>1,'download_flag'=>0]
            );
        }
    }
    public function down() { Schema::dropIfExists('ambulance_details'); }
}
