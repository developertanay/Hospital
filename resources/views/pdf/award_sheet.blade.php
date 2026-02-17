<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style>
        body {
            margin: 0; /* Remove default body margin */
            padding: 0; /* Remove default body padding */
        }

        h1, h2 {
            margin-top: 0; /* Remove top margin for h1 and h2 */
        }
    </style>
</head>
<body>
    <h1 style="text-align: center; font-size:30px; margin-top: 10px;">{{$college_data->college_name}}</h1>
    <h2 style="text-align: center; font-size:20px">University of Delhi</h2>
    <h2 style="text-align: center; font-size:20px">Kalkaji New Delhi 110019</h2>
    <?php
    $date=!empty($date_of_exam)?date('d/m/Y', strtotime($date_of_exam)):date('d/m/Y');
    ?>
    <p style="text-align: left; font-size:15px"><b>Date : {{$date}}</b> <span style="float:right;"><b>Sem : {{$semester}}</b></span></p>
    <p style="text-align: left; font-size:15px"><b>Subject : {{$subject_name}}</b> <span style="margin-left: 100px;"><b>UPC : {{$upc}}</b></span></p>

<table id="example" class="table table-bordered table-hover display nowrap margin-top-10 w-p100" cellpadding="4" border=1>
<thead>
    <tr>
    <th>S. No.</th>
    <th>Name</th>
    <th>University Roll No.</th>
    @if($subject_credit ==1)
    <th>Continuous Assessment (Max :10)</th>
    <th>End Term Practical Exam (Max : 20)</th>
    <th>Viva Voice (Max : 10)</th>
    <th>Total Marks (Max : 40)</th>

    @elseif($subject_credit ==2)
    <th>Continuous Assessment (Max :20)</th>
    <th>End-Term Practical Exam (Max : 40)</th>
    <th>Viva Voice (Max : 20)</th>
    <th>Total Marks (Max : 80)</th>
    @else
    <th>Continuous Assessment</th>
    <th>End Term Practical Exam</th>
    <th>Viva Voice </th>
    <th>Total Marks</th>
    @endif
    <th>Obtain Marks</th>
    <th>Signature</th>
    </tr>
</thead>
<?php
$i=0;
?>
<tbody>
    @foreach($students_arr as $key => $value)
    <tr>
    <td>{{++$i}}</td>
    <td style="text-align:left;">{{$value->name}}</td>
    <td style="text-align:left;">{{$value->examination_roll_no}}</td>
    <td></td>
    <td></td>
    <td></td>
    @if($subject_credit == 1)
    <td>40</td>
    @elseif($subject_credit == 2)
    <td>80</td>
    @else
    <td></td>
    @endif
    <td></td>
    <td></td>
    </tr>
    @endforeach
</tbody>
</table>
</body>
</html>