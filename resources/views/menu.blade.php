<?php 
	use App\Models\Modules;

	$auth_data = Auth::user();

	$role_id = !empty($auth_data->role_id)?$auth_data->role_id:NULL;
    $college_id = !empty($auth_data->college_id)?$auth_data->college_id:NULL;
    $register_type = !empty($auth_data->register_type)?$auth_data->register_type:NULL;

	$module_arr = Modules::getAssignedModules($college_id, $role_id, $register_type);
	// dd($module_arr,12121212);
	$parent_arr = $module_arr['parent'];
	$child_arr = $module_arr['child'];
?>

@foreach($parent_arr as $parent_id => $parent_data)
	<li>
		@if($parent_data['url']=='#' || empty($parent_data['url']))
		<a class="has-arrow" href="javascript:;">
		@else
		<a  href="{{url($parent_data['url'])}}">
		@endif
			<div class="parent-icon"><i class="{{$parent_data['icon']}}"></i>
			</div>
			<div class="menu-title">{{$parent_data['name']}}</div>
		</a>

		@if(!empty($child_arr[$parent_id]))
			@foreach($child_arr[$parent_id] as $child_id => $child_data)
				<ul>
					<li><a  href="{{url($child_data['url'])}}"><i class="{{$child_data['icon']}}"></i>{{$child_data['name']}}</a></li>
				</ul>
			@endforeach
		@endif
	</li>
@endforeach