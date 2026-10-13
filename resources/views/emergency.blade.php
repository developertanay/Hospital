@extends('layouts.header')
@section('title', 'CareRoute Dashboard')
@section('css')
<link href="{{ asset('assets/css/dashboard.css') }}" rel="stylesheet">
<link href="{{ asset('assets/css/dashboard-theme.css') }}" rel="stylesheet">
@endsection
@section('content')
@php($demoProfile = \App\Support\RoleAccess::profile())
@php($assignedModules = \App\Support\RoleAccess::menu())
<div class="page-wrapper"><div class="page-content care-dashboard"><div class="dashboard-bar"><div class="workspace-identity"><img src="{{ asset(config('branding.favicon')) }}" width="32" height="32" alt=""><span>{{ config('branding.name') }} workspace</span><span class="role-pill">{{ ucfirst($demoProfile['role']) }}</span></div><span id="connection" role="status" aria-live="polite">Connecting…</span></div><main>
@php($titles = ['dashboard'=>'Dashboard','requests'=>'Emergency requests','approvals'=>'Hospital approvals','beds'=>'Bed availability','reservations'=>'Reservation status','history'=>'Decision history'])
<div class="hero"><div><div class="eyebrow">{{ $demoProfile['role'] === 'hospital' ? ($demoProfile['hospital_name'] ?? 'Hospital workspace') : ucfirst($demoProfile['role']).' workspace' }}</div><h1>{{ $titles[$page] }}</h1>@if($page === 'dashboard')<p>Welcome back, {{ $demoProfile['name'] }}.</p>@endif</div></div>
@if($demoProfile['role'] !== 'admin')<div class="metrics" id="metrics"></div>@endif
@if($page==='requests')<div class="panel-head"><h2>Your requests</h2><a class="btn btn-primary" href="{{ route('care.requests.create') }}"><i class="bx bx-plus me-1" aria-hidden="true"></i>New request</a></div>@if($errors->any())<div class="note">{{ $errors->first() }}</div>@endif @endif
@if($summary)
<section class="panel"><div class="panel-head"><h2>Overview</h2></div><div class="metrics management-summary">
@foreach($summary as $item)
@if(isset($assignedModules[$item['url']]))<a class="metric summary-card overview-link" href="{{ url($item['url']) }}"><i class="{{ $assignedModules[$item['url']]['icon'] }} metric-icon" aria-hidden="true"></i><strong>{{ number_format($item['value']) }}</strong><span>{{ $item['label'] }}</span><i class="bx bx-right-arrow-alt metric-arrow" aria-hidden="true"></i></a>@endif
@endforeach
</div></section>
@endif

