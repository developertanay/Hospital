import 'dart:convert';
import 'dart:async';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:careroute/api.dart';
import 'package:careroute/app.dart';
import 'package:careroute/session.dart';
import 'package:careroute/care_choices.dart';

class FakeSession extends Session {
  Map<String, dynamic>? submitted;
  FakeSession({CareApi? api})
    : super(api: api ?? CareApi('http://localhost/api/mobile')) {
    online = true;
    loading = false;
  }
  @override
  Future<Map<String, dynamic>> action(Map<String, dynamic> input) async {
    submitted = input;
    return {'id': 'test-request'};
  }
}

void main() {
  testWidgets(
    'Internal request exposes choose ambulance even when patient GPS is missing',
    (tester) async {
      final api = CareApi(
        'http://localhost/api/mobile',
        client: MockClient(
          (request) async => http.Response(
            jsonEncode({
              'ambulances': [],
              'availability_message':
                  'On-duty drivers have not shared fresh live GPS.',
            }),
            200,
          ),
        ),
      )..token = 'test-token';
      final session = FakeSession(api: api);
      session.state = {
        'profile': {'role': 'patient'},
        'capabilities': ['create', 'request'],
        'requests': [
          {
            'id': 'internal-no-gps',
            'patient': 'Patient',
            'problem_type': 'Internal',
            'assistance_preference': 'Ambulance',
            'status': 'New',
            'location': 'Home',
            'care': 'General',
            'severity': 'Urgent',
            'priority': 1,
            'verified': false,
            'matches': [],
          },
        ],
        'events': [],
      };
      await tester.pumpWidget(
        MaterialApp(
          home: RequestDetail(session: session, id: 'internal-no-gps'),
        ),
      );
      await tester.pumpAndSettle();
      expect(find.text('Choose ambulance'), findsOneWidget);
      await tester.tap(find.text('Choose ambulance'));
      await tester.pumpAndSettle();
      expect(
        find.text('Use current location to find ambulances'),
        findsOneWidget,
      );
      expect(
        find.text('On-duty drivers have not shared fresh live GPS.'),
        findsOneWidget,
      );
      await tester.pumpWidget(const SizedBox());
      session.dispose();
      api.client.close();
    },
  );
  testWidgets(
    'Ambulance choices load when the previous action stops being busy',
    (tester) async {
      var requests = 0;
      final api = CareApi(
        'http://localhost/api/mobile',
        client: MockClient((request) async {
          requests++;
          return http.Response(jsonEncode({'ambulances': []}), 200);
        }),
      );
      final session = FakeSession(api: api)..busy = true;
      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: AmbulanceChoices(
              session: session,
              request: {'id': 'care', 'problem_type': 'Internal'},
            ),
          ),
        ),
      );
      await tester.pumpAndSettle();
      expect(requests, 0);
      session.busy = false;
      session.notifyListeners();
      await tester.pumpAndSettle();
      expect(requests, 1);
      await tester.pumpWidget(const SizedBox());
      session.dispose();
      api.client.close();
    },
  );
  testWidgets(
    'Idle ambulance choices show vehicle, distance and ETA before booking',
    (tester) async {
      final api = CareApi(
        'http://localhost/api/mobile',
        client: MockClient(
          (request) async => http.Response(
            jsonEncode({
              'ambulances': [
                {
                  'driver_id': 'driver-1',
                  'vehicle': 'TESTAMB001',
                  'category': 'Basic Life Support',
                  'doctor_name': 'Test Doctor',
                  'distance_km': 1.2,
                  'eta_minutes': 4,
                },
              ],
            }),
            200,
          ),
        ),
      );
      final session = FakeSession(api: api);
      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: SingleChildScrollView(
              child: AmbulanceChoices(
                session: session,
                request: {'id': 'care-1', 'problem_type': 'Internal'},
              ),
            ),
          ),
        ),
      );
      await tester.pumpAndSettle();
      expect(find.text('Ambulance · recommended first'), findsOneWidget);
      expect(find.text('TESTAMB001'), findsOneWidget);
      expect(find.text('1.2 km · estimated arrival 4 min'), findsOneWidget);
      expect(find.text('Doctor: Test Doctor'), findsOneWidget);
      expect(find.text('Book this ambulance'), findsOneWidget);
      await tester.pumpWidget(const SizedBox());
      session.dispose();
      api.client.close();
    },
  );
  testWidgets(
    'External problems suggest hospital and allow changing to ambulance',
    (tester) async {
      final session = FakeSession();
      session.state = {
        'profile': {'role': 'patient'},
        'care_types': ['ICU'],
        'capabilities': ['create', 'request'],
        'hospitals': [
          {'id': '1', 'name': 'Test Hospital'},
        ],
      };
      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: RequestForm(session: session, onCreated: (_) {}),
          ),
        ),
      );
      final problem = find.byType(DropdownButtonFormField<String>).at(0);
      await tester.ensureVisible(problem);
      await tester.tap(problem);
      await tester.pumpAndSettle();
      await tester.tap(find.text('External — accident, injury').last);
      await tester.pumpAndSettle();
      expect(find.text('Preferred hospital'), findsOneWidget);
      final assistance = find.byType(DropdownButtonFormField<String>).at(1);
      await tester.ensureVisible(assistance);
      await tester.tap(assistance);
      await tester.pumpAndSettle();
      await tester.tap(find.text('Ambulance').last);
      await tester.pumpAndSettle();
      expect(find.text('Preferred hospital'), findsNothing);
      expect(
        find.text(
          'Next: compare nearby idle ambulances and hospitals that provide your required service. Both options remain available.',
        ),
        findsOneWidget,
      );
      await tester.pumpWidget(const SizedBox());
      session.dispose();
    },
  );
  testWidgets(
    'Confirmed requests show the assigned bed instead of rejecting their own held bed',
    (tester) async {
      final session = FakeSession();
      session.api.token = 'test-token';
      session.state = {
        'profile': {'role': 'patient'},
        'now': 1000,
        'events': [],
        'requests': [
          {
            'id': 'confirmed',
            'patient': 'Demo patient',
            'location': 'Accident Site',
            'care': 'ICU',
            'severity': 'Critical',
            'verified': true,
            'priority': 3,
            'status': 'Reserved',
            'hospital_id': 'C',
            'bed_id': 'C-ICU-1',
            'expires_at': 1300,
            'matches': [],
          },
        ],
      };
      await tester.pumpWidget(
        MaterialApp(
          home: RequestDetail(session: session, id: 'confirmed'),
        ),
      );
      expect(find.text('Reserved'), findsOneWidget);
      expect(find.text('Hospital C • Bed C-ICU-1'), findsOneWidget);
      expect(find.text('Hospital recommendations'), findsNothing);
      await tester.pumpWidget(const SizedBox());
      session.dispose();
    },
  );
  test(
    'A delayed refresh cannot restore another session after logout',
    () async {
      FlutterSecureStorage.setMockInitialValues({});
      final response = Completer<http.Response>();
      final api = CareApi(
        'http://localhost/api/mobile',
        client: MockClient((_) => response.future),
      )..token = 'old-token';
      final session = Session(api: api);
      final pending = session.refresh();
      await session.clear();
      response.complete(
        http.Response(
          '{"profile":{"role":"admin"},"requests":[{"patient":"Old session"}]}',
          200,
        ),
      );
      await pending;
      expect(session.state, isNull);
      expect(session.online, false);
      expect(session.signedIn, false);
      session.dispose();
    },
  );
  test(
    'API sends bearer authentication and preserves request fields',
    () async {
      final api = CareApi(
        'http://localhost/api/mobile',
        client: MockClient((r) async {
          expect(r.headers['Authorization'], 'Bearer private-token');
          expect(jsonDecode(r.body), {
            'action': 'create',
            'patient': 'Daniel',
            'location': 'Accident Site',
            'care': 'Ventilator',
            'severity': 'Critical',
          });
          return http.Response('{"id":"abc"}', 200);
        }),
      )..token = 'private-token';
      expect(
        (await api.call('action', {
          'action': 'create',
          'patient': 'Daniel',
          'location': 'Accident Site',
          'care': 'Ventilator',
          'severity': 'Critical',
        }))['id'],
        'abc',
      );
      api.client.close();
    },
  );
  test('API exposes validation errors and expired authentication', () async {
    final api = CareApi(
      'http://localhost/api/mobile',
      client: MockClient(
        (_) async => http.Response(
          '{"message":"Invalid","errors":{"email":["Email is required"]}}',
          422,
        ),
      ),
    );
    await expectLater(
      api.call('login', {}),
      throwsA(
        isA<ApiFailure>().having(
          (e) => e.message,
          'message',
          'Email is required',
        ),
      ),
    );
    api.client.close();
    final expired = CareApi(
      'http://localhost/api/mobile',
      client: MockClient(
        (_) async => http.Response('{"message":"Unauthenticated"}', 401),
      ),
    );
    await expectLater(
      expired.call('state'),
      throwsA(isA<ApiFailure>().having((e) => e.status, 'status', 401)),
    );
    expired.client.close();
  });
  test('Connection errors become actionable messages', () async {
    final api = CareApi(
      'http://localhost/api/mobile',
      client: MockClient((_) async => throw http.ClientException('refused')),
    );
    await expectLater(
      api.call('health'),
      throwsA(
        isA<ApiFailure>().having(
          (e) => e.message,
          'message',
          contains('XAMPP'),
        ),
      ),
    );
    api.client.close();
    expect(
      () => CareApi.normalize('file:///tmp/data'),
      throwsA(isA<ApiFailure>()),
    );
    expect(
      () => CareApi.normalize('http://user:password@host/api'),
      throwsA(isA<ApiFailure>()),
    );
  });
  testWidgets('Patient form validates and submits the four required fields', (
    tester,
  ) async {
    final session = FakeSession();
    String? created;
    session.state = {
      'profile': {'role': 'patient'},
      'care_types': ['General', 'ICU', 'Ventilator'],
      'capabilities': ['create', 'request'],
    };
    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: RequestForm(
            session: session,
            onCreated: (id) => created = id,
            locationProvider: () async => {
              'lat': 28.6,
              'lng': 77.2,
              'accuracy': 5,
              'captured_at': 1000,
              'mode': 'gps',
            },
          ),
        ),
      ),
    );
    await tester.ensureVisible(find.text('Continue to ambulance selection'));
    await tester.pumpAndSettle();
    await tester.tap(find.text('Continue to ambulance selection'));
    await tester.pump();
    expect(session.submitted, isNull);
    expect(find.text('Enter the patient name or ID'), findsOneWidget);
    expect(find.text('Enter the current location'), findsOneWidget);
    await tester.enterText(find.byType(TextFormField).at(0), 'Daniel');
    await tester.enterText(find.byType(TextFormField).at(1), 'Accident Site');
    await tester.enterText(
      find.byType(TextFormField).at(2),
      'Pain started this morning',
    );
    tester.testTextInput.hide();
    await tester.pumpAndSettle();
    await tester.ensureVisible(find.text('Continue to ambulance selection'));
    await tester.pumpAndSettle();
    await tester.tap(find.text('Continue to ambulance selection'));
    await tester.pump();
    expect(session.submitted, {
      'action': 'create',
      'patient': 'Daniel',
      'location': 'Accident Site',
      'severity': 'Urgent',
      'care': 'ICU',
      'problem_type': 'Internal',
      'assistance_preference': 'Ambulance',
      'description': 'Pain started this morning',
      'preferred_hospital_id': null,
      'department_id': null,
      'latitude': 28.6,
      'longitude': 77.2,
      'location_captured_at': 1000,
    });
    expect(created, 'test-request');
    await tester.pumpWidget(const SizedBox());
    session.dispose();
  });
  testWidgets('Offline requests cannot be submitted', (tester) async {
    final session = FakeSession()..online = false;
    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: RequestForm(session: session, onCreated: (_) {}),
        ),
      ),
    );
    final button = tester.widget<FilledButton>(
      find.widgetWithText(FilledButton, 'Continue to ambulance selection'),
    );
    expect(button.onPressed, isNull);
    await tester.pumpWidget(const SizedBox());
    session.dispose();
  });
}
