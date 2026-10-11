@extends('layouts.header')
@section('title','Driver Dispatch')
@section('content')
<div class="page-wrapper"><div class="page-content"><div class="card"><div class="card-body">
<h3>Driver Dispatch</h3><p>Vehicle and driver details are linked to your account. Keep this page open for new pickup offers.</p>
<div id="dispatch-message" class="alert alert-info" role="status">Loading dispatch…</div>
<p id="driver-details"></p><button id="driver-duty" class="btn btn-primary">Go online with GPS</button>
<div id="driver-trips" class="mt-4"></div>
</div></div></div></div>
@endsection
@section('js')
<script src="{{ asset('assets/js/driver-dispatch.js') }}" data-state-url="{{ url('emergency/dispatch/state') }}" data-action-url="{{ url('emergency/dispatch/action') }}"></script>
@endsection