@if($page === 'dashboard')
@if($demoProfile['role']==='hospital' && !$demoProfile['hospital'])<div class="note">Ask your administrator to assign a hospital to this account.</div>@endif
@include('dashboard.details')
@endif
@if($page === 'requests')  <section class="panel" id="recommendations"><div class="panel-head"><h2>Matching hospitals</h2></div><select id="selectedRequest" aria-label="Select patient request"></select><div id="selectedSummary"></div><div id="matches" class="matches"></div></section> @endif
@if($page === 'approvals') <section class="panel" id="operatorPanel"><div class="panel-head"><h2>Hospital desk</h2><span id="operatorBadge" class="badge">Role access</span></div><div id="operatorLogin" class="operator-box"><p>A hospital account is required.</p></div><div id="operatorControls" class="hidden"><details class="secondary-details"><summary>Approval steps</summary><p>Review severity, accept a request, then confirm arrival.</p></details></div></section> @endif
@if(in_array($page, ['requests', 'approvals', 'reservations'])) <section class="panel" id="queue-section"><div class="panel-head"><h2>{{ $page === 'approvals' ? 'Hospital requests' : 'Your requests & reservations' }}</h2></div><div id="queue"></div></section> @endif
@if($page === 'beds') <section class="panel" id="bed-details"><div class="panel-head"><h2>Hospital beds</h2></div><div class="legend"><span><i class="bed"></i>Available</span><span><i class="bed Temporary"></i>Pending</span><span><i class="bed Reserved"></i>Reserved</span><span><i class="bed Occupied"></i>Occupied</span></div><div id="hospitals" class="hospital-grid" style="margin-top:16px"></div></section> @endif
@if($page === 'history') <section class="panel"><div class="panel-head"><h2>Recent activity</h2><button type="button" id="history-toggle" class="history-toggle" aria-controls="events" aria-expanded="false" hidden>View all</button></div><div id="events" class="events"></div></section> @endif
</main><div id="toast" class="toast hidden" role="status"></div></div></div>
@endsection
@section('js')
<script>(() => {
const stateUrl = @json(url('/emergency/state').'?page='.$page), actionUrl = @json(url('/emergency/action'));
const page = @json($page);
const requestBase = @json(route('care.requests.index'));
const coverageReviewBase = @json(url('emergency/coverage-review'));
function insuranceInfo(r){return `${r.coverage_id?`<p class="coverage-status">Coverage: ${esc(r.insurance_status)} · Cashless: ${esc(r.cashless_status)}</p>${page==='approvals'&&data.operator?`<a class="btn btn-outline-primary" href="${coverageReviewBase}/${encodeURIComponent(r.id)}/edit">Review insurance / panel</a>`:''}`:''}`;}
function requestDetails(r){
 return `<details class="secondary-details" data-request-details="${esc(r.id)}"><summary>Request details</summary><p>${esc(r.problem_type || 'Not specified')} · Preferred: ${esc(r.assistance_preference || 'Hospital')}</p>${r.description?`<p>${esc(r.description)}</p>`:''}${r.reporter_name?`<p>Raised by: ${esc(r.reporter_name)} · ${esc(r.reporter_phone || '')}</p>`:''}${r.latitude!=null && r.longitude!=null?`<p>GPS: ${esc(r.latitude)}, ${esc(r.longitude)}</p>`:''}${r.insurance_note?`<p>Coverage note: ${esc(r.insurance_note)}</p>`:''}</details>`;
}
const assignedUrls = @json(collect($assignedModules)->mapWithKeys(fn($module)=>[$module['url']=>url($module['url'])]));
const el = id => document.getElementById(id);
const esc = text => String(text ?? '').replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
let data, selected = new URLSearchParams(location.search).get('selected') || '', busy = false, toastTimer;
let historyExpanded = false;
function renderHistory(){
 const events=[...data.events].sort((a,b)=>b.at-a.at), visible=historyExpanded?events:events.slice(0,2);
 el('events').innerHTML=visible.length?visible.map(e=>`<div class="event"><span class="event-dot" aria-hidden="true"></span><div><p>${esc(e.message)}</p><time>${new Date(e.at*1000).toLocaleString(undefined,{month:'short',day:'numeric',hour:'numeric',minute:'2-digit'})}</time></div></div>`).join(''):'<div class="empty">No activity yet.</div>';
 const toggle=el('history-toggle');
 if(toggle){toggle.hidden=events.length<=2;toggle.textContent=historyExpanded?'View less':'View all';toggle.setAttribute('aria-expanded',String(historyExpanded));}
}
function toast(message){el('toast').textContent=message;el('toast').classList.remove('hidden');clearTimeout(toastTimer);toastTimer=setTimeout(()=>el('toast').classList.add('hidden'),6000)}
async function refresh(){try{const res=await fetch(stateUrl,{credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'}});if(!res.ok)throw Error('Server unavailable');data=await res.json();if(data.csrf_token)document.querySelector('meta[name="csrf-token"]').content=data.csrf_token;render();el('connection').textContent='● Connected · '+new Date().toLocaleTimeString()}catch(e){el('connection').textContent='Connection unavailable';toast(e.message)}}
async function act(action, payload={}){
 if(busy)return;
 busy=true;document.querySelectorAll('.care-dashboard button').forEach(b=>b.disabled=true);
 try{
  const send=()=>fetch(action==='create'?requestBase:actionUrl,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json',Accept:'application/json','X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content},body:JSON.stringify({action,...payload})});
  let res=await send();
  if(res.status===419){
   const session=await fetch(stateUrl,{credentials:'same-origin',cache:'no-store',headers:{Accept:'application/json'}});
   if(session.status===401)throw Error('Your login has expired. Sign in again; your form entries are still here.');
   if(!session.ok)throw Error('Unable to refresh your session. Refresh this page and try again.');
   const fresh=await session.json();
   document.querySelector('meta[name="csrf-token"]').content=fresh.csrf_token;
   res=await send();
  }
  if(res.status===419)throw Error('Session could not be refreshed. Sign in again and retry.');
  if(res.status===401)throw Error('Your login has expired. Sign in again.');
  const result=await res.json();
  if(!res.ok)throw Error(result.message || 'Unable to save. Please try again.');
  if(result.id)selected=result.id;
  toast(result.message);await refresh();
 }catch(e){toast(e.message)}finally{busy=false;document.querySelectorAll('.care-dashboard button').forEach(b=>b.disabled=false)}
}
function remaining(r){return r.expires_at ? Math.max(0,r.expires_at-data.now)+'s remaining' : ''}
function render(){

 if(el('metrics')) {
 const metrics = page==='dashboard' && data.profile.role==='patient'
 ? [[data.stats.requests,'Your requests'],[data.stats.pending,'Awaiting approval'],[data.stats.reserved,'Confirmed reservations'],[data.stats.occupied,'Admitted']]
 : [[data.stats.requests,data.profile.role==='hospital' ? 'Hospital requests' : 'Your requests'],[data.stats.pending,'Awaiting approval'],[data.stats.reserved,'Confirmed reservations'],[data.stats.available,'Available care beds']];
 const targets = data.profile.role==='hospital'
 ? ['emergency/approvals','emergency/approvals','emergency/approvals','emergency/beds']
 : ['emergency/requests','emergency/reservations','emergency/reservations',page==='dashboard' ? 'emergency/reservations' : 'emergency/beds'];
 el('metrics').innerHTML=metrics.map(([n,t],index)=>{
   const href = assignedUrls[targets[index]];
   const content = `<strong>${n}</strong><span>${t}</span>`;
   return href ? `<a class="metric summary-card overview-link" href="${esc(href)}">${content}<i class="bx bx-right-arrow-alt metric-arrow" aria-hidden="true"></i></a>` : `<div class="metric">${content}</div>`;
 }).join('');
 }
 if(el('operatorBadge')){el('operatorBadge').textContent=data.operator?'Hospital role verified':'Patient access';el('operatorLogin').classList.toggle('hidden',data.operator);el('operatorControls').classList.toggle('hidden',!data.operator);}
 if(!data.requests.some(r=>r.id===selected))selected=data.requests[0]?.id || '';
 if(el('selectedRequest')){el('selectedRequest').innerHTML=data.requests.length ? data.requests.map(r=>`<option value="${r.id}" ${r.id===selected?'selected':''}>${esc(r.patient)} · ${esc(r.care)} · ${r.status}</option>`).join('') : '<option>Submit a request to compare hospitals</option>';
 const r=data.requests.find(r=>r.id===selected);
 el('selectedSummary').innerHTML=r?`<p><strong>${esc(r.location)}</strong> · ${esc(r.care)} · <span class="badge ${r.priority===3?'critical':r.priority===2?'urgent':''}">${r.verified?'Verified '+r.severity:'Unverified · standard priority'}</span> <span class="badge">${r.status}</span></p>${['Pending','Reserved','Occupied'].includes(r.status)?`<div class="note">Assigned: <strong>Hospital ${esc(r.hospital_id)}</strong> · ${esc(r.bed_id)} · ${esc(remaining(r))}<details><summary>About availability</summary><p>Cards show remaining capacity. Your assigned bed stays with this request.</p></details></div>`:''}`:'';
 let best=false;
 el('matches').innerHTML=r?(r.matches || []).map(m=>{const recommended=m.compatible&&!best;if(recommended)best=true;const canRequest=data.profile.role!=='hospital'&&['New','Released','Expired','Rejected'].includes(r.status);return `<article class="match ${recommended?'best':''} ${!m.compatible?'rejected':''}"><div class="match-top"><h3>${esc(m.name)}</h3>${m.preferred?'<span class="badge">Your preferred hospital</span>':''}<span class="badge ${recommended?'good':''}">${recommended?'Recommended':m.compatible?'Compatible':'Rejected'}</span></div><div class="distance">${m.distance===null?'Distance not provided':m.distance+' <small>km · straight-line estimate</small>'}</div><p>${m.available} ${esc(r.care)} beds available${m.reclaimable?` · ${m.reclaimable} lower-priority hold reclaimable`:''}</p>${m.reason?`<p class="critical">${esc(m.reason)}</p>`:''}${m.compatible&&canRequest?`<button data-action="request" data-id="${r.id}" data-hospital="${m.id}">Request ${r.care==='General'?'General':r.care} Bed</button>`:''}</article>`}).join(''):'<div class="empty">Submit a request to see hospitals with compatible beds.</div>';
 }
 if(el('queue')){
 const expandedDetails=new Set([...el('queue').querySelectorAll('[data-request-details][open]')].map(detail=>detail.dataset.requestDetails));
 el('queue').innerHTML=data.requests.length?data.requests.map(r=>`<article class="request"><div class="request-top"><div><strong>${esc(r.patient)}</strong><div class="request-meta"><span><i class="bx bx-map" aria-hidden="true"></i>${esc(r.location)}</span><span><i class="bx bx-bed" aria-hidden="true"></i>${esc(r.care)}</span></div><p>${r.hospital_id?'Hospital '+r.hospital_id:'No hospital requested'}${remaining(r)?' · '+esc(remaining(r)):''}</p></div><div><span class="badge ${r.priority===3?'critical':''}">P${r.priority} · ${r.verified?r.severity:'Unverified'}</span> <span class="badge ${r.status==='Reserved'?'good':''}">${r.status}</span></div></div>${insuranceInfo(r)}${r.status==='Released'?'<p>Hold reassigned for Critical care. Choose another hospital.</p>':''}<div class="actions">${page==='requests'&&['New','Released','Expired','Rejected'].includes(r.status)?`<a class="overview-link" href="${requestBase}/${r.id}/edit">Edit</a><form method="POST" action="${requestBase}/${r.id}" onsubmit="return confirm('Cancel this request?')"><input type="hidden" name="_token" value="${esc(document.querySelector('meta[name="csrf-token"]').content)}"><input type="hidden" name="_method" value="DELETE"><button class="danger">Cancel request</button></form>`:''}${page==='requests'?`<button class="secondary" data-select="${r.id}">View hospitals</button>`:''}${page==='approvals'&&data.operator&&['New','Pending'].includes(r.status)?`<select id="triage-${r.id}" aria-label="Verified severity for ${esc(r.patient)}">${['Critical','Urgent','Stable'].map(s=>`<option ${s===r.severity?'selected':''}>${s}</option>`).join('')}</select><button class="secondary" data-action="verify" data-id="${r.id}">${r.verified?'Update severity':'Verify severity'}</button>`:''}${page==='approvals'&&data.operator&&r.status==='Pending'?`<button data-action="accept" data-id="${r.id}">Accept → Reserved</button><button class="danger" data-action="reject" data-id="${r.id}">Reject</button>`:''}${page==='approvals'&&data.operator&&r.status==='Reserved'?`<button data-action="arrive" data-id="${r.id}">Patient arrived → Occupied</button>`:''}</div>${requestDetails(r)}</article>`).join(''):'<div class="empty">No emergency requests yet.</div>';
 el('queue').querySelectorAll('[data-request-details]').forEach(detail=>{detail.open=expandedDetails.has(detail.dataset.requestDetails);});
 }
 if(el('hospitals')) el('hospitals').innerHTML=data.hospitals.map(h=>`<article class="hospital"><div class="match-top"><h3>${esc(h.name)}</h3><span class="badge ${h.status==='Open'?'good':''}">${h.status==='Open'?'● Open · accepting requests':'● Offline'}</span></div><p>${esc(h.address || 'Address not provided')}</p>${(data.care_types || []).map(c=>{const bs=h.beds.filter(b=>b.care===c);return `<div class="care-row"><strong>${esc(c)}</strong><span>${bs.filter(b=>b.status==='Available').length} available</span></div><div class="beds">${bs.map(b=>`<span class="bed ${b.status}" title="${esc(b.id)} · ${b.status}" aria-label="${esc(b.id)} ${b.status}">${b.status==='Occupied'?'×':b.status==='Reserved'?'R':b.status==='Temporary'?'T':'+'}</span>`).join('')}</div>`}).join('')}</article>`).join('');
 if(el('events')) renderHistory();
}
if(el('history-toggle'))el('history-toggle').addEventListener('click',()=>{historyExpanded=!historyExpanded;renderHistory();});
document.querySelectorAll('[data-history-list]').forEach(toggle=>toggle.addEventListener('click',()=>{
 const expanded=toggle.getAttribute('aria-expanded')!=='true';
 document.querySelectorAll('#'+toggle.dataset.historyList+' [data-recent-row]').forEach((row,index)=>{row.hidden=!expanded && index>1;});
 toggle.textContent=expanded?'View less':'View all';toggle.setAttribute('aria-expanded',String(expanded));
}));
if(el('patientForm'))el('patientForm').addEventListener('submit',e=>{e.preventDefault();act('create',Object.fromEntries(new FormData(e.target)))});
if(el('selectedRequest'))el('selectedRequest').addEventListener('change',e=>{selected=e.target.value;render()});
document.addEventListener('click',e=>{const select=e.target.closest('[data-select]');if(select){selected=select.dataset.select;render();el('selectedRequest').scrollIntoView({behavior:'smooth'});return}const b=e.target.closest('[data-action]');if(!b)return;const payload={id:b.dataset.id};if(b.dataset.hospital)payload.hospital_id=b.dataset.hospital;if(b.dataset.action==='verify')payload.severity=el('triage-'+payload.id).value;act(b.dataset.action,payload)});
function revealSection(){const target=document.getElementById(location.hash.slice(1));const section=target?.closest('details');if(section)section.open=true;}
window.addEventListener('hashchange',revealSection);revealSection();
if (page==='dashboard' && @json($demoProfile['role'])==='admin') {
 el('connection').textContent='Snapshot · '+new Date().toLocaleTimeString();
} else {
 refresh();setInterval(()=>{if(!busy && !document.hidden && !document.activeElement?.id?.startsWith('triage-'))refresh()},5000);
}
})();</script>
@endsection
