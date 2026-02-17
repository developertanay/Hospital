@extends('layouts.header')

@section('title')
Student
@endsection

@section('content')
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
				
				<!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div>
						<h3>FACULTY</h3>
					</div>
					<div class="ms-auto">
						<div class="btn-group">
							<button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#importFileModal">Import File</button>

							<!-- Import File  -->
							<div class="modal fade" id="importFileModal" tabindex="-1" aria-labelledby="importFileModalLabel" aria-hidden="true">
							    <div class="modal-dialog">
							        <div class="modal-content">
							            <form action="{{ route('faculty.importData') }}" method="POST" enctype="multipart/form-data">
							                @csrf
							                @method('POST')
							                <div class="modal-header">
							                    <h5 class="modal-title" id="importFileModalLabel">Import CSV File</h5>
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
							                    <button type="submit" class="btn btn-warning">Import</button>
							                </div>
							            </form>
							        </div>
							    </div>
							</div>

							@foreach($data as $key => $value)
								<?php 
									// dd($value);
									$encid = Crypt::encryptString($value->id);
									$college = !empty($value->college_id)?$college_mast[$value->college_id]:'-';
									$department = (!empty($value->department_id) && !empty($department_mast[$value->department_id])) ? $department_mast[$value->department_id] : '-';
								?>

							<!-- Edit Faculty Details Modal -->
							<div class="modal fade" id="editFacultyModal{{$key}}" tabindex="-1" aria-labelledby="editFacultyModalLabel" aria-hidden="true">
							    <div class="modal-dialog">
							        <div class="modal-content">
							            <form action="{{ url('generate_faculty_login') }}" method="POST">
							                @csrf
							                <div class="modal-header">
							                    <h5 class="modal-title" id="editFacultyModalLabel">Generate Login</h5>
							                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
							                </div>

							                <div class="modal-body">
							                    <div class="form-group">
							                        <label for="editFacultyName">Name<font color="red"><b>*</b></font></label>
							                        <input type="text" class="form-control" id="editFacultyName" value="{{$value->firstname.' '.$value->lastname }}" name="name" required>
							                        <input type="hidden" name="faculty_id" value="{{$value->id}}">
							                        <input type="hidden" name="college_id" value="{{$value->college_id}}">
							                        <input type="hidden" name="dept_id" value="{{$value->department_id}}">
							                    </div>
							                    <div class="form-group">
							                        <label for="editFacultyEmail">Email<font color="red"><b>*</b></font></label>
							                        <input type="email" class="form-control" id="editFacultyEmail" value="{{$value->email_id}}" name="email" required>
							                    </div>
							                    <div class="form-group">
							                        <label for="editFacultyMobile">Mobile Number<font color="red"><b>*</b></font></label>
							                        <input type="text" class="form-control" id="editFacultyMobile" value="{{$value->whatsapp_no}}" name="mobile" required>
							                    </div>
							                    <!-- Add more fields as needed -->
							                </div>
							                <div class="modal-footer">
							                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
							                    <button type="submit" class="btn btn-primary">Generate</button>
							                </div>
							            </form>
							        </div>
							    </div>
							</div>
							@endforeach

							<a href="{{route($current_menu.'.create')}}" class="btn btn-primary">ADD FACULTY</a>
							

							  
							{{--
							<button type="button" class="btn btn-primary">Settings</button>
							<button type="button" class="btn btn-primary split-bg-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">	<span class="visually-hidden">Toggle Dropdown</span>
							</button>
							<div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end">	<a class="dropdown-item" href="javascript:;">Action</a>
								<a class="dropdown-item" href="javascript:;">Another action</a>
								<a class="dropdown-item" href="javascript:;">Something else here</a>
								<div class="dropdown-divider"></div>	<a class="dropdown-item" href="javascript:;">Separated link</a>
							</div>
							--}}
						</div>
					</div>
				</div>
				<!--end breadcrumb-->
				
				<div class="card">


					<form action="" method="GET">
						@csrf
						<div class="form-group row">

							
							<div class="col-md-2 p-4">
								<label for="single-select-clear-field" class="form-label">College</label>
								 <select class="form-select single-select-clear-field" name="college_id"  data-placeholder="Choose College" >
									 <option></option>
									 @foreach($college_mast as $key => $value)
										<option value="{{$key}}" {{ old('college_id', request('college_id')) == $key ? 'selected' : '' }}>{{$value}}</option>
											
								@endforeach
								 </select>
							 </div>
							 <div class="col-md-4 p-4">
								<label for="single-select-clear-field" class="form-label">Department</label>
								 <select class="form-select single-select-clear-field" name="department_id"  data-placeholder="Choose Department">
									 <option></option>
									 @foreach($department_mast as $key => $value)
									<option value="{{$key}}" {{ old('department_id',request('department_id')) == $key ? 'selected' : '' }}>{{$value}}</option>
								@endforeach
								 </select>
							 </div>
							 
							 <div class="col-md-4 mt-4 p-4">
							 	<input type="submit" class="btn btn-ligth px-4" value="Find">
							 </div>
							 
						</div>
					</form>
					<button id="send-emails-button" class="btn btn-primary">Send Emails to Selected Faculty</button>
					
					<div class="card-body">
													<form action="{{ route('download_csv') }}" method="post">
						    @csrf
						    <button type="submit" style="float: right;"class="btn btn-warning">CSV</button>
						</form>

						<div class="table-responsive">
							
								<table id="example2" class="table table-striped table-bordered">
									<thead>
										<tr>
											<th>Select</th>
											<th>S.No.</th>
											<th>Faculty Name</th>
											<th>College</th>
											<th>Department</th>
											<th>Email Id</th>
											<th>Mob. Number</th>
											<th>Status</th>
											<th>Action</th>
										</tr>								
			                        </thead>
									<tbody>
		        					<?php $i=0; ?>
		
								@foreach($data as $key => $value)
								<?php 
									// dd($value);
									$encid = Crypt::encryptString($value->id);
									$college = !empty($value->college_id)?$college_mast[$value->college_id]:'-';
									$department = (!empty($value->department_id) && !empty($department_mast[$value->department_id])) ? $department_mast[$value->department_id] : '-';
								?>
								<tr>
									
        
							            <td><input type="checkbox" class="faculty-checkbox" data-id="{{$value->id}}"></td>
							            <!-- Other table cells -->
							    
							        
								<td>{{++$i}}</td>
								<td>{{$value->firstname.' '.$value->middlename.' '.$value->lastname }}</td>
								<td>{{$college}}</td>
								<td>{{$department}}</td>
								<td>{{$value->email_id}}</td>
								<td>{{$value->whatsapp_no}}</td>
								<td>{{($value->status==1)?'Active':'Inactive'}}</td>
									<td>
										<a href="{{$current_menu.'/'.$encid.'/edit'}}">
				                    		<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/><path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/></svg>
				                    	</a>
				                    	@if(empty($value->user_profile_id))
							   	 		<button type="button" class="btn btn-link" data-bs-toggle="modal" data-bs-target="#editFacultyModal{{$key}}" data-faculty-id="{{$encid}}">
						        			<svg width="16" height="16" viewBox="-1 0 20 20" xmlns="http://www.w3.org/2000/svg">
						        				<g id="user" transform="translate(-3 -2)">
						        					<path id="secondary" fill="#2ca9bc" d="M9,15h6a5,5,0,0,1,5,5h0a1,1,0,0,1-1,1H5a1,1,0,0,1-1-1H4a5,5,0,0,1,5-5Z"/><path id="primary" d="M20,20h0a1,1,0,0,1-1,1H5a1,1,0,0,1-1-1H4a5,5,0,0,1,5-5h6A5,5,0,0,1,20,20ZM12,3a4,4,0,1,0,4,4A4,4,0,0,0,12,3Z" fill="none" stroke="#000000" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
								  				</g>
											</svg>
						    			</button>
						    			@endif
				           			
				           			<form action="{{ route($current_menu.'.destroy', $encid) }}" method="POST" class="d-inline">
                					@csrf
					                @method('DELETE')
					                <button class="btn btn-link" type="submit"><svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="red" class="bi bi-trash" viewBox="0 0 16 16"><path d="M5.5 5.5A.5.5 0 0 1 6 6v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm2.5 0a.5.5 0 0 1 .5.5v6a.5.5 0 0 1-1 0V6a.5.5 0 0 1 .5-.5Zm3 .5a.5.5 0 0 0-1 0v6a.5.5 0 0 0 1 0V6Z"/><path d="M14.5 3a1 1 0 0 1-1 1H13v9a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V4h-.5a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1H6a1 1 0 0 1 1-1h2a1 1 0 0 1 1 1h3.5a1 1 0 0 1 1 1v1ZM4.118 4 4 4.059V13a1 1 0 0 0 1 1h6a1 1 0 0 0 1-1V4.059L11.882 4H4.118ZM2.5 3h11V2h-11v1Z"/>
									</svg></button>
					            	</form>
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
		<!--end page wrapper -->
