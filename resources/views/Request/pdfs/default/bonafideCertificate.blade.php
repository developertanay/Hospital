@extends('pdfs.layout')
@section('content')   
    <div style="width:100vw;height:200vh;display:flex;justify-content:center;align-items:center;z-index:22">
        <div class="text-center px-5">
            <div class="d-flex align-items-center mt-4">
                <img src="{{asset('assets/images/logos/LOgo_RCDU.original.png')}}" style="position:absolute;width:10%; padding:20px 10px;" alt="Ramanujan-Logo">
                <br>
                <h2 class="mt-5 font-weight-bold" style="font-family:ariel;line-height:3px">RAMANUJAN COLLEGE</h2>
                <h5 class="lh-sm">Acredited Grade 'A++' by NACC <br>(University of Delhi) <br>C.R. Park MAin Road, Block H <br>Kalkaji, New Delhi-110019</h5>
                <h5 class="text-right pt-5">
                    Date: {{date('d-M-Y', strtotime($application_data->verified_at))}}
                </h5>
                
                <h5 class="text-left" style="margin-top:-1.5em">RefNo.: 00014</h5>

            </div>
            <h3 style="margin-top:4em;"><u>BONAFIDE CERTIFICATE</u></h3>
            
            <p class="p-1" style="font-size:1.3rem;text-align:justify; margin-top:4em;" >
                This is to certify that <strong>{{$application_data->student_name}}</strong> is a bonafide student of <strong>{{$application_data->course_name}}, Semester : {{$application_data->semester}}</strong>, Class Roll No <strong>{{$application_data->roll_no}}</strong>.
            </p>
            <p class="" style="font-size:1.3rem;text-align:justify;" >
                The conduct of the student as recorded by the college has been found good.
            </p>

            <p class='mt-4'style="position:absolute;right:60px;">
                <strong>Principal</strong>
            </p>
            
        </div>
    </div>
    {{--
    <img style="position:absolute;z-index:-1;bottom:-50px;left:-80px;" src="wave.svg" alt="">
    --}}
@endsection