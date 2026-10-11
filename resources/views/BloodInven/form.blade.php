@extends('layouts.header')
@section('title',$record->exists ? 'Edit Blood Inventory' : 'Add Blood Inventory')
@section('content')
<div class="page-wrapper"><div class="page-content"><div class="card"><div class="card-body p-4"><h5>{{ $record->exists ? 'Edit' : 'Add' }} Blood Inventory</h5>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form class="row g-3" method="POST" action="{{ $record->exists ? route('BloodInven.update',Crypt::encryptString($record->id)) : route('BloodInven.store') }}">@csrf @if($record->exists) @method('PUT') @endif
<div class="col-md-4"><label class="form-label" for="stock-hospital">Hospital</label><select id="stock-hospital" name="hospital_id" class="form-select"><option value="">Unassigned legacy stock</option>@foreach($hospitals as $hospital)<option value="{{ $hospital->id }}" @if((string)old('hospital_id',$record->hospital_id)===(string)$hospital->id) selected @endif>{{ $hospital->hospital_name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label" for="stock-type">Blood type</label><select id="stock-type" name="blood_id" class="form-select" required>@foreach($types as $type)<option value="{{ $type->id }}" @if((string)old('blood_id',$record->blood_id)===(string)$type->id) selected @endif>{{ $type->blood_type }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label" for="stock-units">Units</label><input id="stock-units" type="number" name="unit" class="form-control" required min="0" max="10000" value="{{ old('unit',$record->unit) }}"></div>
<div class="col-md-4"><label class="form-label" for="stock-status">Status</label><select id="stock-status" name="status" class="form-select"><option value="1" @if(old('status',$record->status ?? 1)==1) selected @endif>Active</option><option value="2" @if(old('status',$record->status)==2) selected @endif>Inactive</option></select></div>
<div class="col-12"><button class="btn btn-primary">{{ $record->exists ? 'Update' : 'Save' }}</button> <a class="btn btn-outline-secondary" href="{{ route('BloodInven.index') }}">Cancel</a></div>
</form></div></div></div></div>
@endsection
