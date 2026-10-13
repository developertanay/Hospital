import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:careroute/api.dart';
import 'package:careroute/session.dart';
import 'package:careroute/sos.dart';
import 'package:careroute/sos_media.dart';

class SosSession extends Session {
  Map<String, dynamic>? sent;
  SosSession(String role) : super(api: CareApi('http://localhost/api/mobile')) {
    online = true;
    loading = false;
    api.token = 'demo-token';
    state = {
      'profile': {'role': role, 'name': 'Test user', 'driver': 'D1'},
    };
    sosState = {'calls': [], 'drivers': [], 'now': 1000};
  }
  @override
  Future<Map<String, dynamic>> sosAction(Map<String, dynamic> input) async {
    sent = input;
    return {'id': 'test-sos'};
  }
}

final details = {
  'name': 'Test patient',
  'age': 30,
  'mobile': '+919000000001',
  'emergency_name': 'Test contact',
  'emergency_phone': '+919000000002',
};

void main() {
  testWidgets('SOS prompts for details and is disabled when offline', (
    tester,
  ) async {
    final s = SosSession('patient');
    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: SosScreen(session: s, openBed: (_) {}),
        ),
      ),
    );
    expect(find.text('Add emergency details'), findsOneWidget);
    final sos = find.widgetWithText(FilledButton, 'SOS · Request ambulance');
    expect(tester.widget<FilledButton>(sos).onPressed, isNotNull);
    await tester.pumpAndSettle();
    expect(
      find.descendant(
        of: find.byType(AlertDialog),
        matching: find.text('Emergency details'),
      ),
      findsOneWidget,
    );
    await tester.tap(find.text('Cancel'));
    await tester.pumpAndSettle();
    s.sosState!['patient_profile'] = details;
    s.online = false;
    s.notifyListeners();
    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: SosScreen(session: s, openBed: (_) {}),
        ),
      ),
    );
    expect(tester.widget<FilledButton>(sos).onPressed, isNull);
    await tester.pumpWidget(const SizedBox());
    s.dispose();
  });
  testWidgets('SOS captures photo and voice automatically with demo pickup', (
    tester,
  ) async {
    final s = SosSession('patient');
    s.sosState!['patient_profile'] = details;
    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: SosScreen(
            session: s,
            openBed: (_) {},
            captureMedia: () async => const SosReporterMedia(
              photoBase64: '/9j/2Q==',
              voiceBase64: 'AAAAFGZ0eXBNNEEgAAAAAA==',
              voiceDurationMs: 10000,
            ),
          ),
        ),
      ),
    );
    await tester.ensureVisible(find.text('Contact & location settings'));
    await tester.tap(find.text('Contact & location settings'));
    await tester.pumpAndSettle();
    await tester.ensureVisible(find.byType(SwitchListTile));
    await Scrollable.ensureVisible(
      tester.element(find.byType(SwitchListTile)),
      alignment: .5,
    );
    await tester.pumpAndSettle();
    await tester.tap(find.byType(SwitchListTile));
    await tester.pump();
    final sos = find.widgetWithText(FilledButton, 'SOS · Request ambulance');
    await tester.ensureVisible(sos);
    await Scrollable.ensureVisible(tester.element(sos), alignment: .5);
    await tester.pumpAndSettle();
    await tester.tap(sos);
    await tester.pump();
    expect(s.sent?['action'], 'sos');
    expect(s.sent?['mode'], 'demo');
    expect(s.sent?['lat'], 28.6139);
    expect(s.sent?['captured_at'], isA<int>());
    expect(s.sent?['reporter_photo_base64'], '/9j/2Q==');
    expect(s.sent?['reporter_voice_base64'], 'AAAAFGZ0eXBNNEEgAAAAAA==');
    expect(s.sent?['reporter_voice_duration_ms'], 10000);
    expect(find.byType(TextFormField), findsNothing);
    await tester.pumpWidget(const SizedBox());
    s.dispose();
  });
  testWidgets(
    'Driver sees offered pickup but no contact details until acceptance',
    (tester) async {
      final s = SosSession('driver');
      s.sosState!['drivers'] = [
        {
          'id': 'D1',
          'name': 'Demo Driver',
          'vehicle': 'DEMO',
          'on_duty': true,
          'location_mode': 'demo',
        },
      ];
      s.sosState!['calls'] = [
        {
          'id': 'sos1',
          'status': 'Searching',
          'offer_until': 1030,
          'driver_id': null,
          'distance_km': 1.2,
          'patient': {'name': 'Emergency pickup', 'age': 30},
          'location': {
            'lat': 28.61,
            'lng': 77.20,
            'mode': 'demo',
            'accuracy': 5,
          },
          'events': [],
          'bed_request_id': null,
        },
      ];
      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: SosScreen(session: s, openBed: (_) {}),
          ),
        ),
      );
      final accept = find.widgetWithText(FilledButton, 'Accept pickup');
      await tester.ensureVisible(accept);
      await tester.pumpAndSettle();
      expect(find.textContaining('Call patient'), findsNothing);
      await tester.tap(accept);
      await tester.pump();
      expect(s.sent, {'action': 'accept', 'id': 'sos1'});
      await tester.pumpWidget(const SizedBox());
      s.dispose();
    },
  );
  testWidgets('Driver onboarding goes online for a GPS-compatible demo', (
    tester,
  ) async {
    final s = SosSession('driver');
    s.sosState!['drivers'] = [
      {
        'id': 'D1',
        'name': 'Demo',
        'vehicle': 'AMB1',
        'phone': '+919000000001',
        'on_duty': false,
        'location_mode': 'demo',
      },
    ];
    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: SosScreen(session: s, openBed: (_) {}),
        ),
      ),
    );
    await tester.pumpAndSettle();
    expect(find.text('Start receiving SOS pickups'), findsOneWidget);
    await tester.tap(find.text('Online for demo'));
    await tester.pumpAndSettle();
    expect(s.sent?['action'], 'duty');
    expect(s.sent?['on_duty'], true);
    expect(s.sent?['follow_pickup'], true);
    await tester.pumpWidget(const SizedBox());
    s.dispose();
  });
  testWidgets('Patient sees driver on the way, ETA, tracking and call action', (
    tester,
  ) async {
    final s = SosSession('patient');
    s.sosState!['patient_profile'] = details;
    s.sosState!['calls'] = [
      {
        'id': 'accepted1',
        'status': 'Accepted',
        'driver_id': 'D2',
        'offer_until': null,
        'patient': details,
        'location': {
          'lat': 19.07,
          'lng': 72.87,
          'mode': 'gps',
          'captured_at': 1000,
          'accuracy': 5,
        },
        'driver': {
          'name': 'Riya',
          'vehicle': 'AMB02',
          'phone': '+919000000003',
          'lat': 19.08,
          'lng': 72.88,
          'location_mode': 'demo',
          'seen_at': 1000,
        },
        'distance_km': 1.2,
        'eta_minutes': 4,
        'tracking_simulated': true,
        'events': [],
        'bed_request_id': null,
      },
    ];
    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: SosScreen(session: s, openBed: (_) {}),
        ),
      ),
    );
    expect(find.text('Driver is on the way'), findsOneWidget);
    await tester.ensureVisible(find.textContaining('1.2 km away'));
    expect(find.textContaining('estimated 4 min'), findsOneWidget);
    await tester.ensureVisible(find.textContaining('Call assigned driver'));
    expect(find.textContaining('+919000000003'), findsOneWidget);
    await tester.pumpWidget(const SizedBox());
    s.dispose();
  });
}
