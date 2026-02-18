<!doctype html>
<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	<link rel="icon" href="assets/images/favicon-32x32.png" type="image/png" />
	<!--plugins-->
	<link href="assets/plugins/simplebar/css/simplebar.css" rel="stylesheet" />
	<link href="assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css" rel="stylesheet" />
	<link href="assets/plugins/metismenu/css/metisMenu.min.css" rel="stylesheet" />
	<!-- loader-->
	<link href="assets/css/pace.min.css" rel="stylesheet" />
	<script src="assets/js/pace.min.js"></script>
	<!-- Bootstrap CSS -->
	<link href="assets/css/bootstrap.min.css" rel="stylesheet">
	<link href="assets/css/bootstrap-extended.css" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&amp;display=swap" rel="stylesheet">
	<link href="assets/css/app.css" rel="stylesheet">
	<link href="assets/css/icons.css" rel="stylesheet">
	<title>Hospital</title>
</head>
<?php 
	// dd($request);
// dd($_SERVER);
	$domain = $_SERVER['HTTP_HOST'];
	$domain2 = str_replace('www.', '', $domain);
	// dd($domain2);
	$domain_arr = explode('.', $domain2);
	$host = $domain_arr[0];
	// dd($host);
// dd($_SERVER['HTTP_HOST']);

	$college_data = DB::table('hospital_mast')
						->where('host', $host)
						->first();
						// dd($college_data);
	$hospital_name = !empty($college_data)?$college_data->hospital_name:'DEMO COLLEGE';
	// dd($hospital_name);
	$college_logo = !empty($college_data)?$college_data->logo:'';
	$college_id = !empty($college_data)?$college_data->id:'';
	$show_signup_option = !empty($college_data)?false:true;
	// dd($host);
	if($host == 'localhost'||$host=='127') {
		$college_id = 33;	//33 stands for LakshmiBai College, 44 for Ramanujan College
	}
	if($host=='college') {
		header("Location: http://lbc.msell.in");
		exit();
		dd('Please contact admin.');
	}
	
	else {

		$college_data = DB::table('hospital_mast')
							->where('host', $host)
							->first();

		$hospital_name = !empty($college_data)?$college_data->hospital_name:NULL;
		$college_logo = !empty($college_data)?$college_data->logo:'images/app_logo/1.jpeg';
		$college_id = !empty($college_data)?$college_data->id:'';
		if($host == 'localhost') {
			$college_id = 1;	//33 stands for LakshmiBai College, 44 for Ramanujan College
		}
	}
	// dd($college_data);

