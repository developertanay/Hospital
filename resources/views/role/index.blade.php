@extends('layouts.header')
@section('title')
Role Assigning
@endsection
@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="card">
            <div class="card-body">
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div>
						<h2>Role And Modules</h2>						
					</div>
					<div class="ms-auto"></div>
				</div>
                <div class="table-responsive">
                    <table class="table table-bordered mt-4" style="margin-left:10px;">
                        <tr>
                            <th>S.no.</th>
                            <th>Role</th>
                            <th>Module</th>
                            <th>Action</th>
                        </tr>
                        <?php
                        $i=0;
                        ?> 
                        @foreach($role_mast as $role_data )
                        <?php
                        $encid= Crypt::encryptString($role_data->id);
                        $role= DB::table('role_default_module')->get();
                        $module_id=!empty($RoleDefaultModule[$role_data->id])?$RoleDefaultModule[$role_data->id]:'-';
                        $module_name=!empty($module[$module_id])?$module[$module_id]:'-';
                        ?>
                        <tr>
                            <td>{{ ++$i }}</td>
                            <td>{{ ($role_data->name) }}</td>
                            <td>{{$module_name}}</td>
                            <td>
                                <form action="{{ route($current_menu.'.edit', $encid)}}" method="GET"  >
                                    <button type="submit" class="btn btn-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor"
                                            class="bi bi-pencil-square" viewBox="0 0 16 16">
                                            <path
                                                d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z" />
                                            <path fill-rule="evenodd"
                                                d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z" />
                                        </svg>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection