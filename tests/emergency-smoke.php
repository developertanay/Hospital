<?php
// Standalone deterministic domain checks, no PHPUnit/database setup required.
require __DIR__.'/../vendor/autoload.php';
use App\Services\EmergencyBedService;
$path = __DIR__.'/../storage/app/emergency-test-'.bin2hex(random_bytes(4)).'.json';
$service = new EmergencyBedService($path,300);
$checks = 0;
function check($condition, $message) { global $checks; if (!$condition) throw new RuntimeException($message); $checks++; }
function requestById($state,$id) { foreach ($state['requests'] as $r) if ($r['id']===$id) return $r; throw new RuntimeException('Missing request'); }
function fails($fn) { try { $fn(); } catch (RuntimeException $e) { return true; } return false; }
try {
    $input=['patient'=>'Accident 01','location'=>'Accident Site','severity'=>'Critical','care'=>'ICU'];
    $first=$service->create($input,'client1');
    $state=$service->state(); $r=requestById($state,$first);
    check(count($state['hospitals'])===5,'Five seeded hospitals');
    check($r['priority']===1,'Self-declared Critical must not increase priority');
    check($r['matches'][0]['id']==='C','Closest compatible hospital C recommended');
    $a=array_values(array_filter($r['matches'],fn($m)=>$m['id']==='A'))[0];
    check(!$a['compatible'],'A rejected because ICU unavailable');
    check(fails(fn()=>$service->requestBed($first,'A')),'Incompatible hospital rejected server-side');
    $service->verify($first,'Critical');$service->requestBed($first,'C');
    check(fails(fn()=>$service->action($first,'accept','B')),'Locked service enforces hospital scope');
    check(fails(fn()=>$service->requestBed($first,'B',999)),'Locked service enforces patient ownership');
    $service->action($first,'accept','C');
    check(requestById($service->state(),$first)['status']==='Reserved','Acceptance reserves bed');
    $second=$service->create(array_merge($input,['patient'=>'Accident 02']),'client2');$service->verify($second,'Critical');
    check(requestById($service->state(),$second)['matches'][0]['id']==='B','Second Critical uses B, confirmed C not evicted');
    check(fails(fn()=>$service->requestBed($second,'C')),'Confirmed bed protected');
    $service->action($first,'arrive');
    check(fails(fn()=>$service->requestBed($second,'C')),'Occupied bed protected');
    check(fails(fn()=>$service->action($first,'reject')),'Occupied request cannot be rejected');
    $service->reset();
    $stable=$service->create(array_merge($input,['patient'=>'Stable 01','severity'=>'Stable']),'s');$service->requestBed($stable,'C');
    check(fails(fn()=>$service->action($stable,'accept')),'Hospital cannot accept unverified severity');
    $critical=$service->create(array_merge($input,['patient'=>'Critical 02']),'c');
    check(fails(fn()=>$service->requestBed($critical,'C')),'Unverified Critical cannot reclaim hold');
    $service->verify($critical,'Critical');$service->requestBed($critical,'C');
    $state=$service->state();
    check(requestById($state,$stable)['status']==='Released','Lower priority temporary hold released');
    check($state['requests'][0]['id']===$critical,'Verified Critical sorted first');
    $service->requestBed($stable,'B');$service->verify($stable,'Stable');$service->action($stable,'accept');
    $raw=json_decode(file_get_contents($path),true);
    foreach($raw['requests'] as &$r) if($r['id']===$stable || $r['id']===$critical)$r['expires_at']=time()-1;
    unset($r);file_put_contents($path,json_encode($raw));$state=$service->state();
    check(requestById($state,$stable)['status']==='Expired','Confirmed reservation expires');
    check(requestById($state,$critical)['status']==='Expired','Temporary hold expires');
    check(requestById($state,$critical)['matches'][0]['available']===1,'Expired ICU hold returns capacity');
    $service->reset();
    for($i=0;$i<5;$i++)$service->create(array_merge($input,['patient'=>'Rate '.$i]),'same-ip');
    check(fails(fn()=>$service->create(array_merge($input,['patient'=>'Rate 6']),'same-ip')),'Sixth request rate limited');
    check(fails(fn()=>$service->create(array_merge($input,['patient'=>'Rate 0']),'other-ip')),'Duplicate active patient blocked');
    echo "PASS: $checks emergency workflow checks\n";
} finally { foreach ([$path,$path.'.lock',$path.'.tmp'] as $f) if(file_exists($f))unlink($f); }
