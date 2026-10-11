<?php
namespace App\Services;
use RuntimeException;

/** Demo dispatch. Offer, acceptance and availability share one exclusive lock. */
class AmbulanceService {
    private $path; private $clock;
    public function __construct($path=null,$clock=null) { $this->path=$path ?: storage_path('app/ambulance-demo.json'); $this->clock=$clock ?: fn()=>time(); }
    private function now() { return ($this->clock)(); }
    private function seed() {
        $drivers=[];
        foreach ([['D1','Aman',28.6139,77.2090],['D2','Riya',28.6239,77.2190],['D3','Kabir',28.6439,77.2390]] as $i=>$d) $drivers[]=['id'=>$d[0],'name'=>$d[1].' · Demo ambulance','phone'=>'+91900000010'.($i+1),'vehicle'=>'DEMO AMB 0'.($i+1),'lat'=>$d[2],'lng'=>$d[3],'on_duty'=>false,'seen_at'=>0,'location_at'=>0,'location_mode'=>'demo'];
        return ['profiles'=>[],'calls'=>[],'drivers'=>$drivers];
    }
    private function transaction($fn) {
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
            if (!$this->active($r)) continue;
            if ($r['status']==='Searching' && $r['search_until']<=$now) { $r['status']='Expired'; $r['offer_driver']=null; $this->event($r,'No driver confirmed within 10 minutes.'); continue; }
            if ($r['driver_id']) $busy[$r['driver_id']]=true;
        } unset($r);
        foreach ($s['calls'] as &$r) {
            if ($r['status']!=='Searching') continue;
            if ($r['offer_driver']) {
                $d=$this->driver($s,$r['offer_driver']);
                if ($r['offer_until']>$now && $d['on_duty'] && $d['seen_at']>$now-90 && ($d['location_mode']==='demo' || ($d['location_at']??0)>$now-120) && !isset($busy[$d['id']])) { $busy[$d['id']]=true; continue; }
                $r['skipped'][]=$r['offer_driver']; $r['offer_driver']=null;
            }
            if ($r['retry_at']<=$now) { $r['skipped']=[]; $r['retry_at']=$now+60; }
            $candidates=[];
            foreach ($s['drivers'] as $d) {
                if (!$d['on_duty'] || $d['seen_at']<=$now-90 || isset($busy[$d['id']]) || in_array($d['id'],$r['skipped'],true) || $d['location_mode']!==$r['location']['mode']) continue;
                if ($d['location_mode']==='gps' && ($d['location_at']??0)<=$now-120) continue;
                $distance=self::distance($r['location']['lat'],$r['location']['lng'],$d['lat'],$d['lng']);
                if ($distance<=25) $candidates[]=['id'=>$d['id'],'distance'=>$distance];
            }
            usort($candidates,fn($a,$b)=>($a['distance']<=>$b['distance']) ?: strcmp($a['id'],$b['id']));
            if ($candidates) { $r['offer_driver']=$candidates[0]['id']; $r['distance_km']=round($candidates[0]['distance'],2); $r['offer_until']=$now+30; $busy[$r['offer_driver']]=true; $this->event($r,'Pickup offered to nearest available demo driver.'); }
        } unset($r);
    }
    public function profile($p,$input) { return $this->transaction(function (&$s) use ($p,$input) { $s['profiles'][(string)$p['id']]=$input; return ['message'=>'Emergency details saved.']; }); }
    public function state($p) {
        return $this->transaction(function (&$s) use ($p) {
            $calls=[];
            foreach (array_reverse($s['calls']) as $r) {
                $own=$r['owner_id']===$p['id']; $assigned=$p['role']==='driver' && ($r['driver_id']===($p['driver']??null) || $r['offer_driver']===($p['driver']??null));
                if ($p['role']!=='admin' && !$own && !$assigned) continue;
                unset($r['skipped'],$r['retry_at']);
                if ($r['driver_id']) $r['driver']=$this->driver($s,$r['driver_id']);
                if ($p['role']==='driver' && !$r['driver_id']) $r['patient']=['name'=>'Emergency pickup','age'=>$r['patient']['age']];
                $calls[]=$r;
            }
            $drivers=$p['role']==='admin'?$s['drivers']:($p['role']==='driver'?[$this->driver($s,$p['driver'])]:[]);
            return ['patient_profile'=>$s['profiles'][(string)$p['id']]??null,'calls'=>$calls,'drivers'=>$drivers,'now'=>$this->now()];
        });
    }
    public function duty($p,$input) {
        return $this->transaction(function (&$s) use ($p,$input) {
            foreach ($s['drivers'] as &$d) if ($d['id']===$p['driver']) { $d['on_duty']=$input['on_duty']; $d['seen_at']=$this->now(); if (isset($input['lat'])) { $d['lat']=$input['lat']; $d['lng']=$input['lng']; $d['location_mode']=$input['mode']; $d['location_at']=$this->now(); } break; } unset($d);
            $this->dispatch($s); return ['message'=>'Driver availability updated.'];
        });
    }
    public function heartbeat($p) {
        return $this->transaction(function (&$s) use ($p) { foreach ($s['drivers'] as &$d) if ($d['id']===$p['driver']) $d['seen_at']=$this->now(); unset($d); $this->dispatch($s); return ['message'=>'Online.']; });
    }
    public function create($p,$location) {
        return $this->transaction(function (&$s) use ($p,$location) {
            $profile=$s['profiles'][(string)$p['id']]??null; if (!$profile) throw new RuntimeException('Save your emergency details before using SOS.');
            if (abs($this->now()-$location['captured_at'])>120) throw new RuntimeException('Location is stale. Get a fresh GPS fix and retry.');
            foreach ($s['calls'] as $r) if ($r['owner_id']===$p['id'] && $this->active($r)) return ['id'=>$r['id'],'message'=>'Your existing SOS is still active.'];
            $recent=array_filter($s['calls'],fn($r)=>$r['owner_id']===$p['id'] && $r['created_at']>$this->now()-600);
            if (count($recent)>=3) throw new RuntimeException('Too many SOS requests. Please wait before retrying.');
            $id=bin2hex(random_bytes(6));
            $r=['id'=>$id,'owner_id'=>$p['id'],'patient'=>$profile,'location'=>$location,'status'=>'Searching','created_at'=>$this->now(),'search_until'=>$this->now()+600,'offer_driver'=>null,'offer_until'=>null,'driver_id'=>null,'distance_km'=>null,'skipped'=>[],'retry_at'=>$this->now()+60,'bed_request_id'=>null,'hospital_id'=>null,'events'=>[]];
            $this->event($r,'SOS received. Searching for an on-duty ambulance within 25 km.'); $s['calls'][]=$r; $this->dispatch($s);
            return ['id'=>$id,'message'=>'SOS sent. Waiting for driver confirmation.'];
        });
    }
    public function location($p,$id,$location) {
        return $this->transaction(function (&$s) use ($p,$id,$location) {
            $i=$this->index($s,$id); $r=&$s['calls'][$i];
            if ($r['owner_id']!==$p['id'] || !in_array($r['status'],['Searching','Accepted','At pickup'],true)) throw new RuntimeException('Pickup location is locked after pickup.');
            if (abs($this->now()-$location['captured_at'])>120 || $location['mode']!==$r['location']['mode']) throw new RuntimeException('Invalid pickup location update.');
            if ($r['status']==='Searching' && self::distance($r['location']['lat'],$r['location']['lng'],$location['lat'],$location['lng'])>0.1) { $r['offer_driver']=null; $r['skipped']=[]; }
            $r['location']=$location; unset($r); $this->dispatch($s); return ['message'=>'Pickup location updated.'];
        });
    }
    public function action($p,$id,$action,$input=[],EmergencyBedService $beds=null) {
        return $this->transaction(function (&$s) use ($p,$id,$action,$input,$beds) {
            $i=$this->index($s,$id); $r=&$s['calls'][$i];
            if ($action==='cancel') {
                if ($r['owner_id']!==$p['id'] || !in_array($r['status'],['Searching','Accepted','At pickup'],true)) throw new RuntimeException('This SOS cannot be cancelled by this account.');
                $r['status']='Cancelled'; $r['offer_driver']=null; $this->event($r,'Patient cancelled SOS.');
            } elseif (in_array($action,['accept','decline'],true)) {
                if ($p['role']!=='driver' || $r['status']!=='Searching' || $r['offer_driver']!==$p['driver'] || $r['offer_until']<=$this->now()) throw new RuntimeException('Offer expired or belongs to another driver.');
                if ($action==='decline') { $r['skipped'][]=$p['driver']; $r['offer_driver']=null; $this->event($r,'Driver declined. Trying next available ambulance.'); }
                else { $r['driver_id']=$p['driver']; $r['offer_driver']=null; $r['status']='Accepted'; $this->event($r,'Driver accepted. Contact details shared with assigned driver.'); }
            } else {
                if ($p['role']!=='driver' || $r['driver_id']!==$p['driver']) throw new RuntimeException('Only the assigned driver can update this trip.');
                $next=['arrive'=>['Accepted','At pickup'],'pickup'=>['At pickup','Picked up'],'first_aid'=>['Picked up','First aid'],'complete'=>['First aid','Completed']];
                if ($action==='transport') {
                    if (!in_array($r['status'],['Picked up','First aid','Transporting'],true)) throw new RuntimeException('Pick up the patient before hospital transfer.');
                    if (!$beds) throw new RuntimeException('Hospital service unavailable.');
                    $care=$input['care']; $hospital=$input['hospital_id'];
                    if (!$r['bed_request_id']) $r['bed_request_id']=$beds->create(['patient'=>$r['patient']['name'].' [SOS '.$r['id'].']','location'=>'SOS GPS '.$r['location']['lat'].', '.$r['location']['lng'],'latitude'=>$r['location']['lat'],'longitude'=>$r['location']['lng'],'severity'=>'Critical','care'=>$care],'sos:'.$r['owner_id'],$r['owner_id']);
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
