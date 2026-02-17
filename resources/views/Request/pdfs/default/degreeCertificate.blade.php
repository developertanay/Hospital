@extends('pdfs.layout')
@section('content')
    <div>
        <img src="letterHead.jpeg" alt="letterHead" style="width:100%;height:9em ;">
    </div>
    <div class="container d-flex justify-content-center align-items-center px-5" style="height:100vh;">
    
        <div class="py-5">
            <h6 class="text-right pt-3 pb-5" style="margin-right:-4em">Date:{{Carbon\Carbon::now()->format('d-m-y')}}</h6>
            <strong>
                <u class="text-center mt-4">
                    <h5>TO WHOMSOEVER IT MAY CONCERN</h5>
                </u>
            </strong>
            <p class="my-5" style="text-align:justify;">This is to certify that <strong>{{$user->name}} D/O {{$user->father_name}}</strong>, University Roll No.{{$user->roll_no}} has successfully
                completed her 03 years <strong>{{$user->class}}</strong> degree course from RamanujanCollege, Kalkaji, New Delhi-110019
                during the session {{$user->academic_year}}. The college is a constituent college of Delhi University.
                <br> <br>
                She has no backlog as per the university mark sheet and she has cleared the <strong>B.Com(honours)</strong> course in the academic session {{$user->academic_year}}.
                <br><br>
                This certificate is being issued on her request for the purpose of higher studies.
                <br><br>
                <br><br>
                <strong>Section Officer</strong>
            </p>

        </div>
    </div>
    @endsection