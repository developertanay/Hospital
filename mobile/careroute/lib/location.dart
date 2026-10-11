import 'dart:async';

import 'package:geolocator/geolocator.dart';

import 'api.dart';

class PickupLocation {
  static Map<String, dynamic> demo({String? driver}) {
    final offset = driver == 'D2'
        ? .01
        : driver == 'D3'
        ? .03
        : 0.0;
    return {
      'lat': 28.6139 + offset,
      'lng': 77.2090 + offset,
      'accuracy': 5,
      'captured_at': DateTime.now().millisecondsSinceEpoch ~/ 1000,
      'mode': 'demo',
    };
  }

  static Future<Map<String, dynamic>> current() async {
    if (!await Geolocator.isLocationServiceEnabled()) {
      throw ApiFailure(
        'Turn on Location/GPS in your phone settings, then retry SOS.',
      );
    }
    var permission = await Geolocator.checkPermission();
    if (permission == LocationPermission.denied) {
      permission = await Geolocator.requestPermission();
    }
    if (permission == LocationPermission.deniedForever) {
      throw ApiFailure(
        'Location permission is blocked. Enable it for CareRoute in phone Settings.',
      );
    }
    if (permission == LocationPermission.denied) {
      throw ApiFailure(
        'Location permission is needed to share the pickup point.',
      );
    }
    Position position;
    try {
      position = await Geolocator.getCurrentPosition(
        locationSettings: const LocationSettings(
          accuracy: LocationAccuracy.high,
          timeLimit: Duration(seconds: 25),
        ),
      );
    } on TimeoutException {
      throw ApiFailure(
        'GPS timed out. Move to an open area and retry. No SOS was sent.',
      );
    }
    if (position.accuracy > 200 ||
        DateTime.now().difference(position.timestamp).inSeconds.abs() > 120) {
      throw ApiFailure(
        'Location is too approximate or old. Enable precise location and retry.',
      );
    }
    return {
      'lat': position.latitude,
      'lng': position.longitude,
      'accuracy': position.accuracy,
      'captured_at': position.timestamp.millisecondsSinceEpoch ~/ 1000,
      'mode': 'gps',
    };
  }
}
