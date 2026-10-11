@extends('layouts.header')
@section('title', $title)
@section('content')
<div class="page-wrapper crud-page"><div class="page-content"><div class="card"><div class="card-body">
@include('crud.list-header',['heading'=>$title,'addUrl'=>route($menu.'.create'),'addLabel'=>$menu === 'Department' ? 'Add Department' : ($menu === 'AmbulanceCategory' ? 'Add Ambulance Category' : 'Add Hospital Department Mapping')])
@if(session('message'))<div class="alert alert-success">{{ session('message') }}</div>@endif
@if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
<div class="table-responsive"><table class="table table-striped table-bordered crud-table">
<thead><tr><th>S.No.</th>@if($field)<th>Name</th>@else<th>Hospital</th><th>Department</th>@endif<th>Status</th><th>Action</th></tr></thead>
<tbody>@forelse($rows as $row)<tr><td>{{ $loop->iteration }}</td>
@if($field)<td>{{ $row->{$field} }}</td>@else<td>{{ optional($row->hospital)->hospital_name }}</td><td>{{ optional($row->department)->department_name }}</td>@endif
<td>{{ $row->status == 1 ? 'Active' : 'Inactive' }}</td>
<td>@include('crud.actions',['editUrl'=>route($menu.'.edit',$row->id),'deleteUrl'=>route($menu.'.destroy',$row->id)])</td></tr>
@empty<tr><td colspan="{{ $field ? 4 : 5 }}">No records found.</td></tr>@endforelse</tbody></table></div>
</div></div></div></div>
@endsection
