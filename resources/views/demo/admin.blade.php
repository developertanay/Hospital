@extends('layouts.header')
@section('title','Modules & Role Access')
@section('content')
<div class="page-wrapper"><div class="page-content"><div class="card"><div class="card-body"><h3>Modules & Role Access</h3><p>Module definitions and assignments come from the database.</p><div class="table-responsive"><table class="table table-bordered"><thead><tr><th>Group</th><th>Module</th><th>Assigned roles</th></tr></thead><tbody>@foreach($modules as $module)<tr><td>{{ $module->care_group }}</td><td>{{ $module->name }}</td><td>{{ $assignments->where('modules_id',$module->id)->pluck('name')->unique()->implode(', ') }}</td></tr>@endforeach</tbody></table></div><a class="btn btn-primary" href="{{ url('ModuleMaster') }}">Manage Modules</a> <a class="btn btn-outline-primary" href="{{ url('ModuleAssigning') }}">Role Assignments</a> <a class="btn btn-outline-primary" href="{{ route('Accounts.index') }}">Accounts & Hospital Access</a></div></div></div></div>
@endsection
