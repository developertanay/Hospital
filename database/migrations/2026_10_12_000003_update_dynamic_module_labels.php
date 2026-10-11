<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
class UpdateDynamicModuleLabels extends Migration
{
    public function up()
    {
        DB::table('modules')->where('url','demo/modules')->update(['name'=>'Modules & Role Access']);
        DB::table('modules')->where('url','demo/users')->update(['care_roles'=>null]);
    }
    public function down() {}
}
