import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:url_launcher/url_launcher.dart';

import 'session.dart';
import 'location.dart';

const _red = Color(0xFFC6283D);
const _navy = Color(0xFF102C46);

class SosScreen extends StatefulWidget {
  final Session session;
  final void Function(String) openBed;
  const SosScreen({super.key, required this.session, required this.openBed});
  @override
  State<SosScreen> createState() => _SosScreenState();
}

class _SosScreenState extends State<SosScreen> {
  bool demo = false, locating = false;
  String? locationError;
  Timer? tracking;
  final Set<String> notified = {};
  Session get s => widget.session;
  List<Map<String, dynamic>> get calls =>
      (s.sosState?['calls'] as List? ?? []).cast<Map<String, dynamic>>();
  Map<String, dynamic>? get driver =>
      (s.sosState?['drivers'] as List? ?? []).isEmpty
      ? null
      : (s.sosState!['drivers'] as List).first as Map<String, dynamic>;
  bool terminal(Map<String, dynamic> c) =>
      ['Completed', 'Cancelled', 'Expired'].contains(c['status']);

  @override
  void initState() {
    super.initState();
    s.addListener(alerts);
    tracking = Timer.periodic(
      const Duration(seconds: 30),
      (_) => updateLocation(),
    );
  }

  @override
  void dispose() {
    s.removeListener(alerts);
    tracking?.cancel();
    super.dispose();
  }

