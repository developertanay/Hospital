@extends('layouts.header')

@section('title')
New User
@endsection

@section('content')
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
				<!--breadcrumb-->
				
				<!--end breadcrumb-->
				<div class="row">
					<div class="col-xl-12 mx-auto">
						
						<div class="card">
							<div class="card-body p-4">
								<h5 class="mb-4">Edit Existing User</h5>
								<form class="row g-3" action="{{route($current_menu.'.update',$id)}}" method="POST" enctype="multipart/form-data">
								  @csrf 
                                  @method('PATCH')
										<div class="col-md-2">
									<label class="form-label">Name<font color="red"><b>*</b></font></label>
									<input type="text" name="name" class="form-control" value="{{!empty($data->name)?$data->name:''}}" placeholder="Enter Name" maxlength="50" required readonly>
								</div>
								<div class="col-md-2">
									<label class="form-label">Father's Name</label>
									<input type="text" name="father_name" class="form-control" value="{{!empty($data->father_name)?$data->father_name:''}}" placeholder="Enter Name" maxlength="50" >
								</div>
								<div class="col-md-2">
									<label class="form-label">Mother's Name</label>
									<input type="text" name="mother_name" class="form-control" value="{{!empty($data->mother_name)?$data->mother_name:''}}"  placeholder="Enter Name" maxlength="50" >
								</div>
								<div class="col-md-2">
									<label class="form-label">Date Of Birth</label>
									<input type="date" name="date_of_birth" class="form-control" value="{{!empty($data->dob)?$data->dob:''}}"  placeholder="Enter DOB" maxlength="50" >
								</div>
								<div class="col-md-2">
									<label class="form-label">Contact No<font color="red"><b>*</b></font></label>
									<input type="text" name="contact_no" class="form-control" value="{{!empty($data->contact_no)?$data->contact_no:''}}"  placeholder="Enter Contact" maxlength="50" readonly required>
								</div>
								<div class="col-md-2">
									<label class="form-label">Alternate Contact No</label>
									<input type="text" name="alternate_no" class="form-control" value=""  placeholder="Enter Alternate Contact" maxlength="50" >
								</div>
								<div class="col-md-2">
									<label class="form-label">Email<font color="red"><b>*</b></font></label>
									<input type="text" name="email" class="form-control" value="{{!empty($data->email)?$data->email:''}}"   placeholder="Enter Email" maxlength="50" required>
								</div>
								<div class="col-md-2">
									<label for="single-select-clear-field" class="form-label">Gender</label>
									 <select class="form-select single-select-clear-field" name="gender"  data-placeholder="Choose Gender" >
										 <option></option>
										@foreach($gender_mast as $key => $value)
											@if($key==$data->gender_id)
											<option value="{{$key}}" selected>{{$value}}</option>
											@endif 
											<option value="{{$key}}">{{$value}}</option>
										@endforeach
									 </select>
								</div>
								<div class="col-md-3">
									<label for="single-select-clear-field" class="form-label">Category</label>
									 <select class="form-select single-select-clear-field" name="category"  data-placeholder="Choose Category" >
										<option></option>
										@foreach($category_mast as $key => $value) 
										@if($key==$data->category_id)
											<option value="{{$key}}" selected>{{$value}}</option>
										@endif 
										<option value="{{$key}}">{{$value}}</option>
										@endforeach
									 </select>
								</div>
								<div class="col-md-2">
								<label for="single-select-clear-field" class="form-label">Department</label>
								 <select class="form-select single-select-clear-field" name="department"  data-placeholder="Choose Department">
									 <option></option>
									 @foreach($department_id as $key => $value)
										
										<option value="{{$key}}" >{{$value}}</option>
								@endforeach
								 </select>
							 </div>
							 <?php
							
							$imagePath = !empty($data->image)?$data->image:'';
							?>
							
							<div class="col-md-2">
								<label class="form-label">Upload Image</label>
								<input class="form-control" style="width: 150%;" type="file" name="image">
							</div>
							
							<div class="col-md-2">
								<label class="form-label">Current Image</label>
								<img src="<?php echo $imagePath; ?>" alt="Current Image">
							</div>
							
								<div class="col-md-7">
									<label class="form-label">Current Address</label>
									<input type="text" name="current_address" id="current_address" value="{{!empty($data->current_address)?$data->current_address:'' }}" class="form-control" placeholder="Enter Current Address">
								</div>
								<div class="col-md-3">
									<label for="single-select-clear-field" class="form-label">State</label>		
									<select class="form-select single-select-clear-field" name="current_state" id="current_state" data-placeholder="Choose State"  >
										<option value="">Select</option>
									@foreach($state as $key => $value)
									@if($key==$data->current_state_id)
											<option value="{{$key}}" selected>{{$value}}</option>
									@endif
									
									  	<option value="{{$key}}">{{$value}}</option> 
									@endforeach
									</select>
								</div>
								<div class="col-md-2">
									<label class="form-label">Current Pincode</label>
									<input type="text" name="current_pincode" id="current_pincode" value="{{!empty($data->current_pincode)?$data->current_pincode:'' }}"  class="form-control" placeholder="Enter Pincode">
								</div>
								<div class="col-md-12">
									<?php
									$isAddressSame = !empty($data->address_same_flag)?$data->address_same_flag:'';
									
									// Check if the value is 1 or not set (null), and set the "checked" attribute accordingly
									$isChecked = ($isAddressSame == 1) ? 'checked' : '';
									?>

									<input type="checkbox" id="address_same" value="1" name="address_same" onclick="check_address_checkbox()" <?= $isChecked ?>>
									<label for="address_same">Is Permanent Address Same As Current Address ?</label>
								</div>

								<div class="col-md-7">
									<label class="form-label">Permanent Address</label>
									<input type="text" name="permanent_address" id="permanent_address"  value="{{!empty($data->permanent_address)?$data->permanent_address:''}}" class="form-control" placeholder="Enter Permanent Address">
								</div>
								<div class="col-md-3">
									<label for="single-select-clear-field" class="form-label">State</label>		
									<select class="form-select single-select-clear-field" name="permanent_state" id="permanent_state"  data-placeholder="Choose State"  >
										<option></option>
									@foreach($state as $key => $value)
									@if(!empty($data->permanent_state_id)?$data->permanent_state_id==$key:0)
									<option value="{{$key}}" selected>{{$value}}</option>
									@endif
									  	<option value="{{$key}}">{{$value}}</option> 
									@endforeach
									</select>
								</div>
								<div class="col-md-2">
									<label class="form-label">Permanent Pincode</label>
									<input type="text" name="permanent_pincode" id="permanent_pincode" value="{{!empty($data->permanent_pincode)?$data->permanent_pincode:''}}"  class="form-control" placeholder="Enter Pincode">
								</div>
							<div class="col-md-3">
									<label class="form-label">Joining Year</label>
									<input type="text" name="joining_year" id="joining_year"  class="form-control" value="{{!empty($data->admission_year)?$data->admission_year:''}}" placeholder="Enter Joining Year">
								</div>
							<div class="col-md-3">
									<label class="form-label">Leaving Year</label>
									<input type="text" name="leaving_year" id="leaving_year"  class="form-control" value="{{!empty($data->passout_year)?$data->passout_year:''}}" placeholder="Enter Leaving Year">
								</div>
								<div class="col-md-3">
									<label for="single-select-clear-field" class="form-label">Role<font color="red"><b>*</b></font></label>		
									<select class="form-select single-select-clear-field" name="role" id="role" data-placeholder="Choose Role"  disabled>
										<option value="">Select</option>
									@foreach($role_id as $key => $value)
									@if(!empty($data->role_id)?$data->role_id==$key:0)
									<option value="{{$key}}" selected>{{$value}}</option> 
									@endif
									  	<option value="{{$key}}">{{$value}}</option> 
									@endforeach
									</select>
								</div>
										

								<div class="col-md-3" >
										<label for="single-select-clear-field" class="form-label">Status<font color="red"><b>*</b></font></label>
										 <select class="form-select single-select-clear-field" name="status"  data-placeholder="Choose Status" required>
										    <option value="1"  {{($data->status == 1)?'selected':''}}>Yes</option>
                                            <option value="2"  {{($data->status == 2)?'selected':''}}>No</option>
											 
										 </select>
								</div>
								<div class="col-md-12"style="margin-top: 10px;">
									<button type="button" onclick="window.location='{{url($current_menu)}}'"class="btn btn-light px-4">Cancel</button>
									<button type="submit" style="float: right;" class="btn btn-primary px-4">Submit</button>
								</div>
				</form>
			</div>
		</div>
	</div>	
