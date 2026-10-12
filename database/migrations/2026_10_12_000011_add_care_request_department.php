<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
class AddCareRequestDepartment extends Migration {
    public function up() { Schema::table('care_requests',function(Blueprint $table){$table->unsignedInteger('department_id')->nullable();}); }
    public function down() { Schema::table('care_requests',function(Blueprint $table){$table->dropColumn('department_id');}); }
}
