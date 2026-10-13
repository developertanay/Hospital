import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:url_launcher/url_launcher.dart';

import 'session.dart';
import 'managed_dialog.dart';
import 'location.dart';
import 'recipient.dart';
import 'responsive.dart';
import 'recent_activity.dart';
import 'sos_photo.dart';
import 'sos_media.dart';

const _red = Color(0xFFC6283D);
const _navy = Color(0xFF17352F);

class SosScreen extends StatefulWidget {
  final Session session;
  final void Function(String) openBed;
  final SosMediaCapture? captureMedia;
  final Future<Map<String, dynamic>> Function()? captureLocation;
  const SosScreen({
    super.key,
    required this.session,
    required this.openBed,
    this.captureMedia,
    this.captureLocation,
  });
  @override
  State<SosScreen> createState() => _SosScreenState();
}

class _SosScreenState extends State<SosScreen> {
  bool demo = false, locating = false;
  String? sosStage;
  String? selfCoverage;
  Future<void> sendForSomeone() => send(forSomeone: true);

  bool setupShown = false;
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
    demo = false;
    s.addListener(alerts);
    WidgetsBinding.instance.addPostFrameCallback((_) => alerts());
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

  bool get profileReady {
    final p = s.sosState?['patient_profile'] as Map?;
    return p != null &&
        ['name', 'mobile', 'age', 'emergency_name', 'emergency_phone'].every(
          (key) => p[key] != null && p[key].toString().trim().isNotEmpty,
        ) &&
        s.sosState?['profile_complete'] != false;
  }

