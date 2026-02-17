@extends('layouts.header')

@section('title')
User
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
								<h4 class="mb-4">UPDATE USER  </h4>

					<form class="row g-3" action="" method="POST" enctype="multipart/form-data">
						@csrf


                            <div class="col-md-4">
									<label for="single-select-clear-field" class="form-label">User<font color="red"><b>*</b></font></label>
                                    <select class="form-select single-select-clear-field" name="user_id" onchange="getrole(this.value,{{$college_id}},{{$role_mast}})"  data-placeholder="Choose User" required>
                                    <option value=""></option>
											
                                             @foreach($users as $key=>$value)
											 @if($user_id==$key)
                                             <option value="{{$key}}" selected>{{$value}}</option>
											 @else
                                             <option value="{{$key}}">{{$value}}</option>
                                             @endif
											 @endforeach
											 
									</select>							
                            </div>

                             <div class="col-md-4">
									<label for="single-select-clear-field" class="form-label">Role<font color="red"><b>*</b></font></label>
                                    <select class="form-select single-select-clear-field" name="role_id" id="role_id" data-placeholder="Choose Role" required>
                                    <option value=""></option>
                                    @foreach($role_mast as $key=>$value)
                                    <option value="{{$key}}">{{$value}}</option>
                                    @endforeach
											
                                             
											 
									</select>							
                            </div>
							
                           
									
									<div class="col-md-12">
									<button type="button" onclick="window.location='{{url('user_update')}}'"  class="btn btn-light px-4">Cancel</button>								
									<button type="submit" style="float: right;" name="find" value="1" class="btn btn-primary px-4">Submit</button>
								</div>
					</form>
					</div>
				</div>
			</div>
		</div>
				
@endsection
@section('js')
<script>
		window.addEventListener('load', function() {
    var user_id=<?php echo json_encode($user_id); ?>;
    var college_id=<?php echo json_encode($college_id); ?>;
    var role_id=<?php echo json_encode($role_id); ?>;
    var role_mast=<?php echo json_encode($role_mast); ?>;
		if(user_id){

			getrole(user_id,college_id,role_mast,role_id);
	
	}
	});
	
function getrole(user_id,college_id,role_mast,role=''){
        
	console.log(role);
    $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: "post",
                url:  '{{route("getrolebyuser")}}',
                dataType: 'json',
                data: {
                    'college_id': college_id,
                    'user_id':user_id
                },
                success: function (data) {
                    if(data.code == 401) { }

                    else if(data.code == 200) { 
                        if(data.alert_message == undefined) {
                            console.log(data,121);
                            var course_obj = data.course_arr;
                            semester_obj = data.semesters_in_course_arr;
                            var course_options = `<option value="">Select</option>`;
							if(course_obj.length !== 0){
									}
									else{
								Object.keys(role_mast).forEach(key2 => {

										console.log('course_obj nhi hai');
										course_options += `<option value=${key2} >${role_mast[key2]} </option>`;

								})}
                            Object.keys(course_obj).forEach(key => {
								Object.keys(role_mast).forEach(key2 => {
									
                                if(role==''){  
                                if(key==key2){
									console.log('role nhi hai')

									course_options += `<option value=${key2} selected>${role_mast[key2]} </option>`;
								}
								else{
									console.log('role nhi hai2')

									course_options += `<option value=${key2} >${role_mast[key2]} </option>`;

								}}
								else{
									console.log('role  hai')

									if(role==key2){

								course_options += `<option value=${key2} selected>${role_mast[key2]} </option>`;
								}
								else{
								course_options += `<option value=${key2} >${role_mast[key2]} </option>`;

								}

								}
                               
							})});
                            document.getElementById('role_id').innerHTML = '';

                            document.getElementById('role_id').innerHTML = course_options;
                         }
                        else{
                            document.getElementById('role_id').innerHTML = '';
                            alert(data.alert_message);
                        }
                    }
                }
            });
        
}


</script>
@endsection
