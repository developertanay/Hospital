@extends('layouts.header')

@section('title')
User Registeration
@endsection

@section('content')
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
				<div id="stepper1" class="bs-stepper">
				  <div class="card">
					<div class="card-header">
						<div class="d-lg-flex flex-lg-row align-items-lg-center justify-content-lg-between" role="tablist">
							<div class="step" data-target="#test-l-1">
							  <div class="step-trigger" role="tab" id="stepper1trigger1" aria-controls="test-l-1">
								<div class="bs-stepper-circle">1</div>
								<div class="">
									<h5 class="mb-0 steper-title">Personal Info</h5>
									<p class="mb-0 steper-sub-title">Enter Your Details</p>
								</div>
							  </div>
							</div>
							<div class="bs-stepper-line"></div>
							<div class="step" data-target="#test-l-2">
								<div class="step-trigger" role="tab" id="stepper1trigger2" aria-controls="test-l-2">
								  <div class="bs-stepper-circle">2</div>
								  <div class="">
									  <h5 class="mb-0 steper-title">Education</h5>
									  <p class="mb-0 steper-sub-title">Educational Details</p>
								  </div>
								</div>
							  </div>
							<div class="bs-stepper-line"></div>
							<div class="step" data-target="#test-l-3">
								<div class="step-trigger" role="tab" id="stepper1trigger3" aria-controls="test-l-3">
								  <div class="bs-stepper-circle">3</div>
								  <div class="">
									  <h5 class="mb-0 steper-title">Work Experience</h5>
									  <p class="mb-0 steper-sub-title">Experience Details</p>
								  </div>
								</div>
							  </div>
							  <div class="bs-stepper-line"></div>
								<div class="step" data-target="#test-l-4">
									<div class="step-trigger" role="tab" id="stepper1trigger4" aria-controls="test-l-4">
									<div class="bs-stepper-circle">4</div>
									<div class="">
										<h5 class="mb-0 steper-title">Additional Details</h5>
										<p class="mb-0 steper-sub-title">Fill Additional Details</p>
									</div>
									</div>
								</div>
						  </div>
					</div>
				    <div class="card-body">
					
					  <div class="bs-stepper-content">
						<form action="{{route($current_menu.'.store')}}" method="POST" enctype="multipart/form-data">
							@csrf
						  <div id="test-l-1" role="tabpanel" class="bs-stepper-pane" aria-labelledby="stepper1trigger1">
							<h5 class="mb-1">Your Personal Information</h5>
							<p class="mb-4">Enter your personal information to get closer to companies</p>

							<div class="row g-3">
								<div class="col-12 col-lg-3">
									<label for="FisrtName" class="form-label">First Name<font color="red"><b>*</b></font></label>
									<input type="text" class="form-control" name="firstname" id="FisrtName" value="{{!empty($user_profile_data->first_name)?$user_profile_data->first_name:''}}" placeholder="First Name" required>
								</div>
								<div class="col-12 col-lg-3">
									<label for="FisrtName" class="form-label">Middle Name</label>
									<input type="text" class="form-control" name="middlename" id="FisrtName" value="{{!empty($user_profile_data->middle_name)?$user_profile_data->middle_name:''}}" placeholder="Middle Name">
								</div>
								<div class="col-12 col-lg-3">
									<label for="LastName" class="form-label">Last Name</label>
									<input type="text" class="form-control" name="lastname" id="LastName" value="{{!empty($user_profile_data->last_name)?$user_profile_data->last_name:''}}" placeholder="Last Name">
								</div>
								<div class="col-12 col-lg-3">
									<label for="AdhaarNo" class="form-label">Adhaar Card No.</label>
									
									<input type="text" class="form-control" name="adhaar_no" id="AdhaarNo" value="{{!empty($user_profile_data->adhaar_no)?$user_profile_data->adhaar_no:''}}" placeholder="Adhaar Number">
								</div>
								<div class="col-12 col-lg-4">
									<label for="PhoneNumber" class="form-label">Phone Number<font color="red"><b>*</b></font></label>
									<input type="text" class="form-control" name="phone_number" value="{{!empty($user_profile_data->contact_no)?$user_profile_data->contact_no:''}}" id="PhoneNumber" placeholder="Phone Number" required>
								</div>
								<div class="col-12 col-lg-4">
									<label for="InputEmail" class="form-label">E-mail Address<font color="red"><b>*</b></font></label>
									<input type="text" class="form-control" name="email" id="InputEmail" value="{{!empty($user_profile_data->email)?$user_profile_data->email:''}}" placeholder="Enter Email Address" required>
								</div>
								<div class="col-12 col-lg-2">
									<label for="single-select-clear-field" class="form-label">Gender<font color="red"><b>*</b></font></label>
									<select class="form-select single-select-clear-field" name="gender" id="gender" data-placeholder="Select Gender" required>
										@foreach($gender as $key =>$value)
										@if((!empty($user_profile_data->gender_id)?$user_profile_data->gender_id:'')==$key)
										<option value="{{$key}}" selected>{{$value}}</option>
										@else
										<option value="{{$key}}">{{$value}}</option>
										@endif
										@endforeach
									  </select>
								</div>
								<div class="col-12 col-lg-2">
								    <label for="PhoneNumber" class="form-label">Date of Birth<font color="red"><b>*</b></font></label>
								    <input type="date" class="form-control" id="dob" name="dob" value="{{!empty($user_profile_data->dob)?$user_profile_data->dob:''}}" placeholder="Date of Birth" required>
								</div>
								<div class="col-12 col-lg-6">
									<label for="InputEmail" class="form-label">Current Address</label>
									<input type="text" class="form-control" name="current_address" value="{{!empty($user_profile_data->current_address)?$user_profile_data->current_address:''}}" id="current_address" placeholder="Enter Address">
								</div>
								<div class="col-12 col-lg-4">
									<label for="single-select-clear-field" class="form-label">State<font color="red"><b>*</b></font></label>
									<select class="form-select single-select-clear-field" name="current_state_id" id="current_state_id" data-placeholder="Select State" required>
										<option ></option>
										@foreach($state_mast as $key =>$value)
										@if((!empty($user_profile_data->current_state_id)?$user_profile_data->current_state_id:'')== $key)
										<option value="{{$key}}" selected>{{$value}}</option>
										@else
										<option value="{{$key}}">{{$value}}</option>
										@endif
										@endforeach
									  </select>
								</div>
							
								<div class="col-12 col-lg-2">
									<label for="InputEmail" class="form-label">Current Pincode</label>
									<input type="text" class="form-control" name="current_pincode" value="{{!empty($user_profile_data->current_pincode)?$user_profile_data->current_pincode:''}}" id="current_pincode" placeholder="Enter Pin Code">
								</div>
								<div class="col-12 col-lg-12">
									<label class="form-check-label" style="margin-top: 10px; font-size: 15px;">Is permanent address same ? </label>
									<input class="form-check-input" type="checkbox" value="1" id="address_same_flag" name="address_same_flag"  style="height: 30px; width: 30px; margin-left: 5px;" onclick="setPermanentAddress(this)">
								</div>
								<div class="col-12 col-lg-6">
									<label for="InputEmail" class="form-label">Permanent Address</label>
									<input type="text" class="form-control" name="permanent_address" value="{{!empty($user_profile_data->permanent_address)?$user_profile_data->permanent_address:''}}" id="permanent_address" placeholder="Enter Address">
								</div>
								<div class="col-12 col-lg-4">
									<label class="form-label">State<font color="red"><b>*</b></font></label>
									<select class="form-select" name="permanent_state_id" id="permanent_state" required>
										<option ></option>
										@foreach($state_mast as $key =>$value)
										@if((!empty($user_profile_data->permanent_state_id)?$user_profile_data->permanent_state_id:'') == $key)
										<option value="{{$key}}" selected>{{$value}}</option>
										@else
										<option value="{{$key}}">{{$value}}</option>
										@endif
										@endforeach
									  </select>
								</div>
								<div class="col-12 col-lg-2">
									<label for="InputEmail" class="form-label">Permanent Pincode</label>
									<input type="text" class="form-control" name="permanent_pincode" value="{{!empty($user_profile_data->permanent_pincode)?$user_profile_data->permanent_pincode:''}}" id="permanent_pincode" placeholder="Enter Pin Code">
								</div>
								<div class="col-12 col-lg-6">
									<button class="btn btn-primary px-4" onclick="stepper1.next()">Next<i class='bx bx-right-arrow-alt ms-2'></i></button>
								</div>
							</div><!---end row-->
							
						  </div>

						  <div id="test-l-2" role="tabpanel" class="bs-stepper-pane" aria-labelledby="stepper1trigger2">

							<h5 class="mb-1">Educational Details</h5>
							<p class="mb-4">Enter Your Educational Details.</p>
								@if(!$candidate_education_data->isEmpty())
								<div class="table-responsive">
								<table id="example1" class="table table-striped table-bordered">
									<thead>
										<tr>
											<th>Level of Education</th>
											<th>Institution</th>
											<th>Course</th>
											<th>Other Courses</th>
											<th>Status</th>
											<th>Year of pasing</th>
										</tr>								
			                        </thead>
									<tbody>
								
								@foreach($candidate_education_data as $key1 => $value2)
								<?php
								// dd($candidate_education_data);
								// dd($value2);
								// dd($qualification_mast);
								$education_level=!empty($qualification_mast[$value2->qualification_type_id])?$qualification_mast[$value2->qualification_type_id]:'';
								$institution=!empty($value2->school_university)?$value2->qualification_type_id:'';
								$course=!empty($course_mast[$value2->course_id])?$course_mast[$value2->course_id]:'';
								?>
								<tr>
									<td>
										<select class="form-select " name="qualification_id[]" id="primary_qualification{{$key1}}" data-placeholder="Select Qualification" required>
										<option></option>
										@foreach($qualification_mast as $key =>$value)
										@if((!empty($value2->qualification_type_id)?$value2->qualification_type_id:'') == $key)
										<option value="{{$key}}" selected>{{$value}}</option>
										@else
										<option value="{{$key}}">{{$value}}</option>
										@endif
										@endforeach
									  </select>
									</td>
									<td>
										<input type="text" class="form-control" name="university[]" value="{{!empty($value2->school_university)?$value2->school_university:''}}" placeholder="Enter School/University" required>
									</td>
									<td>
										<select class="form-select " name="course_id[]" id="course_id{{$key1}}" data-placeholder="Select Course" >
										<option></option>
										@if($value2->course_id == -1)
										<option value="-1" selected>others</option>
										@endif
										@foreach($course_mast as $key =>$value)
										@if((!empty($value2->course_id)?$value2->course_id:'') == $key)
										<option value="{{$key}}" selected>{{$value}}</option>
										@else
										<option value="{{$key}}">{{$value}}</option>
										@endif
										@endforeach
										<option value="-1">others</option>
									  </select>
									</td>
									<td>
										<input class="form-control" type="text" name="other_course[]" id="other_course{{$key1}}" value="{{$value2->other_course_name}}" data-placeholder="Select Course">
									</td>
									<td>
										<select class="form-select " name="qualification_status[]" id="qualification_status{{$key1}}" data-placeholder="Select Status" required>
										@if((!empty($value2->currently_pursuing)?$value2->currently_pursuing:'') == 1 )
										<option ></option>
										<option value="1" selected>Currently Pursuing</option>
										<option value="2">Pass-Out</option>
										@elseif((!empty($value2->currently_pursuing)?$value2->currently_pursuing:'') == 2 )
										<option ></option>
										<option value="1">Currently Pursuing</option>
										<option value="2" selected>Pass-Out</option>
										@else
										<option ></option>
										<option value="1">Currently Pursuing</option>
										<option value="2">Pass-Out</option>
										@endif
									  </select>
									</td>
									<td><input type="number" class="form-control" id="passing_year" name="year_of_passing[]" value="{{!empty($value2->passing_year)?$value2->passing_year:''}}" onkeyup="maxyear();" max="2024" placeholder="Enter Passing Year"></td>
								</tr>
								@endforeach
							</tbody>
							</table>
							<div class="col-12 col-md-1">
									<button type="button" class="btn btn-success" id="addRowBtn" style="margin-bottom:10px" onclick="addNewQualificationRow()"> + ADD MORE</button>
								</div>
								@else
							<div class="row g-3">
								<div class="col-12 col-md-2">
									<label for="" class="form-label">Level of Education<font color="red"><b>*</b></font></label>
									<select class="form-select " name="qualification_id[]" id="primary_qualification" data-placeholder="Select Qualification" required>
										<option ></option>
										@foreach($qualification_mast as $key =>$value)
										@if((!empty($user_education_data->course_id)?$user_education_data->course_id:'') && $key == 3)
										<option value="{{$key}}" selected>{{$value}}</option>
										@else
										<option value="{{$key}}">{{$value}}</option>
										@endif
										@endforeach
									  </select>
								</div>
								<?php
								$college_id=!empty($user_education_data->college_id)?$user_education_data->college_id:null;
								$college=!empty($college_mast[$college_id])?$college_mast[$college_id]:'';
								?>
								<div class="col-12 col-md-2">
									<label class="form-label">School/University<font color="red"><b>*</b></font></label>
									<input type="text" class="form-control" name="university[]" value="{{$college}}" placeholder="Enter School/University" required>
								</div>
								<div class="col-12 col-md-2">
									<label for="" class="form-label">Course</label>
									<select class="form-select " name="course_id[]" id="course_id" data-placeholder="Select Course" onchange="toggleCourseField(this,'other_course_0')">
										<option ></option>
										@foreach($course_mast as $key =>$value)
										@if((!empty($user_education_data->course_id)?$user_education_data->course_id:'') == $key)
										<option value="{{$key}}" selected>{{$value}}</option>
										@else
										<option value="{{$key}}">{{$value}}</option>
										@endif
										@endforeach
										<option value="-1" >others</option>
									</select>
								</div>
								<div class="col-12 col-md-2" id="other_course_0" style="display:none;">
                                    <label  class="form-label" >Course Name</label>
									<input  class="form-control" type="text" name="other_course[]" id="other_course1" data-placeholder="Enter Course">
								</div>
								<div class="col-12 col-md-2">
									<label for="" class="form-label">Status<font color="red"><b>*</b></font></label>
									<select class="form-select " name="qualification_status[]" id="qualification_status" data-placeholder="Select Status" onchange="toggleYearDropdown(this,'year_of_passing_0')" required>
										@if((!empty($user_education_data->status)?$user_education_data->status:'') == 1 )
										<option ></option>
										<option value="1" selected>Currently Pursuing</option>
										<option value="2">Pass-Out</option>
										@elseif((!empty($user_education_data->status)?$user_education_data->status:'') == 2 )
										<option ></option>
										<option value="1">Currently Pursuing</option>
										<option value="2" selected>Pass-Out</option>
										@else
										<option ></option>
										<option value="1">Currently Pursuing</option>
										<option value="2">Pass-Out</option>
										@endif
									  </select>
								</div>
								<div class="col-12 col-md-2" id="year_of_passing_0">
									<label class="form-label">Passing Year<font color="red"><b>*</b></font></label>
									<input type="number" class="form-control" id="passing_year" name="year_of_passing[]" value="{{!empty($user_education_data->passing_year)?$user_education_data->passing_year:''}}" onkeyup="maxyear();" max="2024" placeholder="Enter Passing Year">
								</div>
								<div class="col-12 col-md-3">
									<button type="button" class="btn btn-success" id="addRowBtn" onclick="addNewQualificationRow()"> + Add More Row </button>
								</div>
								@endif
								</div>

								<div  id="additionalRowsContainer" style ="margin-top:15px;"></div>
								<div class="col-12">
									<div class="d-flex align-items-center gap-3" style="margin-top:20px">
										<button class="btn btn-outline-secondary px-4" onclick="stepper1.previous()"><i class='bx bx-left-arrow-alt me-2'></i>Previous</button>
										<button class="btn btn-primary px-4" onclick="stepper1.next()">Next<i class='bx bx-right-arrow-alt ms-2'></i></button>
									</div>
								</div>
							</div><!---end row-->
							
						  </div>

						  <div id="test-l-3" role="tabpanel" class="bs-stepper-pane" aria-labelledby="stepper1trigger3">
							<h5 class="mb-1">Your Work Experience</h5>
							<p class="mb-4">Inform companies about your work experiences</p>
							<p style="font-size: 20px;"><b>Current and Previous Employment Status</b></p>

							@if(count($candidate_experience_data) >0)
							
							<div class="table-responsive">
								<table id="example1" class="table table-striped table-bordered">
									<thead>
										<tr>
											<th>Company</th>
											<th>Title</th>
											<th>Other Titles</th>
											<th>Type</th>
											<th>Status</th>
											<th>From</th>
											<th>Till</th>
										</tr>								
			                        </thead>
									<tbody>
								
								@foreach($candidate_experience_data as $keys => $values)
								<tr>
									<td>
										<input class="form-control" type="text" name="company_name[]" id="company_name_id{{$keys}}" data-placeholder="Enter Name" value="{{!empty($values->company_name)? $values->company_name:''}}">
									</td>
									<td>
									  <select class="form-select " name="company_title_id[]" id="company_title_id{{$keys}}" data-placeholder="Select Title">
										<option ></option>
										@if($values->job_title_id == -1 )
										<option value="-1" selected>others</option>
										@foreach($job_title_mast as $key =>$value)
										@if((!empty($values->job_title_id)?$values->job_title_id:'') == $key)
										<option value="{{$key}}">{{$value}}</option>
										@else
										<option value="{{$key}}">{{$value}}</option>
										@endif
										@endforeach
										@else
										@foreach($job_title_mast as $key =>$value)
										@if((!empty($values->job_title_id)?$values->job_title_id:'') == $key)
										<option value="{{$key}}" selected>{{$value}}</option>
										@else
										<option value="{{$key}}">{{$value}}</option>
										@endif
										@endforeach
										<option value="-1">Other</option>
										@endif
									  </select>
									</td>
									<td>
										@if(!empty($values->other_title_text))
										<input type="text" class="form-control" name="other_title_text[]"  value="{{$values->other_title_text}}">
										@else
										<input type="text" class="form-control"  name="other_title_text[]" placeholder="enter title">
										@endif
									</td>
									<td>
										<select class="form-select " name="company_type_id[]" id="company_type_id{{$keys}}" data-placeholder="Select Title">
										<option ></option>
										@foreach($job_type_mast as $key =>$value)
										@if((!empty($values->job_type_id)?$values->job_type_id:'') == $key)
										<option value="{{$key}}" selected>{{$value}}</option>
										@else
										<option value="{{$key}}">{{$value}}</option>
										@endif
										@endforeach
									  </select>
									</td>.
									<td>
										<select class="form-select " name="employment_status[]" id="employment_status{{$keys}}" data-placeholder="Select Status">
										@if((!empty($values->employment_status)?$values->employment_status:'') == 1)
										<option ></option>
										<option value="1" selected>Currently Pursuing</option>
										<option value="2">Left</option>
										@elseif((!empty($values->employment_status)?$values->employment_status:'') == 2)
										<option ></option>
										<option value="1" >Currently Pursuing</option>
										<option value="2" selected>Left</option>
										@else
										<option ></option>
										<option value="1">Currently Pursuing</option>
										<option value="2">Left</option>
										@endif
									  </select>
									</td>
									
									<td>
									@if(!empty($values->employed_from))
									<input type="number" class="form-control"  name="employed_from[]" id="employed_from{{$keys}}" placeholder="Enter Start Date" value="{{$values->employed_from}}"> 
									@else
									<input type="number" class="form-control"  name="employed_from[]" id="employed_from{{$keys}}" placeholder="Enter Start Date"  required>
									@endif
									</td>

									<td>
									@if(!empty($values->employed_till))
									<input type="number" class="form-control"  name="employed_till[]" id="employed_till{{$keys}}" placeholder="Enter Start Date" value="{{$values->employed_till}}"> 
									@else
									<input type="number" class="form-control"  name="employed_till[]" id="employed_till{{$keys}}" placeholder="Enter Start Date"  required>
									@endif
									</td>
								</tr>
								@endforeach
							</tbody>
						</table>
						<div class="col-12 col-md-3" >
									<button type="button" class="btn btn-success" id="addRowBtn1" onclick="addExperienceRow()">+ADD MORE ROW</button>
								</div>

						@else
							<div class="row g-3">
								<div class="col-12 col-lg-2">
									<label for="" class="form-label">Company</label>
									<input class="form-control " name="company_name[]" id="company_name1" data-placeholder="Select Company">
								</div>
								<div class="col-12 col-lg-2">
									<label for="" class="form-label">Title</label>
									<select class="form-select " name="company_title_id[]" id="company_title_id" data-placeholder="Select Title" onchange="toggleTitleField(this,'other_title0')">
										<option ></option>
										@foreach($job_title_mast as $key =>$value)
										<option value="{{$key}}">{{$value}}</option>
										@endforeach
										<option value="-1">Other</option>
									  </select>
								</div>
								<div class="col-12 col-lg-2" id="other_title0" style="display:none;">
									<label for="" class="form-label">Enter Title</label>
									<input type="text" class="form-control" name="other_title_text[]" id="other_title_id" data-placeholder="Enter Title">
								</div>
								<div class="col-12 col-lg-2">
									<label for="" class="form-label">Type</label>
									<select class="form-select " name="company_type_id[]" id="company_type_id1" data-placeholder="Select Type">
										<option ></option>
										@foreach($job_type_mast as $key =>$value)
										<option value="{{$key}}">{{$value}}</option>
										@endforeach
									  </select>
								</div>
								<div class="col-12 col-md-2">
									<label for="" class="form-label">Employment Status</label>
									<select class="form-select " name="employment_status[]" id="employment_status1" data-placeholder="Select Status">
										<option ></option>
										<option value="1">Currently Pursuing</option>
										<option value="2">Left</option>
									  </select>
								</div>
								<div class="col-12 col-md-2">
									<label for="" class="form-label">From</label>
									<input type="date" class="form-control"  name="employed_from[]" id="employed_from1" placeholder="Enter Start Date" required>
								</div>
								<div class="col-12 col-md-2" id="employed_till">
									<label for="" class="form-label">Till</label>
									<input type="date" class="form-control"  name="employed_till[]" id="employed_till1" onkeyup="maxyear();" max="2024" placeholder="Enter End Date">
								</div>
								<div class="col-12 col-md-3" >
									<button type="button" class="btn btn-success" id="addRowBtn1" onclick="addExperienceRow()">+ADD MORE ROW</button>
								</div>
								@endif
								<div  id="additionalRowsContainer1" style ="margin-top:15px;"></div>
								<div class="col-12">
									<div class="d-flex align-items-center gap-3">
										<button class="btn btn-outline-secondary px-4" onclick="stepper1.previous()"><i class='bx bx-left-arrow-alt me-2'></i>Previous</button>
										<button class="btn btn-primary px-4" onclick="stepper1.next()">Next<i class='bx bx-right-arrow-alt ms-2'></i></button>
									</div>
								</div>
							</div><!---end row-->
							
						  </div>
						  

						  <div id="test-l-4" role="tabpanel" class="bs-stepper-pane" aria-labelledby="stepper1trigger4">
							<h5 class="mb-1">Additional Details</h5>
							<p class="mb-4">Add your  additional skills and  preferences for better experience</p>

							<div class="row g-3">
								<div class="col-12 col-lg-4">
									<label for="multiple-select-field" class="form-label">Skill/s </label>
									<select class="form-select multiple-select-field" name="skills[]" id="skills" data-placeholder="Choose Skills" multiple required onchange="toggleSkillField(this)">
										<option ></option>
						
										@foreach($skill_mast as $key =>$value)
										@if(in_array($key,$candidate_skills_data))
										<option value="{{$key}}" selected>{{$value}}</option>
										@else
										<option value="{{$key}}">{{$value}}</option>
										@endif
										@endforeach
										<option value="-1">other skills</option>
									  </select>
								</div>
								<div class="col-12 col-lg-4" id="additional_skills0" style="display:none;">
									<label for="" class="form-label">Additional Skills</label>
									<input type="text" name="additional_skills" class="form-control" placeholder="Enter skills">
								</div>
								<div class="col-12 col-lg-4">
									<label for="multiple-select-field" class="form-label"
									>Preferred Role/s</label>
									<select class="form-select multiple-select-field" name="preference[]" id="preference" data-placeholder="Select Preferences" multiple required>
										<option ></option>
										@foreach($job_title_mast as $key =>$value)
										@if(in_array($key,$candidate_prefrence_data))
										<option value="{{$key}}" selected>{{$value}}</option>
										@else
										<option value="{{$key}}">{{$value}}</option>
										@endif
										@endforeach
									  </select>
								</div>
								<div class="col-12 col-lg-12">
									<label class="form-label">Career Objective</label>
									<textarea class="form-control" name="career_objective"
									 placeholder="Enter career objective">{{!empty($user_profile_data->career_objective)?$user_profile_data->career_objective:''}}</textarea>
								</div>
								<h5 class="mb-1">Upload Documents(if any)</h5>
								<div class="col-12 col-lg-4">
									<label class="form-label">Degree/Marksheet</label>
									<input type="file" class="form-control" name="degree_marksheet">
									@if(!empty($user_profile_data->degree_marksheet))
									<a href="{{asset($user_profile_data->degree_marksheet)}}">View Attachment</a>
									@endif
								</div>
								<div class="col-12 col-lg-4">
									<label class="form-label">ID Proof</label>
									<input type="file" class="form-control" name="id_proof">
									@if(!empty($user_profile_data->id_proof))
									<a href="{{asset($user_profile_data->id_proof)}}">View Attachment</a>
									@endif
								</div>
								<div class="col-12 col-lg-4">
									<label class="form-label">Professional Certificates</label>
									<input type="file" class="form-control" name="certificates">
									@if(!empty($user_profile_data->additional_certificates))
									<a href="{{asset($user_profile_data->additional_certificates)}}">View Attachment</a>
									@endif
								</div>
								<div class="col-12">
                                <div class="d-flex align-items-center gap-3">
                                    <button type="button" class="btn btn-outline-secondary px-4"
                                        onclick="stepper1.previous()"><i
                                            class='bx bx-left-arrow-alt me-2'></i>Previous</button>
                                    <button type="submit" class="btn btn-success px-4">Submit</button>
                                </div>
							</div>
							
						  </div>
						</form>
					  </div>
					   
					</div>
				   </div>
				 </div>
			</div>
		</div>
				
