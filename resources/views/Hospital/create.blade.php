@extends('layouts.header')

@section('title')
Add Hospital
@endsection

@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		
		<div class="row">
			<div class="col-xl-12 mx-auto">
				<div class="card">
					<div class="card-body p-4">
						<h5 class="mb-4">Add New Hospital</h5>

						<form class="row g-3" action="{{route($current_menu.'.store')}}" method="POST" enctype="multipart/form-data">
									@csrf
									<div class="col-md-2">
										<label class="form-label">Hospital Name<font color="red"><b>*</b></font></label>
										<input type="text" name="name" class="form-control"  placeholder="Enter Name" maxlength="50" required >
									</div>
									<div class="col-md-2">
										<label class="form-label">Short Name</label>
										<input type="text" name="short_name" class="form-control"  placeholder="Enter Name" maxlength="50" >
									</div>
									<div class="col-md-2">
										<label class="form-label">Registration No.</label>
										<input type="text" name="Registration" class="form-control"  placeholder="Enter Number" maxlength="50" >
									</div>
									<div class="col-md-2">
										<label class="form-label">Type</label>
										<input type="text" name="type" class="form-control"  placeholder="Enter Type" maxlength="50" >
									</div>
									<div class="col-md-2">
										<label class="form-label">Host</label>
										<input type="text" name="host" class="form-control"  placeholder="Enter Host" maxlength="50" >
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
										<label class="form-label">Upload Image</label>
										<input class="form-control" style="width: 150%;" type="file" name="image" >
									</div>
									
									<div class="col-md-7">
										<label class="form-label">Address</label>
										<input type="text" name="address" id="address" class="form-control" placeholder="Enter Address">
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
										<label class="form-label">Pincode</label>
										<input type="text" name="pincode" id="pincode"  class="form-control" placeholder="Enter Pincode">
									</div>
									<div class="col-md-3">
											<label class="form-label">Website URL</label>
											<input type="text" name="url" id="url"  class="form-control" placeholder="Enter Website URL">
										</div>
									<div class="col-md-3">
											<label class="form-label">Geo Location</label>
											<input type="text" name="geo_location" id="geo_location"  class="form-control" placeholder="Enter Geo Location">
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
	</div>
</div>
<!--end page wrapper -->

@push('scripts')
@endpush

@endsection