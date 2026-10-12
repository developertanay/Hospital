import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';
import 'package:careroute/api.dart';
import 'package:careroute/session.dart';
import 'package:careroute/recipient.dart';
import 'package:careroute/portfolio.dart';
import 'package:careroute/sos.dart';

void main() {
  testWidgets(
    'Remote unknown-patient tracking does not demand a self profile or claim GPS precision',
    (tester) async {
      final s = Session(api: CareApi('http://localhost/api/mobile'));
      s.online = true;
      s.state = {
        'profile': {'role': 'patient', 'name': 'Reporter'},
      };
      s.sosState = {
        'now': 1000,
        'drivers': [],
        'profile_complete': false,
        'calls': [
          {
            'id': 'remote',
            'status': 'Searching',
            'patient': {'name': 'Unknown patient', 'age': null},
            'reporter': {
              'name': 'Reporter',
              'phone': '9000000001',
              'with_patient': false,
            },
            'location': {
              'lat': 28.6,
              'lng': 77.2,
              'mode': 'manual',
              'accuracy': 0,
              'captured_at': 1000,
            },
            'driver_id': null,
            'bed_request_id': null,
            'nearby_driver_count': 0,
            'events': [],
          },
        ],
      };
      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: SosScreen(session: s, openBed: (_) {}),
          ),
        ),
      );
      await tester.pump(const Duration(milliseconds:100));
      expect(find.text('Emergency details'), findsNothing);
      expect(
        find.textContaining('Patient-provided pickup pin.'),
        findsOneWidget,
      );
      expect(find.textContaining('Location accuracy: 0'), findsNothing);
      await tester.pumpWidget(const SizedBox());
      s.dispose();
    },
  );
  testWidgets(
    'Unknown patient SOS can use a remote patient pin without reporter GPS',
    (tester) async {
      final s = Session(api: CareApi('http://localhost/api/mobile'));
      s.online = true;
      s.state = {
        'profile': {'role': 'patient', 'name': 'Reporter'},
      };
      s.sosState = {'dependents': [], 'coverages': []};
      Map<String, dynamic>? result;
      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: Builder(
              builder: (ctx) => FilledButton(
                onPressed: () async {
                  result = await chooseSosRecipient(ctx, s);
                },
                child: const Text('Start SOS'),
              ),
            ),
          ),
        ),
      );
      await tester.tap(find.text('Start SOS'));
      await tester.pumpAndSettle();
      await tester.tap(find.text('For another person / unknown patient'));
      await tester.pumpAndSettle();
      Future<void> fill(String label, String value) async {
        final finder = find.widgetWithText(TextFormField, label);
        await tester.ensureVisible(finder);
        await tester.pumpAndSettle();
        await tester.enterText(finder, value);
      }

      await fill('Your contact number', '9000000001');
      await fill('What happened / symptoms', 'Accident victim needs help');
      final withPatient = find.widgetWithText(
        SwitchListTile,
        'I am with the patient',
      );
      await tester.ensureVisible(withPatient);
      await tester.pumpAndSettle();
      await tester.tap(withPatient);
      await tester.pumpAndSettle();
      await fill('Patient pickup address / landmark', 'Patient landmark');
      await fill('Patient latitude', '28.61');
      await fill('Patient longitude', '77.20');
      tester.testTextInput.hide();
      await tester.pumpAndSettle();
      await tester.tap(find.text('Confirm SOS'));
      await tester.pumpAndSettle();
      expect(result?['reported_for'], 'Other');
      expect(result?['patient_name'], isNull);
      expect(result?['patient_age'], isNull);
      expect(result?['reporter_name'], 'Reporter');
      expect(result?['reporter_with_patient'], false);
      expect(result?['mode'], 'manual');
      expect(result?['lat'], 28.61);
      expect(result?['coverage_id'], isNull);
      await tester.pumpWidget(const SizedBox());
      s.dispose();
    },
  );
  testWidgets(
    'Family and insurance screen displays saved dependent and policy',
    (tester) async {
      final api = CareApi(
        'http://localhost/api/mobile',
        client: MockClient(
          (request) async => http.Response(
            jsonEncode({
              'dependents': [
                {
                  'id': 1,
                  'name': 'Father Patient',
                  'relationship': 'Father',
                  'age': 65,
                },
              ],
              'coverages': [
                {
                  'id': 2,
                  'organisation': {'name': 'Demo Insurance'},
                  'policy_number': 'POLICY-1',
                  'member_id': 'MEMBER-1',
                  'covers_family': true,
                  'has_card': false,
                },
              ],
              'organisations': [
                {'id': 1, 'name': 'Demo Insurance', 'kind': 'Insurance'},
              ],
            }),
            200,
          ),
        ),
      );
      final s = Session(api: api)..online = true;
      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(body: PatientPortfolio(session: s)),
        ),
      );
      await tester.pumpAndSettle();
      expect(find.text('Father Patient'), findsOneWidget);
      await tester.scrollUntilVisible(find.text('Demo Insurance'), 200);
      expect(find.text('Demo Insurance'), findsOneWidget);
      expect(find.textContaining('POLICY-1'), findsOneWidget);
      await tester.pumpWidget(const SizedBox());
      s.dispose();
      api.client.close();
    },
  );
}
