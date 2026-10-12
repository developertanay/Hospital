<?php
namespace App\Services;

use RuntimeException;
use App\Models\Hospital;
use App\Models\CareBed;
use App\Models\CareRequest;
use App\Models\CareEvent;
use App\Models\BloodInv;
use App\Models\bedtype;
use App\Models\bloodtype;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\RateLimiter;

class EmergencyBedService
{
    private $ttl;

    public function __construct()
    {
        $this->ttl = config('emergency.expiry_seconds', 300);
    }

    public function careTypes()
    {
        return bedtype::where('status', 1)->pluck('name')->all();
    }

    public function bloodTypes()
    {
        return bloodtype::where('status', 1)->pluck('blood_type')->all();
    }

    // One SQL transaction serializes allocation and inventory changes. Business
    // records are read from models; no JSON files or generated hospitals remain.
    private function transaction(callable $callback)
    {
        return DB::transaction(function () use ($callback) {
            DB::table('care_workflow_lock')->where('id', 1)->lockForUpdate()->first();
            $state = $this->loadRecords();
            $before = $state;
            $this->expire($state);
            $result = $callback($state);
            $this->saveChanges($before, $state);
            return $result;
        }, 3);
    }

    private function loadRecords()
    {
        $state = ['hospitals'=>[], 'requests'=>[], 'events'=>[]];
        $types = bedtype::pluck('name', 'id');
        $bloodTypes = bloodtype::pluck('blood_type', 'id');
        $beds = CareBed::all();
        $blood = BloodInv::where('status', 1)->whereNotNull('hospital_id')->get();
        $departments=\Illuminate\Support\Facades\Schema::hasTable('hospital_department_mapping') ? \App\Models\HospitalDepartment::with('department')->where('status',1)->whereHas('department',fn($q)=>$q->where('status',1))->get()->groupBy('hospital_id') : collect();
        foreach (Hospital::where('status', 1)->get() as $hospital) {
            $row = ['id'=>(string)$hospital->id, 'name'=>$hospital->hospital_name, 'status'=>$hospital->accepting_requests ? 'Open':'Offline', 'distance'=>null, 'latitude'=>$hospital->latitude, 'longitude'=>$hospital->longitude, 'contact'=>$hospital->contact_no, 'address'=>$hospital->address, 'beds'=>[], 'blood'=>[]];
            $row['departments']=($departments->get($hospital->id) ?? collect())->map(fn($mapping)=>['id'=>$mapping->department_id,'name'=>$mapping->department->department_name])->values()->all();
            foreach ($beds->where('hospital_id', $hospital->id) as $bed) {
                $row['beds'][] = ['id'=>$bed->id, 'name'=>$bed->name, 'care'=>$types[$bed->bed_type_id] ?? '', 'status'=>$bed->status, 'request_id'=>$bed->request_id];
            }
            foreach ($blood->where('hospital_id', $hospital->id) as $stock) {
                $group = $bloodTypes[$stock->blood_id] ?? null;
                if ($group) $row['blood'][$group] = ($row['blood'][$group] ?? 0) + $stock->unit;
            }
            $state['hospitals'][] = $row;
        }
        foreach (CareRequest::all() as $request) {
            $row=$request->toArray();
            $row['care']=$types[$request->bed_type_id] ?? $request->care;
            $row['hospital_id']=$request->hospital_id ? (string)$request->hospital_id : null;
            $state['requests'][]=$row;
        }
        $state['events']=CareEvent::orderByDesc('id')->get()->toArray();
        foreach ($state['events'] as &$event) $event['hospital_id']=isset($event['hospital_id']) ? (string)$event['hospital_id'] : null;
        unset($event);
        return $state;
    }

