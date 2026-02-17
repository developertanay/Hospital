<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Attendance Sheet</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .attendance-sheet {
            width: 100%;
            border-collapse: collapse;
        }
        .attendance-sheet th, .attendance-sheet td {
            border: 1px solid #000;
            padding: 8px;
            text-align: center;
            font-size: 18px;
        }
        .attendance-sheet th {
            background-color: #f2f2f2;
        }
        .header-section {
            text-align: center;
            margin-bottom: 20px;
        }
        .header-section h1 {
            font-size: 24px;
            margin: 0;
            font-weight: bold;
        }
        .header-section p {
            margin: 5px 0;
            font-size: 16px;
        }
        .header-section .college-name {
            font-size: 24px;
            font-weight: bold;
        }
        .header-section .hindi-text {
            font-family: 'Mangal', Arial, sans-serif;
            font-size: 18px;
        }
        .header-section .session-details {
            font-size: 14px;
            margin-top: 5px;
        }
        .sub-header {
            font-size: 14px;
            margin-top: 10px;
            text-align: center;
        }
        .sub-header .left, .sub-header .right {
            display: inline-block;
            width: 45%;
            vertical-align: top;
        }
        .sub-header .left {
            text-align: left;
        }
        .sub-header .right {
            text-align: right;
        }
        .underline {
            text-decoration: underline;
        }
        .signature {
            margin-top: 20px;
            text-align: center;
        }
        .signature span {
            display: inline-block;
            width: 300px;
            border-top: 1px solid #000;
            margin-top: 70px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="header-section">
    <h1 class="college-name" style="font-size: 35px">{{$college_name}}</h1>
    
    <h1 class="session-details" style="font-size: 25px;">Session: {{$session_data->start_year.' - '.$session_data->end_year}}</h1>
</div>

<div class="sub-header">
    <div class="left" style="font-size: 22px;"><b>
        Subject :<span class="underline" >{{$subject_name.' (Sec : '.$section.')'}}</span><br>
        Term Classes Held: <span class="underline" >________</span><br>
         Teacher: <span class="underline">{{$fac_name}}</span></b>

    </div>
    <div class="right" style="font-size: 22px;"><b>
        Month: <span class="underline">{{$month_name}}</span><br>
        Theory/Practical/Tut. Class Held: <span class="underline" >________</span></b><br>
    </div>
</div>

<div class="sub-header" style="font-size: 20px; text-align: center;">
    <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
        <div style="text-align: left;">
        </div>
        <div style="text-align: right;">
        </div>
    </div>
</div>



<table class="attendance-sheet">
    <thead>
        <tr>
            <th rowspan="2">S.N</th>
            <th rowspan="2">Student Name</th>
            <th rowspan="2">Roll No</th>
            <th rowspan="2">Total Attend</th>
            <th colspan="31">Days</th>
            <th rowspan="2">Roll No</th>
            <th rowspan="2">Total Attend</th>
        </tr>
        <tr>
            <!-- Loop through 31 days -->
            <th>1</th> <th>2</th> <th>3</th> <th>4</th> <th>5</th> <th>6</th> <th>7</th> 
            <th>8</th> <th>9</th> <th>10</th> <th>11</th> <th>12</th> <th>13</th> 
            <th>14</th> <th>15</th> <th>16</th> <th>17</th> <th>18</th> <th>19</th> 
            <th>20</th> <th>21</th> <th>22</th> <th>23</th> <th>24</th> <th>25</th> 
            <th>26</th> <th>27</th> <th>28</th> <th>29</th> <th>30</th> <th>31</th>
        </tr>
    </thead>
    <tbody>
        <!-- Loop for students -->
        @foreach($student_name as $key => $value)
        <tr>
            <td>{{ $key + 1 }}</td>
            <td>{{ strtoupper($value->name) }}</td>
            <td>{{$value->roll_no}}</td>
            <td></td>
            <!-- Loop for 31 days -->
            <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> 
            <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> 
            <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> 
            <td></td> <td></td> <td></td> <td></td> <td></td> <td></td> 
            <td></td> <td></td> <td></td> <td></td> <td></td> <td></td>
            <td>{{$value->roll_no}}</td>
            <td></td>
        </tr>
        @endforeach
        <!-- Add more rows for additional students -->
        <!-- ... -->
    </tbody>
</table>

<div class="signature" style="font-size: 25px">
    <span>Teacher Signature</span>
</div>

</body>
</html>
