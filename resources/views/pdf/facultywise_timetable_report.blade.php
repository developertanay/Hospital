

<table style="width: 100%;" border=0>
<thead>
    <th style="text-align: left; width: 33%;">Year: {{$academic_year}} - {{$academic_year+1}}</th>
    <th style="font-weight: bold; font-size: 25px; text-align: centre;">{{!empty($user_name)?strtoupper($user_name):''}}</th>
    <th style="text-align: right; width: 33%;">{{!empty($session_duration)?('Session: '.$session_duration):''}}</th>
</thead>
</table>

<table id="example3" class="table table-bordered table-hover display nowrap margin-top-10 w-p100" cellpadding="4" border=1>
<thead>
<tr>
<th style="width: {{$workingwidth}}%;">Days/Time</th>
    @if(!empty($timeSlots))
        @foreach ($timeSlots as $slot)
            <th style="width: {{$workingwidth}}%;">{{ $slot }}</th>
        @endforeach
    @endif
</tr>
</thead>
<tbody>
    @foreach ($days as $day)
<tr>
    <td>{{ $day }}</td>
        @foreach ($workingHours as $hour)
        @if($hour->is_break==1)
                <td style="text-align: center; vertical-align: middle; width: {{$workingwidth}}%;"><b>RECESS</b></td>
                @else
            <td style="font-size: 12px;  width: {{$workingwidth}}%; max-width: 100px; word-wrap: break-word; white-space: normal; overflow: hidden; max-lines: 2;">
        @foreach ($data as $record)
            @if ($record->day == $day && $record->timing == $hour->start_time)
                <?php
                     // $course = !empty($course_mast[$record->course_id])?$course_mast[$record->course_id]:'-';
                     $subject = !empty($subject_mast[$record->subject_id])?$subject_mast[$record->subject_id]:'-';
                     $lecture = !empty($lecture_mast[$record->lecture_type_id])?$lecture_mast[$record->lecture_type_id]:'-';
                     $room = !empty($room_mast[$record->room_id])?$room_mast[$record->room_id]:'-';
                ?>

                {{ $subject }}  ({{ $lecture }})<br>
                ({{ $room }})
                (Sem: {{ $record->semester }})<br>
                @if ($record->section || $record->group)
                    @if ($record->section)
                        Section: {{ $record->section }}
                    @endif   
                    @if ($record->section && $record->group)
                        &nbsp;&nbsp;&nbsp;&nbsp;
                    @endif
                    @if ($record->group)
                        Group: {{ $record->group }}
                    @endif
                    <br>
                @endif
            @endif
        @endforeach
    </td>

            @endif
        @endforeach
</tr>
    @endforeach
</tbody>
</table>
    <p style="float: right; font-weight: bold;  font-size: 20px;padding-top: 10px;">
        This Time Table is system generated, hence no signature is required.

    </p>
