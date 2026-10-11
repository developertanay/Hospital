@extends('layouts.header')
@section('title','CareRoute Administration')
@section('content')
<div class="page-wrapper"><div class="page-content"><div class="card"><div class="card-body">
<h3>{{ $page==='users'?'Demo Users & Complete Flow':'Modules & Role Assignments' }}</h3>
<p>Admin manages hospital records, inventories, users and module assignments. Emergency care actions belong to patient and hospital operator roles.</p>
<a class="btn btn-primary mb-3" href="{{ url('emergency') }}">Back to Dashboard</a>
@if($page==='users')
<p>Demo password for these newly seeded accounts: <strong>CareRoute@2026</strong>.</p>
<table class="table table-bordered"><thead><tr><th>User</th><th>Login email</th><th>Role / Hospital</th></tr></thead><tbody>@foreach($accounts as $a)<tr><td>{{ $a->name }}</td><td>{{ $a->email }}</td><td>{{ config('demo.accounts')[$a->email]['role'] }} {{ config('demo.accounts')[$a->email]['hospital'] }}</td></tr>@endforeach</tbody></table>
<p>Admin manages account access. Patients submit emergency requests and track their own reservations. Hospital operators verify, accept and record arrivals for their hospital.</p>
@else
<h5>Working demo module matrix</h5><table class="table table-bordered"><thead><tr><th>Group</th><th>Module</th><th>Admin</th><th>Hospital</th><th>Patient</th></tr></thead><tbody>@foreach(config('demo.modules') as $m)<tr><td>{{ $m['group'] }}</td><td><a href="{{ url($m['url']) }}">{{ $m['name'] }}</a></td>@foreach(['admin','hospital','patient'] as $role)@php($roleAccount=$accounts->first(fn($a)=>config('demo.accounts')[$a->email]['role']===$role))@php($moduleRecord=$modules->firstWhere('url',$m['url']))<td>{{ in_array($role, $m['roles']) && $roleAccount && $moduleRecord && $assignments->where('role_id',$roleAccount->role_id)->where('modules_id',$moduleRecord->id)->isNotEmpty()?'Assigned':'—' }}</td>@endforeach</tr>@endforeach</tbody></table>
<p>Assignments are persisted in the existing <code>modules</code> and <code>module_assigning</code> tables. Role rules limit administration to management modules. Hospital operators and patients have their own care workflow pages.</p>
<a class="btn btn-outline-primary" href="{{ url('ModuleAssigning') }}">Legacy Module Assigning</a> <a class="btn btn-outline-primary" href="{{ url('ModuleMaster') }}">Legacy Module Catalogue</a>
@endif
<div class="alert alert-info mt-3">Legacy MySQL bed inventory is separate from the seeded emergency JSON demo. Change reservation status through the Emergency Dashboard to demonstrate matching and acceptance.</div>
</div></div></div></div>
@endsection
