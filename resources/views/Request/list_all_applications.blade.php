@extends('layouts.header')

@section('title')
All Requests
@endsection

@section('content')
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
				<div class="card">
					<div class="card-body">
						<!--breadcrumb-->
						<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
							<div>
								<h3>Requests Applications</h3>
							</div>
							<div class="ms-auto">
								<div class="btn-group">

								</div>
							</div>
						</div>
						<!--end breadcrumb-->

						<form action="" method="GET">
	                        @csrf
	                       <div class="form-group row" style="margin-bottom: 20px;">
	                            
	                       		<div class="col-md-3">
	                                <label for="single-select-clear-field" class="form-label">Letter / Certificate Type</label>
	                                <select class="form-select single-select-clear-field" name="letter_filter" data-placeholder="Choose Type">
	                                    <option></option>
	                                    @foreach($letters_arr as $key => $value)
	                                        @if(!empty($letter_filter))
	                                        <option value="{{ $key }}" {{ ($letter_filter == $key) ? 'selected' : '' }}>{{ $value }}</option>
	                                        @else
	                                        <option value="{{ $key }}" >{{ $value }}</option>
	                                        @endif
	                                    @endforeach
	                                </select>
	                            </div>

	                            <div class="col-md-3">
	                                <label for="single-select-clear-field" class="form-label">Status</label>
	                                <select class="form-select single-select-clear-field" name="status_filter" data-placeholder="Choose Status">
	                                    <option></option>
	                                    @foreach($status_desc_arr as $key => $value)
	                                        @if(!empty($status_filter))
	                                        <option value="{{ $key }}" {{ ($status_filter == $key) ? 'selected' : '' }}>{{ $value }}</option>
	                                        @else
	                                        <option value="{{ $key }}" >{{ $value }}</option>
	                                        @endif
	                                    @endforeach
	                                </select>
	                            </div>

	                            <div class="col-md-3" style="margin-top: 27px;">
	                                <input type="submit" class="btn btn-outline-primary px-4" value="Find">
	                            </div>
	                        </div>
	                </form>

							<div class="table-responsive">							
								<table id="example2" class="table table-striped table-bordered">
									<thead>
										<tr>
											
											<th>S.No.</th>
											<th>Letter/Certificate</th>
											<th>Requested On</th>
											<th>Requested By</th>
											<th>Roll No.</th>
											<th>Course</th>
											<th>Semester</th>
											<th>Status</th>
											<th>#</th>
										</tr>								
			                        </thead>
									<tbody>
		        					<?php $i=0; ?>
		
								@foreach($applications as $key => $value)
								<?php 
									// dd($value);
									$encid = Crypt::encryptString($value->id);
									$decrypted_id = $value->id;
									$letter_name = $value->letter_name;
									$requested_on = date('d-M-Y h:i A', strtotime($value->created_at));
									$status = $value->status;
									$status_desc = !empty($status_desc_arr[$status])?$status_desc_arr[$status]:'';

									$requested_by_id = $value->users_id;
									if(!empty($student_data[$requested_by_id])) {
										$student_name = $student_data[$requested_by_id]['name'];
										$student_roll_no = $student_data[$requested_by_id]['rno'];
										$student_course = $student_data[$requested_by_id]['course'];
										$student_semester = $student_data[$requested_by_id]['semester'];
										$enrolment_no = $student_data[$requested_by_id]['enrolment_no'];
										$course_id = $student_data[$requested_by_id]['course_id'];
									}
									else {
										$student_name = '';
										$student_roll_no = '';
										$student_course = '';
										$student_semester = '';
										$enrolment_no = '';
										$course_id = '';
									}
									



								?>
								<tr>
									<td>{{++$i}}</td>
									<td>{{$letter_name}}</td>
									<td>{{$requested_on}}</td>
									<td>{{$student_name}}</td>
									<td>{{$student_roll_no}}</td>
									<td>{{substr($student_course,0,30)}}</td>
									<td>{{$student_semester}}</td>
									<td>{{$status_desc}}</td>
									<td><button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modal{{$decrypted_id}}">Action</button></td>	
							</tr>
							 <div class="modal fade" id="modal{{$decrypted_id}}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{url('verify_letter')}}" method="POST">
                                            @csrf
                                            <input type="hidden" name="letter_application_id" value="{{$decrypted_id}}">
                                            <input type="hidden" name="student_name" value="{{$student_name}}">
                                            <input type="hidden" name="student_course" value="{{$student_course}}">
                                            <input type="hidden" name="student_semester" value="{{$student_semester}}">
                                            <input type="hidden" name="student_roll_no" value="{{$student_roll_no}}">
                                            <input type="hidden" name="course_id" value="{{$course_id}}">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editFacultyModalLabel">{{strtoupper($letter_name)}}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="form-group">
                                                	<div class="row">
	                                                    <div class="col-md-3">
	                                                    	<b>Name</b>
	                                                    </div>
	                                                    <div class="col-md-9">
	                                                    	{{$student_name}}
	                                                    </div>
                                                	</div>
                                                	<div class="row">
	                                                    <div class="col-md-3">
	                                                    	<b>Course</b>
	                                                    </div>
	                                                    <div class="col-md-9">
	                                                    	{{--
	                                                    	<span style="text-wrap: wrap;">
	                                                    	--}}

	                                                    	<span style="Word-break:normal; white-space:normal">
	                                                    	{{$student_course}}
	                                                    </span>
	                                                    </div>
                                                	</div>
                                                	<div class="row">
	                                                    <div class="col-md-3">
	                                                    	<b>Semester</b>
	                                                    </div>
	                                                    <div class="col-md-9">
	                                                    	{{$student_semester}}
	                                                    </div>
                                                	</div>
                                                	<div class="row">
	                                                    <div class="col-md-3">
	                                                    	<b>Roll No.</b>
	                                                    </div>
	                                                    <div class="col-md-9">
	                                                    	{{$student_roll_no}}
	                                                    </div>
                                                	</div>
                                                	<div class="row">
	                                                    <div class="col-md-3">
	                                                    	<b>Enrolment No.</b>
	                                                    </div>
	                                                    <div class="col-md-9">
	                                                    	{{$enrolment_no}}
	                                                    </div>
                                                	</div>
                                                	
                                                	<div class="row">
	                                                    <div class="col-md-3">
	                                                    	<b>Requested For</b>
	                                                    </div>
	                                                    <div class="col-md-9">
	                                                    	{{$letter_name}}
	                                                    </div>
                                                	</div>
                                                	<div class="row">
	                                                    <div class="col-md-3">
	                                                    	<b>Requested On</b>
	                                                    </div>
	                                                    <div class="col-md-9">
	                                                    	{{$requested_on}}
	                                                    </div>
                                                	</div>
                                                	{{--
                                                    <center>

                                                        <span style="margin-top: 10px; margin-bottom: 10px;">
                                                        <b>
                                                            Do You really want to request for {{$letter_name}} ?
                                                        </b>
                                                        </span>
                                                    </center>
                                                    --}}
                                                </div>
                                            </div>

                                            
                                            <div class="modal-footer">
                                                <button type="submit" class="btn btn-danger" data-bs-dismiss="modal" name="verification_button" value="5">Reject</button>
                                                <button type="submit" name="verification_button" class="btn btn-success" value="3">Approve</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
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


@endsection