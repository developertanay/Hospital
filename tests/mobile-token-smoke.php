<?php
require __DIR__.'/../vendor/autoload.php';
$app=require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
$user=App\Models\User::where('email','patient@careroute.test')->firstOrFail();
$kernel=$app->make(Illuminate\Contracts\Http\Kernel::class);
$tokens=[];
try {
    foreach ([['mobile',['careroute-mobile'],200,false],['unscoped',['*'],401,false],['expired',['careroute-mobile'],401,true]] as [$name,$abilities,$expected,$expired]) {
        $issued=$user->createToken('mobile-smoke-'.$name,$abilities);
        $tokens[]=$issued->accessToken;
        if($expired) $issued->accessToken->forceFill(['created_at'=>now()->subDays(8)])->save();
        Illuminate\Support\Facades\Auth::forgetGuards();
        $request=Illuminate\Http\Request::create('/api/mobile/state','GET',[],[],[],['HTTP_ACCEPT'=>'application/json','HTTP_AUTHORIZATION'=>'Bearer '.$issued->plainTextToken]);
        $response=$kernel->handle($request);
        if($response->getStatusCode()!==$expected) throw new RuntimeException($name.' token status was '.$response->getStatusCode());
        if($expired && Laravel\Sanctum\PersonalAccessToken::find($issued->accessToken->id)) throw new RuntimeException('Expired token not revoked');
        echo 'PASS '.$name." token policy\n";
        $kernel->terminate($request,$response);
    }
} finally { foreach($tokens as $token) $token->delete(); }
