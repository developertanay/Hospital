import 'dart:convert';
import 'dart:io';
import 'dart:ui' as ui;

import 'package:careroute/api.dart';
import 'package:careroute/app.dart';
import 'package:careroute/portfolio.dart';
import 'package:careroute/session.dart';
import 'package:flutter/material.dart';
import 'package:flutter/rendering.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

Session layoutSession() {
  final api = CareApi(
    'http://localhost/api/mobile',
    client: MockClient((_) async {
      return http.Response(
        jsonEncode({
          'ambulances': [],
          'dependents': [
            {
              'id': 1,
              'name': 'Family member with a longer name',
              'relationship': 'Father',
              'age': 65,
            },
          ],
          'coverages': [
            {
              'id': 2,
              'organisation': {'name': 'Health insurance with a longer name'},
              'policy_number': 'POLICY-1',
              'member_id': 'MEMBER-1',
              'covers_family': true,
              'has_card': false,
            },
          ],
          'organisations': [],
        }),
        200,
      );
    }),
  )..token = 'layout-only-token';
  final s = Session(api: api)..loading = false;
  s.online = true;
  s.state = {
    'profile': {
      'id': 1,
      'name': 'Patient with a longer name',
      'role': 'patient',
      'email': 'patient@example.test',
    },
    'capabilities': ['create', 'request'],
    'care_types': ['ICU', 'General'],
    'departments': [
      {'id': 1, 'department_name': 'Emergency and trauma care department'},
    ],
    'requests': [
      {
        'id': 'layout-request',
        'patient': 'Patient with a longer name',
        'problem_type': 'Internal',
        'assistance_preference': 'Ambulance',
        'status': 'New',
        'location': 'Patient location with a longer address',
        'care': 'ICU',
        'severity': 'Urgent',
        'priority': 1,
        'verified': false,
        'latitude': 28.6,
        'longitude': 77.2,
        'matches': [],
      },
    ],
    'hospitals': [
      {
        'id': 1,
        'name': 'Hospital with a longer name and department',
        'status': 'Open',
        'distance': 2.4,
        'beds': [
          {'id': 'ICU-1', 'care': 'ICU', 'status': 'Available'},
          {'id': 'ICU-2', 'care': 'ICU', 'status': 'Occupied'},
        ],
      },
    ],
    'events': [],
  };
  s.sosState = {
    'now': 1000,
    'calls': [],
    'drivers': [],
    'profile_complete': true,
    'patient_profile': {
      'name': 'Patient',
      'mobile': '9000000001',
      'age': 30,
      'emergency_name': 'Family contact',
      'emergency_phone': '9000000002',
    },
    'dependents': [],
    'coverages': [],
  };
  return s;
}

ThemeData layoutTheme() {
  final theme = careRouteTheme();
  if (Platform.environment['UI_PREVIEW'] != '1') return theme;
  return theme.copyWith(
    textTheme: theme.textTheme.apply(fontFamily: 'Roboto'),
    primaryTextTheme: theme.primaryTextTheme.apply(fontFamily: 'Roboto'),
  );
}

