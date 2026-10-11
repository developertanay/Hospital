@extends('layouts.header')
@section('title', 'Assign Role Modules')
@section('content')
<div class="page-wrapper crud-page"><div class="page-content">
    <form class="card" method="POST" action="{{ route('ModuleAssigning.store') }}">
        @csrf
        <input type="hidden" name="role_id" value="{{ $role->id }}">
        <div class="card-body p-4">
            <h5 class="mb-4">Assign modules to {{ $role->name }}</h5>
            <p>Select the modules this role should see. Clearing every selection removes its menu access.</p>
            @if($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <div class="table-responsive"><table class="table table-striped table-bordered">
                <thead><tr><th>Assigned</th><th>Module</th><th>Parent</th></tr></thead>
                <tbody>@foreach($modules as $module)
                    <tr><td><input type="checkbox" name="module_ids[]" value="{{ $module->id }}"
                        {{ in_array($module->id, old('module_ids', $assigned)) ? 'checked' : '' }}></td>
                        <td>{{ $module->name }}</td><td>{{ optional($modules->firstWhere('id', $module->parent_id))->name }}</td></tr>
                @endforeach</tbody>
            </table></div>
            @include('crud.form-actions',['cancelUrl'=>url('ModuleAssigning'),'saveLabel'=>'Save assignments'])
        </div>
    </form>
</div></div>
@endsection
