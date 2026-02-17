@extends('layouts.header')

@section('title')
Permission
@endsection

@section('content')
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
				<!--breadcrumb-->
				<div class="page-breadcrumb d-none d-sm-flex align-items-center mb-3">
					<div>
						<h3>PERMISSIONS</h3>
					</div>
					<div class="ms-auto">
						
						<div class="btn-group">
							<a href="{{route($current_menu.'.create')}}" class="btn btn-primary">PERMISSIONS</a>
							

					
						</div>
					</div>
				</div>
				<!--end breadcrumb-->
					<div class="card">
					<div class="card-body">
						<div class="table-responsive">
							
							<table id="example2" class="table table-striped table-bordered">
								<thead>
									<tr>
										<th>S.No.</th>
										<th>Module</th>
										<th>Course</th>
										<th>Semester</th>
										<th>Enabled\Disabled</th>
										
										<th>Action</th>
									</tr>
								</thead>
								<tbody>
										<?php 
								$i=0; 
							?>
							@foreach($data as $key => $value)
								<?php 
									$encid = Crypt::encrypt($value->id);
									// dd($value);
									$college_id = $value->college_id;
									$course_id = $value->course_id;
									$semesters = $value->semesters;
									// dd($college_id);
									if($value->status==1){
										$tata = 'Enabled';

									}
									else{
										$tata ='Disabled';
									}
									// $cus_string = $college_id.'-'.$course_id.'-'.$semesters;
									// dd($cus_string);
									
								?>
								<tr>
								
								<td>{{++$i}}</td>

								<td>
								    @if ($value->module == 1)
								        User Subject Mapping
								    @else
								        {{ $value->module }}
								        NULL
								    @endif
								</td>


								<td>@foreach($course_mast as $key=>$course)
										@if($key==$value->course_id)
												{{$course}}
			                            @endif						
									@endforeach
								</td>

								<td>{{$value->semesters}}</td>
								<td>{{$tata}}</td>
								<td>
									<a href="{{url($current_menu.'/'.$encid.'/edit')}}">
										<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-pencil-square" viewBox="0 0 16 16"><path d="M15.502 1.94a.5.5 0 0 1 0 .706L14.459 3.69l-2-2L13.502.646a.5.5 0 0 1 .707 0l1.293 1.293zm-1.75 2.456-2-2L4.939 9.21a.5.5 0 0 0-.121.196l-.805 2.414a.25.25 0 0 0 .316.316l2.414-.805a.5.5 0 0 0 .196-.12l6.813-6.814z"/><path fill-rule="evenodd" d="M1 13.5A1.5 1.5 0 0 0 2.5 15h11a1.5 1.5 0 0 0 1.5-1.5v-6a.5.5 0 0 0-1 0v6a.5.5 0 0 1-.5.5h-11a.5.5 0 0 1-.5-.5v-11a.5.5 0 0 1 .5-.5H9a.5.5 0 0 0 0-1H2.5A1.5 1.5 0 0 0 1 2.5v11z"/></svg>
									</a>
				           		</td>
							</tr>
							@endforeach
						</tbody>
						</table>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!--end page wrapper -->
@endsection
