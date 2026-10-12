import 'dart:async';

import 'package:flutter/foundation.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';

import 'api.dart';

class Session extends ChangeNotifier {
  final FlutterSecureStorage storage;
  final CareApi api;
  late final String bundledServer;
  Map<String, dynamic>? state;
  Map<String, dynamic>? sosState;
  String? sosError;
  bool loading = true, busy = false, online = false;
  String? error;
  Timer? _poll;
  bool _refreshing = false;
  Completer<void>? _refreshDone;
  int _epoch = 0;
  bool _disposed = false;
  @override
  void notifyListeners() {
    if (!_disposed) super.notifyListeners();
  }

  Session({CareApi? api, FlutterSecureStorage? storage})
    : api =
          api ??
          CareApi(
            const String.fromEnvironment(
              'API_BASE_URL',
              defaultValue: kIsWeb
                  ? 'http://localhost/Hospital/public/api/mobile'
                  : 'http://10.0.2.2/Hospital/public/api/mobile',
            ),
          ),
      storage = storage ?? const FlutterSecureStorage() {
    bundledServer = this.api.base;
  }
  Map<String, dynamic>? get profile =>
      state?['profile'] as Map<String, dynamic>?;
  bool get signedIn => api.token != null;
  String get role => profile?['role']?.toString() ?? 'patient';
  List<String> get careTypes =>
      (state?['care_types'] as List? ?? []).cast<String>();
  bool can(String action) => state?.containsKey('capabilities') == true
      ? (state!['capabilities'] as List).contains(action)
      : role == 'patient'
      ? ['create', 'request'].contains(action)
      : role == 'hospital' && action == 'approve';
  List<Map<String, dynamic>> get requests =>
      (state?['requests'] as List? ?? []).cast<Map<String, dynamic>>();
  List<Map<String, dynamic>> get hospitals =>
      (state?['hospitals'] as List? ?? []).cast<Map<String, dynamic>>();
  Future<void> init() async {
    try {
      api.base = await storage.read(key: 'server') ?? api.base;
      api.token = await storage.read(key: 'token');
      if (signedIn) {
        await refresh();
        polling(true);
      }
    } catch (_) {
      api.token = null;
      state = null;
      error = 'Unable to restore secure login. Please sign in again.';
    }
    loading = false;
    notifyListeners();
  }

  Future<void> server(String value) async {
    final next = CareApi.normalize(value);
    final probe = CareApi(next);
    try {
      final health = await probe.call('health');
      if (health['service'] != 'CareRoute' || health['mobile_api'] != 1) {
        throw ApiFailure('This is not a CareRoute mobile API.');
      }
    } finally {
      probe.client.close();
    }
    if (api.base != next) {
      await signOut();
      api.base = next;
    }
    await storage.write(key: 'server', value: next);
    error = null;
    notifyListeners();
  }

  Future<void> authenticate(
    String email,
    String password, {
    String? name,
    String? confirmation,
  }) async {
    final result = await api.call(name == null ? 'login' : 'register', {
      'email': email.trim(),
      'password': password,
      if (name != null) 'name': name.trim(),
      if (name != null) 'password_confirmation': confirmation,
    });
    api.token = result['token'] as String;
    try {
      await storage.write(key: 'token', value: api.token);
    } catch (_) {
      try {
        await api.call('logout', {});
      } catch (_) {}
      api.token = null;
      throw ApiFailure(
        'Secure login storage is unavailable. Check the device Keychain/secure storage setup.',
      );
    }
    state = {
      'profile': result['profile'],
      'requests': [],
      'hospitals': [],
      'events': [],
    };
    await refresh();
    polling(true);
    notifyListeners();
  }

  Future<void> refresh() async {
    if (!signedIn || _refreshing) return;
    final epoch = _epoch;
    _refreshing = true;
    _refreshDone = Completer<void>();
    try {
      final result = await api.call('state');
      if (epoch != _epoch || _disposed) return;
      state = result;
      if (['patient', 'driver', 'admin'].contains(role)) {
        try {
          final dispatch = await api.call('sos/state');
          if (epoch != _epoch || _disposed) return;
          sosState = dispatch;
          sosError = null;
        } on ApiFailure catch (e) {
          if (epoch != _epoch || _disposed) return;
          if (e.status == 401) rethrow;
          sosError = e.message;
        }
      }
      online = true;
      error = null;
    } on ApiFailure catch (e) {
      if (epoch != _epoch || _disposed) return;
      online = false;
      error = e.message;
      if (e.status == 401) {
        await clear();
        error = 'Session expired. Please sign in again.';
      }
    } finally {
      _refreshing = false;
      _refreshDone?.complete();
      _refreshDone = null;
      notifyListeners();
    }
  }

  Future<Map<String, dynamic>> action(Map<String, dynamic> input) async {
    return mutate('action', input);
  }

  Future<Map<String, dynamic>> sosAction(Map<String, dynamic> input) async {
    return mutate('sos/action', input);
  }

  Future<Map<String, dynamic>> mutate(
    String path,
    Map<String, dynamic> input,
  ) async {
    if (busy) throw ApiFailure('Please wait for the current action.');
    if (!online) {
      throw ApiFailure('Reconnect to the server before making changes.');
    }
    busy = true;
    notifyListeners();
    try {
      final result = await api.call(path, input);
      if (_refreshing) await _refreshDone!.future;
      await refresh();
      return result;
    } on ApiFailure catch (e) {
      if (e.status == 401) await clear();
      rethrow;
    } finally {
      busy = false;
      notifyListeners();
    }
  }

  void polling(bool active) {
    _poll?.cancel();
    _poll = null;
    if (active && signedIn) {
      _poll = Timer.periodic(const Duration(seconds: 5), (_) => refresh());
    }
  }

  Future<void> clear() async {
    _epoch++;
    polling(false);
    api.token = null;
    state = null;
    sosState = null;
    sosError = null;
    online = false;
    await storage.delete(key: 'token');
  }

  Future<void> signOut() async {
    if (signedIn) {
      if (role == 'driver') {
        try {
          await api.call('sos/action', {'action': 'duty', 'on_duty': false});
        } catch (_) {}
      }
      try {
        await api.call('logout', {});
      } catch (_) {}
    }
    await clear();
    error = null;
    notifyListeners();
  }

  @override
  void dispose() {
    _disposed = true;
    _poll?.cancel();
    api.client.close();
    super.dispose();
  }
}