    // Save only changed records; existing legacy inventories are never erased.
    private function saveChanges($before, $after)
    {
        $types=bedtype::pluck('id','name');
        $bloodTypes=bloodtype::pluck('id','blood_type');
        foreach ($after['hospitals'] as $hospital) {
            $old=collect($before['hospitals'])->firstWhere('id',$hospital['id']);
            $oldBeds=collect($old['beds'])->keyBy('id');
            foreach ($hospital['beds'] as $bed) {
                $previous=$oldBeds->get($bed['id']);
                if ($previous && $previous['request_id']!==$bed['request_id']) CareBed::where('id',$bed['id'])->update(['request_id'=>null]);
            }
        }
        foreach ($after['hospitals'] as $hospital) {
            $old=collect($before['hospitals'])->firstWhere('id',$hospital['id']);
            if ($hospital === $old) continue;
            if ($hospital['status'] !== $old['status'] || $hospital['contact'] !== $old['contact'] || $hospital['address'] !== $old['address'] || $hospital['latitude'] !== $old['latitude'] || $hospital['longitude'] !== $old['longitude']) {
                Hospital::where('id',$hospital['id'])->update(['accepting_requests'=>$hospital['status']==='Open','contact_no'=>$hospital['contact'],'address'=>$hospital['address'],'latitude'=>$hospital['latitude'],'longitude'=>$hospital['longitude']]);
            }
            $oldBeds=collect($old['beds'])->keyBy('id');
            $newIds=[];
            foreach ($hospital['beds'] as $bed) {
                $newIds[]=$bed['id'];
                if ($oldBeds->get($bed['id']) === $bed) continue;
                CareBed::updateOrCreate(['id'=>$bed['id']],['hospital_id'=>$hospital['id'],'bed_type_id'=>$types[$bed['care']],'name'=>$bed['name'] ?? $bed['id'],'status'=>$bed['status'],'request_id'=>$bed['request_id']]);
            }
            $removed=array_diff(array_keys($oldBeds->all()),$newIds);
            if ($removed) CareBed::where('hospital_id',$hospital['id'])->whereIn('id',$removed)->delete();
            foreach ($hospital['blood'] as $group=>$units) {
                if (array_key_exists($group,$old['blood']) && $old['blood'][$group]===$units) continue;
                $stock=BloodInv::firstOrNew(['hospital_id'=>$hospital['id'],'blood_id'=>$bloodTypes[$group],'status'=>1]);
                $stock->unit=$units; $stock->created_by=$stock->created_by ?? auth()->id() ?? 0; $stock->updated_by=auth()->id() ?? 0; $stock->save();
                BloodInv::where('hospital_id',$hospital['id'])->where('blood_id',$bloodTypes[$group])->where('status',1)->where('id','!=',$stock->id)->update(['status'=>9]);
            }
            foreach (array_diff(array_keys($old['blood']),array_keys($hospital['blood'])) as $group) BloodInv::where('hospital_id',$hospital['id'])->where('blood_id',$bloodTypes[$group])->update(['status'=>9]);
        }
        $oldRequests=collect($before['requests'])->keyBy('id');
        foreach ($after['requests'] as $request) {
            if ($oldRequests->get($request['id']) === $request) continue;
            CareRequest::updateOrCreate(['id'=>$request['id']],$request);
        }
        foreach ($after['events'] as $event) {
            if (isset($event['id'])) continue;
            CareEvent::create($event);
        }
    }

    private function event(&$s, $message, $requestId = null)
    {
        array_unshift($s['events'], ['at'=>time(),'message'=>$message,'request_id'=>$requestId]);

    }

    private function release(&$s, $requestId)
    {
        foreach ($s['hospitals'] as &$h) foreach ($h['beds'] as &$b) {
            if ($b['request_id'] === $requestId && $b['status'] !== 'Occupied') {
                $b['status'] = 'Available'; $b['request_id'] = null;
            }
        }
    }

    private function expire(&$s)
    {
        foreach ($s['requests'] as &$r) {
            if (in_array($r['status'], ['Pending','Reserved']) && $r['expires_at'] <= time()) {
                $this->release($s, $r['id']); $r['status'] = 'Expired';
                $this->event($s, $r['patient'].' reservation expired; bed released.',$r['id']);
            }
        }
    }

