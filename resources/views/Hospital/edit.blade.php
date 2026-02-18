@extends('layouts.header')

@section('title')
Edit Hospital
@endsection

@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		
		<div class="row">
			<div class="col-xl-12 mx-auto">
				<div class="card">
					<div class="card-body p-4">
						<h5 class="mb-4">Edit Hospital</h5>

						<form class="row g-3" action="{{ route($current_menu.'.update', $encid) }}" method="POST" enctype="multipart/form-data">
							@csrf
							@method('PATCH')
							
							<div class="col-md-2">
								<label class="form-label">Hospital Name<font color="red"><b>*</b></font></label>
								<input type="text" name="name" class="form-control" value="{{ $data->hospital_name }}" placeholder="Enter Name" maxlength="50" required>
							</div>
							
							<div class="col-md-2">
								<label class="form-label">Short Name</label>
								<input type="text" name="short_name" class="form-control" value="{{ $data->short_name }}" placeholder="Enter Name" maxlength="50">
							</div>
							
							<div class="col-md-2">
								<label class="form-label">Registration No.</label>
								<input type="text" name="Registration" class="form-control" value="{{ $data->registration_no }}" placeholder="Enter Number" maxlength="50">
							</div>
							
							<div class="col-md-2">
								<label class="form-label">Type</label>
								<input type="text" name="type" class="form-control" value="{{ $data->type }}" placeholder="Enter Type" maxlength="50">
							</div>
							
							<div class="col-md-2">
								<label class="form-label">Host</label>
								<input type="text" name="host" class="form-control" value="{{ $data->host }}" placeholder="Enter Host" maxlength="50">
							</div>
							
							<div class="col-md-2">
								<label class="form-label">Contact No<font color="red"><b>*</b></font></label>
								<input type="text" name="contact_no" class="form-control" value="{{ $data->contact_no }}" placeholder="Enter Contact" maxlength="50" required>
							</div>
							
							<div class="col-md-2">
								<label class="form-label">Alternate Contact No</label>
								<input type="text" name="alternate_no" class="form-control" value="{{ $data->emergency_no }}" placeholder="Enter Alternate Contact" maxlength="50">
							</div>
							
							<div class="col-md-2">
								<label class="form-label">Email<font color="red"><b>*</b></font></label>
								<input type="email" name="email" class="form-control" value="{{ $data->email_id }}" placeholder="Enter Email" maxlength="50" required>
							</div>
							
							<div class="col-md-2">
								<label class="form-label">Upload Image</label>
								<input class="form-control" style="width: 150%;" type="file" name="image">
								@if($data->logo)
									<small class="text-muted">Current: <img src="{{ asset('storage/'.$data->logo) }}" width="30" height="30" class="rounded-circle"></small>
								@endif
							</div>
							
							<div class="col-md-7">
								<label class="form-label">Address</label>
								<input type="text" name="address" id="address" class="form-control" value="{{ $data->address }}" placeholder="Enter Address">
							</div>
							
							<div class="col-md-3">
								<label class="form-label">State</label>		
								<select class="form-select single-select-clear-field" name="current_state" id="current_state">
									<option value="">Select</option>
									@foreach($state as $key => $value)
										<option value="{{ $key }}" {{ $data->state == $key ? 'selected' : '' }}>{{ $value }}</option> 
									@endforeach
								</select>
							</div>
							
							<div class="col-md-2">
								<label class="form-label">Pincode</label>
								<input type="text" name="pincode" id="pincode" class="form-control" value="{{ $data->pin_code }}" placeholder="Enter Pincode">
							</div>
							
							<div class="col-md-3">
								<label class="form-label">Website URL</label>
								<input type="text" name="url" id="url" class="form-control" value="{{ $data->website }}" placeholder="Enter Website URL">
							</div>
							
							<div class="col-md-3">
								<label class="form-label">Geo Location</label>
								<input type="text" name="geo_location" id="geo_location" class="form-control" value="{{ $data->geo_location }}" placeholder="Enter Geo Location">
							</div>

							<div class="col-md-3">
								<label class="form-label">Status<font color="red"><b>*</b></font></label>
								<select class="form-select single-select-clear-field" name="status" required>
									<option value="1" {{ $data->status == 1 ? 'selected' : '' }}>Active</option>
									<option value="0" {{ $data->status == 0 ? 'selected' : '' }}>Inactive</option>
								</select>
							</div>
							
							<div class="col-md-12" style="margin-top: 10px;">
								<button type="button" onclick="window.location='{{ url($current_menu) }}'" class="btn btn-light px-4">Cancel</button>
								<button type="submit" style="float: right;" class="btn btn-primary px-4">Update</button>
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
<script>
// Optional: Add any custom JavaScript for edit page
$(document).ready(function() {
    // You can add state change handlers here if needed
});
</script>
@endpush

@endsection