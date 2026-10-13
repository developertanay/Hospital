<?php
// Read-only rendering fixture for the web presentation checks.
require __DIR__.'/../../vendor/autoload.php';
$app=require __DIR__.'/../../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
view()->share('errors',new Illuminate\Support\ViewErrorBag);
Illuminate\Support\Facades\Auth::setUser(App\Models\User::where('email','patient.test@careroute.test')->firstOrFail());
$html=(new App\Http\Controllers\EmergencyController)->index('history')->render();
preg_match_all('/<script\b[^>]*>(.*?)<\/script>/s',$html,$scripts);
foreach($scripts[1] as $script) if(strpos($script,'function renderHistory()')!==false) echo $script;
