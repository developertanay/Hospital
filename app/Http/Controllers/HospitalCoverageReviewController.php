<?php
namespace App\Http\Controllers;
use App\Models\CareRequest;
use App\Models\PatientCoverage;
use App\Support\RoleAccess;
use Illuminate\Http\Request;
class HospitalCoverageReviewController extends Controller {
    public function __construct(){$this->middleware('auth');$this->middleware(function($r,$next){$p=RoleAccess::profile();abort_unless($p && $p['role']==='hospital' && RoleAccess::canPage('approvals'),403);return $next($r);});}
    private function record($id){return CareRequest::where('hospital_id',RoleAccess::profile()['hospital'])->whereNotNull('coverage_id')->findOrFail($id);}
    public function edit($id){$record=$this->record($id);$coverage=PatientCoverage::with(['organisation','tpa'])->findOrFail($record->coverage_id);return view('coverage-masters.review',compact('record','coverage'));}
    public function update(Request $r,$id){$this->record($id);$r->merge(['request_id'=>$id]);$response=app(\App\Http\Controllers\API\PatientPortfolioController::class)->review($r);if($response->getStatusCode()!==200)return back()->withErrors(['insurance'=>$response->getData()->message])->withInput();return redirect(url('emergency/approvals'))->with('message','Insurance decision saved.');}
}
