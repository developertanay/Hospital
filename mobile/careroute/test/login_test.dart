import 'dart:async';

import 'package:careroute/api.dart';
import 'package:careroute/app.dart';
import 'package:careroute/session.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

class ConnectionSession extends Session {
  final Completer<void>? probe;
  String? checkedAddress;

  ConnectionSession({this.probe})
    : super(api: CareApi('http://localhost/Hospital/public/api/mobile'));

  @override
  Future<void> server(String value) async {
    checkedAddress = value;
    if (probe != null) await probe!.future;
    api.base = value;
  }
}

Future<void> openConnection(
  WidgetTester tester,
  ConnectionSession session,
) async {
  await tester.pumpWidget(MaterialApp(home: AuthScreen(session: session)));
  await tester.ensureVisible(find.text('Server connection'));
  await tester.tap(find.text('Server connection'));
  await tester.pumpAndSettle();
}

void main() {
  testWidgets('Current test server replaces an old saved server address', (
    tester,
  ) async {
    final session = ConnectionSession();
    final bundled = session.api.base;
    const oldServer = 'http://192.168.1.4/Hospital/public/api/mobile';
    session.api.base = oldServer;
    await openConnection(tester, session);
    expect(find.text('Server: $oldServer'), findsOneWidget);
    final address = find.widgetWithText(TextField, 'Mobile API address');
    expect(tester.widget<TextField>(address).controller!.text, oldServer);
    await tester.tap(find.text('Current test server'));
    await tester.pump();
    expect(tester.widget<TextField>(address).controller!.text, bundled);
    await tester.tap(find.text('Test & save'));
    await tester.pumpAndSettle();
    expect(session.checkedAddress, bundled);
    expect(session.api.base, bundled);
    expect(find.text('Connect to your server'), findsNothing);
    expect(tester.takeException(), isNull);
    await tester.pumpWidget(const SizedBox());
    session.dispose();
  });

  testWidgets(
    'Closing server connection keeps its input alive through animation',
    (tester) async {
      final session = ConnectionSession();
      await openConnection(tester, session);
      await tester.enterText(
        find.widgetWithText(TextField, 'Mobile API address'),
        'http://192.168.1.5/Hospital/public/api/mobile',
      );
      await tester.tap(find.text('Cancel'));
      await tester.pump();
      expect(tester.takeException(), isNull);
      await tester.pumpAndSettle();
      expect(tester.takeException(), isNull);
      expect(find.text('Connect to your server'), findsNothing);
      expect(find.text('Sign in'), findsOneWidget);
      await tester.pumpWidget(const SizedBox());
      session.dispose();
    },
  );

  testWidgets('Server probe cannot be dismissed while it is still running', (
    tester,
  ) async {
    final probe = Completer<void>();
    final session = ConnectionSession(probe: probe);
    await openConnection(tester, session);
    await tester.tap(find.text('Test & save'));
    await tester.pump();
    expect(find.text('Checking…'), findsOneWidget);
    expect(session.checkedAddress, session.api.base);
    await tester.tapAt(const Offset(2, 2));
    await tester.pump();
    await tester.binding.handlePopRoute();
    await tester.pump();
    expect(find.text('Connect to your server'), findsOneWidget);
    expect(find.text('Checking…'), findsOneWidget);
    expect(
      tester
          .widget<TextButton>(find.widgetWithText(TextButton, 'Cancel'))
          .onPressed,
      isNull,
    );
    probe.complete();
    await tester.pumpAndSettle();
    expect(tester.takeException(), isNull);
    expect(find.text('Connect to your server'), findsNothing);
    await tester.pumpWidget(const SizedBox());
    session.dispose();
  });

  testWidgets(
    'Server probe failure keeps the dialog available for correction',
    (tester) async {
      final probe = Completer<void>();
      final session = ConnectionSession(probe: probe);
      await openConnection(tester, session);
      await tester.tap(find.text('Test & save'));
      await tester.pump();
      probe.completeError(ApiFailure('Cannot reach this server.'));
      await tester.pumpAndSettle();
      expect(find.text('Cannot reach this server.'), findsOneWidget);
      expect(find.text('Test & save'), findsOneWidget);
      await tester.tap(find.text('Cancel'));
      await tester.pumpAndSettle();
      expect(tester.takeException(), isNull);
      await tester.pumpWidget(const SizedBox());
      session.dispose();
    },
  );
}
