@extends('layouts.header')
@section('title', $type === 'inventory' ? 'Bed Inventory' : 'Blood Inventory')
@section('content')
<div class="page-wrapper crud-page"><div class="page-content"><div class="card"><div class="card-body">
<div class="page-breadcrumb d-flex align-items-center mb-3"><div><h3>{{ $type === 'inventory' ? 'Bed Inventory' : 'Blood Inventory' }}</h3><p>{{ $hospital['name'] }}</p></div><div class="ms-auto"><a class="btn btn-primary" href="{{ route('hospital.'.$type.'.create') }}">Add {{ $type === 'inventory' ? 'Bed' : 'Blood Inventory' }}</a></div></div>
@if(session('management_success'))<div class="alert alert-success">{{ session('management_success') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="table-responsive"><table class="table table-striped table-bordered crud-table"><thead><tr><th>S.No.</th><th>{{ $type === 'inventory' ? 'Bed name / ID' : 'Blood group' }}</th><th>{{ $type === 'inventory' ? 'Care type' : 'Available units' }}</th>@if($type === 'inventory')<th>Status</th>@endif<th>Action</th></tr></thead><tbody>
@php($records = $type === 'inventory' ? $hospital['beds'] : $hospital['blood_records'])
@forelse($records as $key => $value)
@php($recordId = $value['id'])
<tr><td>{{ $loop->iteration }}</td><td>{{ $type === 'inventory' ? ($value['name'] ?? $value['id']) : $value['blood_type'] }}</td><td>{{ $type === 'inventory' ? $value['care'] : $value['units'] }}</td>
@if($type === 'inventory')<td><span class="badge {{ ['Available'=>'bg-success','Occupied'=>'bg-danger','Reserved'=>'bg-primary','Temporary'=>'bg-warning text-dark'][$value['status']] ?? 'bg-secondary' }}">{{ $value['status'] }}</span></td>@endif
<td>
@if($type === 'blood' || $value['status'] === 'Available')
@include('crud.actions',['editUrl'=>route('hospital.'.$type.'.edit',$recordId),'deleteUrl'=>route('hospital.'.$type.'.destroy',$recordId),'confirmation'=>'Delete this inventory record?'])
@elseif($value['status'] === 'Occupied')
<form class="d-inline" method="POST" action="{{ url('emergency/manage') }}" onsubmit="return confirm('Discharge patient and free this bed?')">@csrf<input type="hidden" name="action" value="discharge"><input type="hidden" name="bed_id" value="{{ $recordId }}"><button class="btn btn-sm btn-outline-danger">Discharge</button></form>
@else<span class="text-muted">Protected allocation</span>@endif
</td></tr>
@empty<tr><td colspan="{{ $type === 'inventory' ? 5 : 4 }}">No inventory yet.</td></tr>@endforelse
</tbody></table></div>
@if($type === 'inventory')<p class="text-muted">Only available beds can be edited or deleted.</p>@endif
</div></div></div></div>
@endsection
