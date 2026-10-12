import 'dart:async';

import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import 'session.dart';
import 'location.dart';
import 'sos.dart';

class AmbulanceChoices extends StatefulWidget {
  final Session session;
  final Map<String, dynamic> request;
  const AmbulanceChoices({
    super.key,
    required this.session,
    required this.request,
  });
  @override
  State<AmbulanceChoices> createState() => _AmbulanceChoicesState();
}

class _AmbulanceChoicesState extends State<AmbulanceChoices> {
  List<Map<String, dynamic>> options = [];
  Timer? timer;
  bool loading = false, booking = false;
  bool firstLoaded = false;
  void sessionChanged() {
    if (mounted) setState(() {});
    if (!firstLoaded && !s.busy && s.online) {
      WidgetsBinding.instance.addPostFrameCallback((_) {
        if (mounted) refresh();
      });
    }
  }

  String? error;
  String? availabilityMessage;
  Future<void> locateAndRefresh() async {
    if (loading || booking || s.busy) return;
    setState(() => loading = true);
    try {
      final fix = await PickupLocation.current();
      if (!mounted) return;
      final result = await s.api.call('sos/action', {
        'action': 'nearby',
        'request_id': widget.request['id'],
        ...fix,
      });
      if (mounted) {
        setState(() {
          options = (result['ambulances'] as List).cast<Map<String, dynamic>>();
          availabilityMessage = result['availability_message'] as String?;
          error = null;
        });
      }
    } catch (e) {
      if (mounted) setState(() => error = e.toString());
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  Session get s => widget.session;
  List<Map<String, dynamic>> get activeTrips =>
      (s.sosState?['calls'] as List? ?? [])
          .cast<Map<String, dynamic>>()
          .where(
            (c) => !['Completed', 'Cancelled', 'Expired'].contains(c['status']),
          )
          .toList();
  @override
  void initState() {
    super.initState();
    s.addListener(sessionChanged);
    WidgetsBinding.instance.addPostFrameCallback((_) => refresh());
    timer = Timer.periodic(const Duration(seconds: 20), (_) => refresh());
  }

  @override
  void dispose() {
    timer?.cancel();
    s.removeListener(sessionChanged);
    super.dispose();
  }

  Future<void> refresh() async {
    if (!mounted || loading || booking || !s.online || s.busy) return;
    final lifecycle = WidgetsBinding.instance.lifecycleState;
    if (lifecycle != null && lifecycle != AppLifecycleState.resumed) return;
    firstLoaded = true;
    if (activeTrips.isNotEmpty) {
      final trip = activeTrips.first;
      if (['Searching', 'Accepted', 'At pickup'].contains(trip['status']) &&
          (trip['location'] as Map)['mode'] == 'gps') {
        loading = true;
        try {
          final fix = await PickupLocation.current();
          if (mounted) {
            await s.sosAction({'action': 'location', 'id': trip['id'], ...fix});
          }
        } catch (e) {
          if (mounted) setState(() => error = 'Location update paused: $e');
        } finally {
          loading = false;
        }
      }
      return;
    }
    setState(() {
      loading = true;
    });
    try {
      final result = await s.api.call('sos/action', {
        'action': 'nearby',
        'request_id': widget.request['id'],
      });
      if (mounted) {
        setState(() {
          options = (result['ambulances'] as List).cast<Map<String, dynamic>>();
          error = null;
          availabilityMessage = result['availability_message'] as String?;
        });
      }
    } catch (e) {
      if (mounted) setState(() => error = e.toString());
    } finally {
      if (mounted) setState(() => loading = false);
    }
  }

  Future<void> book(Map<String, dynamic> ambulance) async {
    if (s.sosState?['profile_complete'] != true) {
      await editProfile(context, s);
      if (!mounted || s.sosState?['profile_complete'] != true) return;
    }
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Book this ambulance?'),
        content: Text(
          '${ambulance['vehicle']}\n${ambulance['distance_km']} km away · estimated ${ambulance['eta_minutes']} min\nDriver confirmation is required.',
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Back'),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(ctx, true),
            child: const Text('Confirm booking'),
          ),
        ],
      ),
    );
    if (confirm != true || !mounted) return;
    setState(() {
      booking = true;
      error = null;
    });
    try {
      final fix = await PickupLocation.current();
      if (!mounted) return;
      await s.sosAction({
        'action': 'book',
        'request_id': widget.request['id'],
        'driver_id': ambulance['driver_id'],
        ...fix,
      });
      if (mounted) setState(() => options = []);
    } catch (e) {
      if (mounted) setState(() => error = e.toString());
    } finally {
      if (mounted) setState(() => booking = false);
    }
  }

  void tracking() => Navigator.push(
    context,
    MaterialPageRoute<void>(
      builder: (_) => Scaffold(
        appBar: AppBar(title: const Text('Ambulance tracking')),
        body: SosScreen(session: s, openBed: (_) => Navigator.pop(context)),
      ),
    ),
  );
  @override
  Widget build(BuildContext context) {
    final internal = widget.request['problem_type'] == 'Internal';
    final trip = activeTrips.isEmpty ? null : activeTrips.first;
    final driver = trip?['driver'] as Map<String, dynamic>?;
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(18),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            Row(
              children: [
                const Icon(
                  Icons.local_shipping_outlined,
                  color: Color(0xFF008CFF),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    internal
                        ? 'Ambulance · recommended first'
                        : 'Ambulance · optional pickup',
                    style: const TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 8),
            const Text(
              'Nearby on-duty ambulances with fresh GPS and no active booking. Arrival times are estimates.',
            ),
            if (error != null)
              Padding(
                padding: const EdgeInsets.symmetric(vertical: 8),
                child: Text(error!, style: const TextStyle(color: Colors.red)),
              ),
            if (loading || booking) const LinearProgressIndicator(),
            if (trip != null) ...[
              const SizedBox(height: 12),
              Text(
                trip['status'] == 'Searching'
                    ? 'Booked · waiting for driver confirmation'
                    : 'Booking status: ${trip['status']}',
                style: const TextStyle(fontWeight: FontWeight.bold),
              ),
              if (driver != null) ...[
                Text('Vehicle: ${driver['vehicle']}'),
                Text('Driver: ${driver['name']}'),
                if ((driver['doctor_name'] ?? '').toString().isNotEmpty)
                  Text('Doctor: ${driver['doctor_name']}'),
                Text(
                  '${trip['distance_km']} km away · estimated ${trip['eta_minutes']} min',
                ),
                if (trip['driver_location_stale'] == true)
                  const Text(
                    'Driver GPS is stale; ETA may be out of date.',
                    style: TextStyle(color: Colors.orange),
                  ),
                if (trip['driver_available'] == false)
                  const Text(
                    'Driver is currently offline. Confirmation is pending; you can cancel from tracking and choose another ambulance.',
                    style: TextStyle(color: Colors.orange),
                  ),
                if ((driver['phone'] ?? '').toString().isNotEmpty)
                  OutlinedButton.icon(
                    onPressed: () => launchUrl(
                      Uri(scheme: 'tel', path: driver['phone'].toString()),
                    ),
                    icon: const Icon(Icons.phone),
                    label: const Text('Call driver'),
                  ),
              ],
              FilledButton.icon(
                onPressed: tracking,
                icon: const Icon(Icons.route),
                label: const Text('Track pickup and trip'),
              ),
            ] else ...[
              if (firstLoaded && !loading && options.isEmpty && error == null)
                Padding(
                  padding: EdgeInsets.symmetric(vertical: 14),
                  child: Text(
                    availabilityMessage ?? 'No idle ambulance within 25 km right now. Refresh or choose hospital care below.',
                  ),
                ),
              for (final ambulance in options)
                Container(
                  margin: const EdgeInsets.only(top: 12),
                  padding: const EdgeInsets.all(14),
                  decoration: BoxDecoration(
                    color: const Color(0xFFF1F7FF),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Text(
                        ambulance['vehicle'].toString(),
                        style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 17,
                        ),
                      ),
                      if ((ambulance['category'] ?? '').toString().isNotEmpty)
                        Text(ambulance['category'].toString()),
                      Text(
                        '${ambulance['distance_km']} km · estimated arrival ${ambulance['eta_minutes']} min',
                      ),
                      Text(
                        (ambulance['doctor_name'] ?? '').toString().isEmpty
                            ? 'Doctor details not provided by operator'
                            : 'Doctor: ${ambulance['doctor_name']}',
                      ),
                      FilledButton(
                        onPressed: s.online && !s.busy && !booking
                            ? () => book(ambulance)
                            : null,
                        child: const Text('Book this ambulance'),
                      ),
                    ],
                  ),
                ),
              TextButton.icon(
                onPressed: loading || booking ? null : refresh,
                icon: const Icon(Icons.refresh),
                label: const Text('Refresh idle ambulances'),
              ),
              OutlinedButton.icon(
                onPressed: loading || booking ? null : locateAndRefresh,
                icon: const Icon(Icons.my_location),
                label: const Text('Use current location to find ambulances'),
              ),
            ],
          ],
        ),
      ),
    );
  }
}
