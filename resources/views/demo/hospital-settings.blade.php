@extends('layouts.header')
@section('title','Hospital Settings')
@section('content')
<div class="page-wrapper crud-page"><div class="page-content"><div class="card"><div class="card-body p-4"><h3>{{ $hospital->hospital_name }} · Hospital Settings</h3>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
@if($editing)
<form class="row g-3" method="POST" action="{{ route('hospital.settings.update',$hospital->id) }}">@csrf @method('PUT')
<div class="col-md-6"><label class="form-label" for="settings-status">Accepting requests</label><select id="settings-status" name="status" class="form-select">@foreach(['Open','Offline'] as $status)<option @if(old('status',$hospital->accepting_requests ? 'Open':'Offline')===$status) selected @endif>{{ $status }}</option>@endforeach</select></div>
@foreach(['contact'=>'Contact number','address'=>'Address','latitude'=>'Latitude','longitude'=>'Longitude'] as $field=>$label)
<div class="col-md-6"><label class="form-label" for="settings-{{ $field }}">{{ $label }}</label><input id="settings-{{ $field }}" class="form-control" name="{{ $field }}" @if(in_array($field,['latitude','longitude'])) type="number" step="any" @endif value="{{ old($field,$field==='contact' ? $hospital->contact_no : $hospital->$field) }}"></div>
@endforeach
@include('crud.form-actions',['cancelUrl'=>route('hospital.settings.index'),'saveLabel'=>'Update'])</form>
@else
<table class="table table-bordered"><tr><th>Status</th><td>{{ $hospital->accepting_requests ? 'Open':'Offline' }}</td></tr><tr><th>Contact</th><td>{{ $hospital->contact_no }}</td></tr><tr><th>Address</th><td>{{ $hospital->address }}</td></tr><tr><th>Coordinates</th><td>{{ $hospital->latitude ?? 'Not provided' }} / {{ $hospital->longitude ?? 'Not provided' }}</td></tr></table><a class="btn btn-primary" href="{{ route('hospital.settings.edit',$hospital->id) }}">Edit Hospital Settings</a>
@endif
<p class="mt-3">Offline hospitals are excluded from new recommendations; existing admissions and reservations stay protected.</p>
</div></div></div></div>
@endsection
