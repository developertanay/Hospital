import 'dart:async';
import 'dart:convert';

import 'package:careroute/api.dart';
import 'package:careroute/session.dart';
import 'package:careroute/sos.dart';
import 'package:careroute/sos_photo.dart';
import 'package:careroute/sos_media.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:http/http.dart' as http;
import 'package:http/testing.dart';

const _media = SosReporterMedia(
  photoBase64: '/9j/2Q==',
  voiceBase64: 'AAAAFGZ0eXBNNEEgAAAAAA==',
  voiceDurationMs: 10000,
);

class _MediaSession extends Session {
  final sent = <Map<String, dynamic>>[];
  _MediaSession() : super(api: CareApi('http://localhost/api/mobile')) {
    api.token = 'patient-token';
    online = true;
    loading = false;
    state = {
      'profile': {'role': 'patient', 'name': 'SOS Reporter'},
    };
    sosState = {
      'calls': [],
      'drivers': [],
      'now': 1000,
      'patient_profile': {
        'name': 'SOS Reporter',
        'age': 30,
        'mobile': '9000000001',
        'emergency_name': 'Emergency contact',
        'emergency_phone': '9000000002',
      },
      'dependents': [
        {'id': 7, 'name': 'Father Patient'},
      ],
    };
  }

  @override
  Future<Map<String, dynamic>> sosAction(Map<String, dynamic> input) async {
    sent.add(input);
    return {'id': 'media-sos'};
  }
}

Map<String, dynamic> _fix([double lat = 28.61]) => {
  'lat': lat,
  'lng': 77.20,
  'accuracy': 5,
  'captured_at': DateTime.now().millisecondsSinceEpoch ~/ 1000,
  'mode': 'gps',
};

Future<void> _screen(
  WidgetTester tester,
  _MediaSession session, {
  required SosMediaCapture capture,
  Future<Map<String, dynamic>> Function()? gps,
}) => tester.pumpWidget(
  MaterialApp(
    home: Scaffold(
      body: SosScreen(
        session: session,
        openBed: (_) {},
        captureMedia: capture,
        captureLocation: gps ?? () async => _fix(),
      ),
    ),
  ),
);

Future<void> _fill(WidgetTester tester, String label, String value) async {
  final field = find.widgetWithText(TextFormField, label);
  await tester.ensureVisible(field);
  await tester.pumpAndSettle();
  await tester.enterText(field, value);
}

Future<void> _recipient(
  WidgetTester tester, {
  required bool family,
  bool remote = false,
}) async {
  final button = find.text('SOS for family / another person');
  await tester.ensureVisible(button);
  await tester.tap(button);
  await tester.pumpAndSettle();
  await tester.tap(
    find.text(family ? 'Family member' : 'Another person / unknown patient'),
  );
  await tester.pumpAndSettle();
  if (family) {
    await tester.tap(
      find.widgetWithText(DropdownButtonFormField<String>, 'Patient'),
    );
    await tester.pumpAndSettle();
    await tester.tap(find.text('Father Patient').last);
    await tester.pumpAndSettle();
  }
  await _fill(tester, 'What happened / symptoms', 'Needs urgent care');
  if (remote) {
    final toggle = find.widgetWithText(SwitchListTile, 'I am with the patient');
    await tester.ensureVisible(toggle);
    await tester.tap(toggle);
    await tester.pumpAndSettle();
    await _fill(
      tester,
      'Patient pickup address / landmark',
      'Patient landmark',
    );
    await _fill(tester, 'Patient latitude', '19.07');
    await _fill(tester, 'Patient longitude', '72.87');
  }
  tester.testTextInput.hide();
  await tester.pumpAndSettle();
  await tester.tap(find.text('Confirm SOS'));
  await tester.pumpAndSettle();
}

Future<void> _voiceScreen(WidgetTester tester, CareApi api, {String? url}) =>
    tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: SosSenderVoice(
            api: api,
            url: url ?? 'http://localhost/api/mobile/sos/sos1/reporter-voice',
          ),
        ),
      ),
    );

http.Response _voiceResponse() => http.Response.bytes(
  base64Decode(_media.voiceBase64),
  200,
  headers: {'content-type': 'audio/mp4', 'x-audio-duration-ms': '10000'},
);

void _androidWidgetTest(
  String description,
  Future<void> Function(WidgetTester) body,
) => testWidgets(
  description,
  body,
  variant: TargetPlatformVariant.only(TargetPlatform.android),
);

