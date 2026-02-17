
@if(!empty($pdf_heading))
    @foreach($pdf_heading as $key =>$value)
<h3 style="text-align:center">{{$value}}</h3>
    @endforeach
@endif  
</style>  

<table id="example9" class="table table-bordered table-hover display nowrap margin-top-10 w-p100" cellpadding="2" border=1 style="width: 100%; border-collapse: 1; border-spacing: 1;border: 1px solid black;">
    <colgroup>
        @foreach($column_arr as $column_name)
            <col span="1" style="width: {{$column_name}}%;">  
        }
        }
        @endforeach
    </colgroup>
<thead>
<tr>
    @if(!empty($column_arr))
    <?php
    $column_count=count($column_arr);
    ?>
        @for($i=0;$i<$column_count;$i++)
            <th style="word-wrap: break-word; max-width: {{$column_width}};">{{ $column_arr[$i] }}</th>
        @endfor
</tr>
</thead>
    @endif

@if(!empty($content_arr))
<tbody>
    @foreach ($content_arr as $key =>$value)
<tr>
        <?php
        $content_count=count($value);
        ?>
            @for($j=0;$j<$content_count;$j++)
            <td style="word-wrap: break-word; max-width: {{$column_width}};">{{$value[$j]}}</td>
            @endfor
</tr>
    @endforeach
@endif
</tbody>
</table>