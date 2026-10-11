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

class FakeSession extends Session {
  Map<String, dynamic>? submitted;
  FakeSession() : super(api: CareApi('http://localhost/api/mobile')) {
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
    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: RequestForm(session: session, onCreated: (id) => created = id),
        ),
      ),
    );
    await tester.ensureVisible(find.text('Submit emergency request'));
    await tester.pumpAndSettle();
    await tester.tap(find.text('Submit emergency request'));
    await tester.pump();
    expect(session.submitted, isNull);
    expect(find.text('Enter the patient name or ID'), findsOneWidget);
    expect(find.text('Enter the current location'), findsOneWidget);
    await tester.enterText(find.byType(TextFormField).at(0), 'Daniel');
    await tester.enterText(find.byType(TextFormField).at(1), 'Accident Site');
    await tester.ensureVisible(find.text('Submit emergency request'));
    await tester.pumpAndSettle();
    await tester.tap(find.text('Submit emergency request'));
    await tester.pump();
    expect(session.submitted, {
      'action': 'create',
      'patient': 'Daniel',
      'location': 'Accident Site',
      'severity': 'Urgent',
      'care': 'ICU',
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
      find.widgetWithText(FilledButton, 'Submit emergency request'),
    );
    expect(button.onPressed, isNull);
    await tester.pumpWidget(const SizedBox());
    session.dispose();
  });
}
