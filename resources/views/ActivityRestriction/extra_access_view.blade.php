@extends('layouts.header')

@section('title')
Extra Access
@endsection

@section('content')
    <!-- start page wrapper -->
    <div class="page-wrapper">
        <div class="page-content">
            <!-- breadcrumb -->
            <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
                <!-- Your breadcrumb content -->
            </div>
            <!-- end breadcrumb -->
                    <div class="card">
                        <div class="form-group row">
                            <div class="col-md-6" style="padding-top: 20px;padding-left: 30px; ">
                                <h4>Extra Access For Attendance</h4>
                            </div>
                        </div>
                        <form action="{{route('extra_access_store')}}"  method="POST">
                              @csrf
                            <div class="form-group row">
                                <div class="col-md-3" style="margin-top: 10px; margin-left: 18px;">
                                    <label for="single-select-clear-field" class="form-label">No. of Previous Days to Access<font color="red"><b>*</b></font></label>
                                    <input type="number" class="form-control" name="previous_days" required>
                                </div>
                                <div class="col-md-3" style="margin-top: 10px; margin-left: 18px;">
                                    <label for="single-select-clear-field" class="form-label">Access Required Till (Date)<font color="red"><b>*</b></font></label>
                                    <input type="text" class="date-format" id="datepicker" name="till_date" required>
                                </div>
                                <div class="col-md-1" style="margin-top:38px ;float: right;">
                                    <button type="submit" class="btn btn-primary" >Submit</button>
                                </div>
                             </div>

                         </form>
                         <div class="card-body">
                             
                         </div>
                    </div>
                </div>
            </div>
       
    <!-- end page wrapper -->
@endsection

@section('js')
<script type="text/javascript">
    $("#datepicker").flatpickr({
        altInput: true,
        altFormat: "F j, Y",
        dateFormat: "Y-m-d",
        minDate: "today",
        maxDate: new Date().fp_incr(7)
    });
</script>
@endsection



