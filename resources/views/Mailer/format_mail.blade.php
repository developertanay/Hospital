@extends('layouts.header')

@section('title')
Mail
@endsection

@section('content')
		<!--start page wrapper -->
<div class="page-wrapper">
	<div class="page-content">
		<!--breadcrumb-->
		<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
						{{--
			<div class="breadcrumb-title pe-3">STUDENT</div>
			
			<div class="ps-3">
				<nav aria-label="breadcrumb">
					<ol class="breadcrumb mb-0 p-0">
						<li class="breadcrumb-item"><a href="javascript:;"><i class="bx bx-home-alt"></i></a>
						</li>
						<li class="breadcrumb-item active" aria-current="page">Compose Mail</li>
					</ol>
				</nav>
			</div>
						--}}
			{{--
			<div class="ms-auto">
				<div class="btn-group">
					<button type="button" class="btn btn-primary">Settings</button>
					<button type="button" class="btn btn-primary split-bg-primary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown">	<span class="visually-hidden">Toggle Dropdown</span>
					</button>
					<div class="dropdown-menu dropdown-menu-right dropdown-menu-lg-end">	<a class="dropdown-item" href="javascript:;">Action</a>
						<a class="dropdown-item" href="javascript:;">Another action</a>
						<a class="dropdown-item" href="javascript:;">Something else here</a>
						<div class="dropdown-divider"></div>	<a class="dropdown-item" href="javascript:;">Separated link</a>
					</div>
				</div>
			</div>
			--}}
		</div>
		<!--end breadcrumb-->
		<div class="row">
			<div class="col-xl-12 mx-auto">
				
				<div class="card">
					<div class="card-body p-4">
						<h5 class="mb-4">Compose Mail</h5>
			<form class="row g-3" action="{{url('send_mail_to_students')}}" method="POST" >
				
				@csrf
				<div class="box-body">
                    <div class="form-group row">
					<div class="col-md-12">
                        <label for="single-select-clear-field" class="form-label">College<font color="red"><b>*</b></font></label>
                        <select class="form-select single-select-clear-field" name="college_id" id="college_id" data-placeholder="Select College" onchange="getCourses(this.value)" required>
                            <option value="">Select</option>
                            @if(count($college_arr)==1)
                            @foreach($college_arr as $key => $value)
                                    <option value="{{ $key }}" selected>{{ $value }}</option>
                            @endforeach
                            @else
                            @foreach($college_arr as $key => $value)
                                    <option value="{{ $key }}">{{ $value }}</option>
                            @endforeach
                            @endif
                        </select>
                    </div>
                    <div class="col-md-12">
                        <label for="multiple-select-field" class="form-label">Course</label>
                        <select class="form-select multiple-select-field" name="course_arr[]" id="course_arr" onchange="getSemesters(this.value)" data-placeholder="Select Course" multiple></select>
                    </div>
                    <div class="col-md-9">
                        <label for="multiple-select-field" class="form-label">Semester</label>
                        <select class="form-select multiple-select-field" name="semester_arr[]" id="semester_arr" data-placeholder="Select Semester" multiple>
                        </select>
                   </div>

                   <div class="col-md-3 " style="padding-top: 30px;" >
						<button type="button" onclick="getSelectedValues()" class="btn btn-primary">Filter Recipients </button>
					</div>
					<!-- <div>
						<label>.</label>
					</div> -->
					<div class="col-md-12 mt-7" >
						<label for="multiple-select-field" class="form-label">Recipients</label>
						 <select class="form-select multiple-select-field" name="recipients_arr[]" id="recipients_arr" data-placeholder="Select Recipients" multiple>
							 
						 </select>
					</div>
					<div class="col-md-12">
						<label class="form-label">Subject<font color="red"><b>*</b></font></label>
						<input type="text" name="subject" class="form-control"  placeholder="Enter Subject" maxlength="50" required>
					</div>


					<div class="col-md-11" style="margin-top: 20px;">
                      
                        <textarea id="editor" name="body_arr[]" class="editor-content" ></textarea>              
                    </div>
					
					<div class="col-md-12">
                        <button type="button" onclick="window.location='{{ url($current_menu) }}'" class="btn btn-light px-4">Cancel</button>
                        <button type="submit" style="float: right;" class="btn btn-primary px-4">Submit</button>
                    </div>

				</div>
            </div>
			</form>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

