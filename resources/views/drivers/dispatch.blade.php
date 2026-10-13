@extends('layouts.header')
@section('title','Driver Dispatch')
@section('content')
<div class="page-wrapper crud-page"><div class="page-content"><div class="card"><div class="card-body">
<div class="dispatch-heading"><span class="dispatch-symbol" aria-hidden="true"><i class="bx bxs-ambulance"></i></span><h3>Driver dispatch</h3></div>
<div id="dispatch-message" class="alert alert-info" role="status">Loading dispatch…</div>
<p id="driver-details"></p><button id="driver-duty" class="btn btn-primary">Go online with GPS</button>
<div id="driver-trips" class="mt-4"></div>
</div></div></div></div>
@endsection
@section('js')
<script src="{{ asset('assets/js/driver-dispatch.js') }}" data-state-url="{{ url('emergency/dispatch/state') }}" data-action-url="{{ url('emergency/dispatch/action') }}"></script>
@endsection
