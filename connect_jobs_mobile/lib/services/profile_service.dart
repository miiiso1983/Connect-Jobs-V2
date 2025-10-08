import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

import '../utils/app_config.dart';

class ProfileService {
  final http.Client _client;
  ProfileService({http.Client? client}) : _client = client ?? http.Client();

  Uri _path(String path) => Uri.parse('${AppConfig.baseUrl}$path');

  Map<String, String> _authHeaders(String token) => {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      };

  Map<String, dynamic> _normalize(Map<String, dynamic> raw, int status) {
    if (raw.containsKey('success')) return raw;
    if (status >= 200 && status < 300) {
      return {'success': true, 'data': raw};
    }
    if (raw.containsKey('errors')) {
      final errs = raw['errors'];
      if (errs is Map) {
        final msgs = errs.values
            .whereType<List>()
            .expand((l) => l)
            .whereType<String>()
            .toList();
        return {'success': false, 'message': msgs.join('\n'), 'errors': errs};
      }
    }
    return {
      'success': false,
      'message': (raw['message'] as String?) ?? 'فشل الطلب',
      'data': raw['data'],
      'status': status,
    };
  }

  Future<Map<String, dynamic>> getProfile({required String token}) async {
    try {
      final resp = await _client.get(_path('profile/'), headers: _authHeaders(token));
      Map<String, dynamic> data;
      try {
        data = jsonDecode(resp.body) is Map<String, dynamic>
            ? (jsonDecode(resp.body) as Map<String, dynamic>)
            : <String, dynamic>{};
      } catch (_) {
        data = <String, dynamic>{};
      }
      return _normalize(data, resp.statusCode);
    } catch (_) {
      return {'success': false, 'message': 'تعذّر الاتصال بالخادم'};
    }
  }

  Future<Map<String, dynamic>> updateProfileJson({
    required String token,
    required Map<String, dynamic> body,
  }) async {
    try {
      final resp = await _client.put(
        _path('profile/'),
        headers: {
          ..._authHeaders(token),
          'Content-Type': 'application/json',
        },
        body: jsonEncode(body),
      );
      Map<String, dynamic> data;
      try {
        data = jsonDecode(resp.body) is Map<String, dynamic>
            ? (jsonDecode(resp.body) as Map<String, dynamic>)
            : <String, dynamic>{};
      } catch (_) {
        data = <String, dynamic>{};
      }
      return _normalize(data, resp.statusCode);
    } catch (_) {
      return {'success': false, 'message': 'تعذّر الاتصال بالخادم'};
    }
  }

  Future<Map<String, dynamic>> updateProfileMultipart({
    required String token,
    required Map<String, String> fields,
    Map<String, File> files = const {},
  }) async {
    try {
      final request = http.MultipartRequest('POST', _path('profile/'));
      request.headers.addAll(_authHeaders(token));
      request.fields['_method'] = 'PUT';
      request.fields.addAll(fields);
      for (final entry in files.entries) {
        final file = await http.MultipartFile.fromPath(entry.key, entry.value.path);
        request.files.add(file);
      }
      final streamed = await request.send();
      final resp = await http.Response.fromStream(streamed);
      Map<String, dynamic> data;
      try {
        data = jsonDecode(resp.body) is Map<String, dynamic>
            ? (jsonDecode(resp.body) as Map<String, dynamic>)
            : <String, dynamic>{};
      } catch (_) {
        data = <String, dynamic>{};
      }
      return _normalize(data, resp.statusCode);
    } catch (_) {
      return {'success': false, 'message': 'تعذّر الاتصال بالخادم'};
    }
  }

  void close() => _client.close();
}

