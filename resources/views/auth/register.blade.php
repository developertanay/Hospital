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


    <title>UniOne</title>



</head>

<body class="">
    <div id="overlay">
        <div class="cv-spinner">
            <span class="spinner"></span>
        </div>
    </div>
    <!--wrapper-->
    <div class="wrapper">
        <div class="d-flex align-items-center justify-content-center my-5">
            <div class="container-fluid">
                <div class="row row-cols-1 row-cols-lg-2 row-cols-xl-3">
                    <div class="col mx-auto">
                        <div class="card mb-0">
                            
                                <div class="card-body">
                                    <div class="">
                                        <div class="col">
                                            <div class="mb-3 text-center">
                                            <img src="{{asset('images/app_logo/1.jpeg')}}" width="150" alt="" />
                                        </div>
                                        {{--
                                            <h6 class="mb-0 text-uppercase">Please Fill the following Details</h6>
                                        --}}
                                         
                                               
                                                    <ul class="nav nav-tabs nav-primary" role="tablist">
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link active" data-bs-toggle="tab" href="#primaryhome" role="tab" aria-selected="true">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="tab-icon"><i class='bx bx-buildings font-18 me-1'></i>
                                                                    </div>
                                                                    <div class="tab-title">EMPLOYER</div>
                                                                </div>
                                                            </a>
                                                        </li>
                                                        <li class="nav-item" role="presentation">
                                                            <a class="nav-link" data-bs-toggle="tab" href="#primaryprofile" role="tab" aria-selected="false">
                                                                <div class="d-flex align-items-center">
                                                                    <div class="tab-icon"><i class='bx bx-user-pin font-18 me-1'></i>
                                                                    </div>
                                                                    <div class="tab-title">JOB SEEKER</div>
                                                                </div>
                                                            </a>
                                                        </li>
                                                        
                                                    </ul>
                                                    <div class="tab-content py-3">
                                                        <div class="tab-pane fade show active" id="primaryhome" role="tabpanel">
                                                            <div class="form-body">
                                                                {{--
                                                                <form class="row g-3" id="registerForm">
                                                                --}}
                                                                <form method="POST" action="{{ route('register') }}">
                                                                    @csrf
                                                                    <div class="col-12" style="margin-top: 10px;">
                                                                        <label for="fullname" class="form-label">Full Name</label>
                                                                        <input type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" name="name" placeholder="John" required>
                                                                        @error('name')
                                                                            <span class="invalid-feedback" role="alert">
                                                                                <strong>{{ $message }}</strong>
                                                                            </span>
                                                                        @enderror
                                                                        <input type="hidden" name="register_type" value="1">
                                                                    </div>
                                                                    <div class="col-12" style="margin-top: 10px;">
                                                                        <label for="inputEmailAddress" class="form-label">Email Address</label>
                                                                        <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" id="inputEmailAddress" placeholder="example@user.com" required>

                                                                        @error('email')
                                                                            <span class="invalid-feedback" role="alert">
                                                                                <strong>{{ $message }}</strong>
                                                                            </span>
                                                                        @enderror

                                                                    </div>
                                                                    <div class="col-12" style="margin-top: 10px;">
                                                                        <label for="inputChoosePassword" class="form-label">Password</label>
                                                                        <div class="input-group" id="show_hide_password">
                                                                            <input type="password" class="form-control border-end-0 @error('password') is-invalid @enderror" id="password" name="password" value="" placeholder="Enter Password"  autocomplete="" required> 
                                                                            {{--
                                                                            <a href="javascript:;" class="input-group-text bg-transparent">
                                                                                <i class='bx bx-hide'></i>
                                                                            </a>
                                                                            --}}
                                                                            @error('password')
                                                                                <span class="invalid-feedback" role="alert">
                                                                                    <strong>{{ $message }}</strong>
                                                                                </span>
                                                                            @enderror
                                                                        </div>
                                                                    </div>
                                                                    {{--
                                                                    <div class="col-12">
                                                                        <div class="form-check form-switch">
                                                                            <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked">
                                                                            <label class="form-check-label" for="flexSwitchCheckChecked">I read and agree to Terms & Conditions</label>
                                                                        </div>
                                                                    </div>
                                                                    --}}
                                                                    <div class="col-12" style="margin-top: 20px;">
                                                                        <div class="d-grid">
                                                                            <button type="submit" class="btn btn-primary" onclick="submit_form()">Sign Up</button>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-12">
                                                                        <div class="text-center ">
                                                                            <p class="mb-0 mt-2">Already have an account? <a href="{{url('login')}}">Sign in here</a></p>
                                                                        </div>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                        <div class="tab-pane fade" id="primaryprofile" role="tabpanel">
                                                            <div class="form-body">
                                                                {{--
                                                                <form class="row g-3" id="registerForm">
                                                                --}}
                                                                <form method="POST" action="{{ route('register') }}">
                                                                    @csrf
                                                                    <div class="col-12" style="margin-top: 10px;">
                                                                        <label for="fullname" class="form-label">Full Name</label>
                                                                        <input type="text" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" name="name" placeholder="John" required>
                                                                        @error('name')
                                                                            <span class="invalid-feedback" role="alert">
                                                                                <strong>{{ $message }}</strong>
                                                                            </span>
                                                                        @enderror
                                                                        <input type="hidden" name="register_type" value="2">
                                                                    </div>
                                                                    <div class="col-12" style="margin-top: 10px;">
                                                                        <label for="inputEmailAddress" class="form-label">Email Address</label>
                                                                        <input type="email" name="email" value="{{ old('email') }}" class="form-control @error('email') is-invalid @enderror" id="inputEmailAddress" placeholder="example@user.com" required>

                                                                        @error('email')
                                                                            <span class="invalid-feedback" role="alert">
                                                                                <strong>{{ $message }}</strong>
                                                                            </span>
                                                                        @enderror

                                                                    </div>
                                                                    <div class="col-12" style="margin-top: 10px;">
                                                                        <label for="inputChoosePassword" class="form-label">Password</label>
                                                                        <div class="input-group" id="show_hide_password">
                                                                            <input type="password" class="form-control border-end-0 @error('password') is-invalid @enderror" id="password" name="password" value="" placeholder="Enter Password"  autocomplete="" required> 
                                                                            {{--
                                                                            <a href="javascript:;" class="input-group-text bg-transparent"><i class='bx bx-hide'></i></a>
                                                                            --}}

                                                                            @error('password')
                                                                                <span class="invalid-feedback" role="alert">
                                                                                    <strong>{{ $message }}</strong>
                                                                                </span>
                                                                            @enderror
                                                                        </div>
                                                                    </div>
                                                                    {{--
                                                                    <div class="col-12">
                                                                        <div class="form-check form-switch">
                                                                            <input class="form-check-input" type="checkbox" id="flexSwitchCheckChecked">
                                                                            <label class="form-check-label" for="flexSwitchCheckChecked">I read and agree to Terms & Conditions</label>
                                                                        </div>
                                                                    </div>
                                                                    --}}
                                                                    <div class="col-12" style="margin-top: 20px;">
                                                                        <div class="d-grid">
                                                                            <button type="submit" class="btn btn-primary" onclick="submit_form()">Sign Up</button>
                                                                        </div>
                                                                    </div>
                                                                    <div class="col-12">
                                                                        <div class="text-center ">
                                                                            <p class="mb-0 mt-2">Already have an account? <a href="{{url('login')}}">Sign in here</a></p>
                                                                        </div>
                                                                    </div>
                                                                </form>
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
    <script type="text/javascript">
            
        function submit_form() {
            // $("#overlay").show();
        }
        
    </script>
    <!--app JS-->
    <script src="assets/js/app.js"></script>
</body>

</html>