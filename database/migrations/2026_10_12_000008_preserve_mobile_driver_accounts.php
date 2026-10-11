<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
class PreserveMobileDriverAccounts extends Migration {
    public function up() {
        $roles=DB::table('role_mast')->where('name','Ambulance Driver')->pluck('id');
        DB::table('users')->whereIn('role_id',$roles)->whereIn('email',array_keys(config('ambulance.drivers')))
            ->whereNull('care_role')->update(['care_role'=>'driver']);
        // Never re-enable an account disabled by an administrator.
    }
    public function down() {
        DB::table('users')->where('care_role','driver')->whereIn('email',array_keys(config('ambulance.drivers')))->update(['care_role'=>null]);
    }
}
