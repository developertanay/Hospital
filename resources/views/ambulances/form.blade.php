@extends('layouts.header')
@section('title','Ambulance Details')
@section('content')
<div class="page-wrapper crud-page"><div class="page-content"><div class="card"><div class="card-body p-4">
<h5 class="mb-4">{{ $ambulance->exists ? 'Edit Ambulance' : 'Add Ambulance' }}</h5>
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" enctype="multipart/form-data" action="{{ $ambulance->exists ? route('Ambulance.update',$ambulance->id) : route('Ambulance.store') }}">
@csrf @if($ambulance->exists) @method('PUT') @endif
<div class="row g-3">
<div class="col-md-6"><label class="form-label" for="vehicle_number">Vehicle number *</label><input id="vehicle_number" class="form-control" name="vehicle_number" maxlength="30" value="{{ old('vehicle_number',$ambulance->vehicle_number) }}" placeholder="e.g. DL01AB1234" required></div>
<div class="col-md-6"><label class="form-label" for="ambulance_category_id">Ambulance category *</label><select id="ambulance_category_id" class="form-select" name="ambulance_category_id" required><option value="">Select category</option>@foreach($categories as $category)<option value="{{ $category->id }}" {{ old('ambulance_category_id',$ambulance->ambulance_category_id) == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label" for="operation_type">Operation *</label><select id="operation_type" class="form-select" name="operation_type" required>@foreach(['hospital'=>'Associated with a hospital','private'=>'Run privately'] as $value=>$label)<option value="{{ $value }}" {{ old('operation_type',$ambulance->operation_type ?? 'hospital') === $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
<div class="col-md-6" id="hospital-field"><label class="form-label" for="hospital_id">Associated hospital *</label><select id="hospital_id" class="form-select" name="hospital_id"><option value="">Select hospital</option>@foreach($hospitals as $hospital)<option value="{{ $hospital->id }}" {{ old('hospital_id',$ambulance->hospital_id) == $hospital->id ? 'selected' : '' }}>{{ $hospital->hospital_name }}</option>@endforeach</select></div>
<div class="col-md-6"><label class="form-label" for="operator_name">Owner / operator name</label><input id="operator_name" class="form-control" name="operator_name" maxlength="100" value="{{ old('operator_name',$ambulance->operator_name) }}"></div>
<div class="col-md-6"><label class="form-label" for="operator_contact">Owner / operator contact</label><input id="operator_contact" type="tel" class="form-control" name="operator_contact" maxlength="30" value="{{ old('operator_contact',$ambulance->operator_contact) }}"></div>
<div class="col-md-6"><label class="form-label" for="doctor_name">Doctor name</label><input id="doctor_name" class="form-control" name="doctor_name" maxlength="100" value="{{ old('doctor_name',$ambulance->doctor_name) }}"></div>
<div class="col-md-6"><label class="form-label" for="doctor_contact">Doctor contact</label><input id="doctor_contact" type="tel" class="form-control" name="doctor_contact" maxlength="30" value="{{ old('doctor_contact',$ambulance->doctor_contact) }}"></div>
<div class="col-md-6"><label class="form-label" for="driver_name">Driver name *</label><input id="driver_name" class="form-control" name="driver_name" maxlength="100" value="{{ old('driver_name',$ambulance->driver_name) }}" required></div>
<div class="col-md-6"><label class="form-label" for="driver_contact">Driver contact *</label><input id="driver_contact" type="tel" class="form-control" name="driver_contact" maxlength="30" value="{{ old('driver_contact',$ambulance->driver_contact) }}" required></div>
@foreach(['photo'=>'Ambulance photo','doctor_photo'=>'Doctor photo','driver_photo'=>'Driver photo'] as $field=>$label)
<div class="col-md-4"><label class="form-label" for="{{ $field }}">{{ $label }}</label><input id="{{ $field }}" class="form-control" type="file" name="{{ $field }}" accept="image/jpeg,image/png,image/webp"><small>JPG, PNG or WebP, up to 2 MB.</small>
@if($ambulance->exists && $ambulance->{$field.'_path'})<div class="mt-2"><img src="{{ route('Ambulance.photo',[$ambulance->id,$field]) }}" alt="{{ $label }}" width="120" height="90" style="object-fit:cover"><label class="d-block"><input type="checkbox" name="remove_{{ $field }}" value="1" {{ old('remove_'.$field) ? 'checked' : '' }}> Remove current photo</label></div>@endif</div>
@endforeach
<div class="col-md-6"><label class="form-label" for="status">Status</label><select id="status" class="form-select" name="status">@foreach([1=>'Active',2=>'Inactive'] as $value=>$label)<option value="{{ $value }}" {{ old('status',$ambulance->status ?? 1) == $value ? 'selected' : '' }}>{{ $label }}</option>@endforeach</select></div>
@include('crud.form-actions',['cancelUrl'=>route('Ambulance.index'),'saveLabel'=>$ambulance->exists ? 'Update Ambulance' : 'Save Ambulance'])
</div></form></div></div></div></div>
@endsection
@section('js')
<script>
function updateHospitalField() {
    const associated = document.getElementById('operation_type').value === 'hospital';
    document.getElementById('hospital-field').hidden = !associated;
    document.getElementById('hospital_id').required = associated;
    document.getElementById('hospital_id').disabled = !associated;
}
document.getElementById('operation_type').addEventListener('change', updateHospitalField);
updateHospitalField();
</script>
@endsection
