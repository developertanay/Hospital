import 'package:careroute/recent_activity.dart';
import 'package:careroute/api.dart';
import 'package:careroute/app.dart';
import 'package:careroute/session.dart';
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

void main() {
  testWidgets(
    'Request activity shows latest two without changing stored events',
    (tester) async {
      final events = [2, 5, 1, 4, 3]
          .map((i) => {'request_id': 'history', 'at': i, 'message': 'Event $i'})
          .toList();
      final before = events.map((e) => Map<String, dynamic>.from(e)).toList();
      final session = Session(
        api: CareApi('http://localhost/api/mobile')..token = 'test-token',
      )..online = true;
      session.state = {
        'profile': {'role': 'patient'},
        'events': events,
        'requests': [
          {
            'id': 'history',
            'patient': 'History patient',
            'status': 'Cancelled',
            'location': 'Test location',
            'care': 'ICU',
            'severity': 'Urgent',
            'verified': false,
            'priority': 1,
            'matches': [],
          },
        ],
      };
      await tester.pumpWidget(
        MaterialApp(
          theme: careRouteTheme(),
          home: RequestDetail(session: session, id: 'history'),
        ),
      );
      await tester.scrollUntilVisible(find.text('Recent activity'), 200);
      await tester.pumpAndSettle();
      expect(find.text('Event 5'), findsOneWidget);
      expect(find.text('Event 4'), findsOneWidget);
      expect(find.text('Event 3'), findsNothing);
      await tester.tap(find.text('View all'));
      await tester.pumpAndSettle();
      await tester.scrollUntilVisible(find.text('Event 1'), 150);
      expect(find.text('Event 1'), findsOneWidget);
      expect(session.state!['events'], before);
      await tester.pumpWidget(const SizedBox());
      session.dispose();
    },
  );

  testWidgets('History starts with two records and can reveal every record', (
    tester,
  ) async {
    final records = List.generate(5, (i) => Text('Activity ${5 - i}'));
    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(body: RecentActivityList(children: records)),
      ),
    );
    expect(find.text('Activity 5'), findsOneWidget);
    expect(find.text('Activity 4'), findsOneWidget);
    expect(find.text('Activity 3'), findsNothing);
    await tester.tap(find.text('View all'));
    await tester.pump();
    for (var i = 1; i <= 5; i++) {
      expect(find.text('Activity $i'), findsOneWidget);
    }
    expect(records.length, 5);
    await tester.tap(find.text('View less'));
    await tester.pump();
    expect(find.text('Activity 3'), findsNothing);
    expect(records.length, 5);
  });

  testWidgets('Short history has no extra button and fits a small screen', (
    tester,
  ) async {
    tester.view.devicePixelRatio = 1;
    tester.view.physicalSize = const Size(320, 568);
    addTearDown(tester.view.resetPhysicalSize);
    addTearDown(tester.view.resetDevicePixelRatio);
    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: RecentActivityList(
            children: const [
              Text('Latest activity'),
              Text('Previous activity'),
            ],
          ),
        ),
      ),
    );
    expect(find.text('View all'), findsNothing);
    expect(find.text('Latest activity'), findsOneWidget);
    expect(find.text('Previous activity'), findsOneWidget);
    expect(tester.takeException(), isNull);
  });
}
