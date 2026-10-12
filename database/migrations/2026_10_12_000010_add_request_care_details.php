<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRequestCareDetails extends Migration
{
    public function up()
    {
        Schema::table('care_requests', function (Blueprint $table) {
            $table->string('problem_type', 20)->nullable();
            $table->string('assistance_preference', 20)->nullable();
            $table->integer('preferred_hospital_id')->nullable();
            $table->text('description')->nullable();
            $table->unsignedBigInteger('location_captured_at')->nullable();
        });
    }

    public function down()
    {
        Schema::table('care_requests', function (Blueprint $table) {
            $table->dropColumn(['problem_type','assistance_preference','preferred_hospital_id','description','location_captured_at']);
        });
    }
}