  void alerts() {
    if (!mounted ||
        s.role != 'driver' ||
        WidgetsBinding.instance.lifecycleState != AppLifecycleState.resumed) {
      return;
    }
    for (final c in calls) {
      if (c['status'] == 'Searching' &&
          notified.add('${c['id']}:${c['offer_until']}')) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (!mounted) return;
          SystemSound.play(SystemSoundType.alert);
          HapticFeedback.heavyImpact();
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              backgroundColor: _red,
              duration: const Duration(seconds: 8),
              content: Text(
                'New SOS pickup • ${c['distance_km']} km away. Open Dispatch to accept.',
              ),
            ),
          );
        });
      }
    }
  }

  Future<void> run(Map<String, dynamic> input) async {
    try {
      await s.sosAction(input);
    } catch (e) {
      if (mounted) {
        ScaffoldMessenger.of(context)
            .showSnackBar(SnackBar(content: Text(e.toString())));
      }
    }
  }

  Future<Map<String, dynamic>> locate({bool? useDemo}) => (useDemo ?? demo)
      ? Future.value(
          PickupLocation.demo(
            driver: s.role == 'driver'
                ? (s.profile?['driver'])?.toString()
                : null,
          ),
        )
      : PickupLocation.current();

  Future<void> send() async {
    setState(() {
      locating = true;
      locationError = null;
    });
    final epochToken = s.api.token;
    try {
      final fix = await locate();
      if (!mounted || epochToken != s.api.token) return;
      await s.sosAction({'action': 'sos', ...fix});
    } catch (e) {
      if (mounted) setState(() => locationError = e.toString());
    } finally {
      if (mounted) setState(() => locating = false);
    }
  }

  Future<void> duty(bool on) async {
    setState(() {
      locating = true;
      locationError = null;
    });
    final token = s.api.token;
    try {
      final fix = on ? await locate() : <String, dynamic>{};
      if (!mounted || token != s.api.token) return;
      await s.sosAction({'action': 'duty', 'on_duty': on, ...fix});
    } catch (e) {
      if (mounted) setState(() => locationError = e.toString());
    } finally {
      if (mounted) setState(() => locating = false);
    }
  }

  Future<void> updateLocation() async {
    if (!mounted ||
        !s.online ||
        s.busy ||
        locating ||
        WidgetsBinding.instance.lifecycleState != AppLifecycleState.resumed) {
      return;
    }
    final d = driver;
    final active = calls
        .where(
          (c) => ['Searching', 'Accepted', 'At pickup'].contains(c['status']),
        )
        .toList();
    final isDriver =
        s.role == 'driver' &&
        d?['on_duty'] == true &&
        d?['location_mode'] == 'gps';
    final isPatient =
        s.role == 'patient' &&
        active.isNotEmpty &&
        (active.first['location'] as Map)['mode'] == 'gps';
    if (!isDriver && !isPatient) return;
    setState(() => locating = true);
    final token = s.api.token;
    try {
      final fix = await PickupLocation.current();
      if (!mounted || token != s.api.token) return;
      await s.sosAction(
        isDriver
            ? {'action': 'duty', 'on_duty': true, ...fix}
            : {'action': 'location', 'id': active.first['id'], ...fix},
      );
      if (mounted) setState(() => locationError = null);
    } catch (e) {
      if (mounted) setState(() => locationError = 'Location update paused: $e');
    } finally {
      if (mounted) setState(() => locating = false);
    }
  }

  Widget panel(List<Widget> children) => Card(
    child: Padding(
      padding: const EdgeInsets.all(18),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: children,
      ),
    ),
  );
  Widget button(String label, String action, Map<String, dynamic> c) => Padding(
    padding: const EdgeInsets.only(top: 8),
    child: FilledButton(
      onPressed: enabled ? () => run({'action': action, 'id': c['id']}) : null,
      child: Text(label),
    ),
  );
  bool get enabled => s.online && !s.busy && !locating && s.sosError == null;

  Future<void> external(Uri uri) async {
    try {
      if (!await launchUrl(uri, mode: LaunchMode.externalApplication)) {
        throw Exception('Could not open link');
      }
    } catch (_) {
      if (mounted) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(
            content: Text('No compatible phone/maps app found on this device.'),
          ),
        );
      }
    }
  }

  Widget callButton(String label, dynamic phone) => OutlinedButton.icon(
    onPressed: () => external(Uri(scheme: 'tel', path: phone.toString())),
    icon: const Icon(Icons.call),
    label: Text('$label · $phone'),
  );

  @override
  Widget build(BuildContext context) {
    final patient = s.role == 'patient';
    final isDriver = s.role == 'driver';
    final profile = s.sosState?['patient_profile'] as Map<String, dynamic>?;
    final active = calls.any((c) => !terminal(c));
    return RefreshIndicator(
      onRefresh: s.refresh,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          panel([
            Text(
              isDriver
                  ? 'Ambulance dispatch'
                  : patient
                  ? 'Help starts with one tap'
                  : 'SOS coordination',
              style: const TextStyle(
                fontSize: 25,
                fontWeight: FontWeight.w800,
                color: _navy,
              ),
            ),
            const SizedBox(height: 8),
            Text(
              isDriver
                  ? 'Stay on duty with this app open. Offers go to the nearest available driver first.'
                  : 'Saved emergency details + current pickup location. No request form during SOS.',
            ),
            const SizedBox(height: 8),
            const Text(
              'Demo ambulance service • foreground alerts only',
              style: TextStyle(color: _red, fontWeight: FontWeight.bold),
            ),
          ]),
          if (s.sosError != null)
            panel([
              Text(s.sosError!, style: const TextStyle(color: _red)),
              const Text('SOS actions are paused until dispatch reconnects.'),
            ]),
          if (patient && !active)
            panel([
              SizedBox(
                height: 104,
                child: FilledButton.icon(
                  style: FilledButton.styleFrom(backgroundColor: _red),
                  onPressed: enabled && profile != null ? send : null,
                  icon: const Icon(Icons.sos, size: 36),
                  label: Text(
                    locating ? 'Getting location…' : 'SOS · Request ambulance',
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                      fontSize: 20,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 8),
              Text(
                profile == null
                    ? 'Add your emergency details below to enable SOS.'
                    : demo
                    ? 'DEMO pickup location selected'
                    : 'Uses current GPS and your saved emergency details.',
              ),
              const Text('Pickup is confirmed only after a driver accepts.'),
            ]),
          for (final c in calls.where((c) => !terminal(c))) trip(c),
          if (patient)
            panel([
              Text(
                profile == null
                    ? 'Set up emergency details once'
                    : '${profile['name']} • ${profile['age']} years',
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 18,
                ),
              ),
              if (profile != null)
                Text(
                  'Mobile: ${profile['mobile']}\nEmergency contact: ${profile['emergency_name']} · ${profile['emergency_phone']}',
                ),
              TextButton.icon(
                onPressed: s.busy ? null : () => editProfile(context, s),
                icon: const Icon(Icons.edit_outlined),
                label: Text(
                  profile == null
                      ? 'Add emergency details'
                      : 'Edit emergency details',
                ),
              ),
              if (!active) ...[
                SwitchListTile(
                  contentPadding: EdgeInsets.zero,
                  title: const Text('Use demo pickup location'),
                  subtitle: const Text(
                    'Delhi sample coordinates; never your actual GPS. Demo drivers must use the same mode.',
                  ),
                  value: demo,
                  onChanged: enabled ? (v) => setState(() => demo = v) : null,
                ),
                const SizedBox(height: 8),
                const Text(
                  'Shares your saved details and pickup location with the assigned driver. Driver confirmation is required.',
                ),
              ],
            ]),
          if (isDriver)
            panel([
              Text(
                '${driver?['name'] ?? s.profile?['name']}\n${driver?['vehicle'] ?? ''}',
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  fontSize: 18,
                ),
              ),
              Text(
                driver?['on_duty'] == true
                    ? 'ON DUTY · ${driver?['location_mode']} location'
                    : 'OFF DUTY · no pickups offered',
              ),
              if (driver?['on_duty'] != true)
                SwitchListTile(
                  contentPadding: EdgeInsets.zero,
                  value: demo,
                  onChanged: enabled ? (v) => setState(() => demo = v) : null,
                  title: const Text('Use demo driver coordinates'),
                  subtitle: const Text(
                    'Enable for the Delhi demo. Otherwise current GPS is used.',
                  ),
                ),
              FilledButton.icon(
                onPressed: enabled
                    ? () => duty(driver?['on_duty'] != true)
                    : null,
                icon: Icon(
                  driver?['on_duty'] == true
                      ? Icons.pause_circle
                      : Icons.local_shipping,
                ),
                label: Text(
                  locating
                      ? 'Getting location…'
                      : driver?['on_duty'] == true
                      ? 'Go off duty'
                      : 'Go on duty',
                ),
              ),
              const SizedBox(height: 8),
              const Text(
                'GPS updates every 30 seconds while the app is open. Drivers without a recent heartbeat stop receiving offers.',
              ),
            ]),
          if (locationError != null)
            panel([Text(locationError!, style: const TextStyle(color: _red))]),
          if (calls.isEmpty)
            panel([
              Text(
                isDriver
                    ? 'No pickup offers yet. Go on duty and keep the app open.'
                    : 'Your SOS trips will appear here.',
              ),
            ]),
          for (final c in calls.where(terminal)) trip(c),
        ],
      ),
    );
  }

  Widget trip(Map<String, dynamic> c) {
    final isDriver = s.role == 'driver';
    final p = c['patient'] as Map<String, dynamic>;
    final l = c['location'] as Map<String, dynamic>;
    final d = c['driver'] as Map<String, dynamic>?;
    final status = c['status'];
    final now = (s.sosState?['now'] as num? ?? 0).toInt();
    final remaining = ((c['offer_until'] as num? ?? now).toInt() - now).clamp(
      0,
      30,
    );
    return panel([
      Row(
        children: [
          const Icon(Icons.emergency, color: _red),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              status.toString(),
              style: const TextStyle(
                fontSize: 22,
                fontWeight: FontWeight.bold,
                color: _navy,
              ),
            ),
          ),
        ],
      ),
      Text('SOS ${c['id']} • ${p['name']} • ${p['age']} years'),
      Text(
        'Pickup: ${l['lat']}, ${l['lng']} • ${l['mode'] == 'demo' ? 'DEMO LOCATION' : 'GPS'}',
      ),
      if (l['mode'] == 'gps')
        Text(
          'Pickup location updated ${((now - (l['captured_at'] as num).toInt()).clamp(0, 99999))} seconds ago',
        ),
      if (!terminal(c) &&
          ['Searching', 'Accepted', 'At pickup'].contains(status))
        Text(
          'Location accuracy: ${l['accuracy']} m • updates while patient app is open',
        ),
      OutlinedButton.icon(
        onPressed: () => external(
          Uri.https('www.google.com', '/maps/search/', {
            'api': '1',
            'query': '${l['lat']},${l['lng']}',
          }),
        ),
        icon: const Icon(Icons.map_outlined),
        label: const Text('Open pickup in Maps'),
      ),
      if (status == 'Searching') ...[
        Text(
          isDriver
              ? 'Pickup offer · ${c['distance_km']} km straight-line distance · $remaining seconds left'
              : c['offer_driver'] == null
              ? 'No nearby driver is available yet. Searching within 25 km; no pickup is confirmed.'
              : 'Waiting for the nearest driver to confirm. No pickup is confirmed yet.',
        ),
        if (isDriver) ...[
          button('Accept pickup', 'accept', c),
          button('Decline · offer next driver', 'decline', c),
        ],
      ],
      if (d != null) ...[
        const Divider(),
        Text(
          '${d['name']} • ${d['vehicle']}',
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
        if (!isDriver) callButton('Call assigned driver', d['phone']),
        Text(
          'Driver position: ${d['lat']}, ${d['lng']} • ${d['location_mode']}',
        ),
        if (now - (d['seen_at'] as num).toInt() > 90)
          const Text(
            'Driver connection is stale. Contact the driver directly.',
            style: TextStyle(color: _red),
          ),
      ],
      if (isDriver && c['driver_id'] != null) ...[
        callButton('Call patient', p['mobile']),
        callButton(
          'Call emergency contact: ${p['emergency_name']}',
          p['emergency_phone'],
        ),
        if (status == 'Accepted')
          button('Reached pickup location', 'arrive', c),
        if (status == 'At pickup') button('Patient picked up', 'pickup', c),
        if (status == 'Picked up')
          button('Record first aid provided', 'first_aid', c),
        if (['Picked up', 'First aid', 'Transporting'].contains(status))
          OutlinedButton.icon(
            onPressed: enabled ? () => hospitalDialog(context, s, c) : null,
            icon: const Icon(Icons.local_hospital),
            label: Text(
              c['bed_request_id'] == null
                  ? 'Hospital needed · request a bed'
                  : 'View / retry hospital allocation',
            ),
          ),
        if (status == 'First aid' && c['bed_request_id'] == null)
          button('Complete · no hospital transfer', 'complete', c),
        if (status == 'Transporting')
          button('Complete hospital handoff', 'handoff', c),
      ],
      if (c['bed_request_id'] != null) ...[
        const Divider(),
        Text(
          'Hospital ${c['hospital_id'] ?? 'not allocated'} • ${c['bed_care']} • ${c['bed_status']}',
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
        const Text(
          'Hospital must verify severity, accept the bed, and confirm arrival. SOS does not automatically reserve a confirmed bed.',
        ),
        if (s.role == 'patient')
          TextButton(
            onPressed: () => widget.openBed(c['bed_request_id'].toString()),
            child: const Text('Open linked hospital request'),
          ),
      ],
      if (s.role == 'patient' &&
          ['Searching', 'Accepted', 'At pickup'].contains(status))
        TextButton(
          onPressed: enabled
              ? () async {
                  final cancel = await showDialog<bool>(
                    context: context,
                    builder: (ctx) => AlertDialog(
                      title: const Text('Cancel this SOS?'),
                      content: const Text(
                        'The driver will be told the pickup was cancelled.',
                      ),
                      actions: [
                        TextButton(
                          onPressed: () => Navigator.pop(ctx, false),
                          child: const Text('Keep SOS'),
                        ),
                        FilledButton(
                          onPressed: () => Navigator.pop(ctx, true),
                          child: const Text('Cancel SOS'),
                        ),
                      ],
                    ),
                  );
                  if (cancel == true) {
                    await run({'action': 'cancel', 'id': c['id']});
                  }
                }
              : null,
          child: const Text('Cancel SOS'),
        ),
      const Divider(),
      const Text(
        'Trip activity',
        style: TextStyle(fontWeight: FontWeight.bold),
      ),
      for (final e in (c['events'] as List).reversed.take(6))
        Padding(
          padding: const EdgeInsets.only(top: 6),
          child: Text('• ${e['text']}'),
        ),
    ]);
  }
}

