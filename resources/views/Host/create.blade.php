@extends('layouts.header')

@section('title')
Host Creator
@endsection

@section('content')
<div class="page-wrapper">
    <div class="page-content">
        <div class="row">
            <div class="col-xl-12 mx-auto">
                <div class="card">
                    <div class="card-body">
                        <h5 class="mb-4">Host Creator</h5>
                        <form class='row g-3' action="{{route($current_menu.'.store')}}" method="POST">
                            @csrf
                           <div class="col-md-4">
                                <label for="single-select-clear-field" class="form-label">College<font color="red"><b>*</b></font></label>
                                 <select class="form-select single-select-clear-field" name="college_id"  data-placeholder="Choose College" required>
                                     <option></option>
                                        @foreach($college_mast as $key => $value)
                                    <option value="{{$key}}">{{$value}}</option>
                                        @endforeach
                                 </select>
                             </div>
                            <div class="col-md-3">
                                <label class="form-label">Host Name<font color="red"><b>*</b></font></label>
                                <input type="text" name="host_name" class="form-control"  placeholder="Enter College Host Name" required>
                            </div>
                            <div class="col-md-12">
                                <button type="button" onclick="window.location='{{url($current_menu)}}'"  class="btn btn-light px-4">Cancel</button>
                                <button type="submit" style="float: right;" class="btn btn-primary px-4">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection