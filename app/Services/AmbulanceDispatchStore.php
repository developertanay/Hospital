<?php
namespace App\Services;
use App\Models\AmbulanceDriver;
use App\Models\PatientEmergencyProfile;
use App\Models\AmbulanceTrip;
use Illuminate\Support\Facades\DB;

// The dispatch algorithm keeps its existing payload contract; SQL owns persistence.
class AmbulanceDispatchStore
{
    public function transaction($callback)
    {
        return DB::transaction(function () use ($callback) {
            DB::table('ambulance_dispatch_lock')->where('id',1)->lockForUpdate()->first();
            $profiles=[];
            foreach (PatientEmergencyProfile::all() as $profile) $profiles[(string)$profile->user_id]=$profile->only(['name','mobile','age','emergency_name','emergency_phone']);
            $drivers=[];
            foreach (AmbulanceDriver::with(['user','ambulance'])->get() as $driver) {
                $ambulance=$driver->ambulance;
                $enabled=$driver->user && $driver->user->care_enabled && $driver->user->care_role==='driver' && $ambulance && $ambulance->status==1;
                $drivers[]=['id'=>$driver->id,'name'=>$ambulance->driver_name ?? $driver->user->name,
                    'phone'=>$ambulance->driver_contact ?? '', 'vehicle'=>$ambulance->vehicle_number ?? 'Not assigned',
                    'doctor_name'=>$ambulance->doctor_name ?? '', 'doctor_contact'=>$ambulance->doctor_contact ?? '',
                    'category'=>$ambulance && $ambulance->category ? $ambulance->category->name : '',
                    'lat'=>(float)$driver->latitude,'lng'=>(float)$driver->longitude,
                    'on_duty'=>$enabled && $driver->on_duty,'seen_at'=>$driver->seen_at,'location_at'=>$driver->location_at,
                    'location_mode'=>$driver->location_mode,'follow_pickup'=>$driver->follow_pickup];
            }
            $state=['profiles'=>$profiles,'drivers'=>$drivers,'calls'=>AmbulanceTrip::orderBy('created_at')->get()->pluck('payload')->all()];
            $before=$state;
            $result=$callback($state);
            foreach ($state['profiles'] as $id=>$profile) if (($before['profiles'][$id] ?? null)!==$profile) PatientEmergencyProfile::updateOrCreate(['user_id'=>$id],$profile);
            foreach ($state['drivers'] as $index=>$driver) if ($driver!==$before['drivers'][$index]) {
                $record=AmbulanceDriver::findOrFail($driver['id']);
                $record->fill(['latitude'=>$driver['lat'],'longitude'=>$driver['lng'],'on_duty'=>$driver['on_duty'],
                    'seen_at'=>$driver['seen_at'],'location_at'=>$driver['location_at'],'location_mode'=>$driver['location_mode'],'follow_pickup'=>$driver['follow_pickup'] ?? false])->save();
                if ($record->ambulance && $driver['phone']!==$before['drivers'][$index]['phone']) {
                    $record->ambulance->driver_contact=$driver['phone']; $record->ambulance->save();
                }
            }
            $oldCalls=collect($before['calls'])->keyBy('id');
            foreach ($state['calls'] as $trip) if (($oldCalls[$trip['id']] ?? null)!==$trip) AmbulanceTrip::updateOrCreate(['id'=>$trip['id']],
                ['owner_id'=>$trip['owner_id'],'driver_id'=>$trip['driver_id'],'status'=>$trip['status'],'payload'=>$trip]);
            return $result;
        });
    }
}