void _androidTest(String description, Future<void> Function() body) =>
    test(description, () async {
      debugDefaultTargetPlatformOverride = TargetPlatform.android;
      try {
        await body();
      } finally {
        debugDefaultTargetPlatformOverride = null;
      }
    });

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();
  tearDown(() {
    debugDefaultTargetPlatformOverride = null;
    TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
        .setMockMethodCallHandler(sosCaptureChannel, null);
  });

  _androidTest(
    'Native capture requires a photo and 10 seconds of audio without controls',
    () async {
      final calls = <MethodCall>[];
      TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
          .setMockMethodCallHandler(sosCaptureChannel, (call) async {
            calls.add(call);
            return {
              'photo_base64': _media.photoBase64,
              'voice_base64': _media.voiceBase64,
              'voice_duration_ms': 10000,
            };
          });
      final media = await captureSosReporterMedia();
      expect(calls.single.method, 'capture');
      expect(calls.single.arguments, isNull);
      expect(media!.payload, _media.payload);
    },
  );

  _androidTest(
    'Cancelled and denied native capture cannot supply media',
    () async {
      final messenger =
          TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger;
      messenger.setMockMethodCallHandler(sosCaptureChannel, (_) async => null);
      expect(await captureSosReporterMedia(), isNull);
      messenger.setMockMethodCallHandler(sosCaptureChannel, (_) async {
        throw PlatformException(code: 'permission_denied');
      });
      await expectLater(
        captureSosReporterMedia(),
        throwsA(
          isA<ApiFailure>().having(
            (e) => e.message,
            'message',
            contains('Allow camera and microphone'),
          ),
        ),
      );
      messenger.setMockMethodCallHandler(sosCaptureChannel, (_) async {
        throw PlatformException(code: 'capture_cancelled');
      });
      expect(await captureSosReporterMedia(), isNull);
    },
  );

  _androidTest(
    'Malformed, missing, oversized and short recordings are rejected',
    () async {
      final messenger =
          TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger;
      for (final values in [
        {'photo_base64': _media.photoBase64},
        {
          'photo_base64': 'invalid',
          'voice_base64': _media.voiceBase64,
          'voice_duration_ms': 10000,
        },
        {
          'photo_base64': _media.photoBase64,
          'voice_base64': '',
          'voice_duration_ms': 10000,
        },
        {
          'photo_base64': _media.photoBase64,
          'voice_base64': _media.voiceBase64,
          'voice_duration_ms': 5000,
        },
        {
          'photo_base64': _media.photoBase64,
          'voice_base64': _media.voiceBase64,
          'voice_duration_ms': 12000,
        },
        {
          'photo_base64': _media.photoBase64,
          'voice_base64': base64Encode(Uint8List(2 * 1024 * 1024 + 1)),
          'voice_duration_ms': 10000,
        },
        {
          'photo_base64': base64Encode(Uint8List(2 * 1024 * 1024 + 1)),
          'voice_base64': _media.voiceBase64,
          'voice_duration_ms': 10000,
        },
      ]) {
        messenger.setMockMethodCallHandler(
          sosCaptureChannel,
          (_) async => values,
        );
        await expectLater(
          captureSosReporterMedia(),
          throwsA(isA<ApiFailure>()),
        );
      }
    },
  );

  _androidTest(
    'Unsupported platforms fail clearly without launching a camera',
    () async {
      debugDefaultTargetPlatformOverride = TargetPlatform.iOS;
      var invoked = false;
      TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
          .setMockMethodCallHandler(sosCaptureChannel, (_) async {
            invoked = true;
            return null;
          });
      await expectLater(captureSosReporterMedia(), throwsA(isA<ApiFailure>()));
      expect(invoked, false);
    },
  );

  _androidWidgetTest(
    'Capture cancellation sends no alert and does not request GPS',
    (tester) async {
      final session = _MediaSession();
      var gpsCalls = 0;
      await _screen(
        tester,
        session,
        capture: () async => null,
        gps: () async {
          gpsCalls++;
          return _fix();
        },
      );
      await tester.tap(find.text('SOS · Request ambulance'));
      await tester.pumpAndSettle();
      expect(session.sent, isEmpty);
      expect(gpsCalls, 0);
      expect(
        find.text('Photo and 10-second audio required. SOS was not sent.'),
        findsOneWidget,
      );
      await tester.pumpWidget(const SizedBox());
      session.dispose();
    },
  );

  _androidWidgetTest(
    'Permission denial sends no SOS and leaves SOS available to retry',
    (tester) async {
      final session = _MediaSession();
      await _screen(
        tester,
        session,
        capture: () async {
          throw ApiFailure(
            'Allow camera and microphone access, then try SOS again.',
          );
        },
      );
      await tester.tap(find.text('SOS · Request ambulance'));
      await tester.pumpAndSettle();
      expect(session.sent, isEmpty);
      expect(
        find.text('Allow camera and microphone access, then try SOS again.'),
        findsOneWidget,
      );
      expect(
        tester
            .widget<FilledButton>(
              find.widgetWithText(FilledButton, 'SOS · Request ambulance'),
            )
            .onPressed,
        isNotNull,
      );
      await tester.pumpWidget(const SizedBox());
      session.dispose();
    },
  );

  _androidWidgetTest(
    'Short automatic recording is rejected before GPS and dispatch',
    (tester) async {
      final session = _MediaSession();
      var gpsCalls = 0;
      await _screen(
        tester,
        session,
        capture: () async => SosReporterMedia(
          photoBase64: _media.photoBase64,
          voiceBase64: _media.voiceBase64,
          voiceDurationMs: 1000,
        ),
        gps: () async {
          gpsCalls++;
          return _fix();
        },
      );
      await tester.tap(find.text('SOS · Request ambulance'));
      await tester.pumpAndSettle();
      expect(session.sent, isEmpty);
      expect(gpsCalls, 0);
      await tester.pumpWidget(const SizedBox());
      session.dispose();
    },
  );

  _androidWidgetTest(
    'SOS blocks repeat taps and sends media automatically before fresh GPS',
    (tester) async {
      final session = _MediaSession();
      final capture = Completer<SosReporterMedia?>();
      final events = <String>[];
      await _screen(
        tester,
        session,
        capture: () {
          events.add('capture');
          return capture.future;
        },
        gps: () async {
          events.add('gps');
          return _fix();
        },
      );
      final send = tester
          .widget<FilledButton>(
            find.widgetWithText(FilledButton, 'SOS · Request ambulance'),
          )
          .onPressed!;
      send();
      send();
      await tester.pump();
      expect(events, ['capture']);
      expect(session.sent, isEmpty);
      expect(find.text('Capturing photo & recording 10s…'), findsOneWidget);
      expect(
        tester
            .widget<OutlinedButton>(
              find.widgetWithText(
                OutlinedButton,
                'SOS for family / another person',
              ),
            )
            .onPressed,
        isNull,
      );
      capture.complete(_media);
      await tester.pumpAndSettle();
      expect(events, ['capture', 'gps']);
      expect(session.sent, hasLength(1));
      for (final entry in _media.payload.entries) {
        expect(session.sent.single[entry.key], entry.value);
      }
      expect(find.byType(AlertDialog), findsNothing);
      expect(find.text('Confirm SOS'), findsNothing);
      await tester.pumpWidget(const SizedBox());
      session.dispose();
    },
  );

  _androidWidgetTest(
    'Changing login during recording prevents GPS and SOS submission',
    (tester) async {
      final session = _MediaSession();
      final capture = Completer<SosReporterMedia?>();
      var gpsCalls = 0;
      await _screen(
        tester,
        session,
        capture: () => capture.future,
        gps: () async {
          gpsCalls++;
          return _fix();
        },
      );
      await tester.tap(find.text('SOS · Request ambulance'));
      await tester.pump();
      session.api.token = null;
      capture.complete(_media);
      await tester.pumpAndSettle();
      expect(session.sent, isEmpty);
      expect(gpsCalls, 0);
      await tester.pumpWidget(const SizedBox());
      session.dispose();
    },
  );

  _androidWidgetTest(
    'Family SOS automatically uses reporter media and refreshes GPS',
    (tester) async {
      final session = _MediaSession();
      final events = <String>[];
      var fixes = 0;
      await _screen(
        tester,
        session,
        capture: () async {
          events.add('capture');
          return _media;
        },
        gps: () async {
          events.add('gps');
          return _fix(++fixes == 1 ? 28.61 : 28.62);
        },
      );
      await _recipient(tester, family: true);
      expect(events, ['gps', 'capture', 'gps']);
      expect(session.sent.single['reported_for'], 'Family');
      expect(session.sent.single['dependent_id'], '7');
      expect(session.sent.single['reporter_voice_base64'], _media.voiceBase64);
      expect(session.sent.single['lat'], 28.62);
      await tester.pumpWidget(const SizedBox());
      session.dispose();
    },
  );

  _androidWidgetTest(
    'Other-person SOS preserves the remote patient pin after recording',
    (tester) async {
      final session = _MediaSession();
      var gpsCalls = 0;
      await _screen(
        tester,
        session,
        capture: () async => _media,
        gps: () async {
          gpsCalls++;
          return _fix();
        },
      );
      await _recipient(tester, family: false, remote: true);
      expect(gpsCalls, 0);
      expect(session.sent.single['reported_for'], 'Other');
      expect(session.sent.single['reporter_photo_base64'], _media.photoBase64);
      expect(session.sent.single['reporter_voice_duration_ms'], 10000);
      expect(session.sent.single['mode'], 'manual');
      expect(session.sent.single['lat'], 19.07);
      expect(session.sent.single['lng'], 72.87);
      expect(session.sent.single['pickup_address'], 'Patient landmark');
      await tester.pumpWidget(const SizedBox());
      session.dispose();
    },
  );

  _androidWidgetTest(
    'Photo still uses authenticated same-server requests only',
    (tester) async {
      final requests = <http.Request>[];
      final api = CareApi(
        'http://localhost/api/mobile',
        client: MockClient((request) async {
          requests.add(request);
          return http.Response.bytes(
            base64Decode(
              'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVQIHWP4z8DwHwAFgAI/ScLbtAAAAABJRU5ErkJggg==',
            ),
            200,
          );
        }),
      )..token = 'driver-token';
      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: SosSenderPhoto(
              api: api,
              url: 'http://localhost/api/mobile/sos/sos1/reporter-photo',
            ),
          ),
        ),
      );
      await tester.pumpAndSettle();
      expect(requests.single.headers['Authorization'], 'Bearer driver-token');
      expect(find.text('SOS sender photo'), findsOneWidget);
      expect(find.byType(Image), findsOneWidget);
      requests.clear();
      await tester.pumpWidget(
        MaterialApp(
          home: Scaffold(
            body: SosSenderPhoto(
              api: api,
              url: 'http://other-server.example/photo',
            ),
          ),
        ),
      );
      await tester.pumpAndSettle();
      expect(requests, isEmpty);
      expect(find.text('SOS sender photo unavailable'), findsOneWidget);
      await tester.pumpWidget(const SizedBox());
      api.client.close();
    },
  );

  _androidWidgetTest(
    'Driver audio loads on tap with authentication and plays without caching',
    (tester) async {
      final requests = <http.Request>[];
      final native = <MethodCall>[];
      final playback = Completer<void>();
      TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
          .setMockMethodCallHandler(sosCaptureChannel, (call) async {
            native.add(call);
            if (call.method == 'playVoice') {
              await playback.future;
            }
            return null;
          });
      final api = CareApi(
        'http://localhost/api/mobile',
        client: MockClient((request) async {
          requests.add(request);
          return _voiceResponse();
        }),
      )..token = 'driver-token';
      await _voiceScreen(tester, api);
      await tester.pumpAndSettle();
      expect(requests, isEmpty);
      expect(native, isEmpty);
      await tester.tap(find.text('Play SOS audio (10s)'));
      await tester.pumpAndSettle();
      expect(requests.single.headers['Authorization'], 'Bearer driver-token');
      expect(requests.single.headers['Cache-Control'], 'no-store');
      expect(native.single.method, 'playVoice');
      expect(native.single.arguments, {'voice_base64': _media.voiceBase64});
      expect(find.text('Stop SOS audio'), findsOneWidget);
      playback.complete();
      await tester.pumpAndSettle();
      await tester.tap(find.text('Play SOS audio (10s)'));
      await tester.pumpAndSettle();
      expect(requests, hasLength(2));
      await tester.pumpWidget(const SizedBox());
      api.client.close();
    },
  );

  _androidWidgetTest(
    'Driver audio rejects cross-server URLs before sending a token',
    (tester) async {
      var requests = 0;
      final api = CareApi(
        'http://localhost/api/mobile',
        client: MockClient((_) async {
          requests++;
          return _voiceResponse();
        }),
      )..token = 'driver-token';
      await _voiceScreen(
        tester,
        api,
        url: 'http://elsewhere.example/private.m4a',
      );
      await tester.tap(find.text('Play SOS audio (10s)'));
      await tester.pumpAndSettle();
      expect(requests, 0);
      expect(find.text('SOS audio unavailable. Try again.'), findsOneWidget);
      await tester.pumpWidget(const SizedBox());
      api.client.close();
    },
  );

  _androidWidgetTest(
    'Driver can retry expired voice access without caching failure',
    (tester) async {
      var requests = 0;
      var plays = 0;
      TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
          .setMockMethodCallHandler(sosCaptureChannel, (call) async {
            if (call.method == 'playVoice') {
              plays++;
            }
            return null;
          });
      final api = CareApi(
        'http://localhost/api/mobile',
        client: MockClient((_) async {
          return ++requests == 1 ? http.Response('', 403) : _voiceResponse();
        }),
      )..token = 'driver-token';
      await _voiceScreen(tester, api);
      await tester.tap(find.text('Play SOS audio (10s)'));
      await tester.pumpAndSettle();
      expect(plays, 0);
      expect(find.text('SOS audio unavailable. Try again.'), findsOneWidget);
      await tester.tap(find.text('Play SOS audio (10s)'));
      await tester.pumpAndSettle();
      expect(requests, 2);
      expect(plays, 1);
      expect(find.text('SOS audio unavailable. Try again.'), findsNothing);
      await tester.pumpWidget(const SizedBox());
      api.client.close();
    },
  );

  _androidWidgetTest('Driver voice stops on dispose and on login change', (
    tester,
  ) async {
    final native = <String>[];
    var playback = Completer<void>();
    TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
        .setMockMethodCallHandler(sosCaptureChannel, (call) async {
          native.add(call.method);
          if (call.method == 'playVoice') {
            await playback.future;
          }
          return null;
        });
    final api = CareApi(
      'http://localhost/api/mobile',
      client: MockClient((_) async => _voiceResponse()),
    )..token = 'driver-token';
    await _voiceScreen(tester, api);
    await tester.tap(find.text('Play SOS audio (10s)'));
    await tester.pumpAndSettle();
    api.token = 'new-login';
    await _voiceScreen(tester, api);
    await tester.pumpAndSettle();
    expect(native, ['playVoice', 'stopVoice']);
    playback.complete();
    await tester.pumpAndSettle();
    playback = Completer<void>();
    await tester.tap(find.text('Play SOS audio (10s)'));
    await tester.pumpAndSettle();
    await tester.pumpWidget(const SizedBox());
    await tester.pumpAndSettle();
    expect(native, ['playVoice', 'stopVoice', 'playVoice', 'stopVoice']);
    playback.complete();
    api.client.close();
  });

  _androidWidgetTest(
    'Disposed or logged-out driver downloads cannot start playback',
    (tester) async {
      var plays = 0;
      TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
          .setMockMethodCallHandler(sosCaptureChannel, (call) async {
            if (call.method == 'playVoice') {
              plays++;
            }
            return null;
          });
      final response = Completer<http.Response>();
      final api = CareApi(
        'http://localhost/api/mobile',
        client: MockClient((_) => response.future),
      )..token = 'driver-token';
      await _voiceScreen(tester, api);
      await tester.tap(find.text('Play SOS audio (10s)'));
      await tester.pump();
      api.token = null;
      await tester.pumpWidget(const SizedBox());
      response.complete(_voiceResponse());
      await tester.pumpAndSettle();
      expect(plays, 0);
      api.client.close();
    },
  );

  _androidWidgetTest(
    'Driver rejects truncated and oversized audio before playback',
    (tester) async {
      var plays = 0;
      TestDefaultBinaryMessengerBinding.instance.defaultBinaryMessenger
          .setMockMethodCallHandler(sosCaptureChannel, (call) async {
            if (call.method == 'playVoice') {
              plays++;
            }
            return null;
          });
      for (final bytes in [Uint8List(2), Uint8List(2 * 1024 * 1024 + 1)]) {
        final api = CareApi(
          'http://localhost/api/mobile',
          client: MockClient(
            (_) async => http.Response.bytes(
              bytes,
              200,
              headers: {
                'content-type': 'audio/mp4',
                'x-audio-duration-ms': '10000',
              },
            ),
          ),
        )..token = 'driver-token';
        await _voiceScreen(tester, api);
        await tester.tap(find.text('Play SOS audio (10s)'));
        await tester.pumpAndSettle();
        expect(find.text('SOS audio unavailable. Try again.'), findsOneWidget);
        await tester.pumpWidget(const SizedBox());
        api.client.close();
      }
      expect(plays, 0);
    },
  );
}
