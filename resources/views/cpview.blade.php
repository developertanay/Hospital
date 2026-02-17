<!DOCTYPE html>
<html lang="en">

<head>
	<!-- Required meta tags -->
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<!--favicon-->
	<link rel="icon" href="{{asset('assets/images/favicon-32x32.png')}}" type="image/png" />
	<!-- loader-->
	{{--
	<link href="{{asset('assets/css/pace.min.css')}}" rel="stylesheet" />
	<script src="{{asset('assets/js/pace.min.js')}}"></script>
	--}}
	<!-- Bootstrap CSS -->
	<link href="{{asset('assets/css/bootstrap.min.css')}}" rel="stylesheet">
	<link href="{{asset('assets/css/bootstrap-extended.css')}}" rel="stylesheet">
	<link href="{{asset('assets/css/app.css')}}" rel="stylesheet">
	<link href="{{asset('assets/css/icons.css')}}" rel="stylesheet">
	<link href="{{asset('assets/css/pace.min.css')}}" rel="stylesheet" />

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

	<title>College ERP</title>
</head>

<body class="">
	<div id="overlay">
	    <div class="cv-spinner">
	        <span class="spinner"></span>
	    </div>
	</div>
	<!-- wrapper -->
	<div class="wrapper">
		<div class="authentication-forgot d-flex align-items-center justify-content-center">
			<div class="card forgot-box">
				<div class="card-body">
					<div class="p-3">
						<div class="text-center">
							<img src="assets/images/icons/forgot-2.png" width="100" alt="" />
						</div>
						<h4 class="mt-5 font-weight-bold" id="main_heading">Change Password</h4>
						<p class="text-muted" id="supporting_text">Create a New Password Below</p>
							
							<div class="my-4">
								<label class="form-label"><b>Current Password</b></label>
								<input type="text" name="old_p1" id="old_p1" class="form-control" placeholder="" required />
							</div>
							<div class="my-4" id="p1">
								<label class="form-label"><b>Enter New Password</b></label>
								<input type="password" name="new_p1" id="new_p1" class="form-control" placeholder="" required />
							</div>
							<div class="my-4" id="p2">
								<label class="form-label"><b>Confirm Password</b></label>
								<input type="password" name="new_p2" id="new_p2" class="form-control" placeholder="" required />
							</div>
							<div class="d-grid gap-2" id="submit_btn_div">
								<button type="submit" class="btn btn-primary" onclick="confirm_new_password()">GENERATE</button>
								<a href="{{url('/')}}" class="btn btn-light"><i class='bx bx-arrow-back me-1'></i>Back to Login</a>
							</div>
						

					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- end wrapper -->
</body>
{{--
	<script src="{{asset('assets/js/pace.min.js')}}"></script>
--}}

<script type="text/javascript" src="{{asset('assets/js/jquery.min.js')}}"></script>
<script type="text/javascript">
	var token = '';

	function confirm_new_password() {
		var old_p1 = document.getElementById('old_p1').value;
		var new_p1 = document.getElementById('new_p1').value;
		var new_p2 = document.getElementById('new_p2').value;
		if(old_p1==new_p1) {
			alert('Old and New Password Cannot be same');
		}
		else if(new_p1!=new_p2) {
			alert('Password and Confirm Password Does Not Match');
		}
		else {
			generate(old_p1, new_p2);
		}
	}

	function generate(old_p1, new_p2) {
		var old_password = old_p1;
		var new_password = new_p2;

		$("#overlay").show();
		$.ajaxSetup({
			    headers: {
			        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			    }
			});
			$.ajax({
			    type: "post",
			    url:  '{{route("change_password_at_login")}}',
			    dataType: 'json',
			    data: {
			    	'new_password': new_password,
			    	'old_password': old_password,
			    	'_token' : '{{ csrf_token() }}'
			    },
			    success: function (data) {
			    	$("#overlay").hide();
			        if(data.code == 401) { }

			        else if(data.code == 200) { 
			            if(data.alert_message == undefined) {
			            	window.location.href = data.url;
			            }
			            else{
			                alert(data.alert_message);
			            }
			        }
			    }
			});
	}
</script>

</html>