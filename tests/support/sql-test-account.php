<?php
// Call only inside an outer transaction that is always rolled back.
function isolatedSqlAccount($email){
    $source=\App\Models\User::where('email',$email)->firstOrFail();$user=$source->replicate();$user->email='isolated-'.bin2hex(random_bytes(8)).'@test.invalid';$user->save();
    if($user->care_role==='driver'){
        $original=\App\Models\AmbulanceDriver::with('ambulance')->where('user_id',$source->id)->firstOrFail();$vehicle=$original->ambulance->replicate();$vehicle->vehicle_number='ISOLATED'.strtoupper(bin2hex(random_bytes(5)));$vehicle->save();
        \App\Models\AmbulanceDriver::create(['id'=>'isolated-driver-'.$user->id,'user_id'=>$user->id,'ambulance_id'=>$vehicle->id]);
    }
    return $user;
}
