@extends('layouts.header')

@section('title')
Student
@endsection

@section('content')
    <!--start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!--breadcrumb-->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    {{--
                    <div class="btn-group">
                        <a href="{{ url('Module/create') }}" class="btn btn-primary">Add Module</a>
                    </div>
                    --}}
                </div>
            </div>
            <!--end breadcrumb-->

            <form class="card" method="POST" action="{{ route('ModuleAssigning.store') }}" id="module-form">
                @csrf
                    <input type="hidden" id="college_id" name="college_id" value="{{$data->college_id}}">
                    <input type="hidden" id="role_id" name="role_id" value="{{$data->role_id}}">

                <div class="card-body">
                    <h5 class="mb-4">ASSIGN MODULE </h5>
                        <div class="form-group row">
                            <div class="col-md-4">
                            <label for="single-select-field" class="form-label">College</label>
                             <select class="form-select single-select-field" name="college_id"  data-placeholder="Select College" >
                             @foreach($college_mast as $key => $value)
                            <option value="{{$key}} ">{{$value}}</option>
                            @endforeach
                             </select>
                         </div>
                        <div class="col-md-4">
                            <label for="single-select-clear-field" class="form-label">Role</label>
                             <select class="form-select single-select-clear-field" name="role_id"  data-placeholder="Select Role" disabled>
                             <option></option>
                             @foreach($role_mast as $key => $value)
                             @if($data->role_id == $key)
                            <option value="{{$key}} " selected>{{$value}}</option>
                            @else
                            <option value="{{$key}} " >{{$value}}</option>
                            @endif
                            @endforeach
                             </select>
                        </div>    
                        </div>
                    <div class="table-responsive" style="margin-top:20px">
                        <table id="example1" class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Parent Module</th>
                                    <th>Child Modules</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($parent_arr as $parent_key => $parent_value)
                                    <tr>
                                        <td>{{ $parent_value }}</td>
                                        <?php
                                        $parent_child_arr=!empty($child_arr[$parent_key])?$child_arr[$parent_key]:[];
                                        // dd($parent_child_arr);
                                        ?>
                                        @if(!empty($parent_child_arr))
                                        <td>
                                          <div class="col-md-12">
                                             <select class="form-select multiple-select-field" name="child_arr[{{$parent_key}}][]"  data-placeholder="Select Module" multiple>
                                             <option></option>
                                             @foreach($parent_child_arr as $key => $value)
                                                @if(in_array($value->id,$already_assigned_module_data))
                                                <option value="{{$value->id}} " selected>{{$value->name}}</option>
                                                @else
                                                <option value="{{$value->id}} ">{{$value->name}}</option>
                                                @endif
                                            @endforeach
                                             </select>
                                        </div>  
                                        </td>
                                        @else
                                        <td style="font-size:15px; color: red;">
                                        @if(in_array($parent_key,$already_assigned_module_data))
                                          <label style="display: inline-block; font-size:15px;"><b>Parent Already Asigned</b></label>
                                          <input type="checkbox" name="child_arr[{{$parent_key}}]" value=""  style="width: 25px; height: 18px;" checked />
                                          @else
                                          (No Child Exist for this parent)
                                          <label style="display: inline-block; font-size:15px;"><b>Assign Parent ?</b></label>
                                          <input type="checkbox" name="child_arr[{{$parent_key}}]" value=""  style="width: 25px; height: 18px;"/>
                                        </td>
                                        @endif
                                        @endif


                                        {{--
                                        @if($value->url == '#')
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                            <td></td>
                                        @else
                                            <td>
                                                <input type="checkbox" name="module[{{ $value->id }}][add]" value="2" onchange="checkbox_value(this)"  style="width: 18px; height: 18px;" />
                                            </td>
                                            <td>
                                                <input type="checkbox" name="module[{{ $value->id }}][view]" value="2" onchange="checkbox_value(this)"  style="width: 18px; height: 18px;" />
                                            </td>
                                            <td>
                                                <input type="checkbox" name="module[{{ $value->id }}][edit]" value="2" onchange="checkbox_value(this)"  style="width: 18px; height: 18px;" />
                                            </td>
                                            <td>
                                                <input type="checkbox" name="module[{{ $value->id }}][delete]" value="2" onchange="checkbox_value(this)"  style="width: 18px; height: 18px;" />
                                            </td>
                                        @endif
                                        --}}
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <button type="button" onclick="window.location='{{url($current_menu)}}'"  class="btn btn-light px-4">Cancel</button>
                    <button type="button" onclick="submit_form()" class="btn btn-primary" style="float: right; margin-bottom: 25px; margin-right: 16px;">Assign
                    </button>
                </div>
            </form>
        </div>
    </div>
    <!--end page wrapper -->
@endsection
@section('js')
<script >
function submit_form() {
    var form = document.getElementById("module-form");
    form.submit();
}

</script>
@endsection
