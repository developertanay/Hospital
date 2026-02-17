<!doctype html>
<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta name="csrf-token" content="{{ csrf_token() }}" />
	<!--favicon-->
	<link rel="icon" href="{{asset('assets/images/favicon-32x32.png')}}" type="image/png" />
	{{--
	<link rel="icon" href="" type="image/png" />
	--}}
	<link href="https://fonts.googleapis.com/css?family=Noto+Sans&subset=devanagari" rel="stylesheet">W
	<!--plugins-->
	<link href="{{asset('assets/plugins/simplebar/css/simplebar.css')}}" rel="stylesheet" />
	<link rel="stylesheet" href="{{asset('assets/css/flatpickr.min.css')}}" />
	<link href="{{asset('assets/plugins/perfect-scrollbar/css/perfect-scrollbar.css')}}" rel="stylesheet" />
	<link href="{{asset('assets/plugins/metismenu/css/metisMenu.min.css')}}" rel="stylesheet" />
	<link href="{{asset('assets/plugins/datatable/css/dataTables.bootstrap5.min.css')}}" rel="stylesheet" />
	<link href="{{asset('assets/plugins/bs-stepper/css/bs-stepper.css')}}" rel="stylesheet" />
	<!-- loader-->
	<link href="{{asset('assets/css/pace.min.css')}}" rel="stylesheet" />
	<script src="{{asset('assets/js/pace.min.js')}}"></script>
	<!-- Bootstrap CSS -->
	<link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
	<link href="{{asset('assets/css/bootstrap-extended.css')}}" rel="stylesheet">
	<link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500&amp;display=swap" rel="stylesheet">
	<link href="{{asset('assets/css/app.css')}}" rel="stylesheet">
	<link href="{{asset('assets/css/icons.css')}}" rel="stylesheet">
	<!-- Theme Style CSS -->
	<link rel="stylesheet" href="{{asset('assets/css/dark-theme.css')}}" />
	<link rel="stylesheet" href="{{asset('assets/css/semi-dark.css')}}" />
	<link rel="stylesheet" href="{{asset('assets/css/header-colors.css')}}" />
	<link rel="stylesheet" href="{{asset('assets/select2-4/dist/css/select2.min.css')}}" />
	<link rel="stylesheet" href="{{asset('assets/select2-5/dist/select2-bootstrap-5-theme.min.css')}}" />
	
	<style type="text/css">
		#overlay{ 
		  	position: fixed;
		  	top: 0;
		  	z-index: 100;
		  	width: 100%;
		  	height:100%;
		  	display: none;
		  	background: rgba(0,0,0,0.6);
		}
		.cv-spinner {
		  	height: 100%;
		  	display: flex;
		  	justify-content: center;
		  	align-items: center;  
		}
		.spinner {
		  	width: 40px;
		  	height: 40px;
		  	border: 4px #ddd solid;
		  	border-top: 4px #2e93e6 solid;
		  	border-radius: 50%;
		  	animation: sp-anime 0.8s infinite linear;
		}
		@keyframes sp-anime {
		  	100% { 
		    	transform: rotate(360deg); 
		  	}
		}
	</style>

	@yield('css')

	<?php
        $sweet_alert_status = 0;
        $message = '';
        if(session()->has('message')) {
            $sweet_alert_status = 1;
            $message = session()->get('message');
            $icon = 'success';
            session()->forget('message');
        }
        elseif(session()->has('error')){
            $sweet_alert_status = 1;
            $message = session()->get('error');
            $icon = 'error';
            session()->forget('error');
        }
        else {

        }
    ?>

    <?php
    	$auth_data = Auth::user();
    	if(!empty($auth_data->id)) {

			$users_id = $auth_data->id;
			$user_name = $auth_data->name;
			$user_profile = DB::table('user_profile')
								->where('users_id', $users_id)
								->first();
			if(!empty($user_profile)) {
				$user_profile_image = $user_profile->photo;
			}
			else{
				$user_profile_image = 'na';	
			}

			$college_id = $auth_data->college_id;
			$company_id = !empty($auth_data->company_id)?$auth_data->company_id:NULL;
			$register_type = !empty($auth_data->register_type)?$auth_data->register_type:NULL;

			if(!empty($college_id)) {
				$college_data = DB::table('college_mast')->where('id', $college_id)->first();
				$logo = !empty($college_data->logo)?$college_data->logo:'';
				$short_name = !empty($college_data->short_name)?$college_data->short_name:'';
			}
			else {
				$logo = 'images/app_logo/1.jpeg';
				$short_name = 'UNIONE';
				$college_data = '';
			}
    	}
    	else {
    		$logo = '';
    		$short_name = '';
    		$user_profile_image = '';
    		$college_id = NULL;
			$company_id = NULL;
			$register_type = NULL;
    	}
	?>

	<title>@yield('title')</title>