Future<void> editProfile(BuildContext context, Session session) async {
  final data =
      session.sosState?['patient_profile'] as Map<String, dynamic>? ?? {};
  final fields = ['name', 'mobile', 'age', 'emergency_name', 'emergency_phone'];
  final labels = [
    'Full name',
    'Mobile number',
    'Age',
    'Emergency contact name',
    'Emergency contact number',
  ];
  final controllers = {
    for (final f in fields)
      f: TextEditingController(
        text: (data[f] ?? (f == 'name' ? (session.profile?['name']) : '') ?? '')
            .toString(),
      ),
  };
  final form = GlobalKey<FormState>();
  bool busy = false;
  String? error;
  await showDialog<void>(
    context: context,
    barrierDismissible: false,
    builder: (ctx) => StatefulBuilder(
      builder: (ctx, set) => AlertDialog(
        title: const Text('Emergency details'),
        content: SizedBox(
          width: 420,
          child: SingleChildScrollView(
            child: Form(
              key: form,
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Text(
                    'Save once. SOS shares these details only with your assigned driver.',
                  ),
                  const SizedBox(height: 12),
                  for (var i = 0; i < fields.length; i++)
                    Padding(
                      padding: const EdgeInsets.only(bottom: 12),
                      child: TextFormField(
                        controller: controllers[fields[i]],
                        keyboardType: i == 2
                            ? TextInputType.number
                            : [1, 4].contains(i)
                            ? TextInputType.phone
                            : TextInputType.name,
                        maxLength: i == 2
                            ? 3
                            : [1, 4].contains(i)
                            ? 16
                            : i == 0
                            ? 50
                            : 60,
                        decoration: InputDecoration(
                          labelText: labels[i],
                          counterText: '',
                        ),
                        validator: (v) {
                          final value = v?.trim() ?? '';
                          if (value.isEmpty) return 'Required';
                          if (i == 2 &&
                              ((int.tryParse(value) ?? 0) < 1 ||
                                  (int.tryParse(value) ?? 0) > 120)) {
                            return 'Enter age 1–120';
                          }
                          if ([1, 4].contains(i) &&
                              !RegExp(r'^\+?[0-9]{8,15}$').hasMatch(value)) {
                            return 'Use 8–15 digits; optional + prefix';
                          }
                          return null;
                        },
                      ),
                    ),
                  if (error != null)
                    Text(error!, style: const TextStyle(color: _red)),
                ],
              ),
            ),
          ),
        ),
        actions: [
          TextButton(
            onPressed: busy ? null : () => Navigator.pop(ctx),
            child: const Text('Cancel'),
          ),
          FilledButton(
            onPressed: busy
                ? null
                : () async {
                    if (!form.currentState!.validate()) return;
                    set(() {
                      busy = true;
                      error = null;
                    });
                    try {
                      await session.sosAction({
                        'action': 'profile',
                        for (final f in fields)
                          f: f == 'age'
                              ? int.parse(controllers[f]!.text.trim())
                              : controllers[f]!.text.trim(),
                      });
                      if (ctx.mounted) Navigator.pop(ctx);
                    } catch (e) {
                      if (ctx.mounted) {
                        set(() {
                          busy = false;
                          error = e.toString();
                        });
                      }
                    }
                  },
            child: Text(busy ? 'Saving…' : 'Save details'),
          ),
        ],
      ),
    ),
  );
  for (final c in controllers.values) {
    c.dispose();
  }
}

