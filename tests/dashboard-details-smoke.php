<?php
require __DIR__.'/../vendor/autoload.php';
$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Hospital;
use App\Models\CareRequest;
use App\Http\Controllers\EmergencyController;

$checks=0;
function checkDashboard($condition,$message) { global $checks; if (!$condition) throw new RuntimeException($message); $checks++; }
view()->share('errors',new Illuminate\Support\ViewErrorBag);
DB::beginTransaction();
try {
    $controller=new EmergencyController;
    $patient=User::where('care_role','patient')->firstOrFail();
    $other=User::where('id','!=',$patient->id)->firstOrFail();
    $hospitals=Hospital::where('status',1)->limit(2)->get();
    checkDashboard($hospitals->count()===2,'Two hospitals available for scope checks');
    $suffix=bin2hex(random_bytes(5));
    $base=['location'=>'Verification','care'=>'General','severity'=>'Stable','verified'=>false,'status'=>'Pending','created_at'=>time(),'expires_at'=>time()+3600];
    $own=CareRequest::create(array_merge($base,['id'=>'dashboard-own-'.$suffix,'patient'=>'Own patient '.$suffix,'owner_id'=>$patient->id,'hospital_id'=>$hospitals[0]->id]));
    $foreign=CareRequest::create(array_merge($base,['id'=>'dashboard-other-'.$suffix,'patient'=>'Other patient '.$suffix,'owner_id'=>$other->id,'hospital_id'=>$hospitals[1]->id]));
    CareRequest::create(array_merge($base,['id'=>'dashboard-new-'.$suffix,'patient'=>'Unassigned '.$suffix,'owner_id'=>$other->id,'hospital_id'=>null,'status'=>'New','expires_at'=>null]));
    Auth::setUser($patient);
    $view=$controller->index();
    $html=$view->render();
    checkDashboard(strpos($html,$own->patient)!==false,'Patient sees own request details');
    checkDashboard(strpos($html,$foreign->patient)===false,'Patient cannot see another account request');
    checkDashboard($view->getData()['details']['requests']->count()<=5,'Patient detail list is bounded');
    $hospitalUser=User::where('care_role','hospital')->firstOrFail();
    $hospitalUser->hospital_id=$hospitals[0]->id;
    $hospitalUser->unsetRelation('hospital');
    Auth::setUser($hospitalUser);
    $view=$controller->index();
    checkDashboard($view->getData()['details']['hospital']->id===$hospitals[0]->id,'Hospital detail is assigned hospital');
    $response=$controller->state(Illuminate\Http\Request::create('/emergency/state','GET',['page'=>'dashboard']),new App\Services\EmergencyBedService);
    $state=$response->getData(true);
    checkDashboard($state['stats']['requests']===CareRequest::where('hospital_id',$hospitals[0]->id)->count(),'Hospital counters include only assigned hospital requests');
    checkDashboard($state['requests']===[],'Dashboard counters do not expose patient records in state response');
    Auth::setUser(User::where('care_role','admin')->firstOrFail());
    $view=$controller->index();
    $html=$view->render();
    checkDashboard(strpos($html,'Hospital details')!==false && strpos($html,'Ambulance details')!==false,'Admin sees management details');
    checkDashboard(strpos($html,$own->patient)===false && strpos($html,$foreign->patient)===false,'Admin dashboard excludes patient records');
    checkDashboard(strpos($html,'Your modules')===false && strpos($html,'module-search')===false && strpos($html,'class="module-card"')===false,'Duplicate module navigation removed');
    checkDashboard(strpos($html,'class="metric summary-card overview-link"')!==false,'Summary cards link to assigned pages');
    $markup=preg_replace('/<script\b[^>]*>.*?<\/script>/s','',$html);
    preg_match_all('/<a class="metric summary-card overview-link" href="([^"]+)"/',$markup,$links);
    checkDashboard(in_array(url('BedInven'),$links[1]),'Bed inventory card links to bed inventory');
    $allowedUrls=array_map(fn($module)=>url($module['url']),App\Support\RoleAccess::menu());
    foreach ($links[1] as $href) checkDashboard(in_array(html_entity_decode($href),$allowedUrls),'Each card target is assigned to the role: '.$href);
    checkDashboard($view->getData()['details']['hospitals']->count()<=5 && $view->getData()['details']['ambulances']->count()<=5,'Admin detail lists are bounded');
    echo "PASS: $checks dashboard detail checks; all changes rolled back\n";
} finally { DB::rollBack(); }
