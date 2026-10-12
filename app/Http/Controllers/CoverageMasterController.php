<?php
namespace App\Http\Controllers;
use App\Models\CoverageOrganisation;
use App\Models\HospitalCoverage;
use App\Models\Hospital;
use App\Support\RoleAccess;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class CoverageMasterController extends Controller {
    private $menu;private $mapping;
    public function __construct(){ $this->middleware('auth');$this->middleware(function($r,$next){$this->menu=$r->segment(1);abort_unless(in_array($this->menu,['CoverageOrganisation','HospitalCoverage']) && isset(RoleAccess::menu()[$this->menu]),403);$this->mapping=$this->menu==='HospitalCoverage';return $next($r);});}
    private function records(){ $q=$this->mapping?HospitalCoverage::with(['hospital','organisation']):CoverageOrganisation::query();if($this->mapping && RoleAccess::profile()['role']==='hospital')$q->where('hospital_id',RoleAccess::profile()['hospital']);return $q->where('status','!=',9);}
    public function index(){return view('coverage-masters.index',['rows'=>$this->records()->get(),'mapping'=>$this->mapping,'menu'=>$this->menu]);}
    public function create(){return $this->form($this->mapping?new HospitalCoverage:new CoverageOrganisation);}
    public function edit($id){return $this->form($this->records()->findOrFail($id));}
    private function form($record){$hospitals=Hospital::where('status',1);if(RoleAccess::profile()['role']==='hospital')$hospitals->where('id',RoleAccess::profile()['hospital']);return view('coverage-masters.form',['record'=>$record,'mapping'=>$this->mapping,'menu'=>$this->menu,'organisations'=>CoverageOrganisation::where('status',1)->get(),'hospitals'=>$hospitals->get()]);}
    public function store(Request $r){return $this->save($r,$this->mapping?new HospitalCoverage:new CoverageOrganisation);}
    public function update(Request $r,$id){return $this->save($r,$this->records()->findOrFail($id));}
    private function save(Request $r,$record){
        if($this->mapping && RoleAccess::profile()['role']==='hospital')$r->merge(['hospital_id'=>RoleAccess::profile()['hospital']]);
        $rules=['status'=>'required|in:1,2'];
        $rules+=$this->mapping?['hospital_id'=>['required',Rule::exists('hospital_mast','id')->where('status',1)],'organisation_id'=>['required',Rule::exists('coverage_organisations','id')->where('status',1)],'settlement_mode'=>'required|in:Cashless,Reimbursement,Panel']:['name'=>['required','string','max:100',Rule::unique('coverage_organisations','name')->ignore($record->id)],'kind'=>'required|in:Insurance,TPA,Employer,Scheme'];
        $data=$r->validate($rules);
        if($this->mapping && !$record->exists)$record=HospitalCoverage::where('hospital_id',$data['hospital_id'])->where('organisation_id',$data['organisation_id'])->first()??$record;
        $record->fill($data)->save();return redirect()->route($this->menu.'.index')->with('message','Record saved.');
    }
    public function destroy($id){$r=$this->records()->findOrFail($id);$r->status=9;$r->save();return redirect()->route($this->menu.'.index')->with('message','Record archived.');}
}
