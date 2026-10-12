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
<div class="hero"><div><div class="eyebrow">{{ $demoProfile['role'] === 'hospital' ? ($demoProfile['hospital_name'] ?? 'Hospital workspace') : ucfirst($demoProfile['role']).' workspace' }}</div><h1>{{ $titles[$page] }}</h1><p>{{ $page === 'dashboard' ? 'Welcome back, '.$demoProfile['name'].'. Here is your workspace at a glance.' : 'Records and actions available to your role.' }}</p></div><div class="hero-mark" aria-hidden="true"><i class="bx bx-plus-medical"></i></div></div>
@if($demoProfile['role'] !== 'admin')<div class="metrics" id="metrics"></div>@endif
@if($page==='requests')<div class="panel-head"><h2>Your emergency requests</h2><a class="btn btn-primary" href="{{ route('care.requests.create') }}">Add Emergency Request</a></div>@if($errors->any())<div class="note">{{ $errors->first() }}</div>@endif @endif
@if($summary)
<section class="panel"><div class="panel-head"><h2>Management at a glance</h2><span class="badge">Saved database records</span></div><div class="metrics management-summary">
@foreach($summary as $item)
@if(isset($assignedModules[$item['url']]))<a class="metric summary-card overview-link" href="{{ url($item['url']) }}"><i class="{{ $assignedModules[$item['url']]['icon'] }} metric-icon" aria-hidden="true"></i><strong>{{ number_format($item['value']) }}</strong><span>{{ $item['label'] }}</span><i class="bx bx-right-arrow-alt metric-arrow" aria-hidden="true"></i></a>@endif
@endforeach
</div></section>
@endif

