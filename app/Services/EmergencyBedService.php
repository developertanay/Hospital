<?php
namespace App\Services;

use RuntimeException;

class EmergencyBedService
{
    private $path;
    private $ttl;

    public function __construct($path = null, $ttl = null)
    {
        $this->path = $path ?: config('emergency.store');
        $this->ttl = $ttl ?: config('emergency.expiry_seconds');
    }

    private function seed()
    {
        $hospitals = [];
        foreach ([['A',5,4,0,0,'Open'],['B',10,5,2,1,'Open'],['C',8,3,1,1,'Open'],['D',14,6,3,2,'Open'],['E',3,2,1,1,'Offline']] as $row) {
            [$id,$distance,$general,$icu,$ventilator,$status] = $row;
            $beds = [];
            foreach (['General'=>$general,'ICU'=>$icu,'Ventilator'=>$ventilator] as $care=>$count) {
                for ($i=1; $i<=$count; $i++) $beds[] = ['id'=>"$id-$care-$i",'care'=>$care,'status'=>'Available','request_id'=>null];
                $beds[] = ['id'=>"$id-$care-O",'care'=>$care,'status'=>'Occupied','request_id'=>null];
            }
            $hospitals[] = compact('id','distance','status','beds') + ['name'=>"Hospital $id"];
        }
        return ['hospitals'=>$hospitals,'requests'=>[],'limits'=>[],'events'=>[]];
    }

    // A separate lock file keeps every read/modify/write serialized, including expiry.
    private function transaction(callable $fn)
    {
        $lock = fopen($this->path.'.lock', 'c');
        if (!$lock || !flock($lock, LOCK_EX)) throw new RuntimeException('Unable to lock demo store.');
        try {
            $state = file_exists($this->path) ? json_decode(file_get_contents($this->path), true, 512, JSON_THROW_ON_ERROR) : $this->seed();
            $this->expire($state);
            $result = $fn($state);
            $temp = $this->path.'.tmp';
            if (file_put_contents($temp, json_encode($state, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)) === false || !rename($temp, $this->path)) throw new RuntimeException('Unable to save demo store.');
            return $result;
        } finally { flock($lock, LOCK_UN); fclose($lock); }
    }

    private function event(&$s, $message, $requestId = null)
    {
        array_unshift($s['events'], ['at'=>time(),'message'=>$message,'request_id'=>$requestId]);
        $s['events'] = array_slice($s['events'],0,30);
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
            $matches[] = ['id'=>$h['id'],'name'=>$h['name'],'distance'=>$h['distance'],'available'=>$available,'reclaimable'=>$reclaimable,'compatible'=>$reason === null,'reason'=>$reason];
        }
        usort($matches, function ($a,$b) { return ($b['compatible'] <=> $a['compatible']) ?: ($a['distance'] <=> $b['distance']) ?: ($b['available'] <=> $a['available']); });
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
        return $this->transaction(function (&$s) use ($input,$client,$ownerId) {
            $recent = array_values(array_filter($s['limits'][$client] ?? [], function ($t) { return $t > time()-60; }));
            if (count($recent) >= 5) throw new RuntimeException('Rate limit: at most 5 requests per minute. Wait before retrying.');
            foreach ($s['requests'] as $r) if (strcasecmp($r['patient'],$input['patient']) === 0 && in_array($r['status'],['New','Pending','Reserved','Occupied'])) throw new RuntimeException('This patient already has an active request.');
            $recent[] = time(); $s['limits'][$client] = $recent;
            $r = $input + ['id'=>bin2hex(random_bytes(6)),'owner_id'=>$ownerId,'verified'=>false,'status'=>'New','created_at'=>time(),'expires_at'=>null,'hospital_id'=>null,'bed_id'=>null];
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
            $this->event($s, $r['patient'].' → '.$r['status'].'.',$id);
        });
    }

    public function reset() { return $this->transaction(function (&$s) { $s = $this->seed(); }); }
}