  void alerts() {
    if (!mounted) return;
    if (!setupShown && s.sosState != null && s.online) {
      setupShown = true;
      if ((s.role == 'patient' &&
              !profileReady &&
              !calls.any((c) => !terminal(c))) ||
          (s.role == 'driver' && driver?['on_duty'] != true)) {
        WidgetsBinding.instance.addPostFrameCallback((_) async {
          if (!mounted) return;
          if (s.role == 'patient') {
            await editProfile(context, s);
          } else {
            final choice = await showDialog<String>(
              context: context,
              builder: (ctx) => AlertDialog(
                insetPadding: const EdgeInsets.symmetric(
                  horizontal: 16,
                  vertical: 24,
                ),
                title: const Text('Start receiving SOS pickups'),
                content: const Text(
                  'Go on duty with GPS and keep the app open for pickups. Demo mode uses simulated locations.',
                ),
                actions: [
                  TextButton(
                    onPressed: () => Navigator.pop(ctx),
                    child: const Text('Stay offline'),
                  ),
                  TextButton(
                    onPressed: () => Navigator.pop(ctx, 'gps'),
                    child: const Text('Use my GPS'),
                  ),
                  FilledButton(
                    onPressed: () => Navigator.pop(ctx, 'demo'),
                    child: const Text('Online for demo'),
                  ),
                ],
              ),
            );
            if (mounted && choice != null) {
              setState(() => demo = choice == 'demo');
              await duty(true);
            }
          }
        });
      }
    }
    if (s.role != 'driver' ||
        WidgetsBinding.instance.lifecycleState != AppLifecycleState.resumed) {
      return;
    }
    for (final c in calls) {
      if (c['status'] == 'Searching' && notified.add('${c['id']}')) {
        WidgetsBinding.instance.addPostFrameCallback((_) {
          if (!mounted) return;
          SystemSound.play(SystemSoundType.alert);
          HapticFeedback.heavyImpact();
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(
              backgroundColor: _red,
              duration: const Duration(seconds: 8),
              content: Text(
                'NEW SOS • ${c['distance_km']} km • ~${c['eta_minutes'] ?? '?'} min. Accept below.',
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
      : (widget.captureLocation ?? PickupLocation.current)();

  Future<void> send({bool forSomeone = false}) async {
    if (!enabled || s.role != 'patient' || calls.any((c) => !terminal(c))) {
      return;
    }
    setState(() {
      locating = true;
      sosStage = 'SOS details…';
      locationError = null;
    });
    final epochToken = s.api.token;
    try {
      final input = forSomeone
          ? await chooseSosRecipient(
              context,
              s,
              captureLocation: () => locate(useDemo: false),
            )
          : <String, dynamic>{
              if (selfCoverage != null) ...{
                'reported_for': 'Self',
                'coverage_id': selfCoverage,
              },
            };
      if (input == null || !mounted || epochToken != s.api.token) return;
      if ((input['reported_for'] ?? 'Self') == 'Self' && !profileReady) {
        await editProfile(context, s);
        if (!mounted || !profileReady || epochToken != s.api.token) return;
      }
      setState(() => sosStage = 'Capturing photo & recording 10s…');
      final media = await (widget.captureMedia ?? captureSosReporterMedia)();
      if (!mounted || epochToken != s.api.token) return;
      if (media == null) {
        setState(
          () => locationError =
              'Photo and 10-second audio required. SOS was not sent.',
        );
        return;
      }
      media.validate();
      setState(() => sosStage = 'Getting location…');
      // Camera capture may take a while. Refresh GPS before dispatching, but
      // preserve a remote patient's manually entered pickup pin.
      final fix = input['mode'] == 'manual'
          ? {
              'lat': input['lat'],
              'lng': input['lng'],
              'accuracy': input['accuracy'],
              'mode': 'manual',
              'captured_at': DateTime.now().millisecondsSinceEpoch ~/ 1000,
            }
          : await locate(useDemo: forSomeone ? false : null);
      if (!mounted || epochToken != s.api.token) return;
      setState(() => sosStage = 'Sending SOS…');
      await s.sosAction({'action': 'sos', ...input, ...fix, ...media.payload});
    } catch (e) {
      if (mounted) setState(() => locationError = e.toString());
    } finally {
      if (mounted) {
        setState(() {
          locating = false;
          sosStage = null;
        });
      }
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
      await s.sosAction({
        'action': 'duty',
        'on_duty': on,
        'follow_pickup': demo,
        ...fix,
      });
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
      padding: const EdgeInsets.all(16),
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
    label: Text('$label · $phone', textAlign: TextAlign.center),
  );

  @override
  Widget build(BuildContext context) {
    final patient = s.role == 'patient';
    final isDriver = s.role == 'driver';
    final profile = s.sosState?['patient_profile'] as Map<String, dynamic>?;
    final active = calls.any((c) => !terminal(c));
    return RefreshIndicator(
      onRefresh: s.refresh,
      child: ResponsivePage(
        maxWidth: 860,
        child: ListView(
          padding: const EdgeInsets.symmetric(vertical: 16),
          children: [
            panel([
              Text(
                isDriver
                    ? 'Ambulance dispatch'
                    : patient
                    ? 'Emergency help'
                    : 'SOS coordination',
                style: const TextStyle(
                  fontSize: 23,
                  fontWeight: FontWeight.w800,
                  color: _navy,
                ),
              ),
            ]),
            if (s.sosError != null)
              panel([
                Text(s.sosError!, style: const TextStyle(color: _red)),
                const Text('SOS actions are paused until dispatch reconnects.'),
              ]),
            if (patient && !active)
              panel([
                ConstrainedBox(
                  constraints: const BoxConstraints(minHeight: 96),
                  child: FilledButton.icon(
                    style: FilledButton.styleFrom(backgroundColor: _red),
                    onPressed: enabled ? () => send() : null,
                    icon: const Icon(Icons.emergency_outlined, size: 30),
                    label: Text(
                      locating
                          ? sosStage ?? 'Getting location…'
                          : 'SOS · Request ambulance',
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
                      ? 'Add emergency details before your first SOS.'
                      : demo
                      ? 'DEMO pickup location selected'
                      : 'Live GPS pickup',
                ),
                const Text(
                  'SOS automatically captures a photo and 10 seconds of audio.',
                ),
                if (locationError != null)
                  Text(locationError!, style: const TextStyle(color: _red)),
                if ((s.sosState?['coverages'] as List? ?? []).isNotEmpty)
                  DropdownButtonFormField<String>(
                    initialValue: selfCoverage,
                    isExpanded: true,
                    decoration: const InputDecoration(
                      labelText: 'My insurance / panel (optional)',
                    ),
                    items: [
                      const DropdownMenuItem<String>(
                        value: null,
                        child: Text('No coverage / provide later'),
                      ),
                      ...(s.sosState!['coverages'] as List)
                          .where((c) => c['dependent_id'] == null)
                          .map(
                            (c) => DropdownMenuItem(
                              value: c['id'].toString(),
                              child: Text(
                                (c['organisation'] as Map?)?['name']
                                        ?.toString() ??
                                    'Coverage',
                              ),
                            ),
                          ),
                    ],
                    onChanged: (v) => setState(() => selfCoverage = v),
                  ),
                OutlinedButton.icon(
                  onPressed: enabled ? sendForSomeone : null,
                  icon: const Icon(Icons.group_outlined),
                  label: const Text('SOS for family / another person'),
                ),
              ]),
            for (final c in calls.where((c) => !terminal(c))) trip(c),
            if (patient)
              panel([
                Text(
                  profile == null
                      ? 'Emergency details'
                      : '${profile['name']} • ${profile['age']} years',
                  style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 18,
                  ),
                ),
                TextButton.icon(
                  onPressed: s.busy || locating
                      ? null
                      : () => editProfile(context, s),
                  icon: const Icon(Icons.edit_outlined),
                  label: Text(
                    profile == null
                        ? 'Add emergency details'
                        : 'Edit emergency details',
                  ),
                ),
                ExpansionTile(
                  tilePadding: EdgeInsets.zero,
                  title: const Text('Contact & location settings'),
                  children: [
                    if (profile != null)
                      ListTile(
                        contentPadding: EdgeInsets.zero,
                        leading: const Icon(Icons.contact_phone_outlined),
                        title: Text('${profile['mobile']}'),
                        subtitle: Text(
                          '${profile['emergency_name']} · ${profile['emergency_phone']}',
                        ),
                      ),
                    if (!active)
                      SwitchListTile(
                        contentPadding: EdgeInsets.zero,
                        title: const Text('Use demo pickup location'),
                        subtitle: const Text('Simulated pickup · testing only'),
                        value: demo,
                        onChanged: enabled
                            ? (v) => setState(() => demo = v)
                            : null,
                      ),
                  ],
                ),
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
                ExpansionTile(
                  tilePadding: EdgeInsets.zero,
                  title: const Text('Driver contact'),
                  children: [
                    Text('Contact: ${driver?['phone'] ?? 'Not set'}'),
                    TextButton.icon(
                      onPressed: enabled
                          ? () => editDriverContact(
                              context,
                              s,
                              driver?['phone']?.toString() ?? '',
                            )
                          : null,
                      icon: const Icon(Icons.phone),
                      label: const Text('Set driver phone for call demo'),
                    ),
                  ],
                ),
                if (driver?['on_duty'] == true &&
                    driver?['location_mode'] == 'demo' &&
                    driver?['follow_pickup'] != true)
                  FilledButton(
                    onPressed: enabled
                        ? () {
                            setState(() => demo = true);
                            duty(true);
                          }
                        : null,
                    child: const Text('Enable demo pickups near patient GPS'),
                  ),
                Text(
                  driver?['on_duty'] == true
                      ? 'ON DUTY · ${driver?['location_mode']} location'
                      : 'OFF DUTY · no pickups offered',
                ),
                if (driver?['on_duty'] != true)
                  ExpansionTile(
                    tilePadding: EdgeInsets.zero,
                    title: const Text('Location settings'),
                    children: [
                      SwitchListTile(
                        contentPadding: EdgeInsets.zero,
                        value: demo,
                        onChanged: enabled
                            ? (v) => setState(() => demo = v)
                            : null,
                        title: const Text('Use demo driver coordinates'),
                        subtitle: const Text(
                          'Simulated ambulance · testing only',
                        ),
                      ),
                    ],
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
                        : 'Go online · receive SOS',
                  ),
                ),
                const SizedBox(height: 8),
                const Text('Keep the app open for GPS & pickup alerts.'),
              ]),
            if (locationError != null && (!patient || active))
              panel([
                Text(locationError!, style: const TextStyle(color: _red)),
              ]),
            if (calls.isEmpty)
              panel([
                Text(isDriver ? 'No pickup offers yet.' : 'No SOS trips yet.'),
              ]),
            if (isDriver)
              panel([
                const Text(
                  "Today's trips",
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
                Text(
                  '${calls.where((c) {
                    final dt = DateTime.fromMillisecondsSinceEpoch(((c['created_at'] as num?) ?? 0).toInt() * 1000);
                    final today = DateTime.now();
                    return dt.year == today.year && dt.month == today.month && dt.day == today.day && c['driver_id'] != null;
                  }).length} assigned trips today',
                ),
              ]),
            if (calls.any(terminal))
              RecentActivityList(
                title: 'Recent trips',
                children: [for (final c in calls.where(terminal)) trip(c)],
              ),
          ],
        ),
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
      600,
    );
    final events = (c['events'] as List).reversed.toList()
      ..sort(
        (a, b) => ((b['time'] ?? b['at'] ?? 0) as num).compareTo(
          (a['time'] ?? a['at'] ?? 0) as num,
        ),
      );
    return panel([
      Row(
        children: [
          const Icon(Icons.emergency, color: _red),
          const SizedBox(width: 8),
          Expanded(
            child: Text(
              status == 'Searching' && !isDriver
                  ? c['requested_driver_id'] != null
                        ? 'Ambulance booked · awaiting confirmation'
                        : 'Finding your driver…'
                  : status == 'Accepted' && !isDriver
                  ? 'Driver is on the way'
                  : status.toString(),
              style: const TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: _navy,
              ),
            ),
          ),
        ],
      ),
      Text(
        'SOS ${c['id']} • ${p['name']} • ${p['age'] == null ? 'Age unknown' : '${p['age']} years'}',
      ),
      if ((c['reporter_photo_url'] ?? '').toString().isNotEmpty)
        SosSenderPhoto(
          key: ValueKey('sender-photo-${c['id']}-${s.api.token}'),
          api: s.api,
          url: c['reporter_photo_url'].toString(),
        ),
      if ((c['reporter_voice_url'] ?? '').toString().isNotEmpty)
        SosSenderVoice(
          key: ValueKey('sender-voice-${c['id']}-${s.api.token}'),
          api: s.api,
          url: c['reporter_voice_url'].toString(),
        ),
      if (c['reporter'] is Map) ...[
        Text(
          'Raised by: ${(c['reporter'] as Map)['name']} • ${(c['reporter'] as Map)['phone']}',
        ),
        Text(
          (c['reporter'] as Map)['with_patient'] == true
              ? 'Reporter is with the patient'
              : 'Patient is at a separate pickup location',
        ),
      ],
      if ((c['pickup_address'] ?? '').toString().isNotEmpty)
        Text('Patient pickup: ${c['pickup_address']}'),
      if (!isDriver && c['coverage'] is Map)
        Text(
          'Coverage: ${(c['coverage'] as Map)['organisation']} • verification pending',
        ),
      if ((c['description'] ?? '').toString().isNotEmpty)
        Text(c['description'].toString()),
      Text(
        l['mode'] == 'demo'
            ? 'Demo pickup'
            : l['mode'] == 'manual'
            ? 'Patient pickup pin'
            : 'Live GPS pickup',
        style: const TextStyle(color: Color(0xFF0F766E)),
      ),
      if (l['mode'] == 'manual') const Text('Manual pin · not live GPS.'),
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
      ExpansionTile(
        tilePadding: EdgeInsets.zero,
        title: const Text('Pickup details'),
        children: [
          if (c['care_request_id'] != null)
            Text(
              'Care request: ${c['care_request_id']} • ${c['problem_type']}',
            ),
          Text('Coordinates: ${l['lat']}, ${l['lng']}'),
          if (l['mode'] == 'gps')
            Text(
              'Updated ${((now - (l['captured_at'] as num).toInt()).clamp(0, 99999))} seconds ago',
            ),
          if (l['mode'] == 'gps' &&
              !terminal(c) &&
              ['Searching', 'Accepted', 'At pickup'].contains(status))
            Text('Accuracy: ${l['accuracy']} m · updates while app is open'),
          if (l['mode'] == 'manual')
            const Text('Patient pickup pin · reporter GPS is not used.'),
        ],
      ),
      if (status == 'Searching') ...[
        if (!isDriver) const LinearProgressIndicator(),
        const SizedBox(height: 12),
        Text(
          isDriver
              ? 'NEW PICKUP · ${c['distance_km']} km · estimated ${c['eta_minutes'] ?? '?'} min • $remaining seconds left'
              : (c['nearby_driver_count'] ??
                        (c['offer_driver'] == null ? 0 : 1)) ==
                    0
              ? 'No driver available within 25 km. Pickup unconfirmed.'
              : '${c['nearby_driver_count'] ?? 1} drivers alerted · awaiting acceptance',
        ),
        if (isDriver) ...[
          button('Accept pickup', 'accept', c),
          button('Reject pickup', 'decline', c),
        ],
      ],
      if (d != null) ...[
        const Divider(),
        Text(
          '${d['name']} • ${d['vehicle']}',
          style: const TextStyle(fontWeight: FontWeight.bold),
        ),
        if (!isDriver) ...[
          Text(
            '${c['distance_km']} km away • estimated ${c['eta_minutes'] ?? '?'} min to pickup',
            style: const TextStyle(fontSize: 20, fontWeight: FontWeight.w800),
          ),
          const Text('Estimate · direct distance, no traffic included.'),
          if (c['tracking_simulated'] == true)
            const Text(
              'DEMO • simulated ambulance movement',
              style: TextStyle(color: _red),
            ),
          SizedBox(height: 140, child: CustomPaint(painter: PickupPainter(c))),
          callButton('Call assigned driver', d['phone']),
          OutlinedButton.icon(
            onPressed: () => external(
              Uri.https('www.google.com', '/maps/search/', {
                'api': '1',
                'query': '${d['lat']},${d['lng']}',
              }),
            ),
            icon: const Icon(Icons.pin_drop),
            label: const Text('View driver in Maps'),
          ),
        ],
        ExpansionTile(
          tilePadding: EdgeInsets.zero,
          title: const Text('Driver location details'),
          children: [Text('${d['lat']}, ${d['lng']} • ${d['location_mode']}')],
        ),
        if (now - (d['seen_at'] as num).toInt() > 90 ||
            c['driver_location_stale'] == true)
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
        const Text('Bed confirmed after hospital approval.'),
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
                      insetPadding: const EdgeInsets.symmetric(
                        horizontal: 16,
                        vertical: 24,
                      ),
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
      RecentActivityList(
        title: 'Trip activity',
        children: [
          for (final e in events)
            Padding(
              padding: const EdgeInsets.only(top: 6),
              child: Align(
                alignment: Alignment.centerLeft,
                child: Text('• ${e['text']}'),
              ),
            ),
        ],
      ),
    ]);
  }
}

class PickupPainter extends CustomPainter {
  final Map<String, dynamic> trip;
  PickupPainter(this.trip);
  @override
  void paint(Canvas canvas, Size size) {
    canvas.drawRRect(
      RRect.fromRectAndRadius(Offset.zero & size, const Radius.circular(14)),
      Paint()..color = const Color(0xFFE9F1F6),
    );
    final road = Paint()
      ..color = const Color(0xFFCFDCE6)
      ..strokeWidth = 2;
    for (double x = 20; x < size.width; x += 35) {
      canvas.drawLine(Offset(x, 0), Offset(x, size.height), road);
    }
    for (double y = 20; y < size.height; y += 30) {
      canvas.drawLine(Offset(0, y), Offset(size.width, y), road);
    }
    final remaining = ((trip['distance_km'] as num?) ?? 1).toDouble();
    final initial =
        ((trip['pickup_eta_minutes'] as num?) ?? 1).toDouble() * 25 / 60 / 1.3;
    final ratio = (remaining / (initial > 0 ? initial : 1)).clamp(0.0, 1.0);
    final pickup = Offset(size.width - 45, 65);
    final ambulance = Offset(pickup.dx - (size.width - 90) * ratio, 65);
    canvas.drawLine(
      const Offset(45, 65),
      pickup,
      Paint()
        ..color = _navy
        ..strokeWidth = 4,
    );
    canvas.drawCircle(pickup, 12, Paint()..color = _red);
    canvas.drawRRect(
      RRect.fromRectAndRadius(
        Rect.fromCenter(center: ambulance, width: 30, height: 24),
        const Radius.circular(5),
      ),
      Paint()..color = Colors.white,
    );
    canvas.drawLine(
      ambulance.translate(-7, 0),
      ambulance.translate(7, 0),
      Paint()
        ..color = _red
        ..strokeWidth = 4,
    );
    canvas.drawLine(
      ambulance.translate(0, -7),
      ambulance.translate(0, 7),
      Paint()
        ..color = _red
        ..strokeWidth = 4,
    );
    final text = TextPainter(
      text: const TextSpan(
        text: 'Driver to Pickup • progress diagram',
        style: TextStyle(color: _navy, fontSize: 12),
      ),
      textDirection: TextDirection.ltr,
    )..layout(maxWidth: size.width - 30);
    text.paint(canvas, const Offset(15, 110));
  }

  @override
  bool shouldRepaint(covariant PickupPainter oldDelegate) => true;
}

Future<void> editDriverContact(
  BuildContext context,
  Session session,
  String initial,
) async {
  final controller = TextEditingController(text: initial);
  final form = GlobalKey<FormState>();
  bool busy = false;
  String? error;
  await showManagedDialog<void>(
    context: context,
    builder: (ctx) => StatefulBuilder(
      builder: (ctx, set) => AlertDialog(
        insetPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
        title: const Text('Driver contact number'),
        content: SingleChildScrollView(
          child: Form(
            key: form,
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Text('Calls open in your phone dialer.'),
                TextFormField(
                  controller: controller,
                  keyboardType: TextInputType.phone,
                  decoration: const InputDecoration(labelText: 'Phone number'),
                  validator: (v) =>
                      RegExp(r'^\+?[0-9]{8,15}$').hasMatch(v?.trim() ?? '')
                      ? null
                      : 'Enter 8–15 digits with optional +',
                ),
                if (error != null)
                  Text(error!, style: const TextStyle(color: _red)),
              ],
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
                    set(() => busy = true);
                    try {
                      await session.sosAction({
                        'action': 'driver_contact',
                        'phone': controller.text.trim(),
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
            child: Text(busy ? 'Saving…' : 'Save number'),
          ),
        ],
      ),
    ),
  );
  controller.dispose();
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
  await showManagedDialog<void>(
    context: context,
    barrierDismissible: false,
    builder: (ctx) => StatefulBuilder(
      builder: (ctx, set) => AlertDialog(
        insetPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
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
                    'Save once for SOS. Contacts are shared after driver acceptance.',
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
  await showManagedDialog<void>(
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
          insetPadding: const EdgeInsets.symmetric(
            horizontal: 16,
            vertical: 24,
          ),
          title: const Text('Request hospital care'),
          content: SizedBox(
            width: 450,
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Text('Available hospital beds near the patient.'),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<String>(
                    isExpanded: true,
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
