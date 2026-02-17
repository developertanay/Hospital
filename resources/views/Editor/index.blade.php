@extends('layouts.header')

@section('title')
Editor
@endsection

@section('content')
<!--start page wrapper -->
<div class="page-wrapper">
    <div class="page-content">
        <!--breadcrumb-->
        <div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
            <!-- Your breadcrumb code here -->
        </div>
        <!--end breadcrumb-->
        <div class="row">
            <div class="col-md-12 ">

                <div class="card">
                    <div class="card-body p-4">
                        <h5 class="mb-4">EDITOR</h5>

                        <form method="POST" action="{{ route($current_menu.'.store') }}">
                            @csrf
                            @method('POST')
                            <div class="box-body">
                                <textarea name="content" id="editor" rows="10" cols="100"></textarea>
                                <br>
                            </div>
                            <div>
                                <p></p>
                            </div>
                             <div class="col-md-12">
                                <button type="button" onclick="window.location='{{url($current_menu)}}'" class="btn btn-light px-4">Cancel</button>
                                <button type="submit" style="float: right;" class="btn btn-primary px-4">Submit</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <!--end row-->
    </div>
</div>
<!--end page wrapper -->

<!-- Include CKEditor script at the bottom -->
 <script src="{{asset('ckeditor5-build-classic-39.0.1\ckeditor5-build-classic\ckeditor.js')}}"></script>   
<!-- CKEditor initialization code -->
<script>
    ClassicEditor
        .create(document.querySelector('#editor'), {
            toolbar: {
                // items: [
                //     'heading',
                //     '|',
                //     'bold',
                //     'italic',
                //     'Underline', 
                //     'link',
                //     'bulletedList',
                //     'numberedList',
                //     'blockQuote'
                // ],
                // shouldNotGroupWhenFull: true
            },
        })
        .catch(error => {
            console.error(error);
        });
</script>

@endsection
