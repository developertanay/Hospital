@extends('layouts.header')

@section('title')
Personel Information
@endsection

@section('content')
		<!--start page wrapper -->
	<div class="page-wrapper">
			<div class="page-content">
				<!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<!-- <div class="breadcrumb-title pe-3">Personal Information</div> -->
					<h4>PERSONAL INFORMATION</h4>
				</div>
			  <!--end breadcrumb-->

			  <!--start stepper one--> 
			   
			   
				<div id="stepper1" class="bs-stepper">
				 
				 </div>
				<!--end stepper one--> 

                
				<!--start stepper two--> 
			    <hr>
				<div id="stepper2" class="bs-stepper">
					<div class="card">
					  <div class="card-header">
						  <div class="d-lg-flex flex-lg-row align-items-lg-center justify-content-lg-between" role="tablist">
							  <div class="step" data-target="#test-nl-1">
								<div class="step-trigger" role="tab" id="stepper2trigger1" aria-controls="test-nl-1">
								  <div class="bs-stepper-circle"><i class='bx bx-user fs-4'></i></div>
								  <div class="">
									  <h5 class="mb-0 steper-title">Personal Info</h5>
									  <p class="mb-0 steper-sub-title">Enter Your Details</p>
								  </div>
								</div>
							  </div>
							  <div class="bs-stepper-line"></div>
							  <div class="step" data-target="#test-nl-2">
								  <div class="step-trigger" role="tab" id="stepper2trigger2" aria-controls="test-nl-2">
									<div class="bs-stepper-circle"><i class='bx bx-file fs-4'></i></div>
									<div class="">
										<h5 class="mb-0 steper-title">Academics Details</h5>
										<p class="mb-0 steper-sub-title">Setup Academics Details</p>
									</div>
								  </div>
								</div>
							  {{--<div class="bs-stepper-line"></div>
							
							  <div class="step" data-target="#test-nl-3">
								  <div class="step-trigger" role="tab" id="stepper2trigger3" aria-controls="test-nl-3">
									<div class="bs-stepper-circle"><i class='bx bxs-graduation fs-4'></i></div>
									<div class="">
										<h5 class="mb-0 steper-title">Course/Subjects</h5>
										<p class="mb-0 steper-sub-title">Subject Details</p>
									</div>
								  </div>
								</div>
								--}}
								<div class="bs-stepper-line"></div>
								  <div class="step" data-target="#test-nl-4">
									  <div class="step-trigger" role="tab" id="stepper2trigger4" aria-controls="test-nl-4">
									  <div class="bs-stepper-circle"><i class='bx bx-briefcase fs-4'></i></div>
									  <div class="">
										  <h5 class="mb-0 steper-title">Documents</h5>
										  <p class="mb-0 steper-sub-title">Submit your documents</p>
									  </div>
									  </div>
								  </div>
							</div>
					  </div>
					  <div class="card-body">
					  
						<div class="bs-stepper-content">
                            
						  <form id="myForm"  action="{{route($current_menu.'.update',$data->id)}}" method="POST"  enctype="multipart/form-data" >
						  	@csrf
							<div id="test-nl-1" role="tabpanel" class="bs-stepper-pane" aria-labelledby="stepper2trigger1">
							  <h5 class="mb-1">Your Personal Information</h5>
							  <p class="mb-4">Enter your personal information</p>
  
							  <div class="row g-3">
								  <div class="col-12 col-lg-2">
									  <label for="Name" class="form-label">Name<font color="red"><b>*</b></font></label>
									  <input type="text" class="form-control" name="name" value="{{$data->name}}" placeholder="Enter Your Name" required>
								  </div>
								  <div class="col-12 col-lg-2">
									  <label for="FatherName" class="form-label">Father's Name<font color="red"><b>*</b></font></label>
									  <input type="text" class="form-control" name="father_name" value="{{$data->father_name}}" placeholder="Enter Your Father's Name" required>
								  </div>
								  <div class="col-12 col-lg-2">
									  <label for="Mother Name" class="form-label">Mother's Name<font color="red"><b>*</b></font></label>
									  <input type="text" class="form-control" name="mother_name" value="{{$data->mother_name}}" placeholder="Enter Your Mother's Name" required>
								  </div>

								  <div class="col-12 col-lg-2">
									  <label for="Contact Number" class="form-label">Contact Number<font color="red"><b>*</b></font></label>
									  <input type="text" class="form-control" name="contact_no" value="{{$data->contact_no}}" placeholder="Enter Your Contact  Number" required>
								  </div>
								   <div class="col-12 col-lg-2">
									  <label for="Parent Contact Number" class="form-label">Parent's Contact Number</label>
									  <input type="text" class="form-control" name="parent_contact_no" value="{{$data->parent_contact_no}}" placeholder="Enter Your Parent's Contact  Number">
								  </div>
								   <div class="col-12 col-lg-2">
									  <label for="DOB" class="form-label">Date of Birth<font color="red"><b>*</b></font></label>
									  <input type="text" class="form-control date-format" name="dob" value="{{$data->dob}}" placeholder="Select Date of Birth" required>
								  </div>
								

							  
								<div class="col-12 col-lg-2">
										<label for="single-select-clear-field" class="form-label">Gender<font color="red"><b>*</b></font></label>
										 <select class="form-select single-select-clear-field" name="gender_id"  data-placeholder="Choose Gender" required>
											 <option></option>
											 @foreach($gender_mast as $key => $value) 
                                             @if($key==$data->gender_id)
										<option value="{{$key}}" selected>{{$value}}</option>
                                        @else
                                        <option value="{{$key}}" >{{$value}}</option>
                                        @endif
										@endforeach
										 </select>
									 </div>
									 <div class="col-12 col-lg-2">
										<label for="single-select-clear-field" class="form-label">Category<font color="red"><b>*</b></font></label>
										 <select class="form-select single-select-clear-field" name="category_id"  data-placeholder="Choose Category" required>
											 <option></option>
											 @foreach($category_mast as $key => $value) 
										@if($key==$data->category_id)
                                             <option value="{{$key}}" selected>{{$value}}</option>
                                             @else
                                             <option value="{{$key}}">{{$value}}</option>
										@endif
                                             @if($key==$data->current_state_id)

										<option value="{{$key}}" selected>{{$value}}</option>
                                        @else
                                        <option value="{{$key}}">{{$value}}</option>
                                        @endif
                                             @endforeach


										 </select>
									 </div>
									  

								  <div class="col-12 col-lg-4">
									  <label for="InputEmail" class="form-label">E-mail Address<font color="red"><b>*</b></font></label>
									  <input type="text" class="form-control" value="{{$data->email}}" name="email" value ="{{$data->email}}" placeholder="Enter Email Address"required>
								  </div>
								  <div class="col-12 col-lg-2">
									<label class="form-label">Profile Photo</label>
									<input class="form-control" type="file" name="image">
								</div>
							
							
								<div class="col-12 col-lg-8">
									<label class="form-label">Current Address<font color="red"><b>*</b></font></label>
									<input type="text" name="current_address"  id="current_address"  value="{{$data->current_address}}" class="form-control" placeholder="Enter Current Address" required>
								</div>
								<div class="col-12 col-lg-2">
										<label for="single-select-clear-field" class="form-label">Current State<font color="red"><b>*</b></font></label>
										 <select class="form-select single-select-clear-field" name="current_state_id" id="current_state" data-placeholder="Choose Current State" required>
											 <option></option>
											 @foreach($state_mast as $key => $value) 
                                             @if($key==$data->current_state_id)

										<option value="{{$key}}" selected>{{$value}}</option>
                                        @else
                                        <option value="{{$key}}">{{$value}}</option>
                                        @endif
										@endforeach
										 </select>
									 </div>

								
									<div class="col-12 col-lg-2">
									<label class="form-label">Current PinCode<font color="red"><b>*</b></font></label>
									<input type="text" name="current_pincode" value="{{$data->current_pincode}}" id="current_pin_code" class="form-control" placeholder="Enter Current Pin Code" required>
								</div>

								<!-- <div class="form-group row"> -->
								<div class="col-12 col-lg-12">
									<input type="checkbox" id="address_same" value="1" name="address_same_flag" onclick="check_address_checkbox()">
      								<label for="address_same">Is Permanent Address Same As Current Address ?</label>
									
								</div>
							<!-- </div> -->

								<div class="col-12 col-lg-8">
									<label class="form-label">Permanent Address<font color="red"><b>*</b></font></label>
									<input type="text" name="permanent_address" value="{{$data->permanent_address}}" id="permanent_address"  class="form-control" placeholder="Enter Permanent Address" required>
								</div>
								<div class="col-12 col-lg-2">
										<label for="single-select-clear-field" class="form-label">Permanent State<font color="red"><b>*</b></font></label>
										 <select class="form-select single-select-clear-field" name="permanent_state_id" id="permanent_state"  data-placeholder="Choose Permanent State" required>
											 <option></option>
											 @foreach($state_mast as $key => $value) 
                                             @if($key==$data->permanent_state_id)
										<option value="{{$key}}" selected>{{$value}}</option>
                                        @else
                                        <option value="{{$key}}">{{$value}}</option>
                                        @endif
										@endforeach
										 </select>
									 </div>
									
							
								
								<div class="col-12 col-lg-2">
									<label class="form-label">Permanent PinCode<font color="red"><b>*</b></font></label>
									<input type="text"  name="permanent_pincode" value="{{$data->permanent_pincode}}" id="permanent_pin_code"  class="form-control" placeholder="Enter Permanent Pin Code" required>
								</div>
									
								<div class="row g-3">
                                <div class="col-12 col-lg-3">
                                          
                                        <label for="single-select-clear-field" class="form-label">College<font color="red"><b>*</b></font></label>
                                        <select class="form-select single-select-clear-field" name="college_id" data-placeholder="Choose College" required>
                                            <option></option>
                                            @foreach ($college_mast as $key => $value)
                                                @if ($key == $data->college_id)
                                                    <option value="{{ $key }}" selected>{{ $value }}</option>
                                                @else
                                                    <option value="{{ $key }}">{{ $value }}</option>
                                                @endif
                                            @endforeach
                                        </select>
                                    </div>


									 <div class="col-12 col-lg-3">
										<label for="single-select-clear-field" class="form-label">Course<font color="red"><b>*</b></font></label>
										 <select class="form-select single-select-clear-field" name="course_id"  data-placeholder="Choose Course " required>
											 <option></option>
											 @foreach($course_mast as $key => $value)
                                             @if(!empty($data->course_id) && ($key==$data->course_id))
											<option value="{{$key}}" selected>{{$value}}</option>
											@else
											<option value="{{$key}}">{{$value}}</option>
											@endif
										@endforeach
										 </select>
									 </div>
									 <div class="col-12 col-lg-2">
									<label class="form-label">Semester<font color="red"><b>*</b></font></label>
									<input type="text" name="semester" value="{{$data->semester}}" class="form-control" placeholder="Enter Semester" required>
								</div>
							 
							 <div class="col-12 col-lg-2">
									  <label for="CuetScore" class="form-label">Enrollment Number<font color="red"><b>*</b></font></label>
									  <input type="text" class="form-control" value="{{$data->enrolment_no}}" name="enrolment_no" placeholder="Enter Enrollment Number" required>
								  </div>
									<div class="col-12 col-lg-2">
									  <label for="CuetScore" class="form-label">CUET Score<font color="red"><b>*</b></font></label>
									  <input type="text" class="form-control" name="cuet_score" value="{{$data->cuet_score}}" placeholder="Enter CUET Score" required>
								  </div>

								  <div class="col-12 col-lg-6">
									  <button type="button" class="btn btn-primary px-4" id="nextButton" onclick="stepper1.next()">Next<i class='bx bx-right-arrow-alt ms-2'></i></button>
								  </div>
							  </div>
							  	</div>
							  </div>
                              </form>
							  	<!---end row-->
							  
  
							<div id="test-nl-2" role="tabpanel" class="bs-stepper-pane" aria-labelledby="stepper2trigger2">
							<button class="btn btn-success" id="addRowBtn" style="float:right;">Add Row</button>

							<h5 class="mb-1">Academics Details</h5>
										<p class="mb-4">Enter Your Academics Details.</p>


                            <form id="myForm2"  action="{{route('update_basic_details' )}}" method="post"  enctype="multipart/form-data" >
                                            @csrf
                                            <input type="hidden" name="id" value="{{$data->id}}">
                                            <input type="hidden" name="upload_file" value="true">

										<div class="row g-3">
											<div class="col-12 col-lg-2">
												<label for="single-select-clear-field" class="form-label">Qualification<font color="red"><b>*</b></font></label>
												<select class="form-select single-select-clear-field" name="qualification[0]" data-placeholder="Choose Qualification" required>
													<option></option>
													@foreach($qualification_mast as $key => $value)
														<option value="{{$key}}">{{$value}}</option>
													@endforeach
												</select>
											</div>
											<div class="col-12 col-lg-2">
												<label for="Board/University Name" class="form-label">Board/University<font color="red"><b>*</b></font></label>
												<input type="text" class="form-control" name="board_university_name[0]" placeholder="Enter Your Board/University Name" required>
											</div>
											<div class="col-12 col-lg-2">
												<label for="School/College Name" class="form-label">School/College<font color="red"><b>*</b></font></label>
												<input type="text" class="form-control" name="school_college_name[0]" placeholder="Enter Your School/University Name" required>
											</div>
											<div class="col-12 col-lg-1">
												<label for="marks" class="form-label">Marks(%)<font color="red"><b>*</b></font></label>
												<input type="text" class="form-control" name="marks[0]" placeholder="Enter Your Marks(in %)" required>
											</div>
											<div class="col-12 col-lg-1">
												<label for="passing_year" class="form-label">Year<font color="red"><b>*</b></font></label>
												<input type="text" class="form-control" name="passing_year[0]" placeholder="Enter Your Passing Year" required>
											</div>
											<div class="col-12 col-lg-2">
												<label for="document" class="form-label">Upload Marksheet<font color="red"><b>*</b></font></label>
												<input class="form-control" style="width: 150%;" type="file" name="document[0]" required>
											</div>
											<div  id="additionalRowsContainer" style ="margin-top:15px;">
											
										</div>
											<div  style ="margin-top:15px;" class="col-12 ">
												<div class="d-flex align-items-center gap-3">
													<button class="btn btn-outline-secondary px-4" onclick="stepper2.previous()"><i class='bx bx-left-arrow-alt me-2'></i>Previous</button>
													<button type="button" class="btn btn-primary px-4" id="nextbutton2" onclick="stepper2.next()">Next<i class='bx bx-right-arrow-alt ms-2'></i></button>
												</div>
										    </div>
									    </div>
										</form>
								    </div>

							<!---end row-->
							  <div>
							</div>
						</div>
  
							{{--<div id="test-nl-3" role="tabpanel" class="bs-stepper-pane" aria-labelledby="stepper2trigger3">
							  <h5 class="mb-1">Select Course/Subjects</h5>
							  <p class="mb-4">Select your course and subject category wise</p>

							  <div class="row g-3">
								  <div class="col-12 col-lg-12">
										<label for="single-select-clear-field" class="form-label">College<font color="red"><b>*</b></font></label>
										 <select class="form-select single-select-clear-field" name="college"  data-placeholder="Choose College" required>
											 <option></option>
											 @foreach($college_mast as $key => $value)
											<option value="{{$key}}">{{$value}}</option>
										@endforeach
										 </select>
									 </div>

									 <div class="col-12 col-lg-12">
										<label for="single-select-clear-field" class="form-label">Course<font color="red"><b>*</b></font></label>
										 <select class="form-select single-select-clear-field" name="course"  data-placeholder="Choose Course" required>
											 <option></option>
											 @foreach($course_mast as $key => $value)
											<option value="{{$key}}">{{$value}}</option>
										@endforeach
										 </select>
									 </div>

									 <div class="col-12 col-lg-12">
									<label class="form-label">Semester<font color="red"><b>*</b></font></label>
									<input type="text" name="semester"  class="form-control" placeholder="Enter Your Semester" required>
								</div>

								 <div class="col-md-12">
										<label for="multiple-select-field" class="form-label">Select SEC Subjects</label>
										<select class="form-select multiple-select-field" name="sec_subjects[]" data-placeholder="Choose SEC Subjects" multiple required>
											<option value="">Select</option>
										@foreach($SEC as $key => $value)
										<option value="{{$key}}">{{$value}}</option>
										@endforeach
										</select>
									</div>
									
									<div class="col-12">
									  <div class="d-flex align-items-center gap-3">
										  <button class="btn btn-outline-secondary px-4" onclick="stepper1.previous()"><i class='bx bx-left-arrow-alt me-2'></i>Previous</button>
										  <button class="btn btn-primary px-4" onclick="stepper2.next()">Next<i class='bx bx-right-arrow-alt ms-2'></i></button>
									  </div>
								  </div>

							  </div><!---end row-->
							  
							</div>--}}
  
							<div id="test-nl-4" role="tabpanel" class="bs-stepper-pane"  aria-labelledby="stepper2trigger4">
							<button class="btn btn-success" id="addRowBtn_docx" style="float:right;">Add Row</button>
							  <h5 class="mb-1">Upload Documents</h5>
							  <p class="mb-4">Upload all the relevant documents you have.</p>

							  <form id="myForm3"  action="{{route('update_documents' )}}" method="post"  enctype="multipart/form-data" >
                                            @csrf
											<input type="hidden" name="id" value="{{$data->id}}">
							  <div class="row g-3">
								  <div class="col-12 col-lg-4">
										<label for="single-select-clear-field" class="form-label">Document-1<font color="red"><b>*</b></font></label>
										 <select class="form-select single-select-clear-field" name="document_type[0]"  data-placeholder="Choose one document type" required>
											 <option></option>
											 @foreach($document_mast as $key => $value)
											<option value="{{$key}}">{{$value}}</option>
										@endforeach
										 </select>
									 </div>
								  <div class="col-12 col-lg-4">
									<label class="form-label">Upload Here<font color="red"><b>*</b></font></label>
									<input class="form-control" style="width: 150%;" type="file" name="document1[0]" required>
								</div>
								<div id="additionalRowsContainer_dox">
                                       </div>
									 


								  <div class="col-12">
									  <div class="d-flex align-items-center gap-3">
										  <button class="btn btn-primary px-4" onclick="stepper2.previous()"><i class='bx bx-left-arrow-alt me-2'></i>Previous</button>
										  <button type="submit" class="btn btn-success px-4" id="nextbutton3"  onclick="stepper3.next()">Submit</button>
									  </div>
								  </div>
							  </div><!---end row-->
							</div>
						  </form>
						</div>
						 
					  </div>
					 </div>
                     
                    </div>
				  <!--end stepper two--> 


				

				  
			</div>
		</div>
		<!--end page wrapper -->
@endsection



@section('js')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

    <script type="text/javascript">
		jQuery(function ($) {        
		  	$('form').bind('submit', function () {
		    	$(this).find(':input').prop('disabled', false);
		  	});
		});
function check_address_checkbox() {
			var checkbox_status = document.getElementById('address_same').checked;
			// console.log(checkbox_status);
			if(checkbox_status == true) {
				//disable filled permanent address
				var current_address = document.getElementById('current_address').value;
				var current_state = document.getElementById('current_state').value;
				var current_pin_code = document.getElementById('current_pin_code').value;
				if(current_address == '' || current_state == '' || current_pin_code =='') {
					alert('Please fill current address, state and pin code.');
					document.getElementById('address_same').checked = false;
				}
				else {
					document.getElementById('permanent_address').value = current_address;
					document.getElementById('permanent_address').readOnly = true;
					$('#permanent_state').val(current_state);
					$('#permanent_pin_code').value(current_pin_code);
					document.getElementById('permanent_pin_code').readOnly = true;
					document.querySelector("#permanent_state").disabled = true;

					document.getElementById('current_address').readOnly = true;
					document.getElementById('current_pin_code').readOnly = true;
					document.querySelector("#current_state").disabled = true;

					// $('#permanent_state');
					// var ddl = document.getElementById('permanent_state');
					// var opts = ddl.options.length;
					// for (var i=0; i<opts; i++){
					//     if (ddl.options[i].value == 'ANDHRA PRADESH'){
					//         ddl.options[i].selected = true;
					//         break;
					//     }
					// }


					// document.getElementById('permanent_state').options[current_state] = true;
				}

			} else {
				document.getElementById('permanent_address').value = '';
				document.getElementById('permanent_address').readOnly = false;
				$('#permanent_state').val('');
				$('#permanent_pin_code').val('');
				document.getElementById('permanent_pin_code').readOnly = false;
				document.querySelector("#permanent_state").disabled = false;

				document.getElementById('current_address').readOnly = false;
				document.getElementById('current_pin_code').readOnly = false;
				document.querySelector("#current_state").disabled = false;
			}
		}

	</script>
	<script>
    const addRowButton = document.getElementById('addRowBtn');
    const additionalRowsContainer = document.getElementById('additionalRowsContainer');
    let rowCounter = 1;

    addRowButton.addEventListener('click', () => {
        rowCounter++;

        const newRow = document.createElement('div');
        newRow.className = 'row g-6 additional-row';
        newRow.id = `additionalRow_${rowCounter}`;

        newRow.innerHTML = `
            <div class="col-12 col-lg-2">
                <label for="single-select-clear-field" class="form-label">Qualification</label>
                <select class="form-select single-select-clear-field" name="qualification[${rowCounter}]" data-placeholder="Choose Qualification" >
                    <option></option>
                    @foreach($qualification_mast as $key => $value)
                        <option value="{{$key}}">{{$value}}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-12 col-lg-2">
                <label for="Board/University Name" class="form-label">Board/University</label>
                <input type="text" class="form-control" name="board_university_name[${rowCounter}]" placeholder="Enter Your Board/University Name">
            </div>
            <div class="col-12 col-lg-2">
                <label for="School/College Name" class="form-label">School/College</label>
                <input type="text" class="form-control" name="school_college_name[${rowCounter}]" placeholder="Enter Your School/University Name" >
            </div>
            <div class="col-12 col-lg-1">
                <label for="marks" class="form-label">Marks(%)</label>
                <input type="text" class="form-control" name="marks[${rowCounter}]" placeholder="Enter Your Marks(in %)" >
            </div>
            <div class="col-12 col-lg-1">
                <label for="passing_year" class="form-label">Year</label>
                <input type="text" class="form-control" name="passing_year[${rowCounter}]" placeholder="Enter Your Passing Year" >
            </div>
            <div class="col-12 col-lg-2">
                <label class="form-label" style="width: 300%;">Upload Marksheet</label>
                <input class="form-control" type="file" name="document[${rowCounter}]" >
            </div>
            <div class="col-12 col-lg-1" style="margin-left: 70px;">
                <div class="d-flex align-items-center justify-content-end gap-6">
                    <button class="btn btn-danger" style="margin-top: 28px;" onclick="removeRow(${rowCounter})">Remove</button>
                </div>
            </div>
        `;

        additionalRowsContainer.appendChild(newRow);
    });

    function removeRow(rowNumber) {
        const rowToRemove = document.getElementById(`additionalRow_${rowNumber}`);
        additionalRowsContainer.removeChild(rowToRemove);
    }