</head>

<body>
	<div id="overlay">
	    <div class="cv-spinner">
	        <span class="spinner"></span>
	    </div>
	</div>
	<!--wrapper-->
	<div>
        <button id="sweetalertbtn" onclick="sweetalert()" style="display:none">see sweetalert</button>
    </div>
	<div class="wrapper">
		<!--sidebar wrapper -->
		<div class="sidebar-wrapper" data-simplebar="true">
			<div class="sidebar-header">
				<div>
					<img src="{{asset($logo)}}" class="logo-icon" alt="Org. Logo">
				</div>
				<div>
					<h4 class="logo-text" style="color: #3e3f94;">{{strtoupper($short_name)}}</h4>
				</div>
				<div class="toggle-icon ms-auto"><i class='bx bx-arrow-back'></i>
				</div>
			 </div>
			<!--navigation-->
			<ul class="metismenu" id="menu">
				@include('menu')
			</ul>
{{--
			@if(!empty($college_id) && empty($register_type)) 
			<!-- THIS MENU IS FOR COLLEGES AND STUDENTS (COLLEGE PORTAL) -->
			@elseif(!empty($company_id) && $register_type==1)				
				@include('menu_employer')
			@elseif(empty($company_id) && $register_type==2)
			<!-- this could be having college or not as per the profile that job seeker has saved -->
				@include('menu_jobseeker')
			@else

			@endif
--}}



			<!--end navigation-->
		</div>
		<!--end sidebar wrapper -->
		<!--start header -->
		<header>
			<div class="topbar d-flex align-items-center">
				<nav class="navbar navbar-expand gap-3">
					<div class="mobile-toggle-menu"><i class='bx bx-menu'></i>
					</div>

					  <div class="position-relative search-bar d-lg-block d-none" data-bs-toggle="modal" data-bs-target="#SearchModal">
					  	{{--
						<input class="form-control px-5" disabled type="search" placeholder="Search">
						<span class="position-absolute top-50 search-show ms-3 translate-middle-y start-0 top-50 fs-5"><i class='bx bx-search'></i></span>
					  	--}}
					  </div>


					  <div class="top-menu ms-auto" style="visibility: hidden;">
						<ul class="navbar-nav align-items-center gap-1">
							<li class="nav-item mobile-search-icon d-flex d-lg-none" data-bs-toggle="modal" data-bs-target="#SearchModal">
								<a class="nav-link" href="avascript:;"><i class='bx bx-search'></i>
								</a>
							</li>
							<li class="nav-item dropdown dropdown-laungauge d-none d-sm-flex">
								<a class="nav-link dropdown-toggle dropdown-toggle-nocaret" href="avascript:;" data-bs-toggle="dropdown">
									{{--
									<img src="{{asset('assets/images/county/02.png')}}" width="22" alt="">
									--}}
								</a>
								<ul class="dropdown-menu dropdown-menu-end">
									{{--
									<li><a class="dropdown-item d-flex align-items-center py-2" href="javascript:;"><img src="{{asset('assets/images/county/01.png')}}" width="20" alt=""><span class="ms-2">English</span></a>
									</li>
									<li><a class="dropdown-item d-flex align-items-center py-2" href="javascript:;"><img src="{{asset('assets/images/county/02.png')}}" width="20" alt=""><span class="ms-2">Catalan</span></a>
									</li>
									<li><a class="dropdown-item d-flex align-items-center py-2" href="javascript:;"><img src="{{asset('assets/images/county/03.png')}}" width="20" alt=""><span class="ms-2">French</span></a>
									</li>
									<li><a class="dropdown-item d-flex align-items-center py-2" href="javascript:;"><img src="{{asset('assets/images/county/04.png')}}" width="20" alt=""><span class="ms-2">Belize</span></a>
									</li>
									<li><a class="dropdown-item d-flex align-items-center py-2" href="javascript:;"><img src="{{asset('assets/images/county/05.png')}}" width="20" alt=""><span class="ms-2">Colombia</span></a>
									</li>
									<li><a class="dropdown-item d-flex align-items-center py-2" href="javascript:;"><img src="{{asset('assets/images/county/06.png')}}" width="20" alt=""><span class="ms-2">Spanish</span></a>
									</li>
									<li><a class="dropdown-item d-flex align-items-center py-2" href="javascript:;"><img src="{{asset('assets/images/county/07.png')}}" width="20" alt=""><span class="ms-2">Georgian</span></a>
									</li>
									<li><a class="dropdown-item d-flex align-items-center py-2" href="javascript:;"><img src="{{asset('assets/images/county/08.png')}}" width="20" alt=""><span class="ms-2">Hindi</span></a>
									</li>
									--}}
								</ul>
							</li>
							<li class="nav-item dark-mode d-none d-sm-flex">
								<a class="nav-link dark-mode-icon" href="javascript:;"><i class='bx bx-moon'></i>
								</a>
							</li>

							<li class="nav-item dropdown dropdown-app">
								<a class="nav-link dropdown-toggle dropdown-toggle-nocaret" data-bs-toggle="dropdown" href="javascript:;"><i class='bx bx-grid-alt'></i></a>
								<div class="dropdown-menu dropdown-menu-end p-0">
									<div class="app-container p-2 my-2">
									</div>
								</div>
							</li>

							<li class="nav-item dropdown dropdown-large">
								<a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#" data-bs-toggle="dropdown"><span class="alert-count">7</span>
									<i class='bx bx-bell'></i>
								</a>
								<div class="dropdown-menu dropdown-menu-end">
									<a href="javascript:;">
										<div class="msg-header">
											<p class="msg-header-title">Notifications</p>
											<p class="msg-header-badge">8 New</p>
										</div>
									</a>
									<div class="header-notifications-list">
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												
												<div class="flex-grow-1">
													<h6 class="msg-name">Daisy Anderson<span class="msg-time float-end">5 sec
												ago</span></h6>
													<p class="msg-info">The standard chunk of lorem</p>
												</div>
											</div>
										</a>
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="notify bg-light-danger text-danger">dc
												</div>
												<div class="flex-grow-1">
													<h6 class="msg-name">New Orders <span class="msg-time float-end">2 min
												ago</span></h6>
													<p class="msg-info">You have recived new orders</p>
												</div>
											</div>
										</a>
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												
												<div class="flex-grow-1">
													<h6 class="msg-name">Althea Cabardo <span class="msg-time float-end">14
												sec ago</span></h6>
													<p class="msg-info">Many desktop publishing packages</p>
												</div>
											</div>
										</a>
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="notify bg-light-success text-success">
													
												</div>
												<div class="flex-grow-1">
													<h6 class="msg-name">Account Created<span class="msg-time float-end">28 min
												ago</span></h6>
													<p class="msg-info">Successfully created new email</p>
												</div>
											</div>
										</a>
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												<div class="notify bg-light-info text-info">Ss
												</div>
												<div class="flex-grow-1">
													<h6 class="msg-name">New Product Approved <span
												class="msg-time float-end">2 hrs ago</span></h6>
													<p class="msg-info">Your new product has approved</p>
												</div>
											</div>
										</a>
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												
												<div class="flex-grow-1">
													<h6 class="msg-name">Katherine Pechon <span class="msg-time float-end">15
												min ago</span></h6>
													<p class="msg-info">Making this the first true generator</p>
												</div>
											</div>
										</a>
										
										<a class="dropdown-item" href="javascript:;">
											<div class="d-flex align-items-center">
												
												<div class="flex-grow-1">
													<h6 class="msg-name">Peter Costanzo <span class="msg-time float-end">6 hrs
												ago</span></h6>
													<p class="msg-info">It was popularised in the 1960s</p>
												</div>
											</div>
										</a>
									</div>
									<a href="javascript:;">
										<div class="text-center msg-footer">
											<button class="btn btn-primary w-100">View All Notifications</button>
										</div>
									</a>
								</div>
							</li>
							<li class="nav-item dropdown dropdown-large">
								<a class="nav-link dropdown-toggle dropdown-toggle-nocaret position-relative" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"> <span class="alert-count">8</span>
									<i class='bx bx-shopping-bag'></i>
								</a>
								<div class="dropdown-menu dropdown-menu-end">
									<a href="javascript:;">
										<div class="msg-header">
											<p class="msg-header-title">My Cart</p>
											<p class="msg-header-badge">10 Items</p>
										</div>
									</a>
									<div class="header-message-list">
										
										
									</div>
									<a href="javascript:;">
										<div class="text-center msg-footer">
											<div class="d-flex align-items-center justify-content-between mb-3">
												<h5 class="mb-0">Total</h5>
												<h5 class="mb-0 ms-auto">$489.00</h5>
											</div>
											<button class="btn btn-primary w-100">Checkout</button>
										</div>
									</a>
								</div>
							</li>
						</ul>
					</div>
					<div class="user-box dropdown px-3">
						
						<a class="d-flex align-items-center nav-link dropdown-toggle gap-3 dropdown-toggle-nocaret" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
							<img src="{{asset($user_profile_image)}}" class="user-img" alt="img">
							<div class="user-info">
								<p class="user-name mb-0">{{!empty($user_name)?$user_name:''}}</p>
								{{--<p class="designattion mb-0">Administrator</p>--}}
							</div>
						</a>
						<ul class="dropdown-menu dropdown-menu-end">
							@if(!empty($college_id))
							<li><a class="dropdown-item d-flex align-items-center" href="{{url('UserProfileMast')}}"><i class="bx bx-user fs-5"></i><span>Profile</span></a>
							</li>
							<li>
								<div class="dropdown-divider mb-0"></div>
							</li>
							@endif
							{{--
							<li><a class="dropdown-item d-flex align-items-center" href="javascript:;"><i class="bx bx-cog fs-5"></i><span>Settings</span></a>
							</li>
							<li><a class="dropdown-item d-flex align-items-center" href="javascript:;"><i class="bx bx-home-circle fs-5"></i><span>Dashboard</span></a>
							</li>
							<li><a class="dropdown-item d-flex align-items-center" href="javascript:;"><i class="bx bx-dollar-circle fs-5"></i><span>Earnings</span></a>
							</li>
							<li><a class="dropdown-item d-flex align-items-center" href="javascript:;"><i class="bx bx-download fs-5"></i><span>Downloads</span></a>
							</li>
							--}}
							@if(!empty(Session::get('login_source')) && Session::get('login_source')=='app')
							@else
							<li><a class="dropdown-item d-flex align-items-center" href="#" onclick="logout_fn()"><i class="bx bx-log-out-circle"></i><span>Logout</span></a>
							</li>

							@endif
						</ul>
					</div>
				</nav>
			</div>
		</header>
		<!--end header -->
		<form action="{{route('logout')}}" method="POST" id="logout_form" style="display: none;">
	 		@csrf<button class="" type="submit" name>Logout</button>
	 	</form>

		@yield('content')



				<!--start overlay-->
		<div class="overlay toggle-icon"></div>
		<!--end overlay-->
		<!--Start Back To Top Button--> <a href="javaScript:;" class="back-to-top"><i class='bx bxs-up-arrow-alt'></i></a>
		<!--End Back To Top Button-->
		<footer class="page-footer">
			<p class="mb-0">Copyright © 2024. All rights reserved by Antha Prerna Cell.</p>
		</footer>
	</div>
	<!--end wrapper-->




	{{--
	<!--start switcher-->
	<div class="switcher-wrapper">
		<div class="switcher-btn"> <i class='bx bx-cog bx-spin'></i>
		</div>
		<div class="switcher-body">
			<div class="d-flex align-items-center">
				<h5 class="mb-0 text-uppercase">Theme Customizer</h5>
				<button type="button" class="btn-close ms-auto close-switcher" aria-label="Close"></button>
			</div>
			<hr/>
			<h6 class="mb-0">Theme Styles</h6>
			<hr/>
			<div class="d-flex align-items-center justify-content-between">
				<div class="form-check">
					<input class="form-check-input" type="radio" name="flexRadioDefault" id="lightmode" checked>
					<label class="form-check-label" for="lightmode">Light</label>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="radio" name="flexRadioDefault" id="darkmode">
					<label class="form-check-label" for="darkmode">Dark</label>
				</div>
				<div class="form-check">
					<input class="form-check-input" type="radio" name="flexRadioDefault" id="semidark">
					<label class="form-check-label" for="semidark">Semi Dark</label>
				</div>
			</div>
			<hr/>
			<div class="form-check">
				<input class="form-check-input" type="radio" id="minimaltheme" name="flexRadioDefault">
				<label class="form-check-label" for="minimaltheme">Minimal Theme</label>
			</div>
			<hr/>
			<h6 class="mb-0">Header Colors</h6>
			<hr/>
			<div class="header-colors-indigators">
				<div class="row row-cols-auto g-3">
					<div class="col">
						<div class="indigator headercolor1" id="headercolor1"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor2" id="headercolor2"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor3" id="headercolor3"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor4" id="headercolor4"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor5" id="headercolor5"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor6" id="headercolor6"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor7" id="headercolor7"></div>
					</div>
					<div class="col">
						<div class="indigator headercolor8" id="headercolor8"></div>
					</div>
				</div>
			</div>
			<hr/>
			<h6 class="mb-0">Sidebar Colors</h6>
			<hr/>
			<div class="header-colors-indigators">
				<div class="row row-cols-auto g-3">
					<div class="col">
						<div class="indigator sidebarcolor1" id="sidebarcolor1"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor2" id="sidebarcolor2"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor3" id="sidebarcolor3"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor4" id="sidebarcolor4"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor5" id="sidebarcolor5"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor6" id="sidebarcolor6"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor7" id="sidebarcolor7"></div>
					</div>
					<div class="col">
						<div class="indigator sidebarcolor8" id="sidebarcolor8"></div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!--end switcher-->
	--}}
	
	<!-- Bootstrap JS -->
	<script defer src="{{asset('assets/js/bootstrap.bundle.min.js')}}"></script>
	<!--plugins-->
	<script src="{{asset('assets/js/jquery.min.js')}}"></script>
	<script src="{{asset('assets/plugins/simplebar/js/simplebar.min.js')}}"></script>
	<script src="{{asset('assets/plugins/metismenu/js/metisMenu.min.js')}}"></script>
	<script src="{{asset('assets/plugins/perfect-scrollbar/js/perfect-scrollbar.js')}}"></script>
	<script src="{{asset('assets/plugins/bs-stepper/js/bs-stepper.min.js')}}"></script>
	<script src="{{asset('assets/plugins/bs-stepper/js/main.js')}}"></script>
	<script src="{{asset('assets/plugins/datatable/js/jquery.dataTables.min.js')}}"></script>
	<script src="{{asset('assets/plugins/datatable/js/dataTables.bootstrap5.min.js')}}"></script>
	<script src="{{asset('assets/select2-4/dist/js/select2.min.js')}}"></script>
	<script src="{{asset('assets/plugins/select2/js/select2-custom.js')}}"></script>
	<script src="{{asset('assets/js/flatpickr.js')}}"></script>
	<script>
	$(".date-format").flatpickr({
			altInput: true,
			altFormat: "F j, Y",
			dateFormat: "Y-m-d",
			// maxDate: "today",
			// maxDate: new Date().fp_incr(30),
			// minDate: new Date().fp_incr(-6)
		});
	</script>
	<script>
		$(document).ready(function() {
			$('#example').DataTable();
		  } );
	</script>
	<script>
		$(document).ready(function() {
			var table = $('#example2').DataTable( {
				lengthChange: false,
				buttons: [ 'copy', 'excel', 'pdf', 'print']
			} );
		 
			table.buttons().container()
				.appendTo( '#example2_wrapper .col-md-6:eq(0)' );
		} );
	</script>
	<script>
		$(document).ready(function() {
			var table = $('#example3').DataTable( {
				bPaginate: false,
				lengthChange: false,
				ordering: false,
				buttons: [ 'copy', 'excel', 'pdf', 'print']
			} );
		 
			table.buttons().container()
				.appendTo( '#example3_wrapper .col-md-6:eq(0)' );
		} );
	</script>
	<!--app JS-->
	<script src="{{asset('assets/js/app.js')}}"></script>
	<script type="text/javascript">
		function logout_fn() {
				$('#logout_form').submit();
		}
	</script>

	@yield('js')
	
	<script type="text/javascript" src="{{asset('assets/js/sweet_alert.min.js')}}"></script>  
	<script type="text/javascript" src="{{asset('assets/js/custom.js')}}"></script>  
    <script type="text/javascript">
        // sweetalert code starts here
        var status = "{{!empty($sweet_alert_status)?$sweet_alert_status:''}}";
        var message = "{{!empty($message)?$message:''}}";
        var icon = "{{!empty($icon) ? $icon : 'success'}}";
        $('document').ready(function(){
            if (status == 1) {
                document.getElementById("sweetalertbtn").click();
                sweetalert(); 
            }
        });

        function sweetalert() {
            Swal.fire({
                position: 'middle',
                icon: icon,
                title: message,
                showConfirmButton: false,
                timer: 3000
            });
        }

        //sweetalert code ends here
    </script>
</body>

</html>