@extends('layouts.header')

@section('title')
My Profile
@endsection
@section('css')
<style>
	/*
    .modal {
        display: none;
        position: fixed;
        z-index: 1;
        left: 0;
        top: 0;
        width: 30%;
        height: 30%;
        overflow: auto;
        background-color: rgba(0, 0, 0, 0);
    }

    .modal-content {
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        max-width: 40%; 
        max-height: 40%;
    }

    .close {
        color: white;
        position: absolute;
        top: 15px;
        right: 15px;
        font-size: 30px;
        font-weight: bold;
        cursor: pointer;
    }

    .modal-content {
        animation: zoom 0s;
    }

    @keyframes zoom {
        from {
            transform: scale(0)
        }
        to {
            transform: scale(1)
        }
    }
    */
</style>

@endsection

@section('content')
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
				
				<!--end breadcrumb-->
				<div class="container">
					<div class="main-body">
						<div class="row">
							<div class="col-lg-4">
								<div class="card">
									
									<div class="card-body">
										
										<div class="d-flex flex-column align-items-center text-center">
											<?php 
												$image_path = !empty($user_arr['user_profile_data']->photo)?$user_arr['user_profile_data']->photo:'';
											?>
											

											<img src="{{asset($image_path)}}" alt="Profile Picture" class="rounded-circle p-1 bg-primary" width="110">
											
											<div class="mt-3">
												<h4>{{!empty($user_arr['user_profile_data']->name)?$user_arr['user_profile_data']->name:''}}</h4>
												{{--
												<p class="text-secondary mb-1">Full Stack Developer</p>
												<p class="text-muted font-size-sm">Bay Area, San Francisco, CA</p>
												<button class="btn btn-primary">Follow</button>
												<button class="btn btn-outline-primary">Message</button>
												--}}
											</div>
										</div>
										<hr class="my-4" />
										<ul class="list-group list-group-flush">
											
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<h6 class="mb-0">Hospital</h6>
												<span class="text-secondary">{{$user_arr['college']}}</span>
											</li>
											
											

											{{--
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<h6 class="mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-globe me-2 icon-inline"><circle cx="12" cy="12" r="10"></circle><line x1="2" y1="12" x2="22" y2="12"></line><path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"></path></svg>Website</h6>
												<span class="text-secondary">https://codervent.com</span>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<h6 class="mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-github me-2 icon-inline"><path d="M9 19c-5 1.5-5-2.5-7-3m14 6v-3.87a3.37 3.37 0 0 0-.94-2.61c3.14-.35 6.44-1.54 6.44-7A5.44 5.44 0 0 0 20 4.77 5.07 5.07 0 0 0 19.91 1S18.73.65 16 2.48a13.38 13.38 0 0 0-7 0C6.27.65 5.09 1 5.09 1A5.07 5.07 0 0 0 5 4.77a5.44 5.44 0 0 0-1.5 3.78c0 5.42 3.3 6.61 6.44 7A3.37 3.37 0 0 0 9 18.13V22"></path></svg>Github</h6>
												<span class="text-secondary">codervent</span>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<h6 class="mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-twitter me-2 icon-inline text-info"><path d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z"></path></svg>Twitter</h6>
												<span class="text-secondary">@codervent</span>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<h6 class="mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-instagram me-2 icon-inline text-danger"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"></rect><path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z"></path><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"></line></svg>Instagram</h6>
												<span class="text-secondary">codervent</span>
											</li>
											<li class="list-group-item d-flex justify-content-between align-items-center flex-wrap">
												<h6 class="mb-0"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-facebook me-2 icon-inline text-primary"><path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z"></path></svg>Facebook</h6>
												<span class="text-secondary">codervent</span>
											</li>
											--}}
										</ul>
									</div>
								</div>
							</div>

							<div class="col-lg-8">
								<div class="card">
									<div class="float-right">
								{{--<a class="btn btn-primary ms-2 mt-2 " style="width:250px;height:40px;"  target="_blank" href="https://www.antiragging.in/affidavit_affiliated_form.php">AntiRaging Form</a>--}}
									</div>
									<div class="card-body">
										
									
										<form action="{{url('update_summary_profile')}}" method="POST" enctype="multipart/form-data">
											@csrf
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Full Name</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												<input type="text" style="background-color: aliceblue;" class="form-control" value="{{!empty($user_arr['user_profile_data']->name)?$user_arr['user_profile_data']->name:''}}" readonly />
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Email<font color="red"><b>*</b></font></h6>
											</div>
											<div class="col-sm-9 text-secondary">
												<input type="text" name="email" class="form-control" value="{{!empty($user_arr['user_profile_data']->email)?$user_arr['user_profile_data']->email:''}}"  required />
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Phone<font color="red"><b>*</b></font></h6>
											</div>
											<div class="col-sm-9 text-secondary">
												<input type="text" class="form-control" name="mobile" value="{{!empty($user_arr['user_profile_data']->contact_no)?$user_arr['user_profile_data']->contact_no:''}}"  required />
											</div>
										</div>
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Profile Picture</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												<input type="file" name="profile_picture" class="form-control" />
											</div>
										</div>
										
										
											{{--<div class="col-sm-3">
												<h6 class="mb-0">Payment Refrence Number</h6>
											</div>
											<div class="col-sm-4 text-secondary">
												<input type="text" style="background-color: aliceblue;"  class="form-control" value="" readonly />
											</div>
										</div>--}}
										<div class=" form-group row">
											<div class="col-md-3 " style="margin-top: 30px;">
												<input type="submit"  class="btn btn-warning" value="Update Profile">
											</div>
										</form>
											<div class="col-sm-3">
								{{--<a class="btn btn-primary btn-sm" target="_blank" href="https://www.antiragging.in/affidavit_affiliated_form.php">AntiRaging Form</a>--}}
											</div>
											<div class="col-md-4" style="margin-top: 30px; margin-left: 90px;">
												<!-- Button trigger modal -->
												<button type="button" class="btn btn-primary "  data-bs-toggle="modal" data-bs-target="#exampleVerticallycenteredModal">Change Password</button>
												<!-- Modal -->
												<div class="modal fade" id="exampleVerticallycenteredModal" tabindex="-1" aria-hidden="true">
													<div class="modal-dialog modal-dialog-centered">
														<form action="{{url('change_password')}}" method="POST">
															@csrf
															<div class="modal-content">
																<div class="modal-header">
																	<h5 class="modal-title">Change Password</h5>
																	<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
																</div>
																<div class="modal-body">
																	<div class="form-group">
																		<div class="row">
																			<div class="col-md-6">
																				<label for="current_password">Enter Current Password</label>
																			</div>
																			<div class="col-md-6">
																				<input class="form-control" type="password" name="current_password" id="current_password" autocomplete="off" required>
																			</div>
																		</div>
																		<div class="row">
																			<div class="col-md-6">
																				<label for="current_password">Enter New Password</label>
																			</div>
																			<div class="col-md-6">
																				<input class="form-control" type="password" name="new_password" id="new_password" autocomplete="off" onkeyup="check_new_password()" required>
																			</div>
																		</div>
																		<div class="row">
																			<div class="col-md-6">
																				<label for="confirm_new_password">Confirm New Password</label>
																			</div>
																			<div class="col-md-6">
																				<input class="form-control" type="password" name="confirm_new_password" autocomplete="off" id="confirm_new_password" onkeyup="check_new_password()" required>
																			</div>
																		</div>
																		<div class="row">
																			<div class="col-md-6">
																			</div>
																			<div class="col-md-6">
																				<span id='password_match_msg' style="display: none;"><font color="red">Password Does Not Match</font></span>
																			</div>
																		</div>
																	</div>
																</div>
																<div class="modal-footer">
																	<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
																	<button type="submit" name="update_password_btn" id="update_password_btn" class="btn btn-primary" disabled>Update</button>
																</div>
															</div>
														</form>
													</div>
												</div>
											</div>
										</div>
										{{--
										<div class="row mb-3">
											<div class="col-sm-3">
												<h6 class="mb-0">Address</h6>
											</div>
											<div class="col-sm-9 text-secondary">
												<input type="text" class="form-control" value="Bay Area, San Francisco, CA" />
											</div>
										</div>
										--}}

										{{--
										<div class="row">
											<div class="col-sm-3"></div>
											<div class="col-sm-9 text-secondary">
												<?php 
													$user_profile_id = !empty($user_arr['user_profile_data']->id)?$user_arr['user_profile_data']->id:'';
													if(!empty($user_profile_id)) {
														$encid = Crypt::encrypt($user_profile_id);
														$link = 'UserProfileMast/'.$encid.'/edit';
													}
													else {
														$link = 'UserProfileMast/create';
													}
												 ?>
												<a href="{{$link}}" class="btn btn-warning px-4">Edit Profile</a>
												
											</div>
										</div>
										--}}
									</div>
								</div>
								
								{{--
								<div class="row">
									<div class="col-sm-12">
										<div class="card">
											<div class="card-body">
												<h5 class="d-flex align-items-center mb-3">Project Status</h5>
												<p>Web Design</p>
												<div class="progress mb-3" style="height: 5px">
													<div class="progress-bar bg-primary" role="progressbar" style="width: 80%" aria-valuenow="80" aria-valuemin="0" aria-valuemax="100"></div>
												</div>
												<p>Website Markup</p>
												<div class="progress mb-3" style="height: 5px">
													<div class="progress-bar bg-danger" role="progressbar" style="width: 72%" aria-valuenow="72" aria-valuemin="0" aria-valuemax="100"></div>
												</div>
												<p>One Page</p>
												<div class="progress mb-3" style="height: 5px">
													<div class="progress-bar bg-success" role="progressbar" style="width: 89%" aria-valuenow="89" aria-valuemin="0" aria-valuemax="100"></div>
												</div>
												<p>Mobile Template</p>
												<div class="progress mb-3" style="height: 5px">
													<div class="progress-bar bg-warning" role="progressbar" style="width: 55%" aria-valuenow="55" aria-valuemin="0" aria-valuemax="100"></div>
												</div>
												<p>Backend API</p>
												<div class="progress" style="height: 5px">
													<div class="progress-bar bg-info" role="progressbar" style="width: 66%" aria-valuenow="66" aria-valuemin="0" aria-valuemax="100"></div>
												</div>
											</div>
										</div>
									</div>
								</div>
								--}}
							</div>
							
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--end page wrapper -->
@endsection
@section('js')