?>
<body class="">
	<!--wrapper-->
	<div class="wrapper">
		<div class="section-authentication-signin d-flex align-items-center justify-content-center my-5 my-lg-0">
			<div class="container">
				<div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
					<div class="col mx-auto">
						<div class="card mb-0">
							<div class="card-body">
								<div class="p-4">
									<div class="mb-3 text-center">
										@if(!empty($hospital_name))
										<img src="{{asset($college_logo)}}" width="100" height="100" alt="" />
										@else
										<img src="{{asset($college_logo)}}" width="150" height="" alt="" />
										@endif
									</div>
									<div class="text-center mb-4">
										<h5 class="">{{strtoupper($hospital_name)}}</h5>
										<p class="mb-0">Please log in to your account</p>
									</div>
									<div class="form-body">
										<form class="row g-3" method="POST" action="{{ route('login') }}">
											 @csrf
											<div class="col-12">
												<label for="inputEmailAddress" class="form-label">User Id</label>
												<input id="email" type="text" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="off" autofocus>

				                                @error('email')
				                                    <span class="invalid-feedback" role="alert">
				                                        <strong>{{ $message }}</strong>
				                                    </span>
				                                @enderror
				                                {{--
												<input type="email" class="form-control" id="inputEmailAddress" placeholder="jhon@example.com">
												--}}
											</div>
											<div class="col-12">
												<label for="inputChoosePassword" class="form-label">Password</label>
												<div class="input-group" id="show_hide_password">
													<input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password" required autocomplete="current-password">

					                                @error('password')
					                                    <span class="invalid-feedback" role="alert">
					                                        <strong>{{ $message }}</strong>
					                                    </span>
					                                @enderror

					                                {{--
													<input type="password" class="form-control border-end-0" id="inputChoosePassword" value="12345678" placeholder="Enter Password"> <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
													--}}

													<input type="hidden" name="college_id" value="{{$college_id}}">
												</div>
											</div>
											<div class="col-md-6">
											{{--
												<div class="form-check form-switch">
													<input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked">
													<label class="form-check-label" for="flexSwitchCheckChecked">Remember Me</label>
												</div>
											--}}
											</div>
											<div class="col-md-6 text-end">	<a href="{{url('forgot_password')}}">Forgot Password ?</a>
											</div>
											<div class="col-12">
												<div class="d-grid">
													<button type="submit" class="btn btn-primary"><b>Sign in</b></button>
												</div>
											</div>
											{{--
											--}}
											@if($show_signup_option)
											<div class="col-12">
												<div class="text-center ">
													<p class="mb-0">Don't have an account yet? <a href="{{url('register')}}">Sign up here</a>
													</p>
												</div>
											</div>
											@endif
										</form>
									</div>
									{{--
									<div class="login-separater text-center mb-5"> <span>OR SIGN IN WITH</span>
										<hr/>
									</div>
									<div class="list-inline contacts-social text-center">
										<a href="javascript:;" class="list-inline-item bg-facebook text-white border-0 rounded-3"><i class="bx bxl-facebook"></i></a>
										<a href="javascript:;" class="list-inline-item bg-twitter text-white border-0 rounded-3"><i class="bx bxl-twitter"></i></a>
										<a href="javascript:;" class="list-inline-item bg-google text-white border-0 rounded-3"><i class="bx bxl-google"></i></a>
										<a href="javascript:;" class="list-inline-item bg-linkedin text-white border-0 rounded-3"><i class="bx bxl-linkedin"></i></a>
									</div>
									<div class="col-12" style="margin-top: 10px;">
										<div class="d-grid">
											<button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#exampleVerticallycenteredModal"><b>FAQS</b></button>
										</div>
									</div>
									--}}
									<div class="modal fade" id="exampleVerticallycenteredModal" tabindex="-1" aria-hidden="true">
										<div class="modal-dialog modal-dialog-centered">
											<div class="modal-content">
												<div class="modal-header">
													<h5 class="modal-title">FAQS</h5>
												</div>
												<div>
													<p style="padding-left: 10px; text-align: justify; padding-right: 10px;">
														<ul>
															<li>Default password is Pass@123</li>
															<li>If you are not able to reset your password, send your query to reset your password to Dic1@lb.du.ac.in and your password will be reset. Please provide your CSAS Form no., name, and course.</li>
															<li>If you have correctly entered the login details and still you cannot login, please send the screenshot of the error page along with CSAS Form no. name and Course to the Dic1@lb.du.ac.in</li>
															<li>If the issue is not resolved in 2 days, then send your query to the following email Dic2@lb.du.ac.in</li>      
														</ul>
													</p>
												</div>
												<div class="modal-footer">
													<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
				<!--end row-->
			</div>
		</div>
	</div>
	<!--end wrapper-->
	<!-- Bootstrap JS -->
	<script src="assets/js/bootstrap.bundle.min.js"></script>
	<!--plugins-->
	<script src="assets/js/jquery.min.js"></script>
	<script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
	<script src="assets/plugins/metismenu/js/metisMenu.min.js"></script>
	<script src="assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js"></script>
	<!--Password show & hide js -->
	<script>
		$(document).ready(function () {
			$("#show_hide_password a").on('click', function (event) {
				event.preventDefault();
				if ($('#show_hide_password input').attr("type") == "text") {
					$('#show_hide_password input').attr('type', 'password');
					$('#show_hide_password i').addClass("bx-hide");
					$('#show_hide_password i').removeClass("bx-show");
				} else if ($('#show_hide_password input').attr("type") == "password") {
					$('#show_hide_password input').attr('type', 'text');
					$('#show_hide_password i').removeClass("bx-hide");
					$('#show_hide_password i').addClass("bx-show");
				}
			});
		});
	</script>
	<!--app JS-->
	<script src="assets/js/app.js"></script>
</body>

</html>