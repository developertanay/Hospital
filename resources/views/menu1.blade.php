
@if(Auth::user()->role_id == 4)
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bx-message-square-edit'></i>
						</div>
						<div class="menu-title">Main</div>
					</a>
					<ul>
						<li><a  href="{{url('dashboard')}}"><i class='bx bx-radio-circle'></i>Dashboard</a></li>
						<li><a  href="{{url('Course_Subject_Mapping')}}"><i class='bx bx-radio-circle'></i>Course Subject Mapping</a></li>
						<li><a  href="{{url('UserSubjectMapping')}}"><i class='bx bx-radio-circle'></i>Student Subject Mapping</a></li>
						<li><a  href="{{url('SubjectApproval')}}"><i class='bx bx-radio-circle'></i>Subject Approval</a></li>
						<li><a  href="{{url('section_allotment')}}"><i class='bx bx-radio-circle'></i>Section Allotment</a></li>
						<li><a  href="{{url('sectionwise_student_allotment')}}"><i class='bx bx-radio-circle'></i>Student Wise Section</a></li>

						<li><a  href="{{url('SectionAllotment')}}"><i class='bx bx-radio-circle'></i>Allotment Listing</a></li>
						<li><a  href="{{url('TimeTable')}}"><i class='bx bx-radio-circle'></i>Create Time Table</a></li>
						<li><a  href="{{url('Mailer')}}"><i class='bx bx-radio-circle'></i>Mailer</a></li>
					</ul>
				</li>
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bx-message-square-edit'></i>
						</div>
						<div class="menu-title">Masters</div>
					</a>
					<ul>
						<li><a  href="{{url('Department')}}"><i class='bx bx-radio-circle'></i>Department</a></li>
						<li><a  href="{{url('FacultyMast')}}"><i class='bx bx-radio-circle'></i>Faculty</a></li>
						<li><a  href="{{url('CourseMast')}}"><i class='bx bx-radio-circle'></i>Course</a></li>
						<li><a  href="{{url('SubjectTypeMast')}}"><i class='bx bx-radio-circle'></i>Subject Type</a></li>
						<li><a  href="{{url('SubjectMast')}}"><i class='bx bx-radio-circle'></i>Papers / Subjects</a></li>
						<li><a  href="{{url('CollegeDeptMapping')}}"><i class='bx bx-radio-circle'></i>College Dept Mapping</a></li>
						<li><a  href="{{url('RoomMast')}}"><i class='bx bx-radio-circle'></i>Rooms</a></li>
						<li><a  href="{{url('LectureType')}}"><i class='bx bx-radio-circle'></i>Lecture Type</a></li>
						
					</ul>
				</li>
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bx-message-square-edit'></i>
						</div>
						<div class="menu-title">Admissions</div>
					</a>
					<ul>
						<li><a  href="{{url('Admission')}}"><i class='bx bx-radio-circle'></i>Upload Admission File</a></li>
						<li><a  href="{{url('AdmissionCancel')}}"><i class='bx bx-radio-circle'></i>Cancel Admissions</a></li>
						<li><a  href="{{url('RollNo')}}"><i class='bx bx-radio-circle'></i>Upload Roll No</a></li>
					</ul>
				</li>
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bx-message-square-edit'></i>
						</div>
						<div class="menu-title">Reports</div>
					</a>
					<ul>
						<li><a  href="{{url('genderwise_report')}}"><i class='bx bx-radio-circle'></i>Gender Report</a></li>
						<li><a  href="{{url('categorywise_report')}}"><i class='bx bx-radio-circle'></i>Category Report</a></li>
						<li><a  href="{{url('course_subject_mapping_report')}}"><i class='bx bx-radio-circle'></i>Course Mapping</a></li>
						<li><a  href="{{url('Student')}}"><i class='bx bx-radio-circle'></i>Student Report</a></li>
						<li><a  href="{{url('student_subject_listing')}}"><i class='bx bx-radio-circle'></i>Student Subject Report</a></li>
						<li><a  href="{{url('subject_sectionwise_report')}}"><i class='bx bx-radio-circle'></i>Section Wise Student</a></li>
						<li><a  href="{{url('mapping_defaulters')}}"><i class='bx bx-radio-circle'></i>Mapping Defaulters</a></li>
						<li><a  href="{{url('approve_students_report')}}"><i class='bx bx-radio-circle'></i>Approved Subjects</a></li>
						<li><a  href="{{url('student_in_subject_count')}}"><i class='bx bx-radio-circle'></i>Approved Subjects Count</a></li>
						<li><a  href="{{url('student_in_subject_coursewise_count')}}"><i class='bx bx-radio-circle'></i>Course Wise Approved Student Count</a></li>


					</ul>
				</li>
                  <li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bx-message-square-edit'></i>
						</div>
						<div class="menu-title">Time Table</div>
					</a>
					<ul>
						<li><a  href="{{url('facultywise_timetable_reportPDF')}}" target="blank"><i class='bx bx-radio-circle'></i>My Time Table</a></li>
						<li><a  href="{{url('roomwise_timetable')}}"><i class='bx bx-radio-circle'></i>Room-Wise Time-Table</a></li>
						
					</ul>
				</li>
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bx-message-square-edit'></i>
						</div>
						<div class="menu-title">Attendance</div>
					</a>
					<ul>
						<li><a  href="{{url('Attendance')}}" ><i class='bx bx-radio-circle'></i>Daily Attendance</a></li>
						<li><a  href="{{url('Attendance2')}}"><i class='bx bx-radio-circle'></i>Monthly Attendance</a></li>
						
					</ul>
				</li>
				{{--
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bx-message-square-edit'></i>
						</div>
						<div class="menu-title">Attendance Report</div>
					</a>
					<ul>
						<li><a  href="{{url('attendance_register')}}" ><i class='bx bx-radio-circle'></i> Register</a></li>
						<li><a  href="{{url('consolidated_attendance_report')}}"><i class='bx bx-radio-circle'></i>Monthly </a></li>
						<li><a  href="{{url('total_attendance_subjectwise')}}" ><i class='bx bx-radio-circle'></i>Daily</a></li>
						<li><a  href="{{url('attendance_not_marked_by_faculty')}}" ><i class='bx bx-radio-circle'></i>Unmarked Attendance</a></li>
					</ul>
				</li>
				--}}
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bx-message-square-edit'></i>
						</div>
						<div class="menu-title">APAR</div>
					</a>
					<ul>
					<a  href="{{url('Apar/create')}}"><i class='bx bx-radio-circle'></i>APAR</a>
					</ul>
				</li>
						
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bx-message-square-edit'></i>
						</div>
						<div class="menu-title">HelpDesk</div>
					</a>
					<ul>
						<li><a  href="{{url('UpdateSubjectIndex')}}"><i class='bx bx-radio-circle'></i>Update Subjects</a></li>
						<li><a  href="{{url('UpdateDSCIndex')}}"><i class='bx bx-radio-circle'></i>Update DSC Subjects</a></li>
						
					</ul>
				</li>
				@elseif(Auth::user()->role_id ==5)
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bx-message-square-edit'></i>
						</div>
						<div class="menu-title">Main</div>
					</a>
					<ul>
						<li><a  href="{{url('section_allotment')}}"><i class='bx bx-radio-circle'></i>Section Allotment</a></li>
						<li><a  href="{{url('sectionwise_student_allotment')}}"><i class='bx bx-radio-circle'></i>Student Wise Section</a></li>

						<li><a  href="{{url('SectionAllotment')}}"><i class='bx bx-radio-circle'></i>Allotment Listing</a></li>
						<li><a  href="{{url('TimeTable')}}"><i class='bx bx-radio-circle'></i>Create Time Table</a></li>
					</ul>
				</li>
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bx-message-square-edit'></i>
						</div>
						<div class="menu-title">Reports</div>
					</a>
					<ul>
						<li><a  href="{{url('course_subject_mapping_report')}}"><i class='bx bx-radio-circle'></i>Course Mapping</a></li>
						<li><a  href="{{url('Student')}}"><i class='bx bx-radio-circle'></i>Student Report</a></li>
						<li><a  href="{{url('student_subject_listing')}}"><i class='bx bx-radio-circle'></i>Student Subject Report</a></li>
						<li><a  href="{{url('mapping_defaulters')}}"><i class='bx bx-radio-circle'></i>Mapping Defaulters</a></li>
						<li><a  href="{{url('approve_students_report')}}"><i class='bx bx-radio-circle'></i>Approved Subjects</a></li>
						<li><a  href="{{url('student_in_subject_count')}}"><i class='bx bx-radio-circle'></i>Approved Subjects Count</a></li>
						<li><a  href="{{url('student_in_subject_coursewise_count')}}"><i class='bx bx-radio-circle'></i>Course Wise Approved Student Count</a></li>


					</ul>
				</li>
				@elseif(Auth::user()->role_id == 3)
				<li><a  href="{{url('UserSubjectMapping')}}"><i class='bx bx-menu'></i>Subject Mapping</a></li>

				<li><a  href="{{url('my_subject_report')}}"><i class='bx bx-menu'></i>Approved Subjects</a></li>
				<li><a  href="{{url('studentwise_timetable_reportPDF')}}" target="blank"><i class='bx bx-menu'></i>My Time Table</a></li>
				@else
				<li>
					<a class="has-arrow" href="javascript:;">
						<div class="parent-icon"><i class='bx bx-message-square-edit'></i>
						</div>
						<div class="menu-title">Others</div>
					</a>
					<ul>
						{{--
						<li><a  href="{{url('dashboard')}}"><i class='bx bx-radio-circle'></i>Dashboard</a></li>
						<li><a  href="{{url('SubjectTypeMast')}}"><i class='bx bx-radio-circle'></i>Subject Type</a></li>
						<li><a  href="{{url('SubjectMast')}}"><i class='bx bx-radio-circle'></i>Papers / Subjects</a></li>
						<li><a  href="{{url('FacultyMast')}}"><i class='bx bx-radio-circle'></i>Faculty</a></li>
						--}}
						<li><a  href="{{url('CategoryMast')}}"><i class='bx bx-radio-circle'></i>Category</a></li>
						<li><a  href="{{url('CollegeMaster')}}"><i class='bx bx-radio-circle'></i>College</a></li>
						{{--
						<li><a  href="{{url('Department')}}"><i class='bx bx-radio-circle'></i>Department</a></li>
						<li><a  href="{{url('CourseMast')}}"><i class='bx bx-radio-circle'></i>Course</a></li>
						<li><a  href="{{url('Course_Subject_Mapping')}}"><i class='bx bx-radio-circle'></i>Course Subject Mapping</a></li>
						--}}
						<li><a  href="{{url('GenderMast')}}"><i class='bx bx-radio-circle'></i>Gender</a></li>
						<li><a  href="{{url('DocumentMast')}}"><i class='bx bx-radio-circle'></i>Document</a></li>
						<li><a  href="{{url('QualificationMast')}}"><i class='bx bx-radio-circle'></i>Qualification</a></li>
						{{--
						<li><a  href="{{url('UserSubjectMapping')}}"><i class='bx bx-radio-circle'></i>Student Subject Mapping</a></li>
						<li><a  href="{{url('Admission')}}"><i class='bx bx-radio-circle'></i>Upload Admission File</a></li>
						--}}
						
						
						
					</ul>
				</li>
				@endif