<script type="text/javascript">
	function check_new_password() {
		var current_password = document.getElementById('current_password').value;
		var new_password = document.getElementById('new_password').value;
		var confirm_password = document.getElementById('confirm_new_password').value;
		if(current_password == '' || current_password == undefined) {
			alert('Please Enter Current Password First');
			document.getElementById('new_password').value = '';
			document.getElementById('confirm_new_password').value = '';
			document.getElementById('update_password_btn').disabled = true;
		}
		else if(new_password != confirm_password) {
			document.getElementById('password_match_msg').style.display = 'block';
			document.getElementById('update_password_btn').disabled = true;
		}
		else {
			document.getElementById('password_match_msg').style.display = 'none';
			document.getElementById('update_password_btn').disabled = false;
		}
	}
</script>
<script type="text/javascript">
	var totalAttendance = 50;
        var presentCount = 20;
        var absentCount = totalAttendance - presentCount;

        // Create a data array for the pie chart
        var data = {
            labels: ['Present', 'Absent'],
            datasets: [{
                data: [presentCount, absentCount],
                backgroundColor: ['#36A2EB', '#FFCE56']
            }]
        };

        // Get the canvas element and create a pie chart
        var ctx = document.getElementById('attendanceChart').getContext('2d');
        var myPieChart = new Chart(ctx, {
            type: 'pie',
            data: data
        });
    </script>
    @endsection