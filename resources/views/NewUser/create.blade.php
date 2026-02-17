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
								<h5 class="mb-4">Add New User</h5>
								<form class="row g-3" action="{{route($current_menu.'.store')}}" method="POST" enctype="multipart/form-data">
									@csrf
									<div class="col-md-2">
										<label class="form-label">Name<font color="red"><b>*</b></font></label>
										<input type="text" name="name" class="form-control"  placeholder="Enter Name" maxlength="50" required >
									</div>
									<div class="col-md-2">
										<label class="form-label">Father's Name</label>
										<input type="text" name="father_name" class="form-control"  placeholder="Enter Name" maxlength="50" >
									</div>
									<div class="col-md-2">
										<label class="form-label">Mother's Name</label>
										<input type="text" name="mother_name" class="form-control"  placeholder="Enter Name" maxlength="50" >
									</div>
									<div class="col-md-2">
										<label class="form-label">Date Of Birth</label>
										<input type="date" name="date_of_birth" class="form-control"  placeholder="Enter DOB" maxlength="50" >
									</div>
									<div class="col-md-2">
										<label class="form-label">Contact No<font color="red"><b>*</b></font></label>

										<input type="text" name="contact_no" class="form-control"  placeholder="Enter Contact" maxlength="50" required>

									</div>
									<div class="col-md-2">
										<label class="form-label">Alternate Contact No</label>
										<input type="text" name="alternate_no" class="form-control"  placeholder="Enter Alternate Contact" maxlength="50" >
									</div>
									<div class="col-md-2">
										<label class="form-label">Email<font color="red"><b>*</b></font></label>
										<input type="text" name="email" class="form-control"  placeholder="Enter Email" maxlength="50" required>

									</div>
									<div class="col-md-2">
										<label for="single-select-clear-field" class="form-label">Gender</label>
										 <select class="form-select single-select-clear-field" name="gender"  data-placeholder="Choose Gender" >
											 <option></option>
											 @foreach($gender_mast as $key => $value) 
										<option value="{{$key}}">{{$value}}</option>
										@endforeach
										 </select>
									</div>
									<div class="col-md-3">
										<label for="single-select-clear-field" class="form-label">Category</label>
										 <select class="form-select single-select-clear-field" name="category"  data-placeholder="Choose Category" >
											 <option></option>
											 @foreach($category_mast as $key => $value) 
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
									<div class="col-md-2">
										<label class="form-label">Upload Image</label>
										<input class="form-control" style="width: 150%;" type="file" name="image" >
									</div>
									<div class="col-md-7">
										<label class="form-label">Current Address</label>
										<input type="text" name="current_address" id="current_address" class="form-control" placeholder="Enter Current Address">
									</div>
									<div class="col-md-3">
										<label for="single-select-clear-field" class="form-label">State</label>		
										<select class="form-select single-select-clear-field" name="current_state" id="current_state" data-placeholder="Choose State"  >
											<option value="">Select</option>
										@foreach($state as $key => $value)
										  	<option value="{{$key}}">{{$value}}</option> 
										@endforeach
										</select>
									</div>
									<div class="col-md-2">
										<label class="form-label">Current Pincode</label>
										<input type="text" name="current_pincode" id="current_pincode"  class="form-control" placeholder="Enter Pincode">
									</div>
									<div class="col-md-12">
										<input type="checkbox" id="address_same" value="1" name="address_same" onclick="check_address_checkbox()">
	      								<label for="address_same">Is Permanent Address Same As Current Address ?</label>
										
									</div>
									<div class="col-md-7">
										<label class="form-label">Permanent Address</label>
										<input type="text" name="permanent_address" id="permanent_address"  class="form-control" placeholder="Enter Permanent Address">
									</div>
									<div class="col-md-3">
										<label for="single-select-clear-field" class="form-label">State</label>		
										<select class="form-select single-select-clear-field" name="permanent_state" id="permanent_state"  data-placeholder="Choose State"  >
											<option></option>
										@foreach($state as $key => $value)
										  	<option value="{{$key}}">{{$value}}</option> 
										@endforeach
										</select>
									</div>
									<div class="col-md-2">
										<label class="form-label">Permanent Pincode</label>
										<input type="text" name="permanent_pincode" id="permanent_pincode"  class="form-control" placeholder="Enter Pincode">
									</div>
									<div class="col-md-3">
											<label class="form-label">Joining Year</label>
											<input type="text" name="joining_year" id="joining_year"  class="form-control" placeholder="Enter Joining Year">
										</div>
									<div class="col-md-3">
											<label class="form-label">Leaving Year</label>
											<input type="text" name="leaving_year" id="leaving_year"  class="form-control" placeholder="Enter Leaving Year">
										</div>
										<div class="col-md-3">
											<label for="single-select-clear-field" class="form-label">Role<font color="red"><b>*</b></font></label>		
											<select class="form-select single-select-clear-field" name="role" id="role" data-placeholder="Choose Role"  >
												<option value="">Select</option>
											@foreach($role_id as $key => $value)
											  	<option value="{{$key}}">{{$value}}</option> 
											@endforeach
											</select>
										</div>
												

										<div class="col-md-3" >
												<label for="single-select-clear-field" class="form-label">Status<font color="red"><b>*</b></font></label>
												 <select class="form-select single-select-clear-field" name="status"  data-placeholder="Choose Status" required>
													 <option value="1">Active</option>
													 <option value="2">Inactive</option>
													 
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
            // Enable editing of current address, state, and pincode
            document.getElementById('current_address').readOnly = false;
            document.getElementById('current_pincode').readOnly = false;
            document.querySelector("#current_state").disabled = false;

            document.getElementById('permanent_address').value = current_address;
            document.getElementById('permanent_address').readOnly = true;
            document.getElementById('permanent_pincode').value = current_pincode;
            document.getElementById('permanent_pincode').readOnly = true;
            $('#permanent_state').val(current_state).trigger('change.select2');
            document.querySelector("#permanent_state").disabled = true;
        }
    } else {
        // Enable editing of current and permanent address, state, and pincode
        document.getElementById('current_address').readOnly = false;
        document.getElementById('current_pincode').readOnly = false;
        document.querySelector("#current_state").disabled = false;

        document.getElementById('permanent_address').value = '';
        document.getElementById('permanent_address').readOnly = false;
        document.getElementById('permanent_pincode').value = '';
        document.getElementById('permanent_pincode').readOnly = false;
        $('#permanent_state').val('').trigger('change.select2');
        document.querySelector("#permanent_state").disabled = false;
    }
}

</script>
@endsection