    private function priority($r) { return $r['verified'] ? ['Stable'=>1,'Urgent'=>2,'Critical'=>3][$r['severity']] : 1; }

    private function index($s, $id)
    {
        foreach ($s['requests'] as $i=>$r) if ($r['id'] === $id) return $i;
        throw new RuntimeException('Request not found.');
    }

    private function matching($s, $r)
    {
        $matches = [];
        foreach ($s['hospitals'] as $h) {
            $available = 0; $reclaimable = 0;
            foreach ($h['beds'] as $b) {
                if ($b['care'] !== $r['care']) continue;
                if ($b['status'] === 'Available') $available++;
                if ($b['status'] === 'Temporary' && $this->priority($r) === 3) {
                    $other = $s['requests'][$this->index($s,$b['request_id'])];
                    if ($this->priority($other) < 3) $reclaimable++;
                }
            }
            $reason = $h['status'] !== 'Open' ? 'Hospital offline' : ($available+$reclaimable === 0 ? 'No '.$r['care'].' bed available' : null);
            if (!empty($r['department_id']) && !in_array((int)$r['department_id'],array_column($h['departments'],'id'),true)) $reason='Required service is not available';
            $distance = null;
            if (isset($r['latitude'], $r['longitude'], $h['latitude'], $h['longitude'])) {
                $lat = deg2rad($h['latitude'] - $r['latitude']);
                $lng = deg2rad($h['longitude'] - $r['longitude']);
                $a = sin($lat/2) ** 2 + cos(deg2rad($r['latitude'])) * cos(deg2rad($h['latitude'])) * sin($lng/2) ** 2;
                $distance = round(6371 * 2 * atan2(sqrt($a), sqrt(max(0,1-$a))), 1);
            }
            $matches[] = ['preferred'=>isset($r['preferred_hospital_id']) && (string)$r['preferred_hospital_id']===$h['id'],'id'=>$h['id'],'name'=>$h['name'],'distance'=>$distance,'available'=>$available,'reclaimable'=>$reclaimable,'compatible'=>$reason === null,'reason'=>$reason];
        }
        usort($matches, function ($a,$b) { return ($b['compatible'] <=> $a['compatible']) ?: ($b['preferred'] <=> $a['preferred']) ?: (($a['distance'] ?? PHP_INT_MAX) <=> ($b['distance'] ?? PHP_INT_MAX)) ?: ($b['available'] <=> $a['available']); });
        return $matches;
    }

    public function state()
    {
        return $this->transaction(function (&$s) {
            $out = $s; unset($out['limits']);
            foreach ($out['requests'] as &$r) { $r['priority'] = $this->priority($r); $r['matches'] = $this->matching($s,$r); }
            usort($out['requests'], function ($a,$b) { return ($b['priority'] <=> $a['priority']) ?: ($a['created_at'] <=> $b['created_at']); });
            $out['now'] = time(); $out['ttl'] = $this->ttl;
            return $out;
        });
    }

    public function create($input, $client, $ownerId = null)
    {
        if (!empty($input['problem_type']) && empty($input['assistance_preference'])) $input['assistance_preference']=$input['problem_type']==='Internal'?'Ambulance':'Hospital';
        if (($input['assistance_preference'] ?? null)==='Ambulance') $input['preferred_hospital_id']=null;
        return $this->transaction(function (&$s) use ($input,$client,$ownerId) {
            $recent = array_values(array_filter($s['limits'][$client] ?? [], function ($t) { return $t > time()-60; }));
            if (count($recent) >= 5) throw new RuntimeException('Rate limit: at most 5 requests per minute. Wait before retrying.');
            foreach ($s['requests'] as $r) if ($r['owner_id']===$ownerId && strcasecmp($r['patient'],$input['patient']) === 0 && in_array($r['status'],['New','Pending','Reserved','Occupied'])) throw new RuntimeException('This patient already has an active request.');
            RateLimiter::hit('care-request-'.$ownerId,60);
            $r = $input + ['id'=>bin2hex(random_bytes(6)),'owner_id'=>$ownerId,'verified'=>false,'status'=>'New','created_at'=>time(),'expires_at'=>null,'hospital_id'=>null,'bed_id'=>null];
            $r['bed_type_id']=bedtype::where('name',$input['care'])->where('status',1)->value('id');
            $s['requests'][] = $r; $this->event($s, $r['patient'].' submitted a '.$r['care'].' request. Severity awaits verification.',$r['id']);
            return $r['id'];
        });
    }

