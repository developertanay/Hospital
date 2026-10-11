<?php
require __DIR__.'/../vendor/autoload.php';
use App\Services\AmbulanceService;
if (($argv[1]??'')==='--worker') {
    try { (new AmbulanceService($argv[2]))->action(['id'=>201,'role'=>'driver','driver'=>'D1'],$argv[3],'accept'); echo 'accepted'; }
    catch (RuntimeException $e) { echo 'rejected'; }
    exit;
}
$app=require __DIR__.'/../bootstrap/app.php'; $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$path=storage_path('app/sos-test-race-'.bin2hex(random_bytes(4)).'.json'); $s=new AmbulanceService($path);
$patient=['id'=>101,'role'=>'patient'];$driver=['id'=>201,'role'=>'driver','driver'=>'D1'];$jobs=[];
try {
    $s->profile($patient,['name'=>'Race test','age'=>30,'mobile'=>'+919000000001','emergency_name'=>'Contact','emergency_phone'=>'+919000000002']);
    $s->duty($driver,['on_duty'=>true,'lat'=>28.6139,'lng'=>77.2090,'mode'=>'demo']);
    $id=$s->create($patient,['lat'=>28.6139,'lng'=>77.2090,'accuracy'=>5,'captured_at'=>time(),'mode'=>'demo'])['id'];
    for ($i=0;$i<2;$i++) {
        $pipes=[];$process=proc_open([PHP_BINARY,__FILE__,'--worker',$path,$id],[1=>['pipe','w'],2=>['pipe','w']],$pipes);
        if (!is_resource($process)) throw new RuntimeException('Could not start race worker');
        $jobs[]=[$process,$pipes];
    }
    $answers=[];
    foreach ($jobs as [$process,$pipes]) { $answers[]=trim(stream_get_contents($pipes[1])); $err=stream_get_contents($pipes[2]); fclose($pipes[1]);fclose($pipes[2]); if(proc_close($process)!==0||$err)throw new RuntimeException($err?:'Worker failed'); }
    sort($answers); if ($answers!==['accepted','rejected']) throw new RuntimeException('Race produced '.json_encode($answers));
    if ($s->state($patient)['calls'][0]['driver_id']!=='D1') throw new RuntimeException('Wrong final assignment');
    echo "PASS: concurrent duplicate acceptance has exactly one winner\n";
} finally { foreach ([$path,$path.'.lock',$path.'.tmp'] as $f) if(file_exists($f))unlink($f); }
