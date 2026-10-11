@extends('layouts.header')
@section('title','Accounts & Hospital Access')
@section('content')
<div class="page-wrapper"><div class="page-content"><div class="card"><div class="card-body">
<div class="d-flex justify-content-between mb-3"><h3>Accounts & Hospital Access</h3><a href="{{ route('Accounts.create') }}" class="btn btn-primary">Add Account</a></div>
<div class="table-responsive"><table class="table table-striped table-bordered"><thead><tr><th>Name</th><th>Login</th><th>Role</th><th>Hospital</th><th>Status</th><th>Action</th></tr></thead><tbody>
@forelse($accounts as $account)<tr><td>{{ $account->name }}</td><td>{{ $account->email }}</td><td>{{ $account->care_role ? ucfirst($account->care_role) : 'Unassigned' }}</td><td>{{ $account->hospital->hospital_name ?? ($account->care_role==='hospital' ? 'Assignment required' : '—') }}</td><td>{{ $account->care_enabled ? 'Active' : 'Disabled' }}</td><td><a href="{{ route('Accounts.edit',$account->id) }}" class="btn btn-sm btn-outline-primary">Edit</a>@if($account->id !== auth()->id() && $account->care_enabled)<form method="POST" class="d-inline" action="{{ route('Accounts.destroy',$account->id) }}" onsubmit="return confirm('Disable this account?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Disable</button></form>@endif</td></tr>@empty<tr><td colspan="6">No accounts.</td></tr>@endforelse
</tbody></table></div><p>Assign each hospital operator to an existing hospital. Account disabling preserves patient and inventory records.</p>
</div></div></div></div>
@endsection
