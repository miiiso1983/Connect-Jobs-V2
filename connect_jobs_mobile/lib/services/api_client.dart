import 'dart:convert';
import 'package:http/http.dart' as http;
import '../utils/app_config.dart';

/// Minimal API client wrapper for GET/POST with optional auth token
class ApiClient {
  final http.Client _client;
  final String baseUrl;
  final String? token;

  ApiClient({http.Client? client, String? baseUrl, this.token})
      : _client = client ?? http.Client(),
        baseUrl = baseUrl ?? AppConfig.baseUrl;

  Map<String, String> _headers({Map<String, String>? extra}) {
    final headers = <String, String>{
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };
    if (token != null && token!.isNotEmpty) {
      headers['Authorization'] = 'Bearer $token';
    }
    if (extra != null) headers.addAll(extra);
    return headers;
  }

  Uri _uri(String path) => Uri.parse(baseUrl + path);

  Future<http.Response> get(String path, {Map<String, String>? headers}) {
    return _client.get(_uri(path), headers: _headers(extra: headers));
  }

  Future<http.Response> post(String path, {Object? body, Map<String, String>? headers}) {
    return _client.post(_uri(path), headers: _headers(extra: headers), body: body);
  }

  Future<Map<String, dynamic>> getJson(String path) async {
    final resp = await get(path);
    final data = jsonDecode(resp.body);
    if (resp.statusCode < 200 || resp.statusCode >= 300) {
      throw ApiException(resp.statusCode, data);
    }
    return (data is Map<String, dynamic>) ? data : <String, dynamic>{'data': data};
  }

  Future<Map<String, dynamic>> postJson(String path, Map<String, dynamic> json) async {
    final resp = await post(path, body: jsonEncode(json));
    final data = jsonDecode(resp.body);
    if (resp.statusCode < 200 || resp.statusCode >= 300) {
      throw ApiException(resp.statusCode, data);
    }
    return (data is Map<String, dynamic>) ? data : <String, dynamic>{'data': data};
  }

  void close() => _client.close();
}

class ApiException implements Exception {
  final int statusCode;
  final dynamic body;
  ApiException(this.statusCode, this.body);
  @override
  String toString() => 'ApiException(statusCode: $statusCode, body: $body)';
}

