@if($demoProfile['role']==='admin')
<div class="dashboard-details-grid">
@if(isset($assignedModules['Hospital']))
<section class="panel"><div class="panel-head"><h2>Hospital details</h2><button type="button" class="history-toggle" data-history-list="recent-hospitals" aria-controls="recent-hospitals" aria-expanded="false" @if(count($details['hospitals']) <= 2) hidden @endif>View all</button></div>
<div class="table-responsive"><table><thead><tr><th>Hospital</th><th>Registration</th><th>Contact</th><th>Status</th></tr></thead><tbody id="recent-hospitals">
@forelse($details['hospitals'] as $hospital)<tr data-recent-row @if($loop->index > 1) hidden @endif><td><strong>{{ $hospital->hospital_name }}</strong><p>{{ $hospital->address }}</p></td><td>{{ $hospital->registration_no ?: 'Not provided' }}</td><td>{{ $hospital->contact_no ?: 'Not provided' }}</td><td><span class="badge {{ $hospital->status==1 ? 'good' : '' }}">{{ $hospital->status==1 ? 'Active' : 'Inactive' }}</span></td></tr>
@empty<tr><td colspan="4">No hospitals added yet.</td></tr>@endforelse</tbody></table></div></section>
@endif
@if(isset($assignedModules['Ambulance']))
<section class="panel"><div class="panel-head"><h2>Ambulance details</h2><button type="button" class="history-toggle" data-history-list="recent-ambulances" aria-controls="recent-ambulances" aria-expanded="false" @if(count($details['ambulances']) <= 2) hidden @endif>View all</button></div>
<div class="table-responsive"><table><thead><tr><th>Vehicle</th><th>Category</th><th>Operation</th><th>Status</th></tr></thead><tbody id="recent-ambulances">
@forelse($details['ambulances'] as $ambulance)<tr data-recent-row @if($loop->index > 1) hidden @endif><td><strong>{{ $ambulance->vehicle_number }}</strong></td><td>{{ optional($ambulance->category)->name }}</td><td>{{ $ambulance->operation_type==='hospital' ? optional($ambulance->hospital)->hospital_name : 'Private' }}</td><td><span class="badge {{ $ambulance->status==1 ? 'good' : '' }}">{{ $ambulance->status==1 ? 'Active' : 'Inactive' }}</span></td></tr>
@empty<tr><td colspan="4">No ambulances added yet.</td></tr>@endforelse</tbody></table></div></section>
@endif
</div>
@elseif($demoProfile['role']==='hospital' && $details['hospital'])
<div class="dashboard-details-grid">
<section class="panel"><div class="panel-head"><h2>Your hospital</h2><span class="badge {{ $details['hospital']->accepting_requests ? 'good' : '' }}">{{ $details['hospital']->accepting_requests ? 'Open' : 'Offline' }}</span></div>
<h3>{{ $details['hospital']->hospital_name }}</h3><p>{{ $details['hospital']->address }}</p>
<details class="secondary-details"><summary>Contact & registration</summary><dl class="hospital-facts"><dt>Registration</dt><dd>{{ $details['hospital']->registration_no ?: 'Not provided' }}</dd><dt>Contact</dt><dd>{{ $details['hospital']->contact_no ?: 'Not provided' }}</dd><dt>Emergency contact</dt><dd>{{ $details['hospital']->emergency_no ?: 'Not provided' }}</dd></dl></details>
<h3>Departments</h3><div class="department-tags">@forelse($details['departments'] as $mapping)<span class="badge">{{ $mapping->department->department_name }}</span>@empty<p>No departments mapped yet.</p>@endforelse</div>
</section>
<section class="panel"><div class="panel-head"><h2>Bed inventory</h2></div><table><thead><tr><th>Bed status</th><th>Count</th></tr></thead><tbody>@forelse($details['bedStatus'] as $bed)<tr><td>{{ $bed->status }}</td><td>{{ $bed->total }}</td></tr>@empty<tr><td colspan="2">No bed inventory added yet.</td></tr>@endforelse</tbody></table></section>
</div>
@elseif($demoProfile['role']==='patient')
<section class="panel"><div class="panel-head"><h2>Recent requests</h2><button type="button" class="history-toggle" data-history-list="recent-requests" aria-controls="recent-requests" aria-expanded="false" @if(count($details['requests']) <= 2) hidden @endif>View all</button></div><div class="table-responsive"><table><thead><tr><th>Patient</th><th>Care type</th><th>Hospital</th><th>Status</th><th>Submitted</th></tr></thead><tbody id="recent-requests">
@forelse($details['requests'] as $request)<tr data-recent-row @if($loop->index > 1) hidden @endif><td>{{ $request->patient }}</td><td>{{ optional($request->type)->name ?: $request->care }}</td><td>{{ optional($request->hospital)->hospital_name ?: 'Not assigned' }}</td><td><span class="badge">{{ $request->status }}</span></td><td>{{ \Carbon\Carbon::createFromTimestamp($request->created_at)->timezone('Asia/Kolkata')->format('d M Y, h:i A') }}</td></tr>@empty<tr><td colspan="5">You have no requests yet.</td></tr>@endforelse</tbody></table></div></section>
@endif
