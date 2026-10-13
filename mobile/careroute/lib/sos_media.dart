import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';

import 'api.dart';

const sosCaptureChannel = MethodChannel('careroute/sos_capture');
const _maxMediaBytes = 2 * 1024 * 1024;

typedef SosMediaCapture = Future<SosReporterMedia?> Function();

class SosReporterMedia {
  final String photoBase64;
  final String voiceBase64;
  final int voiceDurationMs;

  const SosReporterMedia({
    required this.photoBase64,
    required this.voiceBase64,
    required this.voiceDurationMs,
  });

  void validate() {
    try {
      if (photoBase64.length > (_maxMediaBytes + 2) ~/ 3 * 4 ||
          voiceBase64.length > (_maxMediaBytes + 2) ~/ 3 * 4) {
        throw const FormatException();
      }
      final photo = base64Decode(photoBase64);
      if (photo.length < 4 ||
          photo.length > _maxMediaBytes ||
          photo[0] != 0xff ||
          photo[1] != 0xd8 ||
          photo[photo.length - 2] != 0xff ||
          photo.last != 0xd9) {
        throw const FormatException();
      }
      validateSosVoice(base64Decode(voiceBase64), voiceDurationMs);
    } catch (_) {
      throw ApiFailure(
        'Photo and 10-second audio could not be captured. Please try SOS again.',
      );
    }
  }

  Map<String, dynamic> get payload => {
    'reporter_photo_base64': photoBase64,
    'reporter_voice_base64': voiceBase64,
    'reporter_voice_duration_ms': voiceDurationMs,
  };
}

void validateSosVoice(Uint8List bytes, int durationMs) {
  if (bytes.length < 12 ||
      bytes.length > _maxMediaBytes ||
      durationMs < 9000 ||
      durationMs > 11000 ||
      String.fromCharCodes(bytes.sublist(4, 8)) != 'ftyp') {
    throw ApiFailure('SOS audio unavailable. Try again.');
  }
}

bool get _android => !kIsWeb && defaultTargetPlatform == TargetPlatform.android;

Future<SosReporterMedia?> captureSosReporterMedia() async {
  if (!_android) {
    throw ApiFailure('Automatic SOS capture is available in the Android app.');
  }
  try {
    final value = await sosCaptureChannel.invokeMapMethod<String, dynamic>(
      'capture',
    );
    if (value == null) return null;
    final photo = value['photo_base64'];
    final voice = value['voice_base64'];
    final duration = value['voice_duration_ms'];
    if (photo is! String || voice is! String || duration is! int) {
      throw ApiFailure('SOS capture could not finish. Please try again.');
    }
    final media = SosReporterMedia(
      photoBase64: photo,
      voiceBase64: voice,
      voiceDurationMs: duration,
    );
    media.validate();
    return media;
  } on PlatformException catch (e) {
    if (e.code.toLowerCase().contains('denied') ||
        e.code.toLowerCase().contains('restricted')) {
      throw ApiFailure(
        'Allow camera and microphone access, then try SOS again.',
      );
    }
    if (e.code.toLowerCase().contains('cancel')) return null;
    throw ApiFailure('SOS capture could not finish. Please try again.');
  } on MissingPluginException {
    throw ApiFailure('Automatic SOS capture is available in the Android app.');
  }
}

class SosSenderVoice extends StatefulWidget {
  final CareApi api;
  final String url;
  const SosSenderVoice({super.key, required this.api, required this.url});

  @override
  State<SosSenderVoice> createState() => _SosSenderVoiceState();
}

class _SosSenderVoiceState extends State<SosSenderVoice> {
  String? token;
  String? error;
  bool loading = false, playing = false;
  int operation = 0;

  @override
  void initState() {
    super.initState();
    token = widget.api.token;
  }

  @override
  void didUpdateWidget(covariant SosSenderVoice oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (oldWidget.url != widget.url ||
        oldWidget.api != widget.api ||
        token != widget.api.token) {
      stop();
      token = widget.api.token;
      error = null;
    }
  }

  Future<void> stop({bool updateUi = true}) async {
    operation++;
    if (loading || playing) {
      loading = false;
      playing = false;
      if (mounted && updateUi) setState(() {});
      try {
        await sosCaptureChannel.invokeMethod<void>('stopVoice');
      } catch (_) {
        // A disposed or unavailable native player has no audio to stop.
      }
    }
  }

  @override
  void dispose() {
    // Invalidate pending downloads before native playback can begin.
    stop(updateUi: false);
    super.dispose();
  }

  Future<void> play() async {
    if (loading || playing) return;
    final current = ++operation;
    final requestToken = widget.api.token;
    token = requestToken;
    setState(() {
      loading = true;
      error = null;
    });
    try {
      if (!_android) {
        throw ApiFailure('SOS audio playback is available in the Android app.');
      }
      final server = Uri.parse(widget.api.base);
      final uri = server.resolve(widget.url);
      if (requestToken == null ||
          uri.scheme != server.scheme ||
          uri.host != server.host ||
          uri.port != server.port ||
          uri.userInfo.isNotEmpty) {
        throw ApiFailure('SOS audio unavailable. Try again.');
      }
      final response = await widget.api.client
          .get(
            uri,
            headers: {
              'Accept': 'audio/mp4',
              'Authorization': 'Bearer $requestToken',
              'Cache-Control': 'no-store',
            },
          )
          .timeout(const Duration(seconds: 15));
      if (!mounted ||
          current != operation ||
          requestToken != widget.api.token) {
        return;
      }
      if (response.statusCode != 200 ||
          !response.headers['content-type'].toString().startsWith(
            'audio/mp4',
          )) {
        throw ApiFailure('SOS audio unavailable. Try again.');
      }
      final duration = int.tryParse(
        response.headers['x-audio-duration-ms'] ?? '',
      );
      validateSosVoice(response.bodyBytes, duration ?? 0);
      setState(() {
        loading = false;
        playing = true;
      });
      await sosCaptureChannel.invokeMethod<void>('playVoice', {
        'voice_base64': base64Encode(response.bodyBytes),
      });
    } catch (e) {
      if (mounted && current == operation) {
        setState(() {
          error = e is ApiFailure
              ? e.message
              : 'SOS audio unavailable. Try again.';
        });
      }
    } finally {
      if (mounted && current == operation) {
        setState(() {
          loading = false;
          playing = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) => Column(
    crossAxisAlignment: CrossAxisAlignment.start,
    children: [
      TextButton.icon(
        onPressed: loading || playing ? stop : play,
        icon: Icon(loading || playing ? Icons.stop : Icons.volume_up_outlined),
        label: Text(
          loading
              ? 'Loading SOS audio…'
              : playing
              ? 'Stop SOS audio'
              : 'Play SOS audio (10s)',
        ),
      ),
      if (error != null)
        Padding(
          padding: const EdgeInsets.only(left: 12, bottom: 8),
          child: Text(error!, style: const TextStyle(color: Color(0xFFC6283D))),
        ),
    ],
  );
}
