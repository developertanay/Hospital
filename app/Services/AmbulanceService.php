<?php
namespace App\Services;
use RuntimeException;

/** SQL dispatch. Explicit file paths are supported only for isolated legacy tests. */
class AmbulanceService {
    private $path; private $clock;
    public function __construct($path=null,$clock=null) { $this->path=$path; $this->clock=$clock ?: fn()=>time(); }
    private function now() { return ($this->clock)(); }
    private function seed() {
        $drivers=[];
        foreach ([['D1','Aman',28.6139,77.2090],['D2','Riya',28.6239,77.2190],['D3','Kabir',28.6439,77.2390]] as $i=>$d) $drivers[]=['id'=>$d[0],'name'=>$d[1].' · Demo ambulance','phone'=>'+91900000010'.($i+1),'vehicle'=>'DEMO AMB 0'.($i+1),'lat'=>$d[2],'lng'=>$d[3],'on_duty'=>false,'seen_at'=>0,'location_at'=>0,'location_mode'=>'demo'];
        return ['profiles'=>[],'calls'=>[],'drivers'=>$drivers];
    }
    private function transaction($fn) {
        if ($this->path===null) return app(AmbulanceDispatchStore::class)->transaction(function (&$state) use ($fn) {
            $this->dispatch($state);
            return $fn($state);
        });
        $f=fopen($this->path.'.lock','c'); if (!$f || !flock($f,LOCK_EX)) throw new RuntimeException('Dispatch store unavailable.');
        try {
            $raw=file_exists($this->path)?file_get_contents($this->path):''; $s=$raw?json_decode($raw,true):$this->seed();
            if (!is_array($s)) throw new RuntimeException('Dispatch store unreadable.');
            $this->dispatch($s);
            try { $result=$fn($s); } finally {
                $encoded=json_encode($s,JSON_PRETTY_PRINT|JSON_UNESCAPED_SLASHES|JSON_THROW_ON_ERROR);
                $temp=$this->path.'.tmp';
                if (file_put_contents($temp,$encoded)===false) throw new RuntimeException('Unable to save dispatch store.');
                // Windows scanners can briefly hold a newly written/read JSON file.
                // Retry replacement under the same lock; never delete the last good file.
                $saved=false;
                for ($attempt=0;$attempt<10;$attempt++) {
                    if (@rename($temp,$this->path)) { $saved=true; break; }
                    usleep(50000);
                }
                if (!$saved) throw new RuntimeException('Unable to replace dispatch store. Retry shortly.');
            }
            return $result;
        } finally { flock($f,LOCK_UN); fclose($f); }
    }
    public static function distance($a,$b,$c,$d) {
        $v=sin(deg2rad($c-$a)/2)**2+cos(deg2rad($a))*cos(deg2rad($c))*sin(deg2rad($d-$b)/2)**2;
        return 6371*2*atan2(sqrt($v),sqrt(max(0,1-$v)));
    }
    private function active($r) { return !in_array($r['status'],['Completed','Cancelled','Expired'],true); }
    private function driver($s,$id) { foreach ($s['drivers'] as $d) if ($d['id']===$id) return $d; throw new RuntimeException('Driver not found.'); }
    private function index($s,$id) { foreach ($s['calls'] as $i=>$r) if ($r['id']===$id) return $i; throw new RuntimeException('SOS not found.'); }
    private function event(&$r,$text) { $r['events'][]=['time'=>$this->now(),'text'=>$text]; }
    private function dispatch(&$s) {
        $now=$this->now(); $busy=[];
        foreach ($s['calls'] as &$r) {
            $r['offers']=$r['offers']??[];
            if (!$this->active($r)) continue;
            if ($r['status']==='Searching' && $r['search_until']<=$now) {
                $r['status']='Expired'; $r['offers']=[]; $r['offer_driver']=null;
                $this->event($r,'No driver confirmed within 10 minutes.'); continue;
            }
            if ($r['driver_id']) $busy[$r['driver_id']]=true;
            if (!empty($r['requested_driver_id'])) $busy[$r['requested_driver_id']]=$r['id'];
        } unset($r);
        foreach ($s['calls'] as &$r) {
            if ($r['status']!=='Searching') { $r['offers']=[]; continue; }
            // Broadcast to all eligible drivers. An offer does not make a driver busy.
            // Only an accepted trip claims a driver, under the shared exclusive lock.
            $offers=[];
            foreach ($s['drivers'] as $d) {
                if (!$d['on_duty'] || $d['seen_at']<=$now-90 || (isset($busy[$d['id']]) && $busy[$d['id']]!==$r['id']) || in_array($d['id'],$r['skipped'],true)) continue;
                if (!empty($r['requested_driver_id']) && $r['requested_driver_id']!==$d['id']) continue;
                $simulated=($d['location_mode']==='demo' && ($d['follow_pickup']??false));
                if (!$simulated && $d['location_mode']!==($r['location']['mode']==='manual'?'gps':$r['location']['mode'])) continue;
                if ($d['location_mode']==='gps' && ($d['location_at']??0)<=$now-120) continue;
                $position=$this->position($d,$r);
                $distance=self::distance($r['location']['lat'],$r['location']['lng'],$position['lat'],$position['lng']);
                if ($distance<=25) $offers[]=['driver_id'=>$d['id'],'distance_km'=>round($distance,2),'eta_minutes'=>$this->eta($distance),'until'=>$r['search_until'],'simulated'=>$simulated];
            }
            usort($offers,fn($a,$b)=>($a['eta_minutes']<=>$b['eta_minutes']) ?: ($a['distance_km']<=>$b['distance_km']) ?: strcmp($a['driver_id'],$b['driver_id']));
            if (!$r['offers'] && $offers) $this->event($r,!empty($r['requested_driver_id'])?'Pickup sent to the selected ambulance driver.':'Pickup broadcast to nearby available drivers. First confirmed acceptance wins.');
            $r['offers']=$offers;
            // Compatibility fields for older clients; authorization uses the whole offer list.
            $r['offer_driver']=$offers[0]['driver_id']??null;
            $r['distance_km']=$offers[0]['distance_km']??null;
            $r['offer_until']=$offers?$r['search_until']:null;
        } unset($r);
    }
    private function eta($distance) { return max(1,(int)ceil($distance*1.3/25*60)); }
    private function position($d,$r) {
        if ($d['location_mode']==='demo' && ($d['follow_pickup']??false)) {
            $offset=['D1'=>0.006,'D2'=>0.012,'D3'=>0.02][$d['id']]??0.01;
            $d['lat']=max(-89.9,min(89.9,$r['location']['lat']+$offset));
            $d['lng']=fmod($r['location']['lng']+$offset+540,360)-180;
        }
        if ($d['location_mode']==='demo' && $r['driver_id']===$d['id'] && isset($r['accepted_at'])) {
            $start=$r['driver_start'];
            $duration=max(20,($r['pickup_eta_minutes']??1)*60);
            $progress=min(1,max(0,($this->now()-$r['accepted_at'])/$duration));
            if (in_array($r['status'],['At pickup','Picked up','First aid','Transporting','Completed'],true)) $progress=1;
            $d['lat']=$start['lat']+($r['location']['lat']-$start['lat'])*$progress;
            $d['lng']=$start['lng']+($r['location']['lng']-$start['lng'])*$progress;
        }
        return $d;
    }
    private function completeProfile($profile) {
        foreach (['name','mobile','age','emergency_name','emergency_phone'] as $key) if (empty($profile[$key])) return false;
        return preg_match('/^\+?[0-9]{8,15}$/',$profile['mobile']) && preg_match('/^\+?[0-9]{8,15}$/',$profile['emergency_phone']) && $profile['age']>=1 && $profile['age']<=120;
    }
    public function profile($p,$input) { return $this->transaction(function (&$s) use ($p,$input) { $s['profiles'][(string)$p['id']]=$input; return ['message'=>'Emergency details saved.']; }); }
    public function state($p) {
        return $this->transaction(function (&$s) use ($p) {
            $calls=[];
            foreach (array_reverse($s['calls']) as $r) {
                $own=$r['owner_id']===$p['id']; $assigned=$p['role']==='driver' && ($r['driver_id']===($p['driver']??null) || in_array($p['driver']??null,array_column($r['offers'],'driver_id'),true));
                if ($p['role']!=='admin' && !$own && !$assigned) continue;
                unset($r['skipped'],$r['retry_at']);
                $r['nearby_driver_count']=count($r['offers']);
                $r['eta_kind']='estimate';
                if ($p['role']==='driver' && !$r['driver_id']) {
                    foreach ($r['offers'] as $offer) if ($offer['driver_id']===$p['driver']) {
                        $r['distance_km']=$offer['distance_km']; $r['eta_minutes']=$offer['eta_minutes']; $r['offer_until']=$offer['until'];
                    }
                }
                if ($r['driver_id']) {
                    $r['driver']=$this->position($this->driver($s,$r['driver_id']),$r);
                    $r['distance_km']=round(self::distance($r['location']['lat'],$r['location']['lng'],$r['driver']['lat'],$r['driver']['lng']),2);
                    $r['eta_minutes']=$this->eta($r['distance_km']);
                    $r['tracking_simulated']=$r['driver']['location_mode']==='demo';
                    $r['driver_location_stale']=$r['driver']['location_mode']==='gps' && ($r['driver']['location_at']??0)<=$this->now()-120;
                }
                elseif (!empty($r['requested_driver_id']) && $own) {
                    $r['driver']=$this->driver($s,$r['requested_driver_id']);
                    $r['distance_km']=round(self::distance($r['location']['lat'],$r['location']['lng'],$r['driver']['lat'],$r['driver']['lng']),2);
                    $r['eta_minutes']=$this->eta($r['distance_km']);
                    $r['driver_location_stale']=($r['driver']['location_at']??0)<=$this->now()-120;
                    $r['driver_available']=$r['driver']['on_duty'] && $r['driver']['seen_at']>$this->now()-90;
                }
                unset($r['offers']);
                if($p['role']==='driver')unset($r['coverage']);
                if ($p['role']==='driver' && !$r['driver_id'] && empty($r['requested_driver_id'])) { $r['patient']=['name'=>'Emergency pickup','age'=>$r['patient']['age']];unset($r['reporter']); }
                $calls[]=$r;
            }
            $drivers=$p['role']==='admin'?$s['drivers']:($p['role']==='driver'?[$this->driver($s,$p['driver'])]:[]);
            return ['patient_profile'=>$s['profiles'][(string)$p['id']]??null,'calls'=>$calls,'drivers'=>$drivers,'profile_complete'=>$this->completeProfile($s['profiles'][(string)$p['id']]??[]),'now'=>$this->now()];
        });
    }
    public function duty($p,$input) {
        return $this->transaction(function (&$s) use ($p,$input) {
            foreach ($s['drivers'] as &$d) if ($d['id']===$p['driver']) { $d['on_duty']=$input['on_duty']; $d['seen_at']=$this->now(); if (isset($input['lat'])) { $d['lat']=$input['lat']; $d['lng']=$input['lng']; $d['location_mode']=$input['mode']; $d['location_at']=$this->now(); $d['follow_pickup']=($input['follow_pickup']??false) && $input['mode']==='demo'; } break; } unset($d);
            $this->dispatch($s); return ['message'=>'Driver availability updated.'];
        });
    }
    public function contact($p,$phone) {
        return $this->transaction(function (&$s) use ($p,$phone) {
            foreach ($s['drivers'] as &$d) if ($d['id']===$p['driver']) $d['phone']=$phone;
            unset($d); return ['message'=>'Driver contact saved.'];
        });
    }
    public function heartbeat($p) {
        return $this->transaction(function (&$s) use ($p) { foreach ($s['drivers'] as &$d) if ($d['id']===$p['driver']) $d['seen_at']=$this->now(); unset($d); $this->dispatch($s); return ['message'=>'Online.']; });
    }
    public function create($p,$location,$recipient=[]) {
        return $this->transaction(function (&$s) use ($p,$location,$recipient) {
            $profile=$s['profiles'][(string)$p['id']]??null; if (($recipient['reported_for']??'Self')==='Self' && (!$profile || !$this->completeProfile($profile))) throw new RuntimeException('Save your emergency details before using SOS.');
            $context=$recipient?app(SosRecipientService::class)->resolve($p,$recipient,$profile):[];
            $profile=$context['patient']??$profile;
            if (abs($this->now()-$location['captured_at'])>120) throw new RuntimeException('Location is stale. Get a fresh GPS fix and retry.');
            foreach ($s['calls'] as $r) if ($r['owner_id']===$p['id'] && $this->active($r)) return ['id'=>$r['id'],'message'=>'Your existing SOS is still active.'];
            $recent=array_filter($s['calls'],fn($r)=>$r['owner_id']===$p['id'] && $r['created_at']>$this->now()-600);
            if (count($recent)>=3) throw new RuntimeException('Too many SOS requests. Please wait before retrying.');
            $id=bin2hex(random_bytes(6));
            $r=['id'=>$id,'owner_id'=>$p['id'],'patient'=>$profile,'location'=>$location,'status'=>'Searching','created_at'=>$this->now(),'search_until'=>$this->now()+600,'offer_driver'=>null,'offer_until'=>null,'driver_id'=>null,'distance_km'=>null,'skipped'=>[],'retry_at'=>$this->now()+60,'bed_request_id'=>null,'hospital_id'=>null,'offers'=>[],'events'=>[]];
            $r=array_merge($r,$context);
            $this->event($r,'SOS received. Searching for an on-duty ambulance within 25 km.'); $s['calls'][]=$r; $this->dispatch($s);
            return ['id'=>$id,'message'=>'SOS sent. Waiting for driver confirmation.'];
        });
    }
    private function idleOptions($s,$location) {
        $busy=[]; $now=$this->now();
        foreach ($s['calls'] as $call) if ($this->active($call)) {
            if ($call['driver_id']) $busy[$call['driver_id']]=true;
            if (!empty($call['requested_driver_id'])) $busy[$call['requested_driver_id']]=true;
        }
        $options=[];
        foreach ($s['drivers'] as $driver) {
            if (!$driver['on_duty'] || $driver['seen_at']<=$now-90 || isset($busy[$driver['id']]) || $driver['location_mode']!=='gps' || ($driver['location_at']??0)<=$now-120) continue;
            $distance=self::distance($location['lat'],$location['lng'],$driver['lat'],$driver['lng']);
            if ($distance>25) continue;
            $options[]=['driver_id'=>$driver['id'],'vehicle'=>$driver['vehicle'],'category'=>$driver['category']??'',
                'doctor_name'=>$driver['doctor_name']??'','distance_km'=>round($distance,2),'eta_minutes'=>$this->eta($distance)];
        }
        usort($options,fn($a,$b)=>($a['distance_km']<=>$b['distance_km']));
        return $options;
    }
    public function nearby($location) {
        return $this->transaction(function(&$s)use($location){
            $options=$this->idleOptions($s,$location);
            $online=array_filter($s['drivers'],fn($d)=>$d['on_duty'] && $d['seen_at']>$this->now()-90);
            $gps=array_filter($online,fn($d)=>$d['location_mode']==='gps' && ($d['location_at']??0)>$this->now()-120);
            return ['ambulances'=>$options,'now'=>$this->now(),'availability_message'=>$options?null:(!$online?'No drivers are on duty right now.':(!$gps?'On-duty drivers have not shared fresh live GPS. Use my GPS must be enabled in the driver app; demo vehicles cannot be booked here.':'No idle ambulance with fresh GPS is available within 25 km.'))];
        });
    }
    public function book($p,$request,$driverId,$location) {
        return $this->transaction(function(&$s) use($p,$request,$driverId,$location) {
            if ($request->owner_id!==$p['id']) throw new RuntimeException('This care request belongs to another patient.');
            if (in_array($request->status,['Cancelled','Occupied','Discharged'],true)) throw new RuntimeException('This care request is no longer bookable.');
            if (abs($this->now()-$location['captured_at'])>120 || $location['mode']!=='gps') throw new RuntimeException('Get a fresh GPS location before booking.');
            foreach($s['calls'] as $call) if($call['owner_id']===$p['id'] && $this->active($call)) throw new RuntimeException('You already have an active ambulance booking. Track or cancel it first.');
            $profile=$s['profiles'][(string)$p['id']]??[];
            if(!$this->completeProfile($profile)) throw new RuntimeException('Save emergency contact details before booking an ambulance.');
            $reporter=['name'=>$profile['name'],'phone'=>$profile['mobile'],'with_patient'=>$request->reporter_with_patient===null?true:(bool)$request->reporter_with_patient];
            $coverageSummary=null;
            if($request->coverage_id){$coverage=app(PatientCoverageService::class)->select($p['id'],$request->coverage_id,$request->dependent_id);$coverageSummary=$coverage->only(['id','organisation_id','tpa_id','policy_number','member_id','valid_from','valid_until']);$coverageSummary['organisation']=$coverage->organisation->name;}
            $profile['name']=$request->patient;
            if($request->dependent_id){$dependent=\App\Models\PatientDependent::where('owner_id',$p['id'])->findOrFail($request->dependent_id);$profile['age']=$dependent->age;$profile['mobile']=$dependent->mobile??'';$profile['emergency_name']=$reporter['name'];$profile['emergency_phone']=$reporter['phone'];}
            if(!in_array($driverId,array_column($this->idleOptions($s,$location),'driver_id'),true)) throw new RuntimeException('This ambulance is no longer idle or nearby. Refresh available ambulances.');
            $id=bin2hex(random_bytes(6));
            $call=['id'=>$id,'owner_id'=>$p['id'],'patient'=>$profile,'location'=>$location,'status'=>'Searching',
                'created_at'=>$this->now(),'search_until'=>$this->now()+600,'offer_driver'=>null,'offer_until'=>null,'driver_id'=>null,
                'requested_driver_id'=>$driverId,'care_request_id'=>$request->id,'description'=>$request->description,
                'problem_type'=>$request->problem_type,'department_id'=>$request->department_id,
                'distance_km'=>null,'skipped'=>[],'retry_at'=>$this->now()+60,'bed_request_id'=>null,'hospital_id'=>$request->hospital_id?(string)$request->hospital_id:null,'offers'=>[],'events'=>[]];
            $this->event($call,'Selected ambulance booked. Waiting for its driver to confirm pickup.');
            $call['reporter']=$reporter;$call['reported_for']=$request->reported_for??($request->dependent_id?'Family':'Self');$call['dependent_id']=$request->dependent_id;$call['coverage_id']=$request->coverage_id;$call['coverage']=$coverageSummary;
            app(EmergencyBedService::class)->updatePatientLocation($request->id,$p['id'],$location);
            $s['calls'][]=$call; $this->dispatch($s);
            return ['id'=>$id,'message'=>'Ambulance booked. Driver confirmation pending.'];
        });
    }
    public function location($p,$id,$location) {
        return $this->transaction(function (&$s) use ($p,$id,$location) {
            $i=$this->index($s,$id); $r=&$s['calls'][$i];
            if ($r['owner_id']!==$p['id'] || !in_array($r['status'],['Searching','Accepted','At pickup'],true)) throw new RuntimeException('Pickup location is locked after pickup.');
            if (abs($this->now()-$location['captured_at'])>120 || $location['mode']!==$r['location']['mode']) throw new RuntimeException('Invalid pickup location update.');
            if ($r['status']==='Searching' && self::distance($r['location']['lat'],$r['location']['lng'],$location['lat'],$location['lng'])>0.1) { $r['offer_driver']=null; $r['skipped']=[]; }
            $r['location']=$location;
            if(!empty($r['care_request_id'])) app(EmergencyBedService::class)->updatePatientLocation($r['care_request_id'],$p['id'],$location);
            unset($r); $this->dispatch($s); return ['message'=>'Pickup location updated.'];
        });
    }
    public function action($p,$id,$action,$input=[],EmergencyBedService $beds=null) {
        return $this->transaction(function (&$s) use ($p,$id,$action,$input,$beds) {
            $i=$this->index($s,$id); $r=&$s['calls'][$i];
            if ($action==='cancel') {
                if ($r['owner_id']!==$p['id'] || !in_array($r['status'],['Searching','Accepted','At pickup'],true)) throw new RuntimeException('This SOS cannot be cancelled by this account.');
                $r['status']='Cancelled'; $r['offer_driver']=null; $this->event($r,'Patient cancelled SOS.');
            } elseif (in_array($action,['accept','decline'],true)) {
                if ($p['role']!=='driver' || $r['status']!=='Searching' || !in_array($p['driver'],array_column($r['offers'],'driver_id'),true) || $r['offer_until']<=$this->now()) throw new RuntimeException('Offer expired or belongs to another driver.');
                if ($action==='decline') { $r['skipped'][]=$p['driver']; $r['offer_driver']=null; if(!empty($r['requested_driver_id'])) { $r['status']='Cancelled'; $this->event($r,'Selected driver declined. Choose another available ambulance.'); } else $this->event($r,'A driver declined. Other nearby drivers can still accept.'); }
                else { $d=$this->position($this->driver($s,$p['driver']),$r); $r['driver_start']=['lat'=>$d['lat'],'lng'=>$d['lng']]; $r['accepted_at']=$this->now(); $r['pickup_eta_minutes']=$this->eta(self::distance($r['location']['lat'],$r['location']['lng'],$d['lat'],$d['lng'])); $r['driver_id']=$p['driver']; $r['offer_driver']=null; $r['status']='Accepted'; $this->event($r,'Driver accepted. Contact details shared with assigned driver.'); }
            } else {
                if ($p['role']!=='driver' || $r['driver_id']!==$p['driver']) throw new RuntimeException('Only the assigned driver can update this trip.');
                $next=['arrive'=>['Accepted','At pickup'],'pickup'=>['At pickup','Picked up'],'first_aid'=>['Picked up','First aid'],'complete'=>['First aid','Completed']];
                if ($action==='transport') {
                    if (!in_array($r['status'],['Picked up','First aid','Transporting'],true)) throw new RuntimeException('Pick up the patient before hospital transfer.');
                    if (!$beds) throw new RuntimeException('Hospital service unavailable.');
                    $care=$input['care']; $hospital=$input['hospital_id'];
                    if (!$r['bed_request_id'] && !empty($r['care_request_id'])) $r['bed_request_id']=$r['care_request_id'];
                    if (!$r['bed_request_id']) {
                        $careInput=['patient'=>$r['patient']['name'].' [SOS '.$r['id'].']','location'=>$r['pickup_address']??('SOS pickup '.$r['location']['lat'].', '.$r['location']['lng']),'latitude'=>$r['location']['lat'],'longitude'=>$r['location']['lng'],'severity'=>'Critical','care'=>$care];
                        if(!empty($r['reported_for'])) {
                            $dependent=$r['dependent_id']??null;$coverage=$r['coverage_id']??null;
                            if($dependent && !\App\Models\PatientDependent::where('owner_id',$r['owner_id'])->where('status',1)->where('id',$dependent)->exists()){$dependent=null;$coverage=null;}
                            if($coverage && !\App\Models\PatientCoverage::where('owner_id',$r['owner_id'])->where('status',1)->where('id',$coverage)->exists())$coverage=null;
                            $careInput+=['coverage_id'=>$coverage,'dependent_id'=>$dependent,'reported_for'=>$r['reported_for'],'reporter_name'=>$r['reporter']['name']??null,'reporter_phone'=>$r['reporter']['phone']??null,'reporter_with_patient'=>$r['reporter']['with_patient']??true,'description'=>$r['description']??null];
                        }
                        $r['bed_request_id']=$beds->create($careInput,'sos:'.$r['owner_id'],$r['owner_id']);
                    }
                    $record=collect($beds->state()['requests'])->firstWhere('id',$r['bed_request_id']);
                    if ($record['care']!==$care) throw new RuntimeException('Use the original care type for this linked hospital request.');
                    if (in_array($record['status'],['New','Released','Expired','Rejected'],true)) $beds->requestBed($r['bed_request_id'],$hospital,$r['owner_id']);
                    elseif ($record['hospital_id']!==$hospital) throw new RuntimeException('Patient already has an allocation at another hospital.');
                    $r['hospital_id']=$hospital; $r['status']='Transporting'; $this->event($r,'Hospital '.$hospital.' bed requested; verification and acceptance required.');
                } elseif ($action==='handoff') {
                    if ($r['status']!=='Transporting' || !$r['bed_request_id']) throw new RuntimeException('No hospital transfer active.');
                    $record=collect($beds->state()['requests'])->firstWhere('id',$r['bed_request_id']);
                    if ($record['status']!=='Occupied') throw new RuntimeException('Hospital must confirm patient arrival before handoff finishes.');
                    $r['status']='Completed'; $this->event($r,'Hospital confirmed arrival. Handoff completed.');
                } else {
                    if (!isset($next[$action]) || $r['status']!==$next[$action][0] || ($action==='complete' && $r['bed_request_id'])) throw new RuntimeException('Invalid trip transition.');
                    $r['status']=$next[$action][1]; $this->event($r,$action==='first_aid'?'Driver recorded first aid provided.':'Trip status: '.$r['status'].'.');
                }
            }
            unset($r); $this->dispatch($s); return ['message'=>'SOS updated.'];
        });
    }
}
