@extends('layouts.header')

@section('title')
My Requests
@endsection

@section('content')
		<!--start page wrapper -->
		<div class="page-wrapper">
			<div class="page-content">
				<div class="card">
					<div class="card-body">
				<!--breadcrumb-->
				<div class="page-breadcrumb d-sm-flex align-items-center mb-3">
					<div>
						<h3>My Requests</h3>						
					</div>
					<div class="ms-auto">
						<div class="btn-group">

						</div>
					</div>
				</div>
				<!--end breadcrumb-->

							<div class="table-responsive">							
								<table id="example2" class="table table-striped table-bordered">
									<thead>
										<tr>
											
											<th style="text-align: center;">S.No.</th>
											<th style="text-align: center;">Letter/Certificate</th>
											<th style="text-align: center;">Requested On</th>
											<th style="text-align: center;">Status</th>
											<th style="text-align: center;">Action</th>
										</tr>								
			                        </thead>
									<tbody>
		        					<?php $i=0; ?>
		
								@foreach($applications as $key => $value)
								<?php 
									// dd($value);
									$encid = Crypt::encryptString($value->id);
									$letter_name = $value->letter_name;
									$requested_on = date('d-M-Y h:i A', strtotime($value->created_at));
									$status = $value->status;
									$status_desc = !empty($status_desc_arr[$status])?$status_desc_arr[$status]:'';
									if($status==3) {
										$color = 'green';
									}
									else if($status==5) {
										$color = 'red';	
									}
									else {
										$color = 'black';	
									}
								?>
								<tr>
									<td style="text-align: center;">{{++$i}}</td>
									<td style="text-align: center;">{{$letter_name}}</td>
									<td style="text-align: center;">{{$requested_on}}</td>
									<td style="text-align: center;"><font color="{{$color}}"><b>{{$status_desc}}</b></font></td>
									<td>
										<?php
											$url = 'downloadLetterPdf/'.$encid;
										?>
										@if($status==3)
										<center><a href='{{url("downloadLetterPdf/{$encid}")}}'><b>Download</b></a></center>
										@endif
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
		<!--end page wrapper -->
@endsection

@section('js')


@endsection