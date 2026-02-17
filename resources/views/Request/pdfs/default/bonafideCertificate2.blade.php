@extends('pdfs.layout')
@section('content')
    <div>
        <img src="letterHead.jpeg" alt="letterHead" style="width:100%;height:9em ;">
    </div>
    <div class="d-flex justify-content-center align-items-center" style="width:100vw;height:100vh;">
        <div >
            <h6 class="text-right pt-4" style="padding-right:20px;">Dated:{{Carbon\Carbon::now()->format('d-m-y')}}</h6>
            <h4 class="text-center" style="margin-top:80px;"><u>BONAFIDE CERTIFICATE</u></h4>
            <p class="p-5" style="text-align:justify;"> Certified that <strong>{{$user->name}} D/O {{$user->father_name}}</strong> was a
                Bonafide student of <strong>B.Com(honours)</strong> 3rd year bearing University Enrollment number <strong>{{$user->roll_no}}</strong> 
                of this college during the session {{$user->academic_year}}.</p>
            <br><br>
            <strong>
                <p class="text-left px-5">Professor Principal</p>
            </strong>
        </div>
    </div>
    @endsection