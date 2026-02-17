<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" 
    integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <title>Foreign-Certificate</title>
</head>
    <div>
        <img src="letterHead.jpeg" alt="letterHead" style="width:100%;height:10em ;">
    </div>
    <div class="container d-flex justify-content-center align-items-center p-5" style="height:100vh;">
        <div>
            <div style="display:flex;justify-content:space-between;">
            <h6 class="text-right pt-4" style="margin-right:-4em;">Date: {{Carbon\Carbon::now()->format('d-m-y')}}</h6>
            <h6 class="text-left" style="margin-top:-1.9em;margin-left:-4em;">RefNo.: 00014</h6>
            </div>
                <h5 class="text-center" style="margin-top:5em;"><strong><u>TO WHOMSOEVER IT MAY CONCERN</u></strong></h5>
            <p class="my-5" style="text-align:justify;">
            
            This is to certify that <strong>{{$user->name}} D/O Mr. {{$user->father_name}}</strong>, bearing Passport 
            No.4235236224 College Roll No.{{$user->roll_no}} & University Enrollemnt  
            No.{{$user->enrolment_no}} from Republic of Botswana, Resident of 
             abcd is a bonafide student of {{$user->academic_year}} Year in the Ramanujan College, University of Delhi. He took
              admission in the Ramanujman College, University of Delhi in the year {{$user->admission_year}}<br><br>
             This certificate is being issued ony for the purpose of registration with the <strong>Foreigner's Regional Registration Office(FRRO).</strong>
             <br><br>
             <br><br>
             <h6 class="text-right"><strong>(Dr.S.P Aggrawal)</strong></h6>
             
    </div>
</body>
</html>