void main() {
  setUpAll(() async {
    if (Platform.environment['UI_PREVIEW'] != '1') return;
    const fonts = '../../.tools/flutter/bin/cache/artifacts/material_fonts';
    for (final entry in {
      'Roboto': ['roboto-regular.ttf', 'roboto-medium.ttf', 'roboto-bold.ttf'],
      'MaterialIcons': ['materialicons-regular.otf'],
    }.entries) {
      final loader = FontLoader(entry.key);
      for (final name in entry.value) {
        loader.addFont(
          File('$fonts/$name').readAsBytes().then(ByteData.sublistView),
        );
      }
      await loader.load();
    }
  });
  final cases = <String, (Size, double)>{
    'small phone': (const Size(320, 568), 1),
    'phone': (const Size(390, 844), 1),
    'landscape phone': (const Size(844, 390), 1),
    'tablet': (const Size(768, 1024), 1),
    'desktop': (const Size(1440, 900), 1),
    'small phone with larger text': (const Size(320, 568), 1.5),
    'tablet with larger text': (const Size(768, 1024), 2),
  };

  for (final entry in cases.entries) {
    testWidgets('Main screens fit ${entry.key}', (tester) async {
      tester.view.devicePixelRatio = 1;
      tester.view.physicalSize = entry.value.$1;
      addTearDown(tester.view.resetPhysicalSize);
      addTearDown(tester.view.resetDevicePixelRatio);
      final session = layoutSession();
      addTearDown(session.dispose);
      final screens = <String, Widget Function()>{
        'login': () => AuthScreen(session: session),
        'home navigation': () => Home(session: session),
        'request form': () => Scaffold(
          body: RequestForm(session: session, onCreated: (_) {}),
        ),
        'requests': () => Scaffold(
          body: Queue(session: session, open: (_) {}),
        ),
        'hospital beds': () => Scaffold(body: Hospitals(session: session)),
        'request details': () =>
            RequestDetail(session: session, id: 'layout-request'),
        'family and insurance': () =>
            Scaffold(body: PatientPortfolio(session: session)),
        'account': () => Scaffold(body: Account(session: session)),
      };
      for (final screen in screens.entries) {
        final previewKey = GlobalKey();
        await tester.pumpWidget(
          RepaintBoundary(
            key: previewKey,
            child: MaterialApp(
              debugShowCheckedModeBanner: false,
              theme: layoutTheme(),
              builder: (context, child) => MediaQuery(
                data: MediaQuery.of(context)
                    .copyWith(textScaler: TextScaler.linear(entry.value.$2)),
                child: child!,
              ),
              home: screen.value(),
            ),
          ),
        );
        await tester.pump(const Duration(milliseconds: 200));
        await tester.pump(const Duration(milliseconds: 200));
        expect(
          tester.takeException(),
          isNull,
          reason: '${screen.key} on ${entry.key}',
        );
        if (screen.key == 'home navigation') {
          final rail =
              entry.value.$1.width >= 720 && entry.value.$1.height >= 600;
          expect(
            find.byType(NavigationRail),
            rail ? findsOneWidget : findsNothing,
          );
          expect(
            find.byType(NavigationBar),
            rail ? findsNothing : findsOneWidget,
          );
        }
        final sectionTitle = switch (screen.key) {
          'request details' => 'Request information',
          'hospital beds' => 'ICU',
          'family and insurance' => 'Policy details',
          _ => null,
        };
        if (sectionTitle != null) {
          final section = find.text(sectionTitle);
          await tester.scrollUntilVisible(section, 160);
          await Scrollable.ensureVisible(
            tester.element(section.first),
            alignment: .5,
          );
          await tester.pumpAndSettle();
          await tester.tap(section.first);
          await tester.pump(const Duration(milliseconds: 400));
          expect(
            tester.takeException(),
            isNull,
            reason: '$sectionTitle expanded on ${entry.key}',
          );
        }
        if (Platform.environment['UI_PREVIEW'] == '1' &&
            ((entry.key == 'phone' && screen.key == 'login') ||
                (entry.key == 'tablet' && screen.key == 'home navigation'))) {
          await tester.runAsync(() async {
            final boundary =
                previewKey.currentContext!.findRenderObject()
                    as RenderRepaintBoundary;
            final image = await boundary.toImage(pixelRatio: 1);
            final bytes = await image.toByteData(
              format: ui.ImageByteFormat.png,
            );
            final folder = Directory('../../docs/images');
            await folder.create(recursive: true);
            final name = screen.key == 'login'
                ? 'app-login-phone.png'
                : 'app-home-tablet.png';
            await File('${folder.path}/$name')
                .writeAsBytes(bytes!.buffer.asUint8List());
            image.dispose();
          });
        }
        await tester.pumpWidget(const SizedBox());
        await tester.pump();
      }
      for (final role in ['hospital', 'driver', 'admin']) {
        session.state!['profile']['role'] = role;
        await tester.pumpWidget(
          MaterialApp(
            debugShowCheckedModeBanner: false,
            theme: layoutTheme(),
            builder: (context, child) => MediaQuery(
              data: MediaQuery.of(context)
                  .copyWith(textScaler: TextScaler.linear(entry.value.$2)),
              child: child!,
            ),
            home: Home(session: session),
          ),
        );
        await tester.pump(const Duration(milliseconds: 200));
        expect(tester.takeException(), isNull, reason: '$role on ${entry.key}');
        await tester.pumpWidget(const SizedBox());
        await tester.pump();
      }
    });
  }

  testWidgets('Server dialog stays usable on a small phone with larger text', (
    tester,
  ) async {
    tester.view.devicePixelRatio = 1;
    tester.view.physicalSize = const Size(320, 568);
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);
    final session = layoutSession();
    addTearDown(session.dispose);
    await tester.pumpWidget(
      MaterialApp(
        debugShowCheckedModeBanner: false,
        theme: layoutTheme(),
        builder: (context, child) => MediaQuery(
          data: MediaQuery.of(context)
              .copyWith(textScaler: TextScaler.linear(1.5)),
          child: child!,
        ),
        home: AuthScreen(session: session),
      ),
    );
    await tester.ensureVisible(find.text('Server connection'));
    await tester.tap(find.text('Server connection'));
    await tester.pumpAndSettle();
    expect(tester.takeException(), isNull);
    await tester.ensureVisible(find.text('Current test server'));
    expect(find.text('Test & save'), findsOneWidget);
    await tester.tap(find.text('Cancel'));
    await tester.pumpAndSettle();
    expect(tester.takeException(), isNull);
    await tester.pumpWidget(const SizedBox());
  });
}
