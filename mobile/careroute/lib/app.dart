import 'care_choices.dart';
import 'portfolio.dart';
import 'recipient.dart';
import 'managed_dialog.dart';
import 'responsive.dart';
import 'recent_activity.dart';

import 'dart:async';

import 'package:geolocator/geolocator.dart';

import 'location.dart';

import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import 'session.dart';
import 'sos.dart';

const navy = Color(0xFF17352F);
const teal = Color(0xFF0F766E);

ThemeData careRouteTheme() => ThemeData(
  useMaterial3: true,
  colorScheme: ColorScheme.fromSeed(
    seedColor: teal,
    primary: teal,
    surface: Colors.white,
  ),
  scaffoldBackgroundColor: const Color(0xFFF4F8F7),
  appBarTheme: const AppBarTheme(
    backgroundColor: Color(0xFFF4F8F7),
    foregroundColor: navy,
    elevation: 0,
    scrolledUnderElevation: 0,
    centerTitle: false,
  ),
  cardTheme: CardThemeData(
    color: Colors.white,
    elevation: 0,
    margin: const EdgeInsets.symmetric(vertical: 6),
    shape: RoundedRectangleBorder(
      borderRadius: BorderRadius.circular(18),
      side: const BorderSide(color: Color(0xFFE1EAE7)),
    ),
  ),
  inputDecorationTheme: InputDecorationTheme(
    filled: true,
    fillColor: Colors.white,
    border: OutlineInputBorder(
      borderRadius: BorderRadius.circular(14),
      borderSide: const BorderSide(color: Color(0xFFDBE6E2)),
    ),
    enabledBorder: OutlineInputBorder(
      borderRadius: BorderRadius.circular(14),
      borderSide: const BorderSide(color: Color(0xFFDBE6E2)),
    ),
    contentPadding: const EdgeInsets.all(16),
  ),
  filledButtonTheme: FilledButtonThemeData(
    style: FilledButton.styleFrom(
      minimumSize: const Size(0, 48),
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 14),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
    ),
  ),
  outlinedButtonTheme: OutlinedButtonThemeData(
    style: OutlinedButton.styleFrom(
      minimumSize: const Size(0, 48),
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
    ),
  ),
  navigationBarTheme: const NavigationBarThemeData(
    backgroundColor: Colors.white,
    indicatorColor: Color(0xFFDDEFE9),
    elevation: 0,
  ),
  navigationRailTheme: const NavigationRailThemeData(
    backgroundColor: Colors.white,
    indicatorColor: Color(0xFFDDEFE9),
  ),
  dividerTheme: const DividerThemeData(color: Color(0xFFE1EAE7)),
  expansionTileTheme: const ExpansionTileThemeData(
    shape: Border(),
    collapsedShape: Border(),
    iconColor: teal,
    textColor: navy,
    collapsedTextColor: navy,
    childrenPadding: EdgeInsets.only(bottom: 16),
  ),
);

class CareRouteApp extends StatefulWidget {
  final Session session;
  const CareRouteApp({super.key, required this.session});
  @override
  State<CareRouteApp> createState() => _CareRouteAppState();
}

class _CareRouteAppState extends State<CareRouteApp>
    with WidgetsBindingObserver {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);
    widget.session.init();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    widget.session.polling(state == AppLifecycleState.resumed);
    if (state == AppLifecycleState.resumed) widget.session.refresh();
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    widget.session.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => MaterialApp(
    title: 'CareRoute',
    debugShowCheckedModeBanner: false,
    theme: careRouteTheme(),
    home: AnimatedBuilder(
      animation: widget.session,
      builder: (context, _) {
        if (widget.session.loading) {
          return const Scaffold(
            body: Center(child: CircularProgressIndicator()),
          );
        }
        return widget.session.signedIn
            ? Home(session: widget.session)
            : AuthScreen(session: widget.session);
      },
    ),
  );
}

void notice(BuildContext context, Object message) {
  ScaffoldMessenger.of(context)
      .showSnackBar(SnackBar(content: Text(message.toString())));
}

Future<void> serverDialog(BuildContext context, Session session) async {
  final controller = TextEditingController(text: session.api.base);
  bool busy = false;
  String? error;
  await showManagedDialog<void>(
    context: context,
    barrierDismissible: false,
    builder: (dialogContext) => StatefulBuilder(
      builder: (context, set) => PopScope(
        canPop: !busy,
        child: AlertDialog(
          title: const Text('Server connection'),
          content: SizedBox(
            width: 450,
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    'Use your PC’s Wi-Fi address on the same network.',
                  ),
                  const SizedBox(height: 16),
                  TextField(
                    controller: controller,
                    keyboardType: TextInputType.url,
                    autocorrect: false,
                    decoration: const InputDecoration(
                      labelText: 'Mobile API address',
                      hintText: 'http://PC-IP/Hospital/public/api/mobile',
                    ),
                  ),
                  const SizedBox(height: 12),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: [
                      ActionChip(
                        label: const Text('Current test server'),
                        onPressed: busy
                            ? null
                            : () => controller.text = session.bundledServer,
                      ),
                      ActionChip(
                        label: const Text('Android emulator'),
                        onPressed: busy
                            ? null
                            : () => controller.text =
                                  'http://10.0.2.2/Hospital/public/api/mobile',
                      ),
                      ActionChip(
                        label: const Text('This PC / simulator'),
                        onPressed: busy
                            ? null
                            : () => controller.text =
                                  'http://localhost/Hospital/public/api/mobile',
                      ),
                    ],
                  ),
                  if (error != null)
                    Padding(
                      padding: const EdgeInsets.only(top: 12),
                      child: Text(
                        error!,
                        style: const TextStyle(color: Colors.red),
                      ),
                    ),
                  const SizedBox(height: 12),
                  const Text('Changing servers signs you out.'),
                ],
              ),
            ),
          ),
          actions: [
            TextButton(
              onPressed: busy ? null : () => Navigator.pop(dialogContext),
              child: const Text('Cancel'),
            ),
            FilledButton(
              onPressed: busy
                  ? null
                  : () async {
                      set(() {
                        busy = true;
                        error = null;
                      });
                      try {
                        await session.server(controller.text);
                        if (dialogContext.mounted) Navigator.pop(dialogContext);
                      } catch (e) {
                        if (!dialogContext.mounted) return;
                        set(() {
                          error = e.toString();
                          busy = false;
                        });
                      }
                    },
              child: Text(busy ? 'Checking…' : 'Test & save'),
            ),
          ],
        ),
      ),
    ),
  );
  controller.dispose();
}

