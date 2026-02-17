@extends('layouts.header')

@section('title')
Create Activity Restriction
@endsection

@section('content')
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
				<!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
								{{--
					<div class="breadcrumb-title pe-3">STUDENT</div>
					
					<div class="ps-3">
						<nav aria-label="breadcrumb">
							<ol class="breadcrumb mb-0 p-0">
								<li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
								</li>
								<li class="breadcrumb-item active" aria-current="page">CREATE STUDENT</li>
							</ol>
						</nav>
					</div>
								--}}
					{{--
					<div class="ms-auto">
						<div class="btn-group">
							<button type="button" class="btn btn-primary">Settings</button>
							<button type="button" class="btn btn-primary split-bg-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">	<span class="visually-hidden">Toggle Dropdown</span>
							</button>
							<div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end">	<a class="dropdown-item" href="javascript:;">Action</a>
								<a class="dropdown-item" href="javascript:;">Another action</a>
								<a class="dropdown-item" href="javascript:;">Something else here</a>
								<div class="dropdown-divider"></div>	<a class="dropdown-item" href="javascript:;">Separated link</a>
							</div>
						</div>
					</div>
					--}}
				</div>
				<!--end breadcrumb-->
				<div class="row">
					<div class="col-xl-12 mx-auto">
						
						<div class="card">
							<div class="card-body p-4">
								<h4 class="mb-4">Activity Restriction</h4>

					<form class="row g-3" action="{{route($current_menu.'.store')}}" method="POST" enctype="multipart/form-data">
						@csrf
						
								
								

								

							<div class="col-md-4 ">
								<label for="single-select-clear-field" class="form-label">Module<font color="red"><b>*</b></font></label>
								 <select class="form-select single-select-clear-field" name="module_url"  data-placeholder="Choose Module" required>
									 <option></option>
									 @foreach($module_mast as $key => $value)
										<option value="{{$key}}" >{{$value}}</option>
								@endforeach
								 </select>
							</div>
							

                            <div class="col-md-4">
                            <label for="single-select-clear-field" class="form-label">Role</label>
                                <select class="form-select single-select-clear-field" name="role"  data-placeholder="Choose Role" >
                                    <option></option>
                                    @foreach($role_mast as $key => $value) 
                            <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                                </select>
                            </div>

							<div class="col-md-4">
                            <label for="single-select-clear-field" class="form-label">Faculty</label>
                                <select class="form-select single-select-clear-field" name="faculty_id"  data-placeholder="Choose Faculty" >
                                    <option></option>
                                    @foreach($faculty_mast as $key => $value) 
                            <option value="{{$key}}">{{$value}}</option>
                            @endforeach
                                </select>
                            </div>

                            <div class="col-md-4">
									<label class="form-label">Allowed Past Days</label>
									<input type="number" name="allowed_past_days" class="form-control" onkeypress="return /[0-9\.]/i.test(event.key)" placeholder="Enter Allowed Past Days" >
							</div>
                            <div class="col-md-4">
									<label class="form-label">Allowed Future Days</label>
									<input type="number" onkeypress="return /[0-9\.]/i.test(event.key)" name="allowed_future_days" class="form-control" placeholder="Enter Allowed Future Days" >
							</div>
							<div class="col-md-4">
									<label class="form-label">Applicable Till</label>
									<input type="date"  name="applicable_till" class="form-control format-date" placeholder="Enter applicable_till" >
							</div>
							
                            <div class="col-md-4">
										<label for="single-select-clear-field" class="form-label">Status<font color="red"><b>*</b></font></label>
										 <select class="form-select single-select-clear-field" name="status"  data-placeholder="Choose Status" required>
											 
											 <option value="1">Active</option>
											 <option value="2">Inactive</option>
										 </select>
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
				
@endsection