@endsection
@section('js')
<script>
    $(document).ready(function() {
        const selectedFaculty = [];

        $('.faculty-checkbox').change(function() {
            const facultyId = $(this).data('id');
            
            if ($(this).prop('checked')) {
                selectedFaculty.push(facultyId);
            } else {
                const index = selectedFaculty.indexOf(facultyId);
                if (index !== -1) {
                    selectedFaculty.splice(index, 1);
                }
            }
        });

        $('#send-emails-button').click(function() {
            // Use AJAX to send the selectedFaculty array to the backend
            $.ajax({
                type: 'POST',
                url: '/send-emails', // Your route for sending emails
                data: {
                    selectedFaculty: selectedFaculty
                },
                success: function(response) {
                    alert(response.message); // Display success message
                },
                error: function(error) {
                    console.log(error);
                }
            });
        });
    });
</script>
@endsection
@section('js')
<script>
    $(document).ready(function() {
        $('#send-emails-button').click(function() {
            const selectedFacultyIds = [];

            // Iterate through each selected checkbox and get the data-id attribute
            $('.faculty-checkbox:checked').each(function() {
                selectedFacultyIds.push($(this).data('id'));
            });

            if (selectedFacultyIds.length > 0) {
                // Use AJAX to send the selected faculty IDs to the backend
                $.ajax({
                    type: 'POST',
                    url: '{{ route("send.emails") }}', // Use the route URL here
                    data: {
                        selectedFacultyIds: selectedFacultyIds,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        alert(response.message); // Display success message
                    },
                    error: function(error) {
                        console.log(error);
                    }
                });
            } else {
                alert('Please select at least one faculty member.');
            }
        });
    });
</script>

@endsection