@if($page === 'dashboard')
@if($demoProfile['role']==='hospital' && !$demoProfile['hospital'])<div class="note">Your account needs a hospital assignment. Ask an administrator to edit your account in Accounts & Hospital Access.</div>@endif
@include('dashboard.details')
@endif
@if($page === 'requests')  <section class="panel" id="recommendations"><div class="panel-head"><h2>Hospital recommendations</h2><span class="badge">02 / Match & request</span></div><select id="selectedRequest" aria-label="Select patient request"></select><div id="selectedSummary"></div><div class="steps"><span>Care compatibility</span><span>→ Distance ranking</span><span>→ Hospital confirmation</span></div><div id="matches" class="matches"></div></section> @endif
@if($page === 'approvals') <section class="panel" id="operatorPanel"><div class="panel-head"><h2>Hospital desk</h2><span id="operatorBadge" class="badge">Role access</span></div><div id="operatorLogin" class="operator-box"><p>Your patient login cannot verify or accept a reservation. After requesting a bed, sign out and use the recommended hospital's operator account. Sign back in here to track your reservation.</p></div><div id="operatorControls" class="hidden"><p>Verify severity, accept requests, then mark arrival. You can verify or update severity while a request is New or Pending. Confirmed reservations and occupied patients cannot be re-triaged here. Queue is ordered by verified priority, then submission time. Hospital operators can accept only their own hospital's requests.</p></div></section> @endif
@if(in_array($page, ['requests', 'approvals', 'reservations'])) <section class="panel" id="queue-section"><div class="panel-head"><h2>{{ $page === 'approvals' ? 'Requests awaiting your hospital' : 'Your requests & reservations' }}</h2><span class="badge">03 / Confirm</span></div><div id="queue"></div></section> @endif
@if($page === 'beds') <section class="panel" id="bed-details"><div class="panel-head"><div><h2>Hospital bed dashboard</h2><p>Your hospital beds · refresh every 5 seconds</p></div></div><div class="legend"><span><i class="bed"></i>Available · green +</span><span><i class="bed Temporary"></i>Temporary · pending</span><span><i class="bed Reserved"></i>Reserved · accepted</span><span><i class="bed Occupied"></i>Occupied · red ×</span></div><div id="hospitals" class="hospital-grid" style="margin-top:16px"></div></section> @endif
@if($page === 'history') <section class="panel"><h2>Decision history</h2><div id="events" class="events"></div></section> @endif
@if($demoProfile['role'] !== 'admin')<footer>Availability comes from saved inventory. Expiry is checked on each refresh.</footer>@endif
</main><div id="toast" class="toast hidden" role="status"></div></div></div>
@endsection
@section('js')
<script>(() => {
const stateUrl = @json(url('/emergency/state').'?page='.$page), actionUrl = @json(url('/emergency/action'));
const page = @json($page);
const requestBase = @json(route('care.requests.index'));
const assignedUrls = @json(collect($assignedModules)->mapWithKeys(fn($module)=>[$module['url']=>url($module['url'])]));
const el = id => document.getElementById(id);
const esc = text => String(text ?? '').replace(/[&<>"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[c]));
let data, selected = new URLSearchParams(location.search).get('selected') || '', busy = false, toastTimer;
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
 el('selectedSummary').innerHTML=r?`<p><strong>${esc(r.location)}</strong> · ${esc(r.care)} · <span class="badge ${r.priority===3?'critical':r.priority===2?'urgent':''}">${r.verified?'Verified '+r.severity:'Unverified · standard priority'}</span> <span class="badge">${r.status}</span></p>${['Pending','Reserved','Occupied'].includes(r.status)?`<div class="note">Assigned: <strong>Hospital ${esc(r.hospital_id)}</strong> · ${esc(r.bed_id)} · ${esc(remaining(r))}<br>Cards below show current remaining capacity. Your assigned bed stays with this request.</div>`:''}`:'';
 let best=false;
 el('matches').innerHTML=r?(r.matches || []).map(m=>{const recommended=m.compatible&&!best;if(recommended)best=true;const canRequest=data.profile.role!=='hospital'&&['New','Released','Expired','Rejected'].includes(r.status);return `<article class="match ${recommended?'best':''} ${!m.compatible?'rejected':''}"><div class="match-top"><h3>${esc(m.name)}</h3>${m.preferred?'<span class="badge">Your preferred hospital</span>':''}<span class="badge ${recommended?'good':''}">${recommended?'Recommended':m.compatible?'Compatible':'Rejected'}</span></div><div class="distance">${m.distance===null?'Distance not provided':m.distance+' <small>km · straight-line estimate</small>'}</div><p>${m.available} ${esc(r.care)} beds available${m.reclaimable?` · ${m.reclaimable} lower-priority hold reclaimable`:''}</p>${m.reason?`<p class="critical">${esc(m.reason)}</p>`:''}${m.compatible&&canRequest?`<button data-action="request" data-id="${r.id}" data-hospital="${m.id}">Request ${r.care==='General'?'General':r.care} Bed</button>`:''}</article>`}).join(''):'<div class="empty">Submit a request to see hospitals with compatible beds.</div>';
 }
 if(el('queue')) el('queue').innerHTML=data.requests.length?data.requests.map(r=>`<article class="request"><div class="request-top"><div><strong>${esc(r.patient)}</strong><p>${esc(r.problem_type || 'Not specified')} · Preferred: ${esc(r.assistance_preference || 'Hospital')}</p>${r.description?`<p>${esc(r.description)}</p>`:''}<p>${esc(r.location)}${r.latitude!=null && r.longitude!=null?` · GPS ${esc(r.latitude)}, ${esc(r.longitude)}`:''}</p><p>${esc(r.care)} · ${r.hospital_id?'Hospital '+r.hospital_id:'No hospital requested'} · ${esc(remaining(r))}</p></div><div><span class="badge ${r.priority===3?'critical':''}">P${r.priority} · ${r.verified?r.severity:'Unverified'}</span> <span class="badge ${r.status==='Reserved'?'good':''}">${r.status}</span></div></div>${r.status==='Released'?'<p>Temporary hold reassigned for verified Critical care. Select this patient to request another hospital.</p>':''}<div class="actions">${page==='requests'&&['New','Released','Expired','Rejected'].includes(r.status)?`<a class="overview-link" href="${requestBase}/${r.id}/edit">Edit</a><form method="POST" action="${requestBase}/${r.id}" onsubmit="return confirm('Cancel this request?')"><input type="hidden" name="_token" value="${esc(document.querySelector('meta[name="csrf-token"]').content)}"><input type="hidden" name="_method" value="DELETE"><button class="danger">Cancel request</button></form>`:''}${page==='requests'?`<button class="secondary" data-select="${r.id}">View hospitals</button>`:''}${page==='approvals'&&data.operator&&['New','Pending'].includes(r.status)?`<select id="triage-${r.id}" aria-label="Verified severity for ${esc(r.patient)}">${['Critical','Urgent','Stable'].map(s=>`<option ${s===r.severity?'selected':''}>${s}</option>`).join('')}</select><button class="secondary" data-action="verify" data-id="${r.id}">${r.verified?'Update severity':'Verify severity'}</button>`:''}${page==='approvals'&&data.operator&&r.status==='Pending'?`<button data-action="accept" data-id="${r.id}">Accept → Reserved</button><button class="danger" data-action="reject" data-id="${r.id}">Reject</button>`:''}${page==='approvals'&&data.operator&&r.status==='Reserved'?`<button data-action="arrive" data-id="${r.id}">Patient arrived → Occupied</button>`:''}</div></article>`).join(''):'<div class="empty">No emergency requests yet.</div>';
 if(el('hospitals')) el('hospitals').innerHTML=data.hospitals.map(h=>`<article class="hospital"><div class="match-top"><h3>${esc(h.name)}</h3><span class="badge ${h.status==='Open'?'good':''}">${h.status==='Open'?'● Open · accepting requests':'● Offline'}</span></div><p>${esc(h.address || 'Address not provided')}</p>${(data.care_types || []).map(c=>{const bs=h.beds.filter(b=>b.care===c);return `<div class="care-row"><strong>${esc(c)}</strong><span>${bs.filter(b=>b.status==='Available').length} available</span></div><div class="beds">${bs.map(b=>`<span class="bed ${b.status}" title="${esc(b.id)} · ${b.status}" aria-label="${esc(b.id)} ${b.status}">${b.status==='Occupied'?'×':b.status==='Reserved'?'R':b.status==='Temporary'?'T':'+'}</span>`).join('')}</div>`}).join('')}</article>`).join('');
 if(el('events')) el('events').innerHTML=data.events.length?data.events.map(e=>`<div class="event"><time>${new Date(e.at*1000).toLocaleTimeString()}</time>${esc(e.message)}</div>`).join(''):'<p>Actions and allocation decisions will appear here.</p>';
}
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
