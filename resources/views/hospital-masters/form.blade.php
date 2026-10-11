@extends('layouts.header')
@section('title', $title)
@section('content')
<div class="page-wrapper crud-page"><div class="page-content"><div class="card"><div class="card-body p-4">
<h5 class="mb-4">{{ $record->exists ? 'Edit' : 'Add' }} — {{ $title }}</h5>
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<form class="row g-3" method="POST" action="{{ $record->exists ? route($menu.'.update',$record->id) : route($menu.'.store') }}">
@csrf @if($record->exists) @method('PUT') @endif
@if($field)
<div class="col-md-6"><label class="form-label" for="master-name">Name <span class="text-danger">*</span></label><input id="master-name" class="form-control" name="{{ $field }}" value="{{ old($field,$record->{$field}) }}" maxlength="100" required></div>
@else
<div class="col-md-4"><label class="form-label" for="master-hospital">Hospital <span class="text-danger">*</span></label><select id="master-hospital" class="form-select" name="hospital_id" required><option value="">Select hospital</option>@foreach($hospitals as $hospital)<option value="{{ $hospital->id }}" {{ old('hospital_id',$record->hospital_id) == $hospital->id ? 'selected' : '' }}>{{ $hospital->hospital_name }}</option>@endforeach</select></div>
<div class="col-md-4"><label class="form-label" for="master-department">Department <span class="text-danger">*</span></label><select id="master-department" class="form-select" name="department_id" required><option value="">Select department</option>@foreach($departments as $department)<option value="{{ $department->id }}" {{ old('department_id',$record->department_id) == $department->id ? 'selected' : '' }}>{{ $department->department_name }}</option>@endforeach</select></div>
@endif
<div class="{{ $field ? 'col-md-6' : 'col-md-4' }}"><label class="form-label" for="master-status">Status</label><select id="master-status" class="form-select" name="status">@foreach([1=>'Active',2=>'Inactive'] as $value=>$label)<option value="{{ $value }}" {{ old('status',$record->status ?? 1) == $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
@include('crud.form-actions',['cancelUrl'=>route($menu.'.index'),'saveLabel'=>$record->exists ? 'Update' : 'Save'])
</form></div></div></div></div>
@endsection
