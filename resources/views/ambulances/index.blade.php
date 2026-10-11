@extends('layouts.header')
@section('title','Ambulance Details')
@section('content')
<div class="page-wrapper crud-page"><div class="page-content"><div class="card"><div class="card-body">
@include('crud.list-header',['heading'=>'Ambulance Details','addUrl'=>route('Ambulance.create'),'addLabel'=>'Add Ambulance'])
@if(session('message'))<div class="alert alert-success">{{ session('message') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="table-responsive"><table class="table table-striped table-bordered crud-table">
<thead><tr><th>S.No.</th><th>Photo</th><th>Vehicle number</th><th>Category</th><th>Operation</th><th>Doctor</th><th>Driver</th><th>Status</th><th>Action</th></tr></thead>
<tbody>@forelse($ambulances as $ambulance)<tr><td>{{ $loop->iteration }}</td>
<td>@if($ambulance->photo_path)<img src="{{ route('Ambulance.photo',[$ambulance->id,'photo']) }}" alt="Ambulance" width="80" height="60" style="object-fit:cover">@else No photo @endif</td>
<td>{{ $ambulance->vehicle_number }}</td><td>{{ optional($ambulance->category)->name }}</td>
<td>{{ $ambulance->operation_type === 'hospital' ? 'Hospital associated' : 'Private' }}<br>
{{ $ambulance->operation_type === 'hospital' ? optional($ambulance->hospital)->hospital_name : $ambulance->operator_name }}
@if($ambulance->operator_contact)<br>{{ $ambulance->operator_contact }}@endif</td>
<td>{{ $ambulance->doctor_name ?: 'Not assigned' }}<br>{{ $ambulance->doctor_contact }}</td>
<td>{{ $ambulance->driver_name }}<br>{{ $ambulance->driver_contact }}</td>
<td>{{ $ambulance->status == 1 ? 'Active' : 'Inactive' }}</td>
<td>@include('crud.actions',['editUrl'=>route('Ambulance.edit',$ambulance->id),'deleteUrl'=>route('Ambulance.destroy',$ambulance->id),'confirmation'=>'Delete this ambulance?'])</td>
</tr>@empty<tr><td colspan="9">No ambulances added yet.</td></tr>@endforelse</tbody>
</table></div></div></div></div></div>
@endsection
