
<table style="width: 100%;" border=0>
<thead>
    <th style="">Student Name : {{$user_name->name}}</th>
    <th style="font-weight: bold; font-size: 25px; text-align: center;"></th>
    <th style="text-align: right; width: 33%;">Semester: {{$user_name->semester}}</th>
</thead>
</table>

<table id="example3" class="table table-bordered table-hover display nowrap margin-top-10 w-p100" cellpadding="4" border=1>
<thead>
    <tr>
    <th>Days/Time</th>
    @if(!empty($timeSlots))
        @foreach ($timeSlots as $slot)
            <th>{{ $slot }}</th>
        @endforeach
    @endif
    </tr>
</thead>

<tbody>
@foreach ($days as $day)
    <tr>
    <td>{{ $day }}</td>
    @foreach ($workingHours as $hour)
        <?php
        $subject_arr = [];
        $lecture_arr =[];
        $faculty_arr =[];
        $room_arr = [];
        $section_arr = [];
        $group_arr = [];
        
        ?>
        @if($hour->is_break==1)
            <td style="text-align: center; vertical-align: middle;max-width: 100px; word-wrap: break-word; white-space: normal; overflow: hidden; max-lines: 2;"><b>RECESS</b></td>
        @else
        <td style="text-align: center; vertical-align: middle;max-width: 100px; word-wrap: break-word; white-space: normal; overflow: hidden; max-lines: 2;">
        @foreach ($data as $recordArray)
            @foreach ($recordArray as $record)
                @if ($record['day'] == $day && $record['timing'] == $hour->start_time)
                    @if($college_id==33 && $record['lecture_type_id']==6)

              @else
                    <?php
                        
                        $subject = !empty($subject_mast[$record['subject_id']]) ? $subject_mast[$record['subject_id']] : '-';
                        $lecture = !empty($lecture_mast[$record['lecture_type_id']]) ? $lecture_mast[$record['lecture_type_id']] : '-';
                        $faculty = !empty($faculty_mast[$record['faculty_id']]) ? $faculty_mast[$record['faculty_id']] : '-';
                        $room = !empty($room_mast[$record['room_id']]) ? $room_mast[$record['room_id']] : '-';
                        // dd($subject,$lecture,$faculty,$subject_mast,$lecture_mast,$faculty_mast,$record['lecture_type_id']);
                        // dd($user_name);

                        $section = $record['section'];
                        $group = $record['group'];


                        if(!in_array($subject, $subject_arr)) {
                            $subject_arr[] = $subject;
                        }
                        if(!in_array($lecture, $lecture_arr)){
                            $lecture_arr[] = $lecture;
                        }
                        if(!in_array($faculty, $faculty_arr)){
                            $faculty_arr[] = $faculty;
                        }
                        if(!in_array($room, $room_arr)){
                            $room_arr[] = $room;
                        }
                        if(!in_array($section, $section_arr)){
                            $section_arr[] = $section;
                        }
                        if(!in_array($group, $group_arr)){
                            $group_arr[] = $group;
                        }

                        // dd($group_arr);

                    ?>

                @endif
                @endif
            @endforeach
        @endforeach
                    <?php 
                        // if(in_array('COMPUTER GRAPHICS', $subject_arr)&&count($subject_arr)>1) {
                        //     dd($subject_arr);
                        // }
                        $subject_str = implode(',', $subject_arr);
                        $lecture_str = implode(',', $lecture_arr);
                        $faculty_str = implode(',', $faculty_arr);
                        $room_str = implode(',', $room_arr);
                        $section_str = implode(',', $section_arr);
                        $group_str = implode(',', $group_arr);
                    ?>
                    @if($subject_str) 
                    {{ $subject_str }} ({{ $lecture_str }})<br>
                    {{ $faculty_str }}<br>

                    @endif
                    @if ((count($section_arr)>0) || (count($group_arr)>0))
                        
                        @if (count($section_arr)>0)
                            Section: {{$section_str }}
                        @endif
                        @if ((count($section_arr)>0) && (count($group_arr)>0))
                            &nbsp;&nbsp;&nbsp;&nbsp;
                        @endif
                        {{--
                        @if (count($group_arr)>0)
                            Group: {{ $group_str }}
                        @endif
                        --}}
                        <br>
                    @endif
                    @if($room_str)
                    ({{ $room_str }})<br>
                    @endif
        </td>
        @endif
      
    @endforeach
    </tr>
    @endforeach
</tbody>
</table>
<p style="float: right; font-weight: bold; font-size: 20px; padding-top: 10px;">
        This Time Table is system generated, hence no signature is required.
    </p>