@endsection


@section('js')

	<script type="text/javascript">
		jQuery(function ($) {        
		  	$('form').bind('submit', function () {
		    	$(this).find(':input').prop('disabled', false);
		  	});
		});
		
	function getSelectedValues() {
    var selected_college = $("#college_id").val();
    var selected_course = $("#course_arr").val();
    var selected_semester = $("#semester_arr").val();

    var data = {
        "_token": "{{ csrf_token() }}",
        "college": selected_college,
        "course": selected_course,
        "semester": selected_semester
    };

    $.ajax({
        type: "POST",
        url: '{{ route("getstudentsbyCollegeCourseSemester") }}',
        dataType: 'json',
        data: data,
        success: function(data) {
		        if(data.code == 401) { }

		        else if(data.code == 200) { 
		        	console.log(data.student_list);
		           	var student_mast = data.student_list;
		           	var student_options = ``;
		     		Object.keys(student_mast).forEach(key => {
                        // console.log(key, student_mast[key]);
                        student_options += `<option value=${key}>${student_mast[key]}</option>`;
                    });

		     		console.log(student_options);
		     		document.getElementById('recipients_arr').innerHTML = '';
		     		document.getElementById('recipients_arr').innerHTML = student_options;
		     		$("#recipients_arr").trigger("chosen:updated");
		        }
		    }
		});


}


		
	</script>

<script>
    var semester_obj = '';

    // $(document).ready(function () {
    //     // console.log("Hello World!");
    //     var college_id = document.getElementById('college_id').value;
    //     console.log(college_id);
    //     getCourses(college_id);
    // });

    document.addEventListener("DOMContentLoaded", () => {
        console.log("Hello World!");
        var college_id = document.getElementById('college_id').value;
        console.log(college_id);
        getCourses(college_id);
    });

    function getCourses(value) {
        var college_id = value;
        if(college_id != '') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $( "#overlay" ).show();

            $.ajax({
                type: "post",
                url:  '{{route("getCoursesFromCollege")}}',
                dataType: 'json',
                data: {
                    'college_id': college_id
                },
                success: function (data) {
                    $( "#overlay" ).hide();

                    if(data.code == 401) { }

                    else if(data.code == 200) { 
                        if(data.alert_message == undefined) {
                            console.log(data);
                            var course_obj = data.course_arr;
                            semester_obj = data.semesters_in_course_arr;
                            var course_options = `<option value="">Select</option>`;
                            Object.keys(course_obj).forEach(key => {
                                console.log(key, course_obj[key]);
                                course_options += `<option value=${key}>${course_obj[key]}</option>`;
                            });
                            document.getElementById('course_arr').innerHTML = '';
                            document.getElementById('course_arr').innerHTML = course_options;
                         }
                        else{
                            document.getElementById('course_arr').innerHTML = '';
                            document.getElementById('semester_arr').innerHTML = '';
                            alert(data.alert_message);
                        }
                    }
                }
            });
        }
        else {

        }
    }

    function getSemesters(value) {
        var course_id = value;
        console.log(semester_obj, semester_obj[course_id]);
        var semesters = semester_obj[course_id];
        var semester_options = `<option value="">Select</option>`;
        if(semesters!=undefined) {
            for(var i=1; i<=semesters; i++) {
                semester_options += `<option value=${i}>${i}</option>`;
            }
        }
        document.getElementById('semester_arr').innerHTML = '';
        document.getElementById('semester_arr').innerHTML = semester_options;
    }


</script>
    <!-- <script src="{{asset('assets/js/ckeditor.js')}}"></script> -->
    <script src="https://cdn.ckeditor.com/4.16.2/full-all/ckeditor.js"></script>
    <!-- <script>
        function executeCommand(command) {
          document.execCommand(command, false, null);
        }

        CKEDITOR.replace('editor-content');
    </script>
    <script>

     
        
        ClassicEditor.create( document.querySelector( '#body' ),{
        label: 'Basic styles',
        // Show the textual label of the dropdown.
        // Note that the "icon" property is not configured and defaults to three dots.
        withText: true,
        items: [ 'bold', 'italic', 'strikethrough', 'superscript', 'subscript' ]
    }
     )
        .catch( error => {
            console.error( error );
        });

    </script> -->
    <script type="text/javascript">
        function transferData(){
            // var html_test = $(".cke_editable").html();
            // var html_test = $(".editor-content").html();
            // var html_test = $("#editor").html();
            var html_test = document.getElementById('editor').innerHTML;
            console.log(html_test);
            var heading = $("#heading").val();
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $( "#overlay" ).show();

            $.ajax({
                type: "post",
                url: domain + '/CkEditorSubmission',
                dataType: 'json',
                data: {"html_test" : html_test,"heading":heading},
                success: function (data) 
                {
                    $( "#overlay" ).hide();

                    if(data.code == 200){
                        alert('successfully submitted');
                        // location.reload();
                        location.href = 'http://shrinivas.msell.in/public/CkEditor';
                    }
                }
            });
        }
    </script>

    <script>
        // Initialize CKEditor
        CKEDITOR.replace('editor');

        // Create dynamic ruler
        var ruler = document.getElementById('ruler');
        var editor = CKEDITOR.instances.editor;
        var alignmentRange = document.getElementById('alignment');
        var pageWidthRange = document.getElementById('page-width');
        var pageHeightRange = document.getElementById('page-height');

        function updateRuler() {
            var editorOffset = editor.container.$.getBoundingClientRect();
            ruler.style.left = editorOffset.left + 'px';
            ruler.style.width = editorOffset.width + 'px';

            var marks = '';
            var contentWindow = editor.document.getWindow().$;
            var contentDocument = contentWindow.document;
            var contentScrollLeft = contentDocument.documentElement.scrollLeft || contentDocument.body.scrollLeft;

            for (var i = 0; i < contentDocument.documentElement.clientWidth; i += 10) {
                var mark = document.createElement('div');
                mark.className = 'mark';
                mark.style.left = (editorOffset.left + i - contentScrollLeft) + 'px';
                marks += mark.outerHTML;
            }

            ruler.innerHTML = marks;
        }

        // Update ruler on content scroll
        editor.on('contentDom', function () {
            var contentWindow = editor.document.getWindow().$;
            contentWindow.addEventListener('scroll', updateRuler);
        });

        // Update ruler on editor resize
        editor.on('resize', updateRuler);

        function adjustPage() {
            var alignmentValue = alignmentRange.value + 'px';
            document.body.style.marginLeft = alignmentValue;
            document.body.style.marginRight = alignmentValue;
        }
        
        function adjustPageWidth() {
            var pageWidthValue = pageWidthRange.value + '%';
            document.body.style.width = pageWidthValue;
        }
        
        function adjustPageHeight() {
            var pageHeightValue = pageHeightRange.value + 'vh';
            document.body.style.height = pageHeightValue;
        }
    </script>

    <script>
    const addRowButton = document.getElementById('addRowBtn');
    const additionalRowsContainer = document.getElementById('additionalRowsContainer');
    let rowCounter = 0;

    addRowButton.addEventListener('click', () => {
        rowCounter++;

        const newRow = document.createElement('div');
        newRow.className = 'row g-1 additional-row';
        newRow.id = `additionalRow_${rowCounter}`;

        newRow.innerHTML = `
            <div class="col-md-11">
                      
                        <textarea name="body_arr[]" class="form-control" placeholder=""></textarea>              
                    </div>
                   <div class="col-md-1">  
                   <center> <button class="btn btn-danger" style="margin-top: 3px;"  style="float: right" onclick="removeRow(${rowCounter})"> -- </button></center>
             
            </div>
        `;

        additionalRowsContainer.appendChild(newRow);
    });
     function removeRow(rowNumber) {
        const rowToRemove = document.getElementById(`additionalRow_${rowNumber}`);
        additionalRowsContainer.removeChild(rowToRemove);
    }
</script>
@endsection
