@extends('layouts.header')

@section('title')
ROLES
@endsection

@section('content')
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
				<!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<h3>ROLES</h3>
					<div class="ms-auto">
						<div class="btn-group">
							<button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#importFileModal">Upload File</button>

							<!-- Import File Modal -->
							<div class="modal fade" id="importFileModal" tabindex="-1" aria-labelledby="importFileModalLabel" aria-hidden="true">
							    <div class="modal-dialog">
							        <div class="modal-content">
							            <form action="{{ route('category.importData') }}" method="POST" enctype="multipart/form-data">
							                @csrf
							                @method('POST')
							                <div class="modal-header">
							                    <h5 class="modal-title" id="importFileModalLabel">Upload CSV File</h5>
							                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							                </div>
							                <div class="modal-body">
							                    <div class="form-group">
							                        <!-- Styled file input -->
							                        <div class="input-group mb-3">
							                            <div class="col-md-6">
							                            	<label class="custom-file-label" for="excelFile">Choose CSV File</label>
							                            </div>
							                            <div class="col-md-6">
							                                <input type="file" class="custom-file-input form-control" id="excelFile" name="excelFile" accept=".csv" required>
							                                
							                            </div>
							                        </div>
							                    </div>
							                </div>
							                <div class="modal-footer">
							                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
							                    <button type="submit" class="btn btn-success">Upload</button>
							                </div>
							            </form>
							        </div>
							    </div>
							</div>
							<a href="{{url('RoleMast/create')}}" class="btn btn-primary">Add Roles</a>
							
						</div>
					</div>
				</div>
				<!--end breadcrumb-->
				
				<div class="card">
					<div class="card-body">
						<div class="table-responsive">
							<table id="example2" class="table table-striped table-bordered">
								<thead>
									<tr>
								<th>S.No.</th>
								<th>Role</th>
								<th>Status</th>
								<th>Action</th>
							</tr>								
                        </thead>
						<tbody>
		        			<?php 
								$i=0; 
							?>
		
							@foreach($data as $key => $value)
								<?php 
									// dd($value);
									$encid = Crypt::encryptString($value->id);
									
								?>
							<tr>
								<td>{{++$i}}</td>
								<td>{{$value->name}}</td>
								<td>{{($value->status==1)?'Active':'Inactive'}}</td>
								<td>
									@if(!empty($value->college_id))
									<a href="{{$current_menu.'/'.$encid.'/edit'}}">
										<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/><path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/></svg>
									</a>
									&nbsp;&nbsp;&nbsp;
									<form action="{{ route($current_menu.'.destroy', $encid) }}" method="POST" class="d-inline">
                					@csrf
					                @method('DELETE')
					                <button class="btn btn-link" type="submit"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="red" class="bi bi-trash" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/>
									</svg></button>
					            	</form>
									@endif								
								</td>
							
							</tr>
							@endforeach

					</tbody>
								
					</table>
				</div>
			</div>
		</div>
	</div>
</div>
		<!--end page wrapper -->
@endsection