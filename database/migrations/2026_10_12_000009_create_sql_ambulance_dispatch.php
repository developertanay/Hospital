<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class CreateSqlAmbulanceDispatch extends Migration
{
    public function up()
    {
        Schema::create('ambulance_drivers',function (Blueprint $t) {
            $t->string('id',32)->primary();
            $t->unsignedBigInteger('user_id')->unique();
            $t->unsignedInteger('ambulance_id')->nullable()->unique();
            $t->decimal('latitude',10,7)->nullable();
            $t->decimal('longitude',10,7)->nullable();
            $t->boolean('on_duty')->default(false);
            $t->integer('seen_at')->default(0);
            $t->integer('location_at')->default(0);
            $t->string('location_mode',10)->default('gps');
            $t->boolean('follow_pickup')->default(false);
            $t->timestamps();
            $t->foreign('user_id')->references('id')->on('users');
            $t->foreign('ambulance_id')->references('id')->on('ambulance_details');
        });
        Schema::create('patient_emergency_profiles',function (Blueprint $t) {
            $t->unsignedBigInteger('user_id')->primary();
            $t->string('name',80); $t->string('mobile',30); $t->integer('age');
            $t->string('emergency_name',80); $t->string('emergency_phone',30);
            $t->timestamps();
            $t->foreign('user_id')->references('id')->on('users');
        });
        Schema::create('ambulance_trips',function (Blueprint $t) {
            $t->string('id',32)->primary();
            $t->unsignedBigInteger('owner_id')->index();
            $t->string('driver_id',32)->nullable()->index();
            $t->string('status',30)->index();
            $t->json('payload');
            $t->timestamps();
            $t->foreign('owner_id')->references('id')->on('users');
            $t->foreign('driver_id')->references('id')->on('ambulance_drivers');
        });
        Schema::create('ambulance_dispatch_lock',function (Blueprint $t) { $t->integer('id')->primary(); });
        DB::table('ambulance_dispatch_lock')->insert(['id'=>1]);
        // Import a previous dispatch snapshot once. Keep its source file as a backup.
        $path=storage_path('app/ambulance-demo.json');
        $legacy=is_file($path) ? json_decode(file_get_contents($path),true) : [];
        foreach (DB::table('users')->where('care_role','driver')->get() as $user) {
            $old=config('ambulance.drivers')[$user->email] ?? null;
            $id=$old['driver'] ?? ('driver-'.$user->id);
            DB::table('ambulance_drivers')->insert(['id'=>$id,'user_id'=>$user->id,'created_at'=>now(),'updated_at'=>now()]);
        }
        foreach (($legacy['profiles'] ?? []) as $userId=>$profile) {
            if (!DB::table('users')->where('id',$userId)->exists()) continue;
            DB::table('patient_emergency_profiles')->insert($profile+['user_id'=>$userId,'created_at'=>now(),'updated_at'=>now()]);
        }
        foreach (($legacy['calls'] ?? []) as $trip) {
            if (!DB::table('users')->where('id',$trip['owner_id'])->exists()) continue;
            if ($trip['driver_id'] && !DB::table('ambulance_drivers')->where('id',$trip['driver_id'])->exists()) continue;
            DB::table('ambulance_trips')->insert(['id'=>$trip['id'],'owner_id'=>$trip['owner_id'],'driver_id'=>$trip['driver_id'],
                'status'=>$trip['status'],'payload'=>json_encode($trip),'created_at'=>now(),'updated_at'=>now()]);
        }
    }
    public function down()
    {
        Schema::dropIfExists('ambulance_trips');
        Schema::dropIfExists('patient_emergency_profiles');
        Schema::dropIfExists('ambulance_drivers');
        Schema::dropIfExists('ambulance_dispatch_lock');
    }
}