Future<void> hospitalDialog(
  BuildContext context,
  Session session,
  Map<String, dynamic> call,
) async {
  final types = (session.sosState?['care_types'] as List? ?? session.careTypes)
      .cast<String>();
  String care =
      call['bed_care']?.toString() ?? (types.isEmpty ? '' : types.first);
  bool busy = false;
  String? error;
  await showDialog<void>(
    context: context,
    builder: (ctx) => StatefulBuilder(
      builder: (ctx, set) {
        final hospitals =
            (call['hospital_options'] as List? ??
                    session.sosState?['hospitals'] as List? ??
                    [])
                .cast<Map<String, dynamic>>()
                .toList()
              ..sort(
                (a, b) => ((a['distance'] as num?) ?? double.infinity)
                    .compareTo((b['distance'] as num?) ?? double.infinity),
              );
        return AlertDialog(
          title: const Text('Request hospital care'),
          content: SizedBox(
            width: 450,
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Text(
                    'Hospital inventory comes from the database. Distance uses hospital and pickup coordinates when provided.',
                  ),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<String>(
                    initialValue: care.isEmpty ? null : care,
                    decoration: const InputDecoration(
                      labelText: 'Required care',
                    ),
                    items: types
                        .map((c) => DropdownMenuItem(value: c, child: Text(c)))
                        .toList(),
                    onChanged: busy || call['bed_request_id'] != null
                        ? null
                        : (v) => set(() => care = v!),
                  ),
                  if (error != null)
                    Padding(
                      padding: const EdgeInsets.all(8),
                      child: Text(error!, style: const TextStyle(color: _red)),
                    ),
                  for (final h in hospitals)
                    ListTile(
                      contentPadding: EdgeInsets.zero,
                      title: Text(
                        '${h['name']} • ${h['distance'] == null ? 'Distance not provided' : '${h['distance']} km'}',
                      ),
                      subtitle: Text(
                        h['status'] != 'Open'
                            ? 'Rejected · hospital offline'
                            : '${h['available'][care]} $care beds available',
                      ),
                      trailing: TextButton(
                        onPressed:
                            busy ||
                                h['status'] != 'Open' ||
                                ((h['available'][care] as num?) ?? 0) == 0
                            ? null
                            : () async {
                                set(() {
                                  busy = true;
                                  error = null;
                                });
                                try {
                                  await session.sosAction({
                                    'action': 'transport',
                                    'id': call['id'],
                                    'care': care,
                                    'hospital_id': h['id'],
                                  });
                                  if (ctx.mounted) Navigator.pop(ctx);
                                } catch (e) {
                                  if (ctx.mounted) {
                                    set(() {
                                      busy = false;
                                      error = e.toString();
                                    });
                                  }
                                }
                              },
                        child: const Text('Request'),
                      ),
                    ),
                ],
              ),
            ),
          ),
          actions: [
            TextButton(
              onPressed: busy ? null : () => Navigator.pop(ctx),
              child: const Text('Close'),
            ),
          ],
        );
      },
    ),
  );
}
