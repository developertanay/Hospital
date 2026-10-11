@extends('layouts.header')
@section('title', 'Hospital Management')
@section('content')
<div class="page-wrapper"><div class="page-content">
    <div class="page-breadcrumb mb-3"><h3>{{ $hospital['name'] }} · {{ ['inventory'=>'Bed Inventory', 'blood'=>'Blood Inventory', 'settings'=>'Hospital Settings'][$page] }}</h3><p>Manage records for your hospital.</p></div>
    @if(session('management_success'))<div class="alert alert-success" role="status">{{ session('management_success') }}</div>@endif
    @if($errors->any())<div class="alert alert-danger" role="alert">@foreach($errors->all() as $error)<div>{{ $error }}</div>@endforeach</div>@endif
    @if($page === 'inventory')
    <div class="card"><div class="card-body"><h5>Capacity by care type</h5><p>Set total beds, including occupied, reserved and held beds. Only available beds can be removed. Updates apply to emergency matching immediately.</p><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Care</th><th>Total</th><th>Available</th><th>Held</th><th>Reserved</th><th>Occupied</th><th>Update capacity</th></tr></thead><tbody>
    @foreach(['General','ICU','Ventilator'] as $care)
    @php($beds = collect($hospital['beds'])->where('care', $care))
    <tr><td>{{ $care }}</td><td>{{ $beds->count() }}</td><td>{{ $beds->where('status','Available')->count() }}</td><td>{{ $beds->where('status','Temporary')->count() }}</td><td>{{ $beds->where('status','Reserved')->count() }}</td><td>{{ $beds->where('status','Occupied')->count() }}</td><td><form method="POST" action="{{ url('emergency/manage') }}" class="d-flex gap-2">@csrf<input type="hidden" name="action" value="capacity"><input type="hidden" name="care" value="{{ $care }}"><input aria-label="Total {{ $care }} beds" class="form-control" style="max-width:100px" type="number" name="total" min="0" max="200" required value="{{ old('care') === $care ? old('total', $beds->count()) : $beds->count() }}"><button class="btn btn-primary">Save</button></form></td></tr>
    @endforeach
    </tbody></table></div></div></div>
    <div class="card"><div class="card-body"><h5>Occupied beds & discharge</h5><p>Discharge makes a bed available again. A linked patient reservation is marked Discharged.</p><div class="table-responsive"><table class="table"><thead><tr><th>Bed</th><th>Care</th><th>Admission reference</th><th>Action</th></tr></thead><tbody>
    @forelse(collect($hospital['beds'])->where('status','Occupied') as $bed)
    <tr><td>{{ $bed['id'] }}</td><td>{{ $bed['care'] }}</td><td>{{ $bed['request_id'] ?? 'Existing occupied bed' }}</td><td><form method="POST" action="{{ url('emergency/manage') }}" onsubmit="return confirm('Confirm patient discharge and make this bed available?')">@csrf<input type="hidden" name="action" value="discharge"><input type="hidden" name="bed_id" value="{{ $bed['id'] }}"><button class="btn btn-outline-danger">Discharge</button></form></td></tr>
    @empty<tr><td colspan="4">No occupied beds.</td></tr>@endforelse
    </tbody></table></div><a href="{{ url('emergency/beds') }}" class="btn btn-outline-primary">View individual bed availability</a></div></div>
    @elseif($page === 'blood')
    <div class="card"><div class="card-body"><h5>Blood stock by group</h5><p>Update available units for your hospital. Stock without an entry is shown as not recorded.</p><div class="table-responsive"><table class="table align-middle"><thead><tr><th>Blood group</th><th>Available units</th><th>Update stock</th></tr></thead><tbody>
    @foreach(['A+','A-','B+','B-','AB+','AB-','O+','O-'] as $group)
    <tr><td>{{ $group }}</td><td>{{ $hospital['blood'][$group] ?? 'Not recorded' }}</td><td><form method="POST" action="{{ url('emergency/manage') }}" class="d-flex gap-2">@csrf<input type="hidden" name="action" value="blood"><input type="hidden" name="blood_type" value="{{ $group }}"><input class="form-control" style="max-width:120px" aria-label="Units for {{ $group }}" type="number" name="units" min="0" max="10000" required value="{{ old('blood_type') === $group ? old('units') : ($hospital['blood'][$group] ?? '') }}"><button class="btn btn-primary">Save</button></form></td></tr>
    @endforeach
    </tbody></table></div></div></div>
    @else
    <div class="card"><div class="card-body"><h5>Hospital details & accepting status</h5><p>Offline hospitals are excluded from new bed recommendations. Existing reservations and admissions stay protected.</p><form method="POST" action="{{ url('emergency/manage') }}" style="max-width:600px">@csrf<input type="hidden" name="action" value="settings"><label for="hospital-status" class="form-label">Accepting new requests</label><select id="hospital-status" name="status" class="form-select mb-3">@foreach(['Open','Offline'] as $status)<option @if(old('status', $hospital['status']) === $status) selected @endif>{{ $status }}</option>@endforeach</select><label for="hospital-contact" class="form-label">Contact number</label><input id="hospital-contact" name="contact" class="form-control mb-3" maxlength="40" value="{{ old('contact', $hospital['contact'] ?? '') }}"><label for="hospital-address" class="form-label">Address</label><textarea id="hospital-address" name="address" class="form-control mb-3" maxlength="200">{{ old('address', $hospital['address'] ?? '') }}</textarea><button class="btn btn-primary">Save settings</button></form></div></div>
    @endif
</div></div>
@endsection
