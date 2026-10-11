@extends('layouts.header')
@section('title',$record->exists ? 'Edit Emergency Request' : 'Add Emergency Request')
@section('content')
<div class="page-wrapper"><div class="page-content"><div class="card"><div class="card-body p-4"><h5>{{ $record->exists ? 'Edit' : 'Add' }} Emergency Request</h5>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form class="row g-3" method="POST" action="{{ $record->exists ? route('care.requests.update',$record->id) : route('care.requests.store') }}">@csrf @if($record->exists) @method('PUT') @endif
@foreach(['patient'=>'Patient name / ID','location'=>'Current location'] as $key=>$label)<div class="col-md-6"><label for="request-{{ $key }}" class="form-label">{{ $label }}</label><input id="request-{{ $key }}" name="{{ $key }}" class="form-control" required maxlength="{{ $key==='patient' ? 80 : 120 }}" value="{{ old($key,$record->$key) }}"></div>@endforeach
<div class="col-md-6"><label for="request-care" class="form-label">Care type</label><select id="request-care" class="form-select" name="care" required><option value="">Select care type</option>@foreach($careTypes as $care)<option @if(old('care',$record->care)===$care) selected @endif>{{ $care }}</option>@endforeach</select></div>
<div class="col-md-6"><label for="request-severity" class="form-label">Reported severity</label><select id="request-severity" class="form-select" name="severity" required><option value="">Select severity</option>@foreach(['Critical','Urgent','Stable'] as $severity)<option @if(old('severity',$record->severity)===$severity) selected @endif>{{ $severity }}</option>@endforeach</select></div>
@foreach(['latitude','longitude'] as $key)<div class="col-md-6"><label class="form-label" for="request-{{ $key }}">{{ ucfirst($key) }} (optional)</label><input id="request-{{ $key }}" name="{{ $key }}" class="form-control" type="number" step="any" value="{{ old($key,$record->$key) }}"></div>@endforeach
<div class="col-12"><button class="btn btn-primary">{{ $record->exists ? 'Update' : 'Save' }}</button> <a class="btn btn-outline-secondary" href="{{ route('care.requests.index') }}">Cancel</a></div></form></div></div></div></div>
@endsection
