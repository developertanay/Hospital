import 'dart:convert';

import 'package:flutter/foundation.dart';

import 'package:http/http.dart' as http;

class ApiFailure implements Exception {
  final String message;
  final int status;
  ApiFailure(this.message, [this.status = 0]);
  @override
  String toString() => message;
}

class CareApi {
  final http.Client client;
  String base;
  String? token;
  CareApi(this.base, {http.Client? client}) : client = client ?? http.Client();

  static String normalize(String value) {
    final uri = Uri.tryParse(value.trim().replaceAll(RegExp(r'/+$'), ''));
    if (uri == null ||
        !['http', 'https'].contains(uri.scheme) ||
        uri.host.isEmpty ||
        uri.userInfo.isNotEmpty ||
        uri.hasQuery ||
        uri.hasFragment) {
      throw ApiFailure(
        'Enter an http:// or https:// API address without credentials or query parameters.',
      );
    }
    return uri.toString();
  }

  Future<Map<String, dynamic>> call(
    String path, [
    Map<String, dynamic>? body,
  ]) => request(path, body);
  Future<Map<String, dynamic>> request(
    String path,
    Map<String, dynamic>? body, {
    String? method,
  }) async {
    try {
      final uri = Uri.parse('$base/$path');
      if (kReleaseMode && !kIsWeb && uri.scheme != 'https') {
        throw ApiFailure(
          'Release builds require an HTTPS backend. Use a debug build for the local HTTP demo.',
        );
      }
      final headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
      };
      final response =
          await (method == 'PUT'
                  ? client.put(uri, headers: headers, body: jsonEncode(body))
                  : method == 'DELETE'
                  ? client.delete(uri, headers: headers, body: jsonEncode(body))
                  : body == null
                  ? client.get(uri, headers: headers)
                  : client.post(uri, headers: headers, body: jsonEncode(body)))
              .timeout(const Duration(seconds: 15));
      Map<String, dynamic> data;
      try {
        data = jsonDecode(response.body) as Map<String, dynamic>;
      } catch (_) {
        throw ApiFailure(
          'Server returned an unexpected response. Check the API address and Laravel logs.',
          response.statusCode,
        );
      }
      if (response.statusCode >= 400) {
        final errors = data['errors'];
        final message = errors is Map && errors.isNotEmpty
            ? (errors.values.first as List).first.toString()
            : data['message']?.toString() ??
                  'Request failed (${response.statusCode}).';
        throw ApiFailure(message, response.statusCode);
      }
      return data;
    } on ApiFailure {
      rethrow;
    } catch (_) {
      throw ApiFailure(
        'Cannot reach the server. Keep XAMPP running, check the server address, and connect your phone and PC to the same Wi-Fi.',
      );
    }
  }
}
