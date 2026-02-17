@extends('layouts.header')

@section('title')
Module
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
								<h5 class="mb-4">MODULE</h5>

					    <form class="row g-3"action="{{route($current_menu.'.update',$encrypted_id)}}" method="POST" enctype="multipart/form-data">
				            @csrf
            				@method('PATCH')
							<div class="box-body">
					            <div class="form-group row">
					              	<div class="col-md-3">
										<label for="single-select-clear-field" class="form-label">Parent Module</label>
									 	<select class="form-select single-select-clear-field" name="parent_id"  data-placeholder="Select Parent" >
									 		<option></option>
									 		@foreach($module as $key => $value)
									 			@if($key == $data->parent_id)
									 			<option value="{{$key}} " selected >{{$value}}</option>
									 			@else
									 			<option value="{{$key}} "  >{{$value}}</option>
												@endif
											@endforeach
									 	</select>
								 	</div>
					                <div class="col-md-3">
					                  	<label class="form-label">Module Name<font color="red"><b>*</b></font></label>
					                  	<input type="text" name="name" class="form-control"  placeholder="Enter Module Name" value="{{$data->name}}" maxlength="50" required>
					                </div>
					                <div class="col-md-3">
					                  	<label class="form-label">URL</label>
					                  	<input type="text" name="url" class="form-control"  placeholder="Enter URL" value="{{$data->url}}" maxlength="50" >
					                </div>
					                <div class="col-md-3">
					                  	<label class="form-label">Sequence</label>
					                  	<input type="text" name="sequence" class="form-control"  placeholder="Enter Sequence" value="{{$data->sequence}}" maxlength="50" >
					                </div>
					            </div>
					            <div class="form-group row" style="margin-top: 10px;">
					                <div class="col-md-9">
					                  	<label class="form-label">Icon</label>
					                  	<input type="text" name="icon" class="form-control"  placeholder="Enter Icon" value="{{$data->icon}}" maxlength="1000" >
					                </div>
					                <div class="col-md-3">
										<label for="single-select-clear-field" class="form-label">Status</label>
										<select class="form-select single-select-clear-field" name="status" data-placeholder="Choose Status">
											<option></option>
						                 	<option value="1" {{($data->status ==  1)?'selected':''}}>Active</option>
						                 	<option value="2" {{($data->status == 2)?'selected':''}}>Inactive</option>
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
				<!--end row-->
	</div>
</div>
		<!--end page wrapper -->
@endsection