    public function verify($id, $severity, $hospitalId = null)
    {
        return $this->transaction(function (&$s) use ($id,$severity,$hospitalId) {
            $i = $this->index($s,$id);
            if ($hospitalId && $s['requests'][$i]['status']!=='New' && $s['requests'][$i]['hospital_id']!==$hospitalId) throw new RuntimeException('Request belongs to another hospital.');
            if (!in_array($s['requests'][$i]['status'],['New','Pending'])) throw new RuntimeException('Only new or pending requests can be triaged.');
            $s['requests'][$i]['severity'] = $severity; $s['requests'][$i]['verified'] = true;
            $this->event($s, $s['requests'][$i]['patient'].' triaged by operator: '.$severity.'.',$id);
        });
    }

    public function requestBed($id, $hospitalId, $ownerId = null)
    {
        return $this->transaction(function (&$s) use ($id,$hospitalId,$ownerId) {
            $i = $this->index($s,$id); $r = $s['requests'][$i];
            if ($ownerId !== null && ($r['owner_id'] ?? null)!==$ownerId) throw new RuntimeException('Request belongs to another patient.');
            if (!in_array($r['status'],['New','Released','Expired','Rejected'])) throw new RuntimeException('Request already has a bed or is completed.');
            $hi = null;
            foreach ($s['hospitals'] as $key=>$h) if ($h['id'] === $hospitalId) $hi = $key;
            if ($hi === null || $s['hospitals'][$hi]['status'] !== 'Open') throw new RuntimeException('Hospital unavailable.');
            if (!empty($r['department_id']) && !in_array((int)$r['department_id'],array_column($s['hospitals'][$hi]['departments'],'id'),true)) throw new RuntimeException('This hospital does not provide the required service.');
            $bi = null;
            foreach ($s['hospitals'][$hi]['beds'] as $key=>$b) if ($b['care'] === $r['care'] && $b['status'] === 'Available') { $bi = $key; break; }
            if ($bi === null && $this->priority($r) === 3) {
                foreach ($s['hospitals'][$hi]['beds'] as $key=>$b) {
                    if ($b['care'] !== $r['care'] || $b['status'] !== 'Temporary') continue;
                    $oi = $this->index($s,$b['request_id']);
                    if ($this->priority($s['requests'][$oi]) >= 3) continue;
                    $s['requests'][$oi]['status'] = 'Released'; $s['requests'][$oi]['expires_at'] = null;
                    $this->event($s, 'Temporary hold for '.$s['requests'][$oi]['patient'].' released for verified Critical care. Patient can request another compatible hospital.',$s['requests'][$oi]['id']);
                    $bi = $key; break;
                }
            }
            if ($bi === null) throw new RuntimeException('No eligible bed remains. Refresh recommendations.');
            $bed = &$s['hospitals'][$hi]['beds'][$bi]; $bed['status'] = 'Temporary'; $bed['request_id'] = $id;
            $s['requests'][$i]['status'] = 'Pending'; $s['requests'][$i]['hospital_id'] = $hospitalId;
            $s['requests'][$i]['bed_id'] = $bed['id']; $s['requests'][$i]['expires_at'] = time()+$this->ttl;
            $this->event($s, $r['patient'].' requested '.$s['hospitals'][$hi]['name'].'. Temporary hold awaits hospital acceptance.',$id);
        });
    }

