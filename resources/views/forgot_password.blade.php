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

	<title>Hospital</title>
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
						<h4 class="mt-5 font-weight-bold" id="main_heading">Forgot Password?</h4>
						<p class="text-muted" id="supporting_text">Enter your registered USER ID to reset the password</p>
							
							<div class="my-4">
								<label class="form-label"><b>USER ID</b></label>
								<input type="text" name="user_id" id="user_id" class="form-control" placeholder="" required />
							</div>
							<div class="my-4" id="token_div" style="display: none;">
								<label class="form-label"><b>Security Token (sent on registered email id)</b></label>
								<input type="text" name="user_token" id="user_token" class="form-control" placeholder="" required />
							</div>
							<div class="my-4" id="p1" style="display: none;">
								<label class="form-label"><b>Enter New Password</b></label>
								<input type="password" name="new_p1" id="new_p1" class="form-control" placeholder="" required />
							</div>
							<div class="my-4" id="p2" style="display: none;">
								<label class="form-label"><b>Confirm Password</b></label>
								<input type="password" name="new_p2" id="new_p2" class="form-control" placeholder="" required />
							</div>
							<div class="d-grid gap-2" id="submit_btn_div">
								<button type="submit" class="btn btn-primary" onclick="submit_form()">SUBMIT</button>
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
	function submit_form() {
		var user_id = document.getElementById('user_id').value;
		if(user_id == undefined || user_id == '') {
			alert('Please Fill Your User Id');
		}
		else {	//user id is passed
			//call ajax
			$("#overlay").show();
			$.ajaxSetup({
			    headers: {
			        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			    }
			});
			$.ajax({
			    type: "post",
			    url:  '{{route("forgot_password_submit")}}',
			    dataType: 'json',
			    data: {
			    	'user_id': user_id,
			    	'_token' : '{{ csrf_token() }}'
			    },
			    success: function (data) {
			    	 $( "#overlay").hide();
			        if(data.code == 401) { }

			        else if(data.code == 200) { 
			            if(data.alert_message == undefined) {
			            	if(data.token==undefined) {
			            		alert('Please Try Again Later');
			            	}
			            	else {
			            		token = data.token;
			            		document.getElementById('user_id').readOnly = true;
			            		document.getElementById('token_div').style.display = 'block';
			            		
			            		var new_html = `<button type="submit" class="btn btn-primary" onclick="verify_token()">Verify</button>
								 <a href="{{url('/')}}" class="btn btn-light"><i class='bx bx-arrow-back me-1'></i>Back to Login</a>`;
			            		document.getElementById('submit_btn_div').innerHTML = '';
			            		document.getElementById('submit_btn_div').innerHTML = new_html;
			            	}
			            }
			            else{
			                alert(data.alert_message);
			            }
			        }
			    }
			});
		}
	}

	function verify_token(){
		var user_inserted_token = document.getElementById('user_token').value;
		
		if(user_inserted_token == token && token != '') {
			// alert('token matched successfully');
			document.getElementById('supporting_text').innerHTML = 'TOKEN VERIFIED, PLEASE GENERATE NEW PASSWORD BELOW';
			document.getElementById('user_token').readOnly = true;
			document.getElementById('p1').style.display = 'block';
			document.getElementById('p2').style.display = 'block';
			var new_html = `<button type="submit" class="btn btn-primary" onclick="confirm_new_password()">GENERATE</button>
				<a href="{{url('/')}}" class="btn btn-light"><i class='bx bx-arrow-back me-1'></i>Back to Login</a>`;
			document.getElementById('submit_btn_div').innerHTML = '';
			document.getElementById('submit_btn_div').innerHTML = new_html;
		}
		else {
			alert('token mismatch');
		}
	}

	function confirm_new_password() {
		var new_p1 = document.getElementById('new_p1').value;
		var new_p2 = document.getElementById('new_p2').value;
		if(new_p1!=new_p2) {
			alert('Password and Confirm Password Does Not Match');
		}
		else {
			var user_id = document.getElementById('user_id').value;
			var user_token = document.getElementById('user_token').value;
			generate(user_id, user_token, new_p1, new_p2);
		}
	}

	function generate(user_id, user_token, new_p1, new_p2) {
		$("#overlay").show();
		$.ajaxSetup({
			    headers: {
			        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			    }
			});
			$.ajax({
			    type: "post",
			    url:  '{{route("generate_password")}}',
			    dataType: 'json',
			    data: {
			    	'user_id': user_id,
			    	'user_token': user_token,
			    	'new_p1': new_p1,
			    	'new_p2': new_p2,
			    	'_token' : '{{ csrf_token() }}'
			    },
			    success: function (data) {
			    	$("#overlay").hide();
			        if(data.code == 401) { }

			        else if(data.code == 200) { 
			            if(data.alert_message == undefined) {
			            	if(data.success==undefined) {
			            		alert('Auth Does Not Satisfy All Requirements');
			            	}
			            	else {
			            		document.getElementById('main_heading').innerHTML = 'PASSWORD REGENRATION SUCCESSFUL';
			            		document.getElementById('supporting_text').innerHTML = '';

			            		document.getElementById('token_div').style.display = 'none';
								document.getElementById('p1').style.display = 'none';
								document.getElementById('p2').style.display = 'none';
								document.getElementById('submit_btn_div').innerHTML = '';
								var new_html = `<h5>`+data.success+`</h5><a href="{{url('/')}}" class="btn btn-light"><i class='bx bx-arrow-back me-1'></i>Back to Login</a>`;
								document.getElementById('submit_btn_div').innerHTML = new_html;
			            	}
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