</div>
@endsection

@section('js')
<script type="text/javascript">
	  jQuery(function ($) {        
      $('form').bind('submit', function () {
        // console.log('1');
        $(this).find('select').prop('disabled', false);
        // alert(1);
      });
    });
	
function check_address_checkbox() {
    var checkbox_status = document.getElementById('address_same').checked;

    if (checkbox_status) {
        var current_address = document.getElementById('current_address').value;
        var current_state = document.getElementById('current_state').value;
        var current_pincode = document.getElementById('current_pincode').value;

        if (current_address === '' || current_state === '' || current_pincode === '') {
            alert('Please fill all Current Address, State, and Pincode.');
            document.getElementById('address_same').checked = false;
        } else {
            document.getElementById('permanent_address').value = current_address;
            document.getElementById('permanent_address').readOnly = true;
            document.getElementById('permanent_pincode').value = current_pincode;
            document.getElementById('permanent_pincode').readOnly = true;
            $('#permanent_state').val(current_state).trigger('change.select2');
            document.querySelector("#permanent_state").disabled = true;

            // Disable editing of current address, state, and pincode
            document.getElementById('current_address').readOnly = true;
            document.getElementById('current_pincode').readOnly = true;
            document.querySelector("#current_state").disabled = true;
        }
    } else {
        document.getElementById('permanent_address').value = '';
        document.getElementById('permanent_address').readOnly = false;
        document.getElementById('permanent_pincode').value = '';
        document.getElementById('permanent_pincode').readOnly = false;
        $('#permanent_state').val('').trigger('change.select2');
        document.querySelector("#permanent_state").disabled = false;

        // Enable editing of current address, state, and pincode
        document.getElementById('current_address').readOnly = false;
        document.getElementById('current_pincode').readOnly = false;
        document.querySelector("#current_state").disabled = false;
    }
}
</script>
@endsection