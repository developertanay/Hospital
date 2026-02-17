@extends('layouts.header')

@section('title')
Student
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
								<h5 class="mb-4">MODULE ASSIGNING</h5>

					    <form class="row g-3" action="{{route('ModuleAssigning.create')}}" method="GET">
				            @csrf
							<div class="box-body">
					              <div class="form-group row">
					              	<div class="col-md-4">
									<label for="single-select-field" class="form-label">College</label>
									 <select class="form-select single-select-field" name="college_id"  data-placeholder="Select College" >
									 @foreach($college_mast as $key => $value)
									<option value="{{$key}} ">{{$value}}</option>
									@endforeach
									 </select>
								 </div>
					              	<div class="col-md-4">
									<label for="single-select-clear-field" class="form-label">Role<font color="red"><b>*</b></font></label>
									 <select class="form-select single-select-clear-field" name="role_id"  data-placeholder="Select Role" required>
									 <option></option>
									 @foreach($role_mast as $key => $value)
									<option value="{{$key}} ">{{$value}}</option>
									
									@endforeach
									 </select>
								 </div>    
					          	<div class="col-md-2" style="margin-top:30px">
									<button type="submit" style="float: right;" class="btn btn-primary px-4">Next</button>
					            </div>
							</div>   
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