</script>
<script>
        const addRowButtonDocx = document.getElementById('addRowBtn_docx');
        const additionalRowsContainerDocx = document.getElementById('additionalRowsContainer_dox');
        var rowCounterDocx = 1;

        addRowButtonDocx.addEventListener('click', () => {
            rowCounterDocx++;

            const newRowDocx = document.createElement('div');
            newRowDocx.className = 'row g-6 additional-row';
            newRowDocx.id = `additionalRow_${rowCounterDocx}`;

            newRowDocx.innerHTML = `
                <div class="row g-3">
                    <div class="col-12 col-lg-4">
                        <label for="single-select-clear-field" class="form-label">Document-${rowCounterDocx + 1}<font color="red"><b>*</b></font></label>
                        <select class="form-select single-select-clear-field" name="document_type[${rowCounterDocx}]"  data-placeholder="Choose one document type" required>
                            <option></option>
                            @foreach($document_mast as $key => $value)
                                <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-lg-4">
                        <label class="form-label">Upload Here<font color="red"><b>*</b></font></label>
                        <input class="form-control" style="width: 150%;" type="file" name="document1[${rowCounterDocx}]" required>
                    </div>
                    <div class="col-12 col-lg-4">
                        <button class="btn btn-danger" style="margin-top: 27px; margin-left: 240px;" onclick="removeRowDocx(${rowCounterDocx})">Remove</button>
                    </div>
                </div>
            `;

            additionalRowsContainerDocx.appendChild(newRowDocx);
        });

        function removeRowDocx(rowNumber) {
            const rowToRemove = document.getElementById(`additionalRow_${rowNumber}`);
            additionalRowsContainerDocx.removeChild(rowToRemove);
        }
    </script>
    <script>
