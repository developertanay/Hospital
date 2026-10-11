<?php
// Cleanup only uniquely named smoke-test records, never reset the demo store.
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$path=config('emergency.store');
$lock=fopen($path.'.lock','c'); flock($lock,LOCK_EX);
try {
    $state=json_decode(file_get_contents($path),true,512,JSON_THROW_ON_ERROR);
    $id=$argv[1] ?? '';
    $record=collect($state['requests'])->firstWhere('id',$id);
    if (!$record || strpos($record['patient'],'Mobile API Smoke ')!==0) exit;
    $state['requests']=array_values(array_filter($state['requests'],fn($r)=>$r['id']!==$id));
    $state['events']=array_values(array_filter($state['events'],fn($e)=>($e['request_id'] ?? null)!==$id));
    foreach($state['hospitals'] as &$h) foreach($h['beds'] as &$b) if($b['request_id']===$id) { $b['status']='Available'; $b['request_id']=null; }
    file_put_contents($path.'.tmp',json_encode($state,JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR));
    rename($path.'.tmp',$path);
} finally { flock($lock,LOCK_UN); fclose($lock); }
$email=$argv[2] ?? '';
if(preg_match('/^mobile-smoke-[0-9]+@mobile-smoke\.test$/',$email)) {
    $user=App\Models\User::where('email',$email)->first();
    if($user) { $user->tokens()->delete(); $user->delete(); }
}
