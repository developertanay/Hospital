@php($demoGroups = collect(\App\Support\RoleAccess::menu())->groupBy('group'))
@foreach($demoGroups as $group => $items)
<li class="menu-label">{{ $group }}</li>
@foreach($items as $item)
<li><a href="{{ url($item['url']) }}"><div class="parent-icon"><i class="{{ $item['icon'] }}"></i></div><div class="menu-title">{{ $item['name'] }}</div></a></li>
@endforeach
@endforeach