    public function action($id, $action, $hospitalId = null)
    {
        return $this->transaction(function (&$s) use ($id,$action,$hospitalId) {
            $i = $this->index($s,$id); $r = &$s['requests'][$i];
            if ($hospitalId && $r['hospital_id']!==$hospitalId) throw new RuntimeException('Request belongs to another hospital.');
            if ($action === 'reject') {
                if ($r['status'] !== 'Pending') throw new RuntimeException('Only pending requests can be rejected.');
                $this->release($s,$id); $r['status'] = 'Rejected'; $r['expires_at'] = null;
            } else {
                $expected = $action === 'accept' ? 'Pending' : 'Reserved';
                if ($r['status'] !== $expected) throw new RuntimeException('Invalid or expired reservation transition.');
                if (!$r['verified']) throw new RuntimeException('Verify severity before accepting.');
                foreach ($s['hospitals'] as &$h) foreach ($h['beds'] as &$b) if ($b['request_id'] === $id) $b['status'] = $action === 'accept' ? 'Reserved' : 'Occupied';
                $r['status'] = $action === 'accept' ? 'Reserved' : 'Occupied';
                $r['expires_at'] = $action === 'accept' ? time()+$this->ttl : null;
            }
            $this->event($s, $r['patient'].' â†’ '.$r['status'].'.',$id);
        });
    }

    public function manageHospital($hospitalId, $action, $input)
    {
        return $this->transaction(function (&$s) use ($hospitalId, $action, $input) {
            $index = array_search($hospitalId, array_column($s['hospitals'], 'id'), true);
            if ($index === false) throw new RuntimeException('Hospital not found.');
            $h =& $s['hospitals'][$index];
            if ($action === 'discharge') {
                $bedIndex = array_search($input['bed_id'], array_column($h['beds'], 'id'), true);
                if ($bedIndex === false) throw new RuntimeException('This bed does not belong to your hospital.');
                $bed =& $h['beds'][$bedIndex];
                if ($bed['status'] !== 'Occupied') throw new RuntimeException('Only occupied beds can be discharged.');
                $requestId = $bed['request_id'];
                if ($requestId) {
                    $rIndex = $this->index($s, $requestId);
                    if ($s['requests'][$rIndex]['hospital_id'] !== $hospitalId || $s['requests'][$rIndex]['status'] !== 'Occupied') throw new RuntimeException('Patient admission does not match this hospital bed.');
                    $s['requests'][$rIndex]['status'] = 'Discharged'; $s['requests'][$rIndex]['expires_at'] = null;
                }
                $bed['status'] = 'Available'; $bed['request_id'] = null;
                $this->event($s, $h['name'].' discharged bed '.$bed['id'].'.', $requestId);
            } elseif ($action === 'settings') {
                if (!in_array($input['status'], ['Open','Offline'], true)) throw new RuntimeException('Invalid hospital status.');
                $h['status'] = $input['status']; $h['contact'] = $input['contact'] ?? ''; $h['address'] = $input['address'] ?? '';
                $h['latitude']=$input['latitude'] ?? null; $h['longitude']=$input['longitude'] ?? null;
                $this->event($s, $h['name'].' updated its hospital settings.');
            } else throw new RuntimeException('Unknown hospital management action.');
            $s['events'][0]['hospital_id'] = $hospitalId;
        });
    }

    public function inventoryCrud($hospitalId, $type, $operation, $record, $input)
    {
        return $this->transaction(function (&$s) use ($hospitalId, $type, $operation, $record, $input) {
            $index = array_search($hospitalId, array_column($s['hospitals'], 'id'), true);
            if ($index === false) throw new RuntimeException('Hospital not found.');
            if (!in_array($operation, ['create','update','delete'], true)) throw new RuntimeException('Invalid operation.');
            $h =& $s['hospitals'][$index];
            if ($type === 'inventory') {
                if ($operation !== 'delete' && (!in_array($input['care'], $this->careTypes(), true) || empty(trim($input['name'])))) throw new RuntimeException('Choose an active bed type and enter a bed name.');
                $bedIndex = array_search($record, array_column($h['beds'], 'id'), true);
                if ($operation === 'create') {
                    if (count(array_filter($h['beds'], fn($b)=>$b['care']===$input['care'])) >= 200) throw new RuntimeException('Maximum capacity for this care type reached.');
                    $h['beds'][] = ['id'=>$hospitalId.'-'.bin2hex(random_bytes(6)), 'name'=>$input['name'], 'care'=>$input['care'], 'status'=>'Available', 'request_id'=>null];
                } else {
                    if ($bedIndex === false) throw new RuntimeException('Record not found in your hospital.');
                    if ($h['beds'][$bedIndex]['status'] !== 'Available') throw new RuntimeException('Only available beds can be edited or deleted. Discharge an occupied patient first.');
                    if ($operation === 'delete') { unset($h['beds'][$bedIndex]); $h['beds'] = array_values($h['beds']); }
                    else {
                        if ($h['beds'][$bedIndex]['care'] !== $input['care'] && count(array_filter($h['beds'], fn($b)=>$b['care']===$input['care'])) >= 200) throw new RuntimeException('Maximum capacity for this care type reached.');
                        $h['beds'][$bedIndex]['care']=$input['care']; $h['beds'][$bedIndex]['name']=$input['name'];
                    }
                }
            } elseif ($type === 'blood') {
                if ($operation !== 'create' && !array_key_exists($record, $h['blood'] ?? [])) throw new RuntimeException('Record not found in your hospital.');
                if ($operation === 'delete') unset($h['blood'][$record]);
                else {
                    $group=$input['blood_type'];
                    if (!in_array($group, $this->bloodTypes(), true) || (int)$input['units'] < 0 || (int)$input['units'] > 10000) throw new RuntimeException('Invalid blood stock.');
                    if (($operation==='create' || $group!==$record) && array_key_exists($group,$h['blood'] ?? [])) throw new RuntimeException('This blood group already exists. Edit its existing record.');
                    if ($operation==='update' && $group!==$record) unset($h['blood'][$record]);
                    $h['blood'][$group]=(int)$input['units'];
                }
            } else throw new RuntimeException('Invalid inventory type.');
            $this->event($s, $h['name'].' '.$operation.'d a '.($type==='inventory'?'bed':'blood inventory').' record.');
            $s['events'][0]['hospital_id']=$hospitalId;
        });
    }

    public function changeRequest($id,$ownerId,$action,$input)
    {
        return $this->transaction(function (&$state) use ($id,$ownerId,$action,$input) {
            $index=$this->index($state,$id);
            $record =& $state['requests'][$index];
            if ($record['owner_id']!==$ownerId) throw new RuntimeException('This request belongs to another patient.');
            if (!in_array($record['status'],['New','Released','Expired','Rejected'])) throw new RuntimeException('Allocated or completed requests cannot be edited or cancelled.');
            if ($action==='cancel') $record['status']='Cancelled';
            else {
                foreach (['patient','location','care','severity','latitude','longitude'] as $field) $record[$field]=$input[$field] ?? null;
                foreach (['problem_type','assistance_preference','description','preferred_hospital_id','location_captured_at','department_id'] as $field) if (array_key_exists($field,$input)) $record[$field]=$input[$field];
                if (($record['assistance_preference'] ?? null)==='Ambulance') $record['preferred_hospital_id']=null;
                $record['bed_type_id']=bedtype::where('name',$input['care'])->where('status',1)->value('id');
                $record['verified']=false;
            }
            $this->event($state,$record['patient'].' request '.$action.'d.',$id);
        });
    }

    public function updatePatientLocation($id,$ownerId,$location)
    {
        return $this->transaction(function(&$state) use($id,$ownerId,$location) {
            $index=$this->index($state,$id);
            if($state['requests'][$index]['owner_id']!==$ownerId) throw new RuntimeException('This request belongs to another patient.');
            $state['requests'][$index]['latitude']=$location['lat'];
            $state['requests'][$index]['longitude']=$location['lng'];
            $state['requests'][$index]['location_captured_at']=$location['captured_at'];
        });
    }


}