class AuthScreen extends StatefulWidget {
  final Session session;
  const AuthScreen({super.key, required this.session});
  @override
  State<AuthScreen> createState() => _AuthScreenState();
}

class _AuthScreenState extends State<AuthScreen> {
  final form = GlobalKey<FormState>();
  final email = TextEditingController(),
      password = TextEditingController(),
      name = TextEditingController(),
      confirmation = TextEditingController();
  bool register = false, busy = false, hidden = true;
  String? error;
  @override
  void dispose() {
    email.dispose();
    password.dispose();
    name.dispose();
    confirmation.dispose();
    super.dispose();
  }

  Future<void> submit() async {
    if (!form.currentState!.validate()) return;
    setState(() {
      busy = true;
      error = null;
    });
    try {
      await widget.session.authenticate(
        email.text,
        password.text,
        name: register ? name.text : null,
        confirmation: confirmation.text,
      );
    } catch (e) {
      if (mounted) setState(() => error = e.toString());
    } finally {
      if (mounted) setState(() => busy = false);
    }
  }

  @override
  Widget build(BuildContext context) => Scaffold(
    body: SafeArea(
      child: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(24),
          child: ConstrainedBox(
            constraints: const BoxConstraints(maxWidth: 440),
            child: AutofillGroup(
              child: Form(
                key: form,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    Image.asset('assets/hospital-logo.png', height: 70),
                    const SizedBox(height: 22),
                    Text(
                      register ? 'Create an account' : 'Welcome to CareRoute',
                      style: const TextStyle(
                        fontSize: 27,
                        fontWeight: FontWeight.w800,
                        color: navy,
                      ),
                    ),
                    const SizedBox(height: 24),
                    if (register) ...[
                      TextFormField(
                        controller: name,
                        maxLength: 80,
                        textCapitalization: TextCapitalization.words,
                        decoration: const InputDecoration(
                          labelText: 'Your name',
                        ),
                        validator: (v) =>
                            v!.trim().isEmpty ? 'Enter your name' : null,
                      ),
                      const SizedBox(height: 14),
                    ],
                    TextFormField(
                      controller: email,
                      keyboardType: TextInputType.emailAddress,
                      autofillHints: const [AutofillHints.username],
                      autocorrect: false,
                      decoration: InputDecoration(
                        labelText: register
                            ? 'Email address'
                            : 'Email / User ID',
                        prefixIcon: const Icon(Icons.mail_outline),
                      ),
                      validator: (v) => !register
                          ? (v!.trim().isEmpty
                                ? 'Enter your email or user ID'
                                : null)
                          : RegExp(r'^[^\s@]+@[^\s@]+\.[^\s@]+$')
                                .hasMatch(v!.trim())
                          ? null
                          : 'Enter a valid email',
                    ),
                    const SizedBox(height: 14),
                    TextFormField(
                      controller: password,
                      obscureText: hidden,
                      autofillHints: [
                        register
                            ? AutofillHints.newPassword
                            : AutofillHints.password,
                      ],
                      decoration: InputDecoration(
                        labelText: 'Password',
                        prefixIcon: const Icon(Icons.lock_outline),
                        suffixIcon: IconButton(
                          onPressed: () => setState(() => hidden = !hidden),
                          icon: Icon(
                            hidden
                                ? Icons.visibility_outlined
                                : Icons.visibility_off_outlined,
                          ),
                        ),
                      ),
                      validator: (v) => v!.isEmpty
                          ? 'Enter your password'
                          : register && v.length < 8
                          ? 'Use at least 8 characters'
                          : null,
                    ),
                    if (register) ...[
                      const SizedBox(height: 14),
                      TextFormField(
                        controller: confirmation,
                        obscureText: hidden,
                        decoration: const InputDecoration(
                          labelText: 'Confirm password',
                        ),
                        validator: (v) => v != password.text
                            ? 'Passwords do not match'
                            : null,
                      ),
                    ],
                    if (error ?? widget.session.error case final String message)
                      Padding(
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        child: Text(
                          message,
                          style: const TextStyle(color: Colors.red),
                        ),
                      ),
                    const SizedBox(height: 20),
                    FilledButton(
                      onPressed: busy ? null : submit,
                      child: Text(
                        busy
                            ? 'Please wait…'
                            : register
                            ? 'Create account'
                            : 'Sign in',
                      ),
                    ),
                    TextButton(
                      onPressed: busy
                          ? null
                          : () => setState(() {
                              register = !register;
                              error = null;
                            }),
                      child: Text(
                        register
                            ? 'Already registered? Sign in'
                            : 'New patient? Create an account',
                      ),
                    ),
                    const Divider(height: 28),
                    OutlinedButton.icon(
                      onPressed: busy
                          ? null
                          : () => serverDialog(context, widget.session),
                      icon: const Icon(Icons.dns_outlined),
                      label: const Text('Server connection'),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'Server: ${widget.session.api.base}',
                      textAlign: TextAlign.center,
                      style: const TextStyle(
                        fontSize: 12,
                        color: Colors.black54,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    ),
  );
}

class Home extends StatefulWidget {
  final Session session;
  const Home({super.key, required this.session});
  @override
  State<Home> createState() => _HomeState();
}

class _HomeState extends State<Home> {
  int tab = 0;
  void open(String id) => Navigator.push(
    context,
    MaterialPageRoute<void>(
      builder: (_) => RequestDetail(session: widget.session, id: id),
    ),
  );
  @override
  Widget build(BuildContext context) {
    final s = widget.session;
    final patient = s.role == 'patient' && s.can('create');
    final ambulance = ['patient', 'driver', 'admin'].contains(s.role);
    final pages = <Widget>[
      if (ambulance) SosScreen(session: s, openBed: open),
      if (patient)
        RequestForm(
          session: s,
          onCreated: open,
          onAmbulance: () => setState(() => tab = 0),
        ),
      if (['patient', 'hospital'].contains(s.role))
        Queue(session: s, open: open),
      if (['patient', 'hospital'].contains(s.role)) Hospitals(session: s),
      Account(session: s),
    ];
    final destinations = <NavigationDestination>[
      if (ambulance)
        NavigationDestination(
          icon: const Icon(Icons.emergency_outlined),
          label: s.role == 'driver' ? 'Dispatch' : 'SOS',
        ),
      if (patient)
        const NavigationDestination(
          icon: Icon(Icons.add_circle_outline),
          label: 'Request care',
        ),
      if (['patient', 'hospital'].contains(s.role))
        NavigationDestination(
          icon: const Icon(Icons.receipt_long_outlined),
          label: s.role == 'patient' ? 'My requests' : 'Requests',
        ),
      if (['patient', 'hospital'].contains(s.role))
        const NavigationDestination(
          icon: Icon(Icons.local_hospital_outlined),
          label: 'Beds',
        ),
      const NavigationDestination(
        icon: Icon(Icons.person_outline),
        label: 'Account',
      ),
    ];
    if (tab >= pages.length) tab = 0;
    return LayoutBuilder(
      builder: (context, constraints) {
        final wide =
            constraints.maxWidth >= 720 && constraints.maxHeight >= 600;
        final content = Column(
          children: [
            if (!s.online)
              Material(
                color: const Color(0xFFFFE8D9),
                child: ListTile(
                  dense: true,
                  leading: const Icon(Icons.wifi_off),
                  title: Text(s.error ?? 'Connecting to server…'),
                  subtitle: s.state == null
                      ? null
                      : const Text(
                          'Showing saved data. Reconnect to make changes.',
                        ),
                  trailing: IconButton(
                    tooltip: 'Server connection',
                    onPressed: () => serverDialog(context, s),
                    icon: const Icon(Icons.settings_outlined),
                  ),
                ),
              ),
            if (s.busy) const LinearProgressIndicator(),
            Expanded(
              child: IndexedStack(index: tab, children: pages),
            ),
          ],
        );
        return Scaffold(
          appBar: AppBar(
            title: const Text(
              'CareRoute',
              style: TextStyle(fontWeight: FontWeight.w800),
            ),
            actions: [
              IconButton(
                tooltip: 'Refresh',
                onPressed: s.refresh,
                icon: const Icon(Icons.refresh),
              ),
            ],
          ),
          body: SafeArea(
            bottom: wide,
            child: wide
                ? Row(
                    children: [
                      NavigationRail(
                        extended: constraints.maxWidth >= 1100,
                        labelType: constraints.maxWidth >= 1100
                            ? NavigationRailLabelType.none
                            : NavigationRailLabelType.all,
                        selectedIndex: tab,
                        destinations: destinations
                            .map(
                              (d) => NavigationRailDestination(
                                icon: d.icon,
                                selectedIcon: d.selectedIcon,
                                label: Text(d.label),
                              ),
                            )
                            .toList(),
                        onDestinationSelected: (v) => setState(() => tab = v),
                      ),
                      const VerticalDivider(width: 1),
                      Expanded(child: content),
                    ],
                  )
                : content,
          ),
          bottomNavigationBar: wide
              ? null
              : NavigationBar(
                  selectedIndex: tab,
                  labelBehavior:
                      constraints.maxWidth < 420 ||
                          MediaQuery.textScalerOf(context).scale(1) > 1.3
                      ? NavigationDestinationLabelBehavior.onlyShowSelected
                      : NavigationDestinationLabelBehavior.alwaysShow,
                  destinations: destinations,
                  onDestinationSelected: (v) => setState(() => tab = v),
                ),
        );
      },
    );
  }
}

class RequestForm extends StatefulWidget {
  final Session session;
  final void Function(String) onCreated;
  final VoidCallback? onAmbulance;
  final Future<Map<String, dynamic>> Function()? locationProvider;
  const RequestForm({
    super.key,
    required this.session,
    required this.onCreated,
    this.onAmbulance,
    this.locationProvider,
  });
  @override
  State<RequestForm> createState() => _RequestFormState();
}

class _RequestFormState extends State<RequestForm> {
  final form = GlobalKey<FormState>();
  final patient = TextEditingController(),
      location = TextEditingController(),
      description = TextEditingController();
  String problemType = 'Internal', assistance = 'Ambulance';
  String? preferredHospital, department, coveredPatient, coverage;
  Map<String, dynamic>? gps;
  StreamSubscription<Position>? locationStream;
  String locationStatus = 'Share live GPS while this form is open.';
  bool locating = false;
  bool gpsLabel = false;
  Future<void> shareLocation() async {
    setState(() {
      locating = true;
      locationStatus = 'Getting live GPS…';
    });
    try {
      final current =
          await (widget.locationProvider?.call() ?? PickupLocation.current());
      if (!mounted) return;
      setState(() {
        gps = current;
        locationStatus = 'Live GPS captured';
        gpsLabel = location.text.isEmpty || gpsLabel;
        if (gpsLabel) {
          location.text = 'GPS ${current['lat']}, ${current['lng']}';
        }
      });
      if (widget.locationProvider != null) return;
      await locationStream?.cancel();
      locationStream =
          Geolocator.getPositionStream(
            locationSettings: const LocationSettings(
              accuracy: LocationAccuracy.high,
              distanceFilter: 5,
            ),
          ).listen(
            (position) {
              if (!mounted) return;
              if (position.accuracy > 200) return;
              setState(() {
                gps = {
                  'lat': position.latitude,
                  'lng': position.longitude,
                  'captured_at':
                      position.timestamp.millisecondsSinceEpoch ~/ 1000,
                };
                locationStatus =
                    'Live GPS updated ${position.timestamp.toLocal()}';
                if (gpsLabel) {
                  location.text =
                      'GPS ${position.latitude}, ${position.longitude}';
                }
              });
            },
            onError: (Object error) {
              if (mounted) {
                setState(() {
                  gps = null;
                  locationStatus =
                      'GPS unavailable. Retry or enter location manually.';
                });
              }
            },
          );
    } catch (error) {
      if (mounted) {
        setState(() {
          gps = null;
          locationStatus = error.toString();
        });
      }
    } finally {
      if (mounted) setState(() => locating = false);
    }
  }

  String severity = 'Urgent', care = 'ICU';
  String get selectedCare => widget.session.careTypes.contains(care)
      ? care
      : widget.session.careTypes.isEmpty
      ? ''
      : widget.session.careTypes.first;
  @override
  void dispose() {
    patient.dispose();
    location.dispose();
    description.dispose();
    locationStream?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => ResponsivePage(
    maxWidth: 760,
    child: ListView(
      padding: EdgeInsets.zero,
      children: [
        const HeroPanel(
          title: 'Request care',
          subtitle: '',
          icon: Icons.health_and_safety_outlined,
        ),
        const SizedBox(height: 20),
        Form(
          key: form,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              if ((widget.session.state?['dependents'] as List? ?? [])
                  .isNotEmpty)
                DropdownButtonFormField<String>(
                  initialValue: coveredPatient,
                  isExpanded: true,
                  decoration: const InputDecoration(
                    labelText: 'Patient profile',
                  ),
                  items: [
                    const DropdownMenuItem<String>(
                      value: null,
                      child: Text('Myself'),
                    ),
                    ...(widget.session.state!['dependents'] as List).map(
                      (d) => DropdownMenuItem(
                        value: d['id'].toString(),
                        child: Text(
                          d['name'].toString(),
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    ),
                  ],
                  onChanged: (v) => setState(() {
                    coveredPatient = v;
                    coverage = null;
                    if (v != null) {
                      patient
                          .text = (widget.session.state!['dependents'] as List)
                          .firstWhere((d) => d['id'].toString() == v)['name']
                          .toString();
                    }
                  }),
                ),
              if ((widget.session.state?['coverages'] as List? ?? [])
                  .isNotEmpty)
                DropdownButtonFormField<String>(
                  key: ValueKey(coveredPatient),
                  initialValue: coverage,
                  isExpanded: true,
                  decoration: const InputDecoration(
                    labelText: 'Insurance / panel (optional)',
                  ),
                  items: [
                    const DropdownMenuItem<String>(
                      value: null,
                      child: Text('No coverage'),
                    ),
                    ...(widget.session.state!['coverages'] as List)
                        .where(
                          (c) => coveredPatient == null
                              ? c['dependent_id'] == null
                              : c['dependent_id']?.toString() ==
                                        coveredPatient ||
                                    (c['dependent_id'] == null &&
                                        c['covers_family'] == true),
                        )
                        .map(
                          (c) => DropdownMenuItem(
                            value: c['id'].toString(),
                            child: Text(
                              (c['organisation'] as Map?)?['name']
                                      ?.toString() ??
                                  'Coverage',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ),
                  ],
                  onChanged: (v) => setState(() => coverage = v),
                ),
              TextFormField(
                controller: patient,
                maxLength: 80,
                textCapitalization: TextCapitalization.words,
                decoration: const InputDecoration(
                  labelText: 'Patient name / ID',
                  prefixIcon: Icon(Icons.person_outline),
                ),
                validator: (v) =>
                    v!.trim().isEmpty ? 'Enter the patient name or ID' : null,
              ),
              const SizedBox(height: 10),
              TextFormField(
                controller: location,
                onChanged: (_) => gpsLabel = false,
                maxLength: 120,
                decoration: const InputDecoration(
                  labelText: 'Current location',
                  hintText: 'Accident Site',
                  prefixIcon: Icon(Icons.location_on_outlined),
                ),
                validator: (v) =>
                    v!.trim().isEmpty ? 'Enter the current location' : null,
              ),
              const SizedBox(height: 10),
              DropdownButtonFormField<String>(
                initialValue: problemType,
                isExpanded: true,
                decoration: const InputDecoration(labelText: 'Problem type'),
                items: const [
                  DropdownMenuItem(
                    value: 'Internal',
                    child: Text(
                      'Internal — illness or symptoms',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                  DropdownMenuItem(
                    value: 'External',
                    child: Text(
                      'External — accident or injury',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ],
                onChanged: (value) => setState(() {
                  problemType = value!;
                  assistance = value == 'Internal' ? 'Ambulance' : 'Hospital';
                }),
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<String>(
                key: ValueKey(assistance),
                isExpanded: true,
                initialValue: assistance,
                decoration: const InputDecoration(
                  labelText: 'Preferred assistance',
                ),
                items: const [
                  DropdownMenuItem(
                    value: 'Ambulance',
                    child: Text('Ambulance'),
                  ),
                  DropdownMenuItem(value: 'Hospital', child: Text('Hospital')),
                ],
                onChanged: (value) => setState(() => assistance = value!),
              ),
              if (assistance == 'Hospital') ...[
                const SizedBox(height: 16),
                DropdownButtonFormField<String>(
                  initialValue: preferredHospital,
                  isExpanded: true,
                  decoration: const InputDecoration(
                    labelText: 'Preferred hospital',
                  ),
                  items: [
                    const DropdownMenuItem<String>(
                      value: null,
                      child: Text(
                        'Nearest suitable hospital',
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    ),
                    ...widget.session.hospitals
                        .where(
                          (h) =>
                              h['status'] == 'Open' &&
                              (department == null ||
                                  (h['departments'] as List? ?? []).any(
                                    (d) => d['id'].toString() == department,
                                  )) &&
                              (h['beds'] as List? ?? []).any(
                                (b) =>
                                    b['care'] == selectedCare &&
                                    b['status'] == 'Available',
                              ),
                        )
                        .map(
                          (h) => DropdownMenuItem(
                            value: h['id'].toString(),
                            child: Text(
                              h['name'].toString(),
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ),
                  ],
                  onChanged: (value) =>
                      setState(() => preferredHospital = value),
                ),
              ],
              const SizedBox(height: 16),
              TextFormField(
                controller: description,
                maxLength: 2000,
                maxLines: 3,
                decoration: const InputDecoration(
                  labelText: 'Problem description',
                  hintText: 'Symptoms or injuries and when they started',
                ),
              ),
              OutlinedButton.icon(
                onPressed: locating ? null : shareLocation,
                icon: const Icon(Icons.my_location),
                label: const Text('Share live location'),
              ),
              Text(locationStatus),
              const SizedBox(height: 16),
              if ((widget.session.state?['departments'] as List? ?? [])
                  .isNotEmpty) ...[
                DropdownButtonFormField<String>(
                  initialValue: department,
                  isExpanded: true,
                  decoration: const InputDecoration(
                    labelText: 'Required service',
                  ),
                  items: (widget.session.state!['departments'] as List)
                      .map(
                        (d) => DropdownMenuItem(
                          value: d['id'].toString(),
                          child: Text(
                            d['department_name'].toString(),
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                          ),
                        ),
                      )
                      .toList(),
                  onChanged: (value) => setState(() {
                    department = value;
                    preferredHospital = null;
                  }),
                  validator: (value) =>
                      value == null ? 'Select the service you need' : null,
                ),
                const SizedBox(height: 16),
              ],
              DropdownButtonFormField<String>(
                initialValue: severity,
                isExpanded: true,
                decoration: const InputDecoration(labelText: 'Severity'),
                items: ['Critical', 'Urgent', 'Stable']
                    .map((v) => DropdownMenuItem(value: v, child: Text(v)))
                    .toList(),
                onChanged: (v) => setState(() => severity = v!),
              ),
              const SizedBox(height: 16),
              DropdownButtonFormField<String>(
                initialValue: selectedCare.isEmpty ? null : selectedCare,
                isExpanded: true,
                decoration: const InputDecoration(labelText: 'Required care'),
                items: widget.session.careTypes
                    .map(
                      (v) => DropdownMenuItem(
                        value: v,
                        child: Text(
                          v == 'General' ? 'General Bed' : v,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                    )
                    .toList(),
                onChanged: (v) => setState(() => care = v!),
              ),
              const SizedBox(height: 20),
              FilledButton.icon(
                onPressed:
                    widget.session.busy ||
                        !widget.session.online ||
                        selectedCare.isEmpty ||
                        !widget.session.can('create')
                    ? null
                    : () async {
                        if (!form.currentState!.validate()) return;
                        try {
                          if (gps == null) await shareLocation();
                          if (!mounted || !context.mounted) return;
                          if (gps == null) {
                            notice(context, locationStatus);
                            return;
                          }
                          final result = await widget.session.action({
                            'action': 'create',
                            'patient': patient.text.trim(),
                            'location': location.text.trim(),
                            'severity': severity,
                            'care': selectedCare,
                            if (coverage != null) 'coverage_id': coverage,
                            if (coveredPatient != null)
                              'dependent_id': coveredPatient,
                            'problem_type': problemType,
                            'department_id': department,
                            'assistance_preference': assistance,
                            'description': description.text.trim(),
                            'preferred_hospital_id': assistance == 'Hospital'
                                ? preferredHospital
                                : null,
                            if (gps != null) 'latitude': gps!['lat'],
                            if (gps != null) 'longitude': gps!['lng'],
                            if (gps != null)
                              'location_captured_at': gps!['captured_at'],
                          });
                          if (!mounted) return;
                          patient.clear();
                          location.clear();
                          description.clear();
                          await locationStream?.cancel();
                          gps = null;
                          widget.onCreated(result['id'] as String);
                        } catch (e) {
                          if (context.mounted) notice(context, e);
                        }
                      },
                icon: const Icon(Icons.send_outlined),
                label: Text(
                  problemType == 'Internal'
                      ? 'Choose ambulance'
                      : 'Choose hospital',
                ),
              ),
            ],
          ),
        ),
      ],
    ),
  );
}

class HeroPanel extends StatelessWidget {
  final String title, subtitle;
  final IconData icon;
  const HeroPanel({
    super.key,
    required this.title,
    required this.subtitle,
    required this.icon,
  });
  @override
  Widget build(BuildContext context) => Container(
    padding: const EdgeInsets.symmetric(vertical: 8),
    decoration: BoxDecoration(borderRadius: BorderRadius.circular(18)),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFDDEFE9),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Icon(icon, color: teal, size: 24),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Text(
                title,
                style: const TextStyle(
                  color: navy,
                  fontSize: 23,
                  fontWeight: FontWeight.w700,
                ),
              ),
            ),
          ],
        ),
        if (subtitle.trim().isNotEmpty) ...[
          const SizedBox(height: 8),
          Text(
            subtitle,
            style: const TextStyle(color: Color(0xFF5C716A), height: 1.4),
          ),
        ],
      ],
    ),
  );
}

Color statusColor(String status) => switch (status) {
  'Occupied' ||
  'Critical' ||
  'Offline' ||
  'Rejected' => const Color(0xFFB63543),
  'Pending' || 'Temporary' || 'Urgent' => const Color(0xFF976000),
  'Reserved' => const Color(0xFF3863A0),
  _ => teal,
};
Widget badge(String label) => Container(
  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
  decoration: BoxDecoration(
    color: statusColor(label).withValues(alpha: .08),
    borderRadius: BorderRadius.circular(12),
  ),
  child: Text(
    label,
    style: TextStyle(
      color: statusColor(label),
      fontSize: 12,
      fontWeight: FontWeight.w600,
    ),
  ),
);

class Queue extends StatelessWidget {
  final Session session;
  final void Function(String) open;
  const Queue({super.key, required this.session, required this.open});
  @override
  Widget build(BuildContext context) => RefreshIndicator(
    onRefresh: session.refresh,
    child: ResponsivePage(
      maxWidth: 960,
      child: ListView(
        padding: EdgeInsets.zero,
        children: [
          Text(
            session.role == 'patient'
                ? 'Your care requests'
                : 'Emergency queue',
            style: const TextStyle(
              fontSize: 25,
              fontWeight: FontWeight.bold,
              color: navy,
            ),
          ),
          const SizedBox(height: 16),
          if (session.requests.isEmpty)
            const Card(
              child: Padding(
                padding: EdgeInsets.all(28),
                child: Text('No requests yet.'),
              ),
            ),
          for (final r in session.requests)
            Card(
              child: InkWell(
                borderRadius: BorderRadius.circular(12),
                onTap: () => open(r['id'] as String),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              r['patient'].toString(),
                              style: const TextStyle(
                                fontSize: 18,
                                fontWeight: FontWeight.w700,
                              ),
                            ),
                          ),
                          const Icon(Icons.chevron_right),
                        ],
                      ),
                      const SizedBox(height: 6),
                      Text('${r['care']} • ${r['location']}'),
                      const SizedBox(height: 8),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: [
                          badge(r['status'].toString()),
                          badge(r['severity'].toString()),
                        ],
                      ),
                      if (r['hospital_id'] != null)
                        Text('Hospital ${r['hospital_id']} • ${r['bed_id']}'),
                    ],
                  ),
                ),
              ),
            ),
        ],
      ),
    ),
  );
}

class RequestDetail extends StatelessWidget {
  final Session session;
  final String id;
  const RequestDetail({super.key, required this.session, required this.id});
  Future<void> act(
    BuildContext context,
    String action, [
    Map<String, dynamic> extra = const {},
  ]) async {
    try {
      final result = await session.action({
        'action': action,
        'id': id,
        ...extra,
      });
      if (context.mounted) notice(context, result['message'] ?? 'Updated');
    } catch (e) {
      if (context.mounted) notice(context, e);
    }
  }

  @override
  Widget build(BuildContext context) => AnimatedBuilder(
    animation: session,
    builder: (context, _) {
      final found = session.requests.where((r) => r['id'] == id);
      if (!session.signedIn) {
        return Scaffold(
          appBar: AppBar(title: const Text('Sign in required')),
          body: Center(
            child: FilledButton(
              onPressed: () => Navigator.pop(context),
              child: const Text('Back to login'),
            ),
          ),
        );
      }
      if (found.isEmpty) {
        return Scaffold(
          appBar: AppBar(title: const Text('Request')),
          body: const Center(child: Text('This request is no longer visible.')),
        );
      }
      final r = found.first;
      final status = r['status'] as String;
      final showRecommendations = [
        'New',
        'Released',
        'Expired',
        'Rejected',
      ].contains(status);
      final canRequest =
          session.role == 'patient' &&
          session.can('request') &&
          showRecommendations;
      final operator = session.role == 'hospital' && session.can('approve');
      final enabled = session.online && !session.busy;
      final seconds = r['expires_at'] is num
          ? ((r['expires_at'] as num) - (session.state?['now'] as num? ?? 0))
                .clamp(0, 99999)
                .toInt()
          : null;
      final activities =
          (session.state?['events'] as List? ?? [])
              .where((e) => e['request_id'] == id)
              .toList()
            ..sort(
              (a, b) => (b['at'] as num? ?? 0).compareTo(a['at'] as num? ?? 0),
            );
      return Scaffold(
        appBar: AppBar(
          title: const Text('Request details'),
          actions: [
            IconButton(
              onPressed: session.refresh,
              icon: const Icon(Icons.refresh),
            ),
          ],
        ),
        body: RefreshIndicator(
          onRefresh: session.refresh,
          child: ResponsivePage(
            maxWidth: 900,
            child: ListView(
              padding: EdgeInsets.zero,
              children: [
                HeroPanel(
                  title: r['patient'].toString(),
                  subtitle:
                      '${r['location']}\n${r['care']} care • Request #$id',
                  icon: Icons.medical_information_outlined,
                ),
                if (session.role == 'patient' &&
                    (r['problem_type'] == 'Internal' ||
                        r['assistance_preference'] == 'Ambulance') &&
                    ![
                      'Cancelled',
                      'Discharged',
                      'Occupied',
                    ].contains(status)) ...[
                  const SizedBox(height: 12),
                  FilledButton.icon(
                    onPressed: () => Navigator.push(
                      context,
                      MaterialPageRoute<void>(
                        builder: (_) => Scaffold(
                          appBar: AppBar(title: const Text('Choose ambulance')),
                          body: ResponsivePage(
                            maxWidth: 760,
                            child: ListView(
                              padding: EdgeInsets.zero,
                              children: [
                                AmbulanceChoices(session: session, request: r),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                    icon: const Icon(Icons.emergency_outlined),
                    label: const Text('Choose ambulance'),
                  ),
                ],
                const SizedBox(height: 16),
                Wrap(
                  spacing: 8,
                  runSpacing: 8,
                  children: [
                    badge(status),
                    badge(r['severity'].toString()),
                    badge('Priority ${r['priority']}'),
                  ],
                ),
                if ((r['description'] ?? '').toString().isNotEmpty)
                  Padding(
                    padding: const EdgeInsets.only(top: 12),
                    child: Text(r['description'].toString()),
                  ),
                ExpansionTile(
                  tilePadding: EdgeInsets.zero,
                  title: const Text('Request information'),
                  children: [
                    Align(
                      alignment: Alignment.centerLeft,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          if ((r['problem_type'] ?? '').toString().isNotEmpty)
                            Text(
                              '${r['problem_type']} • ${r['assistance_preference'] ?? 'Hospital'}',
                            ),
                          if ((r['reporter_name'] ?? '').toString().isNotEmpty)
                            Text(
                              'Raised by: ${r['reporter_name']}${(r['reporter_phone'] ?? '').toString().isNotEmpty ? ' • ${r['reporter_phone']}' : ''}',
                            ),
                          if (r['latitude'] != null && r['longitude'] != null)
                            Text(
                              'Patient GPS: ${r['latitude']}, ${r['longitude']}',
                            ),
                          Text(
                            r['verified'] == true
                                ? 'Severity verified'
                                : 'Awaiting hospital triage',
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
                if (r['coverage_id'] != null) ...[
                  Text(
                    'Coverage: ${r['insurance_status']} • Cashless: ${r['cashless_status']}',
                  ),
                  if (r['coverage_details'] is Map)
                    ExpansionTile(
                      tilePadding: EdgeInsets.zero,
                      title: const Text('Insurance details'),
                      children: [
                        Align(
                          alignment: Alignment.centerLeft,
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              if (((r['coverage_details']
                                          as Map)['organisation']
                                      as Map?)?['name'] !=
                                  null)
                                Text(
                                  'Organisation: ${((r['coverage_details'] as Map)['organisation'] as Map)['name']}',
                                ),
                              if (((r['coverage_details']
                                          as Map)['policy_number'] ??
                                      '')
                                  .toString()
                                  .isNotEmpty)
                                Text(
                                  'Policy: ${(r['coverage_details'] as Map)['policy_number']}',
                                ),
                              if (((r['coverage_details']
                                          as Map)['member_id'] ??
                                      '')
                                  .toString()
                                  .isNotEmpty)
                                Text(
                                  'Member: ${(r['coverage_details'] as Map)['member_id']}',
                                ),
                              if ((r['coverage_details']
                                      as Map)['valid_from'] !=
                                  null)
                                Text(
                                  'Valid from: ${(r['coverage_details'] as Map)['valid_from']}',
                                ),
                              if ((r['coverage_details']
                                      as Map)['valid_until'] !=
                                  null)
                                Text(
                                  'Valid until: ${(r['coverage_details'] as Map)['valid_until']}',
                                ),
                              if ((r['coverage_details'] as Map)['has_card'] ==
                                  true)
                                TextButton(
                                  onPressed: () => showDialog<void>(
                                    context: context,
                                    builder: (ctx) => AlertDialog(
                                      title: const Text('Insurance card'),
                                      content: ConstrainedBox(
                                        constraints: BoxConstraints(
                                          maxHeight:
                                              MediaQuery.sizeOf(ctx).height *
                                              .6,
                                          maxWidth: 480,
                                        ),
                                        child: Image.network(
                                          '${session.api.base}/coverages/${r['coverage_id']}/card',
                                          fit: BoxFit.contain,
                                          headers: {
                                            'Authorization':
                                                'Bearer ${session.api.token}',
                                          },
                                          errorBuilder: (_, _, _) =>
                                              const Text('Card unavailable.'),
                                        ),
                                      ),
                                      actions: [
                                        TextButton(
                                          onPressed: () => Navigator.pop(ctx),
                                          child: const Text('Close'),
                                        ),
                                      ],
                                    ),
                                  ),
                                  child: const Text('View insurance card'),
                                ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  if ((r['insurance_note'] ?? '').toString().isNotEmpty)
                    Text(r['insurance_note'].toString()),
                  if (operator)
                    OutlinedButton.icon(
                      onPressed: enabled
                          ? () => reviewInsurance(context, session, r)
                          : null,
                      icon: const Icon(Icons.fact_check_outlined),
                      label: const Text('Review insurance / panel'),
                    ),
                ],
                if (r['hospital_id'] != null)
                  Padding(
                    padding: const EdgeInsets.symmetric(vertical: 12),
                    child: Text(
                      '${['Pending', 'Reserved', 'Occupied'].contains(status) ? '' : 'Previous allocation: '}Hospital ${r['hospital_id']} • Bed ${r['bed_id']}',
                      style: const TextStyle(fontWeight: FontWeight.w700),
                    ),
                  ),
                if (seconds != null && ['Pending', 'Reserved'].contains(status))
                  Text('Hold expires in ${seconds ~/ 60}m ${seconds % 60}s.'),
                if (status == 'Pending')
                  const Text('Waiting for hospital acceptance.'),
                if (status == 'Reserved')
                  const Text('Bed confirmed. Arrive before expiry.'),
                if (status == 'Occupied') const Text('Arrival recorded.'),
                if (status == 'Expired')
                  const Text('Hold expired. Choose a hospital again.'),
                if (status == 'Released')
                  const Text(
                    'Hold released for critical care. Choose another hospital.',
                  ),
                if (status == 'Rejected')
                  const Text('Request declined. Choose another hospital.'),
                if (!session.online)
                  const Padding(
                    padding: EdgeInsets.symmetric(vertical: 12),
                    child: Text(
                      'Offline: displayed status may be out of date.',
                      style: TextStyle(color: Colors.red),
                    ),
                  ),
                if (operator && ['New', 'Pending'].contains(status)) ...[
                  const Divider(height: 32),
                  const Text(
                    'Hospital triage',
                    style: TextStyle(fontWeight: FontWeight.bold),
                  ),
                  Wrap(
                    spacing: 8,
                    runSpacing: 8,
                    children: [
                      for (final severity in ['Critical', 'Urgent', 'Stable'])
                        OutlinedButton(
                          onPressed: enabled
                              ? () => act(context, 'verify', {
                                  'severity': severity,
                                })
                              : null,
                          child: Text('Verify $severity'),
                        ),
                    ],
                  ),
                ],
                if (operator && status == 'Pending')
                  Wrap(
                    spacing: 10,
                    runSpacing: 10,
                    children: [
                      FilledButton.icon(
                        onPressed: enabled
                            ? () => act(context, 'accept')
                            : null,
                        icon: const Icon(Icons.check),
                        label: const Text('Accept bed request'),
                      ),
                      OutlinedButton(
                        onPressed: enabled
                            ? () => act(context, 'reject')
                            : null,
                        child: const Text('Reject'),
                      ),
                    ],
                  ),
                if (operator && status == 'Reserved')
                  FilledButton.icon(
                    onPressed: enabled ? () => act(context, 'arrive') : null,
                    icon: const Icon(Icons.how_to_reg),
                    label: const Text('Mark patient arrived'),
                  ),
                const Divider(height: 32),
                if (showRecommendations) ...[
                  Text(
                    r['problem_type'] == 'Internal'
                        ? 'Hospital beds'
                        : 'Choose a hospital',
                    style: TextStyle(
                      fontSize: 21,
                      color: navy,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 12),
                  if ((r['matches'] as List)
                      .where((m) => m['compatible'] == true)
                      .isEmpty)
                    const Text(
                      'No suitable beds available. Try ambulance assistance or refresh.',
                    ),
                  for (final m in (r['matches'] as List).where(
                    (m) => m['compatible'] == true,
                  ))
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.stretch,
                          children: [
                            Wrap(
                              spacing: 12,
                              runSpacing: 6,
                              crossAxisAlignment: WrapCrossAlignment.center,
                              children: [
                                Text(
                                  m['name'].toString(),
                                  style: const TextStyle(
                                    fontSize: 18,
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                                Text(
                                  m['distance'] == null
                                      ? 'Distance unavailable'
                                      : '${m['distance']} km',
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            if (m['coverage'] != null)
                              Text(
                                'Coverage: ${(m['coverage'] as Map)['status']}',
                              ),
                            if (m['preferred'] == true)
                              const Text('Your preferred hospital'),
                            Text('${r['care']} • ${m['available']} available'),
                            if (m['reclaimable'] != 0)
                              Text(
                                '${m['reclaimable']} eligible temporary holds',
                              ),
                            if (canRequest && m['compatible'] == true)
                              Padding(
                                padding: const EdgeInsets.only(top: 12),
                                child: FilledButton(
                                  onPressed: enabled
                                      ? () => act(context, 'request', {
                                          'hospital_id': m['id'],
                                        })
                                      : null,
                                  child: Text('Request ${r['care']} bed'),
                                ),
                              ),
                          ],
                        ),
                      ),
                    ),
                  const Divider(height: 32),
                ],
                if (session.role == 'patient' &&
                    r['problem_type'] == 'External' &&
                    !['Cancelled', 'Discharged', 'Occupied'].contains(status))
                  AmbulanceChoices(
                    key: ValueKey('ambulance-$id'),
                    session: session,
                    request: r,
                  ),
                RecentActivityList(
                  children: [
                    for (final e in activities)
                      ListTile(
                        contentPadding: EdgeInsets.zero,
                        leading: const Icon(Icons.history, color: teal),
                        title: Text(e['message'].toString()),
                        subtitle: Text(
                          DateTime.fromMillisecondsSinceEpoch(
                            (e['at'] as num).toInt() * 1000,
                          ).toLocal().toString().split('.').first,
                        ),
                      ),
                  ],
                ),
              ],
            ),
          ),
        ),
      );
    },
  );
}

class Hospitals extends StatelessWidget {
  final Session session;
  const Hospitals({super.key, required this.session});
  @override
  Widget build(BuildContext context) => RefreshIndicator(
    onRefresh: session.refresh,
    child: ResponsivePage(
      maxWidth: 960,
      child: ListView(
        padding: EdgeInsets.zero,
        children: [
          const Text(
            'Hospital beds',
            style: TextStyle(
              fontSize: 25,
              fontWeight: FontWeight.bold,
              color: navy,
            ),
          ),
          const SizedBox(height: 12),
          Wrap(
            spacing: 8,
            runSpacing: 8,
            children: [
              'Available',
              'Temporary',
              'Reserved',
              'Occupied',
            ].map(badge).toList(),
          ),
          for (final h in session.hospitals)
            Card(
              child: Padding(
                padding: const EdgeInsets.all(18),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Wrap(
                      spacing: 12,
                      runSpacing: 8,
                      crossAxisAlignment: WrapCrossAlignment.center,
                      children: [
                        Text(
                          h['name'].toString(),
                          style: const TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                        badge(h['status'].toString()),
                      ],
                    ),
                    if (h['distance'] != null) Text('${h['distance']} km'),
                    for (final care in session.careTypes) ...[
                      ExpansionTile(
                        tilePadding: EdgeInsets.zero,
                        title: Text(
                          care == 'General' ? 'General beds' : care,
                          style: const TextStyle(fontWeight: FontWeight.bold),
                        ),
                        subtitle: Text(
                          '${(h['beds'] as List).where((b) => b['care'] == care && b['status'] == 'Available').length} available • ${(h['beds'] as List).where((b) => b['care'] == care).length} total',
                        ),
                        children: [
                          Align(
                            alignment: Alignment.centerLeft,
                            child: Wrap(
                              spacing: 6,
                              runSpacing: 6,
                              children: [
                                for (final b in (h['beds'] as List).where(
                                  (b) => b['care'] == care,
                                ))
                                  Tooltip(
                                    message: '${b['id']}: ${b['status']}',
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 10,
                                        vertical: 8,
                                      ),
                                      decoration: BoxDecoration(
                                        color: statusColor(
                                          b['status'].toString(),
                                        ).withValues(alpha: .1),
                                        borderRadius: BorderRadius.circular(8),
                                      ),
                                      child: Row(
                                        mainAxisSize: MainAxisSize.min,
                                        children: [
                                          Icon(
                                            Icons.bed_outlined,
                                            size: 19,
                                            color: statusColor(
                                              b['status'].toString(),
                                            ),
                                          ),
                                          const SizedBox(width: 5),
                                          Flexible(
                                            child: Text(
                                              b['status'].toString(),
                                              style: TextStyle(
                                                color: statusColor(
                                                  b['status'].toString(),
                                                ),
                                                fontSize: 12,
                                              ),
                                            ),
                                          ),
                                        ],
                                      ),
                                    ),
                                  ),
                              ],
                            ),
                          ),
                        ],
                      ),
                    ],
                  ],
                ),
              ),
            ),
        ],
      ),
    ),
  );
}

class Account extends StatelessWidget {
  final Session session;
  const Account({super.key, required this.session});
  Future<void> webPortal(BuildContext context) async {
    final base = Uri.parse(session.api.base);
    final url = base.replace(
      path: '${base.path.replaceFirst(RegExp(r'/api/mobile$'), '')}/login',
    );
    try {
      if (!await launchUrl(url, mode: LaunchMode.externalApplication)) {
        throw Exception('Unable to open browser. Open $url manually.');
      }
    } catch (e) {
      if (context.mounted) notice(context, e);
    }
  }

  @override
  Widget build(BuildContext context) => ResponsivePage(
    maxWidth: 760,
    child: ListView(
      padding: EdgeInsets.zero,
      children: [
        HeroPanel(
          title: session.profile?['name']?.toString() ?? 'Your account',
          subtitle:
              '${session.profile?['email'] ?? ''}\n${session.role.toUpperCase()}${session.profile?['hospital'] != null ? ' • Hospital ${session.profile!['hospital']}' : ''}',
          icon: Icons.person_outline,
        ),
        const SizedBox(height: 20),
        Card(
          child: ListTile(
            leading: const Icon(Icons.dns_outlined),
            title: const Text('Server connection'),
            subtitle: Text(session.api.base),
            onTap: () => serverDialog(context, session),
          ),
        ),
        if (session.role == 'admin') ...[
          const SizedBox(height: 16),
          Card(
            child: Padding(
              padding: const EdgeInsets.all(18),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  const Text(
                    'Admin portal',
                    style: TextStyle(fontWeight: FontWeight.bold),
                  ),
                  const SizedBox(height: 12),
                  OutlinedButton.icon(
                    onPressed: () => webPortal(context),
                    icon: const Icon(Icons.open_in_browser),
                    label: const Text('Open admin portal'),
                  ),
                ],
              ),
            ),
          ),
        ],
        const SizedBox(height: 18),
        if (session.role == 'patient')
          OutlinedButton.icon(
            onPressed: () => Navigator.push(
              context,
              MaterialPageRoute<void>(
                builder: (_) => Scaffold(
                  appBar: AppBar(title: const Text('Family & insurance')),
                  body: PatientPortfolio(session: session),
                ),
              ),
            ),
            icon: const Icon(Icons.shield_outlined),
            label: const Text('Family & insurance'),
          ),
        FilledButton.tonalIcon(
          onPressed: session.signOut,
          icon: const Icon(Icons.logout),
          label: const Text('Sign out'),
        ),
      ],
    ),
  );
}
