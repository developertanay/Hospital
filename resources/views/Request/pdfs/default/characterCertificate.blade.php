@extends('pdfs.layout')
@section('content')
<body>
    <div style="width:100vw;height:100vh;display:flex;justify-content:center;align-items:center;">
        <div class="border border-dark text-center px-5" style="height:43em;">
            <div class="d-flex align-items-center mt-4">
                <img src="LOgo_RCDU.original.png" style="position:absolute;width:10%; padding:20px 10px;" alt="Ramanujan-Logo">
                <h1 class="mt-5 font-weight-bold" style="font-family:ariel;line-height:3px">RAMANUJAN COLLEGE</h1>
                <h5 class="lh-sm">Acredited Grade 'A++' by NACC <br>(University of Delhi) <br>C.R. Park MAin Road, Block H <br>Kalkaji, New Delhi-110019</h5>
                <h5 class="text-right pt-4">Date: {{Carbon\Carbon::now()->format('d-m-y')}}</h5>
                <h5 class="text-left" style="margin-top:-2em">RefNo.: 00014</h5>
            </div>
            <h3 style="margin-top:4em;"><u>CHARACTER CERTIFICATE</u></h3>
            <p class="p-1" style="font-size:1.3rem;text-align:justify; margin-top:4em;" >Certified that <strong>{{$user->name}} S/o-D/o {{$user->father_name}}</strong> is/was Bonafide 
            student <strong>Roll No. {{$user->roll_no}}</strong> of <strong>B.Com(honours)</strong> course in this College during year <strong>{{$user->academic_year}}</strong></p>  
            <br><br>
            <strong>
                <p class='mt-4'style="position:absolute;right:60px;">Principal</p>
            </strong>
        </div>
    </div>
    @endsection