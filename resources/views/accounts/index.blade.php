@extends('layouts.header')
@section('title','Accounts & Hospital Access')
@section('content')
<div class="page-wrapper crud-page"><div class="page-content"><div class="card"><div class="card-body">
@include('crud.list-header',['heading'=>'Accounts & Hospital Access','addUrl'=>route('Accounts.create'),'addLabel'=>'Add Account'])
<div class="table-responsive"><table class="table table-striped table-bordered crud-table"><thead><tr><th>S.No.</th><th>Name</th><th>Login</th><th>Role</th><th>Hospital</th><th>Status</th><th>Action</th></tr></thead><tbody>
@forelse($accounts as $account)<tr><td>{{ $loop->iteration }}</td><td>{{ $account->name }}</td><td>{{ $account->email }}</td><td>{{ $account->care_role ? ucfirst($account->care_role) : 'Unassigned' }}</td><td>{{ $account->hospital->hospital_name ?? ($account->care_role==='hospital' ? 'Assignment required' : '—') }}</td><td>{{ $account->care_enabled ? 'Active' : 'Disabled' }}</td><td>@include('crud.actions',['editUrl'=>route('Accounts.edit',$account->id),'deleteUrl'=>$account->id !== auth()->id() && $account->care_enabled ? route('Accounts.destroy',$account->id) : null,'deleteLabel'=>'Disable','confirmation'=>'Disable this account?'])</td></tr>@empty<tr><td colspan="7">No accounts.</td></tr>@endforelse
</tbody></table></div><p>Assign each hospital operator to an existing hospital. Account disabling preserves patient and inventory records.</p>
</div></div></div></div>
@endsection
