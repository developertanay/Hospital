@extends('layouts.header')
@section('title', ($record ? 'Edit ' : 'Add ').($type === 'inventory' ? 'Bed' : 'Blood Inventory'))
@section('content')
<div class="page-wrapper"><div class="page-content"><div class="row"><div class="col-xl-12 mx-auto"><div class="card"><div class="card-body p-4">
<h5 class="mb-4">{{ $record ? 'Edit' : 'Add New' }} {{ $type === 'inventory' ? 'Bed' : 'Blood Inventory' }} · {{ $hospital['name'] }}</h5>
@if($errors->any())<div class="alert alert-danger">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
<form class="row g-3" method="POST" action="{{ $record ? route('hospital.'.$type.'.update', $record) : route('hospital.'.$type.'.store') }}">@csrf @if($record) @method('PUT') @endif
@if($type === 'inventory')
<div class="col-md-6"><label for="bed-name" class="form-label">Bed name <span class="text-danger">*</span></label><input id="bed-name" name="name" class="form-control" required maxlength="60" value="{{ old('name', $entry['name'] ?? $entry['id'] ?? '') }}" placeholder="e.g. ICU Bed 04"></div>
<div class="col-md-6"><label for="bed-care" class="form-label">Care type <span class="text-danger">*</span></label><select id="bed-care" name="care" class="form-select" required><option value="">Select care type</option>@foreach($careTypes as $care)<option @if(old('care', $entry['care'] ?? '') === $care) selected @endif>{{ $care }}</option>@endforeach</select></div>
@else
<div class="col-md-6"><label for="blood-group" class="form-label">Blood group <span class="text-danger">*</span></label><select id="blood-group" name="blood_type" class="form-select" required><option value="">Select blood type</option>@foreach($bloodTypes as $group)<option @if(old('blood_type', $entry['blood_type'] ?? '') === $group) selected @endif>{{ $group }}</option>@endforeach</select></div>
<div class="col-md-6"><label for="blood-units" class="form-label">Available units <span class="text-danger">*</span></label><input id="blood-units" name="units" type="number" class="form-control" min="0" max="10000" required value="{{ old('units', $entry['units'] ?? '') }}"></div>
@endif
<div class="col-12"><button class="btn btn-primary">{{ $record ? 'Update' : 'Save' }}</button> <a class="btn btn-outline-secondary" href="{{ route('hospital.'.$type.'.index') }}">Cancel</a></div>
</form></div></div></div></div></div></div>
@endsection
