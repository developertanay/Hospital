 <center><u><h2>STUDENT SUBJECT MAPPING REPORT</h2></u></center>
<table id="example2" class="table table-bordered table-hover display nowrap margin-top-10 w-p100" cellpadding="4" border=1>								<thead>
									<tr>
								<th>S.No.</th>
								{{--<th>Student Roll Number</th>--}}
								<th>Name / Course</th>
								{{--
								<th>Course</th>
								<th>Semester</th>
								--}}
								<th>Subject Type</th>
								<th>Subjects</th>
							</tr>								
                        </thead>
						<tbody>
		        					<?php $i=0; ?>
		        			@foreach($final_data_arr as $key => $course_arr)
								<?php 
									// dd($value);
									$name=!empty($student_name_arr[$key])?$student_name_arr[$key]:'';
									$semester=!empty($student_semester_arr[$key])?$student_semester_arr[$key]:'';
									$course_id=!empty($student_course_arr[$key])?$student_course_arr[$key]:'';
									$course = !empty($course_id)?$course_mast[$course_id]:'-';
									$curr_rowspan=!empty($rowspan_count[$key])?($rowspan_count[$key]):0;
								?>
								<tr>
									<td rowspan="{{$curr_rowspan}}">{{++$i}}</td>
									{{--<td></td>--}}
									<td rowspan="{{$curr_rowspan}}">
										{{$name}}<br>{{$course}}<br>Semester : {{$semester}}
									</td>
									{{--
									<td rowspan="{{$curr_rowspan}}">{{$course}}</td>
									<td rowspan="{{$curr_rowspan}}">{{$semester}}</td>
									--}}
									@foreach($course_arr as $course_id=>$subject_type_id_arr)
										@foreach($subject_type_id_arr as $subject_type_id=>$subject_id_arr)

										<td rowspan="{{count($subject_id_arr)}}">  
											{{!empty($subject_type_mast[$subject_type_id])?$subject_type_mast[$subject_type_id]:$subject_type_id}}  
										</td>

											@foreach($subject_id_arr as  $subject_id )
								
												<td>
													{{!empty($subject_mast[$subject_id])?$subject_mast[$subject_id]:$subject_id}}
												</td>
											</tr>
											@endforeach
											<tr>
										@endforeach	
									

									@endforeach		
									<tr>
							@endforeach		


		        			{{--

							<?php $i=0; ?>
							@foreach($final_data_arr as $user_profile_id => $subject_type_arr)
								<?php
									$name = !empty($student_name_arr[$user_profile_id])?$student_name_arr[$user_profile_id]:'';
									$course = !empty($student_course_arr[$user_profile_id])?$student_course_arr[$user_profile_id]:'';
									$semester = !empty($student_semester_arr[$user_profile_id])?$student_semester_arr[$user_profile_id]:'';
									$rowspan_count = !empty($rowspan_count[$user_profile_id])?$rowspan_count[$user_profile_id]:1;
								?>
								<tr>
									<td>{{++$i}}</td>
									<td>-</td>
									<td rowspan="{{$rowspan_count}}">{{$name}}</td>
									<td rowspan="{{$rowspan_count}}">{{$course}}</td>
									<td rowspan="{{$rowspan_count}}">{{$semester}}</td>
									@foreach($subject_type_arr as $subject_type_id => $subject_arr)
										<?php 
											$inner_rowspan = count($subject_arr);
											$subject_type = !empty($value->subject_type_id)?$subject_type_mast[$value->subject_type_id]:'-';

										?>
										<td rowspan="{{$inner_rowspan}}">{{$subject_type}}</td>
										@foreach($subject_arr as $key => $subject_id)
											<?php 
												$subject = !empty($value->subject_id)?$subject_mast[$value->subject_id]:'-';
											?>
											<td>{{$subject}}</td>
										@endforeach
									@endforeach
								</tr>
							@endforeach
							--}}
					</tbody>
					</table>