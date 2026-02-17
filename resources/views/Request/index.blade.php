@extends('layouts.header')

@section('title')
Request
@endsection

@section('content')
        <!--start page wrapper -->
        <div class="page-wrapper">
            <div class="page-content">
                <div class="card">
                    <h4 style="padding-left: 20px; padding-top:20px;">REQUEST FOR</h4>
                    <div class="card-body">
                        <div class="row row-cols-1 row-cols-lg-4 g-4 align-items-center">
                            @foreach($letters as $key => $value)
                            <button type="button" class="btn" data-bs-toggle="modal" data-bs-target="#modal{{$key}}" data-faculty-id="{{$key}}">
                                <div class="col">
                                    <div class="shadow p-3 rounded"><center>{{$value}}</center></div>
                                </div>
                            </button>
                            <div class="modal fade" id="modal{{$key}}" tabindex="-1" aria-labelledby="editStudentModalLabel" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content">
                                        <form action="{{url('apply_letter')}}" method="POST">
                                        
                                            @csrf
                                            <input type="hidden" name="letter_id" value="{{$key}}">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="editFacultyModalLabel">{{strtoupper($value)}}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="form-group">
                                                    @if($key==2)
                                                        <div class="row">
                                                            <div class="col-md-3" style="display: flex; align-items: center;">
                                                                <b>Company</b>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <input type="text" class="form-control" name="company_name" placeholder="Enter Company Name Here" required>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-3" style="display: flex; align-items: center;">
                                                                <b>Website</b>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <input type="text" class="form-control" name="company_website" placeholder="Enter Company Website Here" required>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-3" style="display: flex; align-items: center;">
                                                                <b>Profile</b>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <input type="text" class="form-control" name="profile" placeholder="Enter Offered Profile Here" required>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-3" style="display: flex; align-items: center;">
                                                                <b>From Date</b>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <input type="text" class="date-format form-control" name="from_date" placeholder="Select From Date" required>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-3" style="display: flex; align-items: center;">
                                                                <b>To Date</b>
                                                            </div>
                                                            <div class="col-md-9">
                                                               <input type="text" class="date-format form-control" name="to_date" placeholder="Select To Date" required>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-3" style="display: flex; align-items: center;">
                                                                <b>Ref. Name</b>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <input type="text" class="form-control" name="hr_name" placeholder="Enter Company Contact Person Name Here" required>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-3" style="display: flex; align-items: center;">
                                                                <b>Ref. Email Id</b>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <input type="text" class="form-control" name="hr_email" placeholder="Enter Company Contact Person Email Id Here" required>
                                                            </div>
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-3" style="display: flex; align-items: center;">
                                                                <b>Ref. Contact No.</b>
                                                            </div>
                                                            <div class="col-md-9">
                                                                <input type="text" class="form-control" name="hr_contact_no" placeholder="Enter Company Contact Person Number Here" required>
                                                            </div>
                                                        </div>

                                                    @else
                                                    <div class="row">
                                                        <center>
                                                            <span style="margin-top: 10px; margin-bottom: 10px;">
                                                            <b>
                                                                Do You really want to request for {{$value}} ?
                                                            </b>
                                                            </span>
                                                        </center>
                                                    </div>
                                                    @endif
                                                </div>
                                            </div>

                                            
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Proceed</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endforeach

                        </div>
                        <!--end row-->
                    </div>
                </div>
            </div>
        </div>
        <!--end page wrapper -->
@endsection

@section('js')

@endsection