@extends('layouts.header')
@section('title',$record->exists ? 'Edit Emergency Request' : 'Add Emergency Request')
@section('content')
<div class="page-wrapper crud-page"><div class="page-content"><div class="card"><div class="card-body p-4"><h5 class="mb-4">{{ $record->exists ? 'Edit' : 'Add' }} Emergency Request</h5>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form class="row g-3" method="POST" action="{{ $record->exists ? route('care.requests.update',$record->id) : route('care.requests.store') }}">@csrf @if($record->exists) @method('PUT') @endif
@foreach(['patient'=>'Patient name / ID','location'=>'Current location'] as $key=>$label)<div class="col-md-6"><label for="request-{{ $key }}" class="form-label">{{ $label }}</label><input id="request-{{ $key }}" name="{{ $key }}" class="form-control" required maxlength="{{ $key==='patient' ? 80 : 120 }}" value="{{ old($key,$record->$key) }}"></div>@endforeach
<div class="col-md-6"><label for="request-care" class="form-label">Care type</label><select id="request-care" class="form-select" name="care" required><option value="">Select care type</option>@foreach($careTypes as $care)<option @if(old('care',$record->care)===$care) selected @endif>{{ $care }}</option>@endforeach</select></div>
<div class="col-md-6"><label for="request-severity" class="form-label">Reported severity</label><select id="request-severity" class="form-select" name="severity" required><option value="">Select severity</option>@foreach(['Critical','Urgent','Stable'] as $severity)<option @if(old('severity',$record->severity)===$severity) selected @endif>{{ $severity }}</option>@endforeach</select></div>
<div class="col-md-6"><label for="request-problem" class="form-label">Problem type</label><select id="request-problem" name="problem_type" class="form-select" required>@foreach(['Internal'=>'Internal · symptoms','External'=>'External · injury'] as $value=>$label)<option value="{{ $value }}" @if(old('problem_type',$record->problem_type ?? 'Internal')===$value) selected @endif>{{ $label }}</option>@endforeach</select></div>
<div class="col-md-6"><label for="request-preference" class="form-label">Preferred assistance</label><select id="request-preference" name="assistance_preference" class="form-select">@foreach(['Ambulance','Hospital'] as $value)<option @if(old('assistance_preference',$record->assistance_preference ?? 'Ambulance')===$value) selected @endif>{{ $value }}</option>@endforeach</select></div>
<div class="col-12" id="hospital-preference"><label for="request-hospital" class="form-label">Preferred hospital</label><select id="request-hospital" name="preferred_hospital_id" class="form-select"><option value="">Nearest suitable hospital</option>@foreach($hospitals as $hospital)<option value="{{ $hospital->id }}" @if((string)old('preferred_hospital_id',$record->preferred_hospital_id)===(string)$hospital->id) selected @endif>{{ $hospital->hospital_name }}</option>@endforeach</select><small>Choose a bed from the matches after saving.</small></div>
<div class="col-12" id="ambulance-preference"><div class="alert alert-info">Save this request, then book an ambulance in the Android app.</div></div>
<div class="col-12"><label for="request-description" class="form-label">Problem description</label><textarea id="request-description" name="description" class="form-control" rows="3" maxlength="2000" placeholder="Describe symptoms or injuries and when they started">{{ old('description',$record->description) }}</textarea></div>
<div class="col-12"><button id="request-live-location" class="btn btn-outline-primary" type="button">Share live location</button> <span id="request-location-status" role="status">Location sharing off.</span><input id="request-location-time" type="hidden" name="location_captured_at" value="{{ old('location_captured_at',$record->location_captured_at) }}"></div>
<details class="col-12 form-details"><summary>GPS / manual coordinates</summary><div class="row g-3">@foreach(['latitude','longitude'] as $key)<div class="col-md-6"><label class="form-label" for="request-{{ $key }}">{{ ucfirst($key) }} (GPS or manual)</label><input id="request-{{ $key }}" name="{{ $key }}" class="form-control" type="number" step="any" value="{{ old($key,$record->$key) }}"></div>@endforeach</div></details>
@include('crud.form-actions',['cancelUrl'=>route('care.requests.index'),'saveLabel'=>$record->exists ? 'Update' : 'Save'])</form></div></div></div></div>
@endsection

@section('js')
<script>
(() => {
 const problem=document.getElementById('request-problem'), preference=document.getElementById('request-preference');
 const show=()=>{document.getElementById('hospital-preference').hidden=preference.value!=='Hospital';document.getElementById('request-hospital').disabled=preference.value!=='Hospital';document.getElementById('ambulance-preference').hidden=preference.value!=='Ambulance';};
 problem.addEventListener('change',()=>{preference.value=problem.value==='Internal'?'Ambulance':'Hospital';show();});
 preference.addEventListener('change',show);show();
 let watch=null;
 const button=document.getElementById('request-live-location'), status=document.getElementById('request-location-status');
 button.addEventListener('click',()=>{
   if(watch!==null){navigator.geolocation.clearWatch(watch);watch=null;button.textContent='Share live location';status.textContent='Location sharing stopped.';return;}
   if(!navigator.geolocation){status.textContent='GPS is unavailable. Enter your location manually.';return;}
   status.textContent='Waiting for location permission and GPS…';
   watch=navigator.geolocation.watchPosition(position=>{
     document.getElementById('request-latitude').value=position.coords.latitude;
     document.getElementById('request-longitude').value=position.coords.longitude;
     document.getElementById('request-location-time').value=Math.floor(position.timestamp/1000);
     const location=document.getElementById('request-location');
     if(!location.value || location.dataset.gps==='yes'){location.value='GPS '+position.coords.latitude.toFixed(6)+', '+position.coords.longitude.toFixed(6);location.dataset.gps='yes';}
     status.textContent='Live GPS updated '+new Date(position.timestamp).toLocaleTimeString()+' (accuracy '+Math.round(position.coords.accuracy)+' m).';
   },error=>{status.textContent=error.code===1?'Location permission denied. Enter location manually.':'Unable to get GPS. Retry or enter location manually.';navigator.geolocation.clearWatch(watch);watch=null;button.textContent='Share live location';},{enableHighAccuracy:true,maximumAge:0,timeout:25000});
   button.textContent='Stop location sharing';
 });
 document.getElementById('request-location').addEventListener('input',event=>event.target.dataset.gps='no');
 window.addEventListener('pagehide',()=>{if(watch!==null)navigator.geolocation.clearWatch(watch);});
})();
</script>
@endsection
