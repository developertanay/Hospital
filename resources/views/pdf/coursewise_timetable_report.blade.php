<table id="example3" class="table table-bordered table-hover display nowrap margin-top-10" cellpadding="4" border="1">
    <thead>
        <tr>
            <th style="text-align: center; vertical-align: middle; width: {{$workingwidth}}%;">Days/Time</th>
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
                <td style="text-align: center; vertical-align: middle; width: {{$workingwidth}}%;">{{ $day }}</td>
                @foreach ($workingHours as $hour)
                @if($hour->is_break==1)
                    <td style="text-align: center; vertical-align: middle ; font-size: 15px; width: {{$workingwidth}}%;"><b>RECESS</b></td>
                @else
                    <td style="font-size: 12px; width: {{$workingwidth}}%;">
                        @foreach ($data as $record)
                            @if ($record->day == $day && $record->timing == $hour->start_time)
                                <?php
                                $faculty = !empty($faculty_mast[$record->faculty_id]) ? $faculty_mast[$record->faculty_id] : '-';
                                $subject = !empty($subject_mast[$record->subject_id]) ? $subject_mast[$record->subject_id] : '-';
                                $lecture = !empty($lecture_mast[$record->lecture_type_id]) ? $lecture_mast[$record->lecture_type_id] : '-';
                                $room = !empty($room_mast[$record->room_id]) ? $room_mast[$record->room_id] : '-';
                                ?>
                                <span style="font-weight: bold;">{{ $subject }}</span> ({{ $lecture }})<br>
                                {{ $faculty }}<br>
                                Room: {{ $room }}<br>
                                @if ($record->section || $record->group)
                                    @if ($record->section)
                                        Section: {{ $record->section }}<br>
                                    @endif
                                    @if ($record->section && $record->group)
                                        
                                    @endif
                                    @if ($record->group)
                                        Group: {{ $record->group }}<br>
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
