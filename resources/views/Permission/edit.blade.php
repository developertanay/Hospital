@extends('layouts.header')

@section('title')
Permission
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

            <div class="row">
                <div class="col-xl-12 mx-auto">
                    <div class="card">
                        <div class="card-body p-4">
                            <h5 class="mb-4">Permissions</h5>
                            <form class="row g-3" action="{{ route($current_menu.'.update',$encrypted_id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                @method('PATCH')
                                 <div class="col-md-3">
                                    <label for="single-select-clear-field" class="form-label">Module<font color="red"><b>*</b></font></label>
                                     <select class="form-select single-select-clear-field" name="module_id"  data-placeholder="Choose Subject Type" required>
                                        <option></option>
                                        <option value="1" selected>User Subject Mapping</option>
                                         
                                     </select>
                                 </div>
                                  <div class="col-md-3">
                                    <label for="single-select-clear-field" class="form-label">Course</label>
                                    <select class="form-select single-select-clear-field" name="course_id" data-placeholder="Select Course">
                                        <option></option>
                                        @foreach($course_arr as $key => $value)
                                             @if($key == $data->course_id)
                                            <option value="{{$key}}"selected>{{$value}}</option>
                                            @else
                                            <option value="{{$key}}">{{$value}}</option>
                                            @endif
                                                
                                        @endforeach 
                                    </select>
                                </div>
                                    <div class="col-md-3">
                                    <label for="single-select-clear-field" class="form-label">Semester</label>
                                    <select class="form-select single-select-clear-field" name="semesters" data-placeholder="Select Semester">
                                        @for ($i = 1; $i <= $sem; $i++)
                                            @if ($i == $data->semesters)
                                                <option value="{{ $i }}" selected>{{ $i }}</option>
                                            @else
                                                <option value="{{ $i }}">{{ $i }}</option>
                                            @endif
                                        @endfor

                                    </select>
                                </div>
                                <div class="col-md-3">
                                        <label for="single-select-clear-field" class="form-label">Status<font color="red"><b>*</b></font></label>
                                         <select class="form-select single-select-clear-field" name="status"  data-placeholder="Choose Status" required>
                                             
                                             <option value="1">Enable</option>
                                             <option value="2">Disable</option>
                                         </select>
                                     </div>                 
                                <input type="hidden" name="college_id" value="{{ implode(',', $college_ids) }}">

                                <div class="col-md-12">
                                    <button type="button" onclick="window.location='{{ url($current_menu) }}'" class="btn btn-light px-4">Cancel</button>
                                    <button type="submit" style="float: right;" class="btn btn-primary px-4">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end page wrapper -->
@endsection

<script>
    var semester_obj = '';
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
                                course_options += `<option value=${key} >${course_obj[key]}</option>`;
                            });
                            document.getElementById('course_id').innerHTML = '';
                            document.getElementById('course_id').innerHTML = course_options;
                         }
                        else{
                            document.getElementById('course_id').innerHTML = '';
                            document.getElementById('semester').innerHTML = '';
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
        if(semester_obj=='') {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                type: "post",
                url:  '{{route("getSemesterFromCourse")}}',
                dataType: 'json',
                data: {
                    'course_id': course_id
                },
                success: function (data) {
                    if(data.code == 401) { }

                    else if(data.code == 200) { 
                        if(data.alert_message == undefined) {
                            console.log(data);
                            var total_semester = data.semester;
                            // var course_obj = data.course_arr;
                            // semester_obj = data.semesters_in_course_arr;
                            var semester_html = `<option value="">Select</option>`;
                            
                            if(total_semester != undefined && total_semester != '') {
                                for(var i=1; i<=total_semester; i++) {
                                    semester_html += `<option value=${i} >${i}</option>`;
                                }
                            }
                            
                            document.getElementById('semester').innerHTML = '';
                            document.getElementById('semester').innerHTML = semester_html;
                         }
                        else{
                            alert(data.alert_message);
                        }
                    }
                }
            });
        }
        else {
            console.log(semester_obj, semester_obj[course_id]);
            var semesters = semester_obj[course_id];
            var semester_options = `<option value="">Select</option>`;
            if(semesters!=undefined) {
                for(var i=1; i<=semesters; i++) {
                    semester_options += `<option value=${i}>${i}</option>`;
                }
            }
            document.getElementById('semester').innerHTML = '';
            document.getElementById('semester').innerHTML = semester_options;
        }
        
    }



    $('#subject_type_id').change(function() {
        var subjectTypeId = $(this).val();
        $('#subject_id').data('subject-type-id', subjectTypeId);
        updateSubjectOptions();
    });

    function updateSubjectOptions() {
        var subjectTypeId = $('#subject_id').data('subject-type-id');
        $.ajax({
            url: '/getSubjectsBySubjectType/' + subjectTypeId,
            type: 'GET',
            success: function(response) {
                var options = '';
                $.each(response.subjects, function(key, subject) {
                    options += '<option value="' + subject.id + '">' + subject.name + '</option>';
                });
                $('#subject_id').html(options);
            }
        });
    }

    // Initial call to populate subjects when the page loads
    updateSubjectOptions();
</script>
