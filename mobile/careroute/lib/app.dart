import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';

import 'session.dart';
import 'sos.dart';

const navy = Color(0xFF102C46);
const teal = Color(0xFF087F8C);

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
    theme: ThemeData(
      useMaterial3: true,
      colorScheme: ColorScheme.fromSeed(seedColor: teal),
      scaffoldBackgroundColor: const Color(0xFFF3F7FA),
      appBarTheme: const AppBarTheme(
        backgroundColor: Color(0xFFF3F7FA),
        foregroundColor: navy,
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: Colors.white,
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(14)),
        contentPadding: const EdgeInsets.all(16),
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          minimumSize: const Size(0, 48),
          shape: RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(14),
          ),
        ),
      ),
    ),
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
  await showDialog<void>(
    context: context,
    builder: (dialogContext) => StatefulBuilder(
      builder: (context, set) => AlertDialog(
        title: const Text('Connect to your server'),
        content: SizedBox(
          width: 450,
          child: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'Keep Apache and MySQL running. For a real phone, use your PC’s Wi-Fi IPv4 address. Both devices must be on the same network.',
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
                  children: [
                    ActionChip(
                      label: const Text('Android emulator'),
                      onPressed: busy
                          ? null
                          : () => controller.text =
                                'http://10.0.2.2/Hospital/public/api/mobile',
                    ),
                    ActionChip(
                      label: const Text('This PC / iOS simulator'),
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
                const Text(
                  'Changing servers signs you out. The API address is saved on this device.',
                ),
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
                    Image.asset('assets/hospital-logo.png', height: 82),
                    const SizedBox(height: 22),
                    Text(
                      register
                          ? 'Create your patient account'
                          : 'Care starts with the right bed',
                      style: const TextStyle(
                        fontSize: 29,
                        fontWeight: FontWeight.w800,
                        color: navy,
                      ),
                    ),
                    const SizedBox(height: 10),
                    const Text(
                      'Request emergency care, compare suitable hospitals, and follow your bed confirmation.',
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
                      decoration: const InputDecoration(
                        labelText: 'Email address',
                        prefixIcon: Icon(Icons.mail_outline),
                      ),
                      validator: (v) =>
                          RegExp(r'^[^\s@]+@[^\s@]+\.[^\s@]+$')
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
                    const SizedBox(height: 12),
                    const Text(
                      'Prototype • Seeded hospital data\nSeverity is verified by a hospital before elevated priority applies.',
                      textAlign: TextAlign.center,
                      style: TextStyle(fontSize: 12, color: Colors.black54),
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
    final patient = ['patient', 'admin'].contains(s.role);
    final ambulance = ['patient', 'driver', 'admin'].contains(s.role);
    final pages = <Widget>[
      if (ambulance) SosScreen(session: s, openBed: open),
      if (patient) RequestForm(session: s, onCreated: open),
      if (s.role != 'driver') Queue(session: s, open: open),
      if (s.role != 'driver') Hospitals(session: s),
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
      if (s.role != 'driver')
        NavigationDestination(
          icon: const Icon(Icons.receipt_long_outlined),
          label: s.role == 'patient' ? 'My requests' : 'Requests',
        ),
      if (s.role != 'driver')
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
      body: Column(
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
                        'Showing last fetched data. Actions require a connection.',
                      ),
                trailing: IconButton(
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
      ),
      bottomNavigationBar: NavigationBar(
        selectedIndex: tab,
        destinations: destinations,
        onDestinationSelected: (v) => setState(() => tab = v),
      ),
    );
  }
}

class RequestForm extends StatefulWidget {
  final Session session;
  final void Function(String) onCreated;
  const RequestForm({
    super.key,
    required this.session,
    required this.onCreated,
  });
  @override
  State<RequestForm> createState() => _RequestFormState();
}

class _RequestFormState extends State<RequestForm> {
  final form = GlobalKey<FormState>();
  final patient = TextEditingController(), location = TextEditingController();
  String severity = 'Urgent', care = 'ICU';
  @override
  void dispose() {
    patient.dispose();
    location.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => ListView(
    padding: const EdgeInsets.all(20),
    children: [
      const HeroPanel(
        title: 'Find care that can receive you',
        subtitle: 'A nearby hospital is useful only when the required care is available.',
        icon: Icons.health_and_safety_outlined,
      ),
      const SizedBox(height: 20),
      Form(
        key: form,
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
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
              initialValue: severity,
              decoration: const InputDecoration(labelText: 'Severity'),
              items: [
                'Critical',
                'Urgent',
                'Stable',
              ].map((v) => DropdownMenuItem(value: v, child: Text(v))).toList(),
              onChanged: (v) => setState(() => severity = v!),
            ),
            const SizedBox(height: 16),
            DropdownButtonFormField<String>(
              initialValue: care,
              decoration: const InputDecoration(labelText: 'Required care'),
              items: ['General', 'ICU', 'Ventilator']
                  .map(
                    (v) => DropdownMenuItem(
                      value: v,
                      child: Text(v == 'General' ? 'General Bed' : v),
                    ),
                  )
                  .toList(),
              onChanged: (v) => setState(() => care = v!),
            ),
            const SizedBox(height: 16),
            const Text(
              'A hospital verifies severity before Critical/Urgent priority applies. Distances and availability are seeded demo data.',
            ),
            const SizedBox(height: 20),
            FilledButton.icon(
              onPressed: widget.session.busy || !widget.session.online
                  ? null
                  : () async {
                      if (!form.currentState!.validate()) return;
                      try {
                        final result = await widget.session.action({
                          'action': 'create',
                          'patient': patient.text.trim(),
                          'location': location.text.trim(),
                          'severity': severity,
                          'care': care,
                        });
                        if (!mounted) return;
                        patient.clear();
                        location.clear();
                        widget.onCreated(result['id'] as String);
                      } catch (e) {
                        if (context.mounted) notice(context, e);
                      }
                    },
              icon: const Icon(Icons.send_outlined),
              label: const Text('Submit emergency request'),
            ),
          ],
        ),
      ),
    ],
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
    padding: const EdgeInsets.all(24),
    decoration: BoxDecoration(
      borderRadius: BorderRadius.circular(24),
      gradient: const LinearGradient(colors: [navy, teal]),
    ),
    child: Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Icon(icon, color: const Color(0xFF9DEBE1), size: 32),
        const SizedBox(height: 16),
        Text(
          title,
          style: const TextStyle(
            color: Colors.white,
            fontSize: 24,
            fontWeight: FontWeight.w700,
          ),
        ),
        const SizedBox(height: 10),
        Text(
          subtitle,
          style: const TextStyle(color: Colors.white, height: 1.5),
        ),
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
Widget badge(String label) => Chip(
  label: Text(
    label,
    style: TextStyle(
      color: statusColor(label),
      fontSize: 12,
      fontWeight: FontWeight.w600,
    ),
  ),
  backgroundColor: statusColor(label).withValues(alpha: .08),
  side: BorderSide.none,
  visualDensity: VisualDensity.compact,
);

class Queue extends StatelessWidget {
  final Session session;
  final void Function(String) open;
  const Queue({super.key, required this.session, required this.open});
  @override
  Widget build(BuildContext context) => RefreshIndicator(
    onRefresh: session.refresh,
    child: ListView(
      padding: const EdgeInsets.all(20),
      children: [
        Text(
          session.role == 'patient' ? 'Your care requests' : 'Emergency queue',
          style: const TextStyle(
            fontSize: 25,
            fontWeight: FontWeight.bold,
            color: navy,
          ),
        ),
        const SizedBox(height: 8),
        Text(
          session.role == 'patient'
              ? 'Open a request to choose a hospital or track confirmation.'
              : 'Verified Critical patients appear first. Occupied beds are protected.',
        ),
        const SizedBox(height: 16),
        if (session.requests.isEmpty)
          const Card(
            child: Padding(
              padding: EdgeInsets.all(28),
              child: Text('No requests yet. Submitted requests appear here.'),
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
                    Text('${r['care']} • ${r['location']}'),
                    const SizedBox(height: 8),
                    Wrap(
                      spacing: 8,
                      children: [
                        badge(r['status'].toString()),
                        badge(r['severity'].toString()),
                        badge(
                          r['verified'] == true
                              ? 'Verified • P${r['priority']}'
                              : 'Awaiting triage • P1',
                        ),
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
      final canRequest = session.role != 'hospital' && showRecommendations;
      final operator = session.role != 'patient';
      final enabled = session.online && !session.busy;
      final seconds = r['expires_at'] is num
          ? ((r['expires_at'] as num) - (session.state?['now'] as num? ?? 0))
                .clamp(0, 99999)
                .toInt()
          : null;
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
          child: ListView(
            padding: const EdgeInsets.all(20),
            children: [
              HeroPanel(
                title: r['patient'].toString(),
                subtitle: '${r['location']}\n${r['care']} care • Request #$id',
                icon: Icons.medical_information_outlined,
              ),
              const SizedBox(height: 16),
              Wrap(
                spacing: 8,
                children: [
                  badge(status),
                  badge(r['severity'].toString()),
                  badge('Priority ${r['priority']}'),
                ],
              ),
              Text(
                r['verified'] == true
                    ? 'Severity verified by a hospital operator.'
                    : 'Severity awaits hospital verification. Elevated priority is not active yet.',
              ),
              if (r['hospital_id'] != null)
                Padding(
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  child: Text(
                    '${['Pending', 'Reserved', 'Occupied'].contains(status) ? '' : 'Previous allocation: '}Hospital ${r['hospital_id']} • Bed ${r['bed_id']}',
                    style: const TextStyle(fontWeight: FontWeight.w700),
                  ),
                ),
              if (seconds != null && ['Pending', 'Reserved'].contains(status))
                Text(
                  'Hold expires in about ${seconds ~/ 60}m ${seconds % 60}s. Updated every 5 seconds.',
                ),
              if (status == 'Pending')
                const Text(
                  'Waiting for hospital acceptance. This is a temporary hold.',
                ),
              if (status == 'Reserved')
                const Text(
                  'Hospital confirmed your bed. Reach the hospital before the hold expires.',
                ),
              if (status == 'Occupied')
                const Text(
                  'Arrival recorded. This occupied bed cannot be reassigned.',
                ),
              if (status == 'Expired')
                const Text(
                  'The hold expired and the bed was released. Choose a suitable hospital to request again.',
                ),
              if (status == 'Released')
                const Text(
                  'The temporary hold was reassigned to a verified Critical request. Choose another suitable hospital.',
                ),
              if (status == 'Rejected')
                const Text(
                  'The hospital rejected this request. You can request a bed at a compatible hospital again.',
                ),
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
                  children: [
                    for (final severity in ['Critical', 'Urgent', 'Stable'])
                      OutlinedButton(
                        onPressed: enabled
                            ? () =>
                                  act(context, 'verify', {'severity': severity})
                            : null,
                        child: Text('Verify $severity'),
                      ),
                  ],
                ),
              ],
              if (operator && status == 'Pending')
                Wrap(
                  spacing: 10,
                  children: [
                    FilledButton.icon(
                      onPressed: enabled ? () => act(context, 'accept') : null,
                      icon: const Icon(Icons.check),
                      label: const Text('Accept bed request'),
                    ),
                    OutlinedButton(
                      onPressed: enabled ? () => act(context, 'reject') : null,
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
                const Text(
                  'Hospital recommendations',
                  style: TextStyle(
                    fontSize: 21,
                    color: navy,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                const SizedBox(height: 6),
                const Text(
                  'Required care and an open hospital are mandatory. Compatible hospitals are ranked by demo distance.',
                ),
                const SizedBox(height: 12),
                for (final m in (r['matches'] as List))
                  Card(
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          Row(
                            children: [
                              Expanded(
                                child: Text(
                                  m['name'].toString(),
                                  style: const TextStyle(
                                    fontSize: 18,
                                    fontWeight: FontWeight.w700,
                                  ),
                                ),
                              ),
                              Text('${m['distance']} km'),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Text(
                            '${r['care']}: ${m['available']} available${m['reclaimable'] != 0 ? ' • ${m['reclaimable']} temporary holds eligible for reassignment' : ''}',
                          ),
                          const SizedBox(height: 8),
                          Text(
                            m['compatible'] == true
                                ? 'Suitable • priority ${r['priority']}'
                                : 'Rejected: ${m['reason']}',
                            style: TextStyle(
                              color: m['compatible'] == true
                                  ? teal
                                  : Colors.red,
                            ),
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
              const Text(
                'Request activity',
                style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
              ),
              for (final e in (session.state?['events'] as List? ?? []).where(
                (e) => e['request_id'] == id,
              ))
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
    child: ListView(
      padding: const EdgeInsets.all(20),
      children: [
        const Text(
          'Hospital bed dashboard',
          style: TextStyle(
            fontSize: 25,
            fontWeight: FontWeight.bold,
            color: navy,
          ),
        ),
        const SizedBox(height: 8),
        const Text(
          'Demo inventory • Temporary holds need acceptance. Offline hospitals cannot receive new requests.',
        ),
        const SizedBox(height: 12),
        Wrap(
          spacing: 8,
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
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          h['name'].toString(),
                          style: const TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.w700,
                          ),
                        ),
                      ),
                      badge(h['status'].toString()),
                    ],
                  ),
                  Text('${h['distance']} km • seeded distance'),
                  for (final care in ['General', 'ICU', 'Ventilator']) ...[
                    const SizedBox(height: 16),
                    Text(
                      care == 'General' ? 'General beds' : care,
                      style: const TextStyle(fontWeight: FontWeight.bold),
                    ),
                    const SizedBox(height: 8),
                    Wrap(
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
                                color: statusColor(b['status'].toString())
                                    .withValues(alpha: .1),
                                borderRadius: BorderRadius.circular(8),
                              ),
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Icon(
                                    Icons.bed_outlined,
                                    size: 19,
                                    color: statusColor(b['status'].toString()),
                                  ),
                                  const SizedBox(width: 5),
                                  Text(
                                    b['status'].toString(),
                                    style: TextStyle(
                                      color: statusColor(
                                        b['status'].toString(),
                                      ),
                                      fontSize: 12,
                                    ),
                                  ),
                                ],
                              ),
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
  Widget build(BuildContext context) => ListView(
    padding: const EdgeInsets.all(20),
    children: [
      HeroPanel(
        title: session.profile?['name']?.toString() ?? 'Your account',
        subtitle:
            '${session.profile?['email'] ?? ''}\n${session.role.toUpperCase()}${session.profile?['hospital'] != null ? ' • Hospital ${session.profile!['hospital']}' : ''}',
        icon: Icons.person_outline,
      ),
      const SizedBox(height: 20),
      ListTile(
        leading: const Icon(Icons.dns_outlined),
        title: const Text('Backend server'),
        subtitle: Text(session.api.base),
        onTap: () => serverDialog(context, session),
      ),
      const Card(
        child: Padding(
          padding: EdgeInsets.all(18),
          child: Text(
            'Demo process\n1. Patient submits a request and chooses a suitable hospital.\n2. Hospital reviews severity and accepts the bed request.\n3. Patient sees Reserved status.\n4. Hospital records arrival: the bed becomes Occupied.\n\nTemporary and confirmed holds expire after 5 minutes. Only unconfirmed lower-priority holds can be reassigned to verified Critical requests.',
          ),
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
                  'Admin web modules',
                  style: TextStyle(fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 8),
                const Text(
                  'Hospital, bed/blood type, inventory, users and module permissions use the existing web admin screens. Sign in there with the same admin account.',
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
        const SizedBox(height: 16),
        OutlinedButton.icon(
          onPressed: session.busy || !session.online
              ? null
              : () async {
                  final yes = await showDialog<bool>(
                    context: context,
                    builder: (c) => AlertDialog(
                      title: const Text('Reset emergency demo?'),
                      content: const Text(
                        'This removes all emergency requests and restores seeded beds. It affects both the app and website.',
                      ),
                      actions: [
                        TextButton(
                          onPressed: () => Navigator.pop(c, false),
                          child: const Text('Cancel'),
                        ),
                        FilledButton(
                          onPressed: () => Navigator.pop(c, true),
                          child: const Text('Reset'),
                        ),
                      ],
                    ),
                  );
                  if (yes != true || !context.mounted) return;
                  try {
                    await session.action({'action': 'reset'});
                    if (context.mounted) notice(context, 'Demo reset.');
                  } catch (e) {
                    if (context.mounted) notice(context, e);
                  }
                },
          icon: const Icon(Icons.restart_alt),
          label: const Text('Reset emergency demo'),
        ),
      ],
      const SizedBox(height: 18),
      FilledButton.tonalIcon(
        onPressed: session.signOut,
        icon: const Icon(Icons.logout),
        label: const Text('Sign out'),
      ),
      const SizedBox(height: 20),
      const Text(
        'CareRoute prototype • Hospitals and ambulance drivers are mocked. SOS uses current GPS while the app is open; no real ambulance provider is connected.',
        textAlign: TextAlign.center,
        style: TextStyle(color: Colors.black54, fontSize: 12),
      ),
    ],
  );
}