$(document).ready(function () {
    $('#nextButton').click(function () {
        var formData = new FormData($('#myForm')[0]);
		$( "#overlay" ).show();
        $.ajax({
            type: 'POST',
            url: "{{url('updateprofilestep1' , $data->id)}}",
            // url: $('#myForm').attr('action'),
            data: formData,
			cache: false,
        contentType: false,
        processData: false,
            success: function (response) {
               
                stepper2.next();
            },
            error: function (error) {
            },
                complete: function () {
                    // Hide the loader after the AJAX call is complete
                    $("#overlay").hide();
                }
        });
    });
});
</script>

<script>
$(document).ready(function () {
    $('#nextbutton2').click(function () {
        var formData = new FormData($('#myForm2')[0]);
		// console.log(formData);
        $("#overlay").show();
		$.ajax({
            type: 'POST',
            url: $('#myForm2').attr('action'),
            // url: {{route("update_basic_details")}},
            
            data: formData,
			cache: false,
        contentType: false,
        processData: false,
		
            success: function (response) {
               
                stepper3.next();
            },
            error: function (error) {
            },
                complete: function () {
                    // Hide the loader after the AJAX call is complete
                    $("#overlay").hide();
                }
        });
    });
});
</script>

<script>
$(document).ready(function () {
    $('#nextbutton3').click(function () {
        var formData = new FormData($('#myForm3')[0]);
			cache: false,
        contentType: false,
        processData: false,
		$("#overlay").show();

        $.ajax({
            type: 'post',
            url: $('#myForm3').attr('action'),
            // url: {{route("update_basic_details")}},
            
            data: formData,
			cache: false,
        contentType: false,
        processData: false,
            success: function (response) {
               
                stepper3.next();
            },
            error: function (error) {
            },
                complete: function () {
                    // Hide the loader after the AJAX call is complete
                    $("#overlay").hide();
                }
        });
    });
});
</script>

	@endsection

