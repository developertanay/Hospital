@extends('layouts.header')

@section('title')
Blood-Type
@endsection

@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		<!--breadcrumb-->
		<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
						
		</div>
		<!--end breadcrumb-->
		<div class="row">
			<div class="col-xl-12 mx-auto">
				
				<div class="card">
					<div class="card-body p-4">
						<h5 class="mb-4">Blood-Type</h5>

			    <form class="row g-3"action="{{route($current_menu.'.store')}}" method="POST" enctype="multipart/form-data">
		            @csrf
					<div class="box-body">
			            
			            <div class="form-group row" style="margin-top: 10px;">
                            <div class="col-md-4">
	                            <label for="single-select-field" class="form-label">Blood-Type</label>
	                             <select class="form-select single-select-field" name="college_id"  data-placeholder="Select College" >
	                            <option value="">Select</option>

	                             @foreach($data as $key => $value)
	                            <option value="{{$key}} ">{{$value}}</option>
	                            @endforeach
	                             </select>
                         </div>
			                <div class="col-md-5">
							    <label class="form-label">Blood Unit</label>
							    <input type="number" name="blood" class="form-control"  placeholder="Enter Blood Unit" min="0" step="1" 
							           oninput="this.value = Math.abs(this.value)">
							</div>
			                <div class="col-md-3">
								<label for="single-select-clear-field" class="form-label">Status</label>
								<select class="form-select single-select-clear-field" data-placeholder="Choose Status">
									<option value="1">Active</option>
									<option value="2">Inactive</option>
								</select>
							</div>
			            </div>
			        </div>

			        <div class="col-md-12">
						<button type="button" onclick="window.location='{{url($current_menu)}}'"  class="btn btn-light px-4">Cancel</button>
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
@endsection