@endsection
@section('js')
<script>
    let rowCounter = 0;
	function addNewQualificationRow(){
	const addRowButton = document.getElementById('addRowBtn');
    const additionalRowsContainer = document.getElementById('additionalRowsContainer');

        rowCounter++;

        const newRow = document.createElement('div');
        newRow.className = 'row g-1 additional-row';
        newRow.id = `additionalRow_${rowCounter}`;

        newRow.innerHTML = `
        	<div class="row g-3">
				<div class="col-12 col-md-2">
					<label for="" class="form-label">Level of Education<font color="red"><b>*</b></font></label>
					<select class="form-select " name="qualification_id[]" id="primary_qualification" data-placeholder="Select Qualification" required>
						<option ></option>
						@foreach($qualification_mast as $key =>$value)
						<option value="{{$key}}">{{$value}}</option>
						@endforeach
					  </select>
				</div>
				<div class="col-12 col-md-2">
					<label class="form-label">School/University<font color="red"><b>*</b></font></label>
					<input type="text" class="form-control" name="university[]" placeholder="Enter School/University" required>
				</div>
				<div class="col-12 col-md-2">
					<label for="" class="form-label">Course</label>
					<select class="form-select " name="course_id[]" id="course_id" data-placeholder="Select Course" onchange="toggleCourseField(this,'other_course_${rowCounter}')" >
						<option ></option>
						@foreach($course_mast as $key =>$value)
						<option value="{{$key}}">{{$value}}</option>
						@endforeach
						<option value="-1" >others</option>
					  </select>
				</div>
				<div class="col-12 col-md-2" id="other_course_${rowCounter}" style="display:none;">
                    <label  class="form-label" >Course Name</label>
					<input  class="form-control" type="text" name="other_course[]" id="other_course" data-placeholder="Enter Course">
				</div>
				<div class="col-12 col-md-2">
					<label for="" class="form-label">Status<font color="red"><b>*</b></font></label>
					<select class="form-select " name="qualification_status[]" id="qualification_status333" data-placeholder="Select Status" onchange="toggleYearDropdown(this,'year_of_passing_${rowCounter}')" required>
						<option ></option>
						<option value="1">Currently Pursuing</option>
						<option value="2">Pass-Out</option>
					  </select>
				</div>
				<div class="col-12 col-md-1" id="year_of_passing_${rowCounter}">
					<label class="form-label">Passing Year</label>
					<input type="number" class="form-control" id="passing_year" name="year_of_passing[]" onkeyup="maxyear();" max="{{date('Y')}}" >
				</div>
				<div class="col-md-1 col-12" style="margin-top:43px !important;">  
                <button class="btn btn-danger" onclick="removeQualificationRow(${rowCounter})"> - </button>
             
            </div>
				</div>
        `;

        additionalRowsContainer.appendChild(newRow);


	}
     function removeQualificationRow(rowNumber) {
        const rowToRemove = document.getElementById(`additionalRow_${rowNumber}`);
        additionalRowsContainer.removeChild(rowToRemove);
    }


    let rowCounter1 = 0;
    function addExperienceRow(){
    	const addRowButton1 = document.getElementById('addRowBtn1');
        const additionalRowsContainer1 = document.getElementById('additionalRowsContainer1');

        rowCounter1++;

        const newRow1 = document.createElement('div');
        newRow1.className = 'row g-1 additional-row';
        newRow1.id = `additionalExpRow_${rowCounter1}`;

        newRow1.innerHTML = `
    	<div class="row g-3">
			<div class="col-12 col-lg-2">
			    <label for="" class="form-label">Company</label>
				<input class="form-control " name="company_name[]" id="company_name1" data-placeholder="Select Company">
			</div>
			<div class="col-12 col-lg-2">
				<label for="" class="form-label">Title</label>
				<select class="form-select " name="company_title_id[]" id="company_title_id" data-placeholder = "Select Title" onchange="toggleTitleField(this,'other_title${rowCounter1}')">
					<option></option>
					@foreach($job_title_mast as $key =>$value)
					<option value="{{$key}}">{{$value}}</option>
					@endforeach
					<option value="-1">Other</option>
				  </select>
			</div>
			<div class="col-12 col-lg-2" id="other_title${rowCounter1}" style="display:none;">
				<label for="" class="form-label">Enter Title</label>
				<input type="text" class="form-control" name="other_title_text[]" id="other_title_id" data-placeholder="Enter Title">
			</div>
			<div class="col-12 col-lg-2">
				<label for="" class="form-label">Type</label>
				<select class="form-select " name="company_type_id[]" id="company_type_id1" data-placeholder ="Select Type">
					<option ></option>
					@foreach($job_type_mast as $key =>$value)
					<option value="{{$key}}">{{$value}}</option>
					@endforeach
				  </select>
			</div>
			<div class="col-12 col-md-2">
				<label for="" class="form-label">Employment Status</label>
				<select class="form-select " name="employment_status[]" id="employment_status" data-placeholder ="Select Status" >
					<option ></option>
					<option value="1">Currently Pursuing</option>
					<option value="2">Left</option>
				  </select>
			</div>
			<div class="col-12 col-md-1" id="employed_from">
									<label for="" class="form-label">From</label>
									<input type="date" class="form-control"  name="employed_from" placeholder="Enter Start Date" required>
								</div>
								<div class="col-12 col-md-1" id="employed_till">
									<label for="" class="form-label">Till</label>
									<input type="date" class="form-control"  name="employed_till" onkeyup="maxyear();" max="2024" placeholder="Enter End Date">
								</div>
			<div class="col-md-2">  
	        <button class="btn btn-danger"  onclick="removeExperienceRow(${rowCounter1})"> -</button>
            </div>
		</div>
        `;

        additionalRowsContainer1.appendChild(newRow1);
    }

    function removeExperienceRow(rowNumber1) {
        const rowToRemove1 = document.getElementById(`additionalExpRow_${rowNumber1}`);
        additionalRowsContainer1.removeChild(rowToRemove1);
    }

	function setPermanentAddress(checkbox) {
    var is_check = checkbox.checked;
    const current_address = document.getElementById('current_address').value;
    const current_state = document.getElementById('current_state_id').value;
    const current_pincode = document.getElementById('current_pincode').value;
    const current_address_element = document.getElementById('current_address');
    const current_state_element = document.getElementById('current_state_id');
    const current_pincode_element = document.getElementById('current_pincode');
    const permanent_address = document.getElementById('permanent_address');
    const permanent_state = document.getElementById('permanent_state');
    const permanent_pincode = document.getElementById('permanent_pincode');
    console.log(permanent_state.options[1].innerHTML,current_state);
    if (current_state == '' || current_address == '') {
        alert('Enter Current Address and State First');
        checkbox.checked = false;
    } else if (is_check == true) {
        permanent_address.value = current_address;
        permanent_address.readOnly = true;
        permanent_pincode.readOnly = true;
        permanent_pincode.value = current_pincode;
        permanent_state.disabled = true;
        current_address_element.readOnly = true;
        current_pincode_element.readOnly = true;
        current_state_element.disabled = true;
        
        // Find the corresponding option in the dropdown and set it as selected
        for (let i = 0; i < permanent_state.options.length; i++) {
            if (permanent_state.options[i].value == current_state) {
                permanent_state.options[i].selected = true;
                break;
            }
        }
    } else {
        permanent_address.value = '';
        permanent_state.value = '';
        permanent_address.readOnly = false;
        permanent_state.disabled = false;
        permanent_pincode.value = '';
        current_address_element.readOnly = false;
        current_pincode_element.readOnly = false;
        current_state_element.disabled = false;
    }
}

function toggleCourseField(courseDropdown,courseField){

	const course_field = document.getElementById(courseField);

	// Retrieve selected option's text
	const selectedOptionText = courseDropdown.options[courseDropdown.selectedIndex].text;
    
	// checking if selected option is 'others' or not
	if(selectedOptionText === 'others'){
		// if yes show course textfield
		console.log(1);
		course_field.style.display = "block";
		course_field.required = true;
	}else{
		// hide course textfield
		course_field.style.display = 'none';

	}
}

function toggleTitleField(titleDropdown,titleField){

const title_field = document.getElementById(titleField);

// Retrieve selected option's text
const selectedOptionText = titleDropdown.options[titleDropdown.selectedIndex].text;

// checking if selected option is 'others' or not
if(selectedOptionText === 'Other'){
	// if yes show course textfield
	console.log(1);
	title_field.style.display = "block";
	title_field.required = true;
}else{
	// hide course textfield
	title_field.style.display = 'none';

}
}


function toggleYearDropdown(typeDropdown,passingYear) {

	    
        const passing_year = document.getElementById(passingYear);

        // Retrieve the selected option's text
        const selectedOptionText = typeDropdown.options[typeDropdown.selectedIndex].text;

        // Check if the selected text is "Departmental"
        if (selectedOptionText === 'Pass-Out') {
            // If yes, show the department dropdown
            console.log(1);
            passing_year.style.display = 'block';
            passing_year.required = true;
        } else {
            // If no, hide the department dropdown
            passing_year.style.display = 'none';
        }
    }

function toggleSkillField(){

       const skillDropdown = document.getElementById('skills').selectedOptions;

	   const selectedOptions = Array.from(skillDropdown).map(({value}) => value);

	   const skill_field =  document.getElementById('additional_skills0');

	   if(selectedOptions.includes('-1')==true) {
		skill_field.style.display = 'block';
	   }
	   else{
		skill_field.style.display = 'none';
	   }

}
     var today = new Date();
	var twelveYearsAgo = new Date(today.getFullYear() - 12, today.getMonth(), today.getDate());
	var maxDate = twelveYearsAgo.toISOString().split('T')[0];
	document.getElementById('dob').setAttribute('max', maxDate);

function maxyear() {
	var x = document.getElementById("passing_year").value;
    var currentYear = today.getFullYear();
	if(x>currentYear){
		alert('Year Of Passing Can Not Be Greater than Current Year');
		document.getElementById("passing_year").value='';
	}
	else{
    console.log(x);

	}

}

</script>
@endsection