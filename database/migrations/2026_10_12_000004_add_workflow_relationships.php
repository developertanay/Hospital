<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
class AddWorkflowRelationships extends Migration
{
    public function up()
    {
        Schema::table('care_requests', function (Blueprint $table) { $table->integer('bed_type_id')->nullable()->index(); });
        foreach (DB::table('care_requests')->get() as $request) DB::table('care_requests')->where('id',$request->id)->update(['bed_type_id'=>DB::table('bedding_type')->where('name',$request->care)->value('id')]);
        Schema::table('care_beds', function (Blueprint $table) {
            $table->foreign('hospital_id')->references('id')->on('hospital_mast');
            $table->foreign('bed_type_id')->references('id')->on('bedding_type');
        });
        Schema::table('care_requests', function (Blueprint $table) {
            $table->foreign('owner_id')->references('id')->on('users');
            $table->foreign('hospital_id')->references('id')->on('hospital_mast');
            $table->foreign('bed_type_id')->references('id')->on('bedding_type');
        });
        Schema::table('users', function (Blueprint $table) { $table->foreign('hospital_id')->references('id')->on('hospital_mast'); });
        Schema::table('blodd_inventory_management', function (Blueprint $table) { $table->foreign('hospital_id')->references('id')->on('hospital_mast'); });
    }
    public function down()
    {
        Schema::table('care_beds', function (Blueprint $table) { $table->dropForeign(['hospital_id']); $table->dropForeign(['bed_type_id']); });
        Schema::table('care_requests', function (Blueprint $table) { $table->dropForeign(['owner_id']); $table->dropForeign(['hospital_id']); $table->dropForeign(['bed_type_id']); $table->dropColumn('bed_type_id'); });
        Schema::table('users', function (Blueprint $table) { $table->dropForeign(['hospital_id']); });
        Schema::table('blodd_inventory_management', function (Blueprint $table) { $table->dropForeign(['hospital_id']); });
    }
}
