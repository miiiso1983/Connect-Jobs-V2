import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;

import '../utils/runtime_config.dart';

class ProfileService {
  final http.Client _client;
  ProfileService({http.Client? client}) : _client = client ?? http.Client();

  List<Uri> _candidatesForPath(String path) {
    final baseUri = Uri.parse(RuntimeConfig.baseUrl);
    final domain = '${baseUri.scheme}://${baseUri.host}${baseUri.hasPort ? ':${baseUri.port}' : ''}/';
    return [
      Uri.parse('${RuntimeConfig.baseUrl}$path'),
      Uri.parse('${domain}api/v1/$path'),
      Uri.parse('${domain}api/$path'),
      Uri.parse(domain + path),
    ];
  }

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
    http.Response? last;
    for (final uri in _candidatesForPath('profile/')) {
      try {
        final resp = await _client.get(uri, headers: _authHeaders(token));
        last = resp;
        Map<String, dynamic> data;
        try {
          data = jsonDecode(resp.body) is Map<String, dynamic>
              ? (jsonDecode(resp.body) as Map<String, dynamic>)
              : <String, dynamic>{};
        } catch (_) {
          data = <String, dynamic>{};
        }
        if (resp.statusCode == 200) {
          return data.isNotEmpty ? data : {'success': true, 'data': {}};
        }
        // for 401/404 keep trying others
      } catch (_) {
        // try next
      }
    }
    if (last == null) return {'success': false, 'message': 'تعذّر الاتصال بالخادم'};
    try {
      final data = jsonDecode(last.body) as Map<String, dynamic>;
      return _normalize(data, last.statusCode);
    } catch (_) {
      return {'success': false, 'message': 'تعذّر الاتصال بالخادم', 'status': last.statusCode};
    }
  }

  Future<Map<String, dynamic>> updateProfileJson({
    required String token,
    required Map<String, dynamic> body,
  }) async {
    http.Response? last;
    for (final uri in _candidatesForPath('profile/')) {
      try {
        final resp = await _client.put(
          uri,
          headers: {
            ..._authHeaders(token),
            'Content-Type': 'application/json',
          },
          body: jsonEncode(body),
        );
        last = resp;
        Map<String, dynamic> data;
        try {
          data = jsonDecode(resp.body) is Map<String, dynamic>
              ? (jsonDecode(resp.body) as Map<String, dynamic>)
              : <String, dynamic>{};
        } catch (_) {
          data = <String, dynamic>{};
        }
        if (resp.statusCode == 200) {
          return data.isNotEmpty ? data : {'success': true, 'data': {}};
        }
        if (resp.statusCode == 422) {
          return _normalize(data, resp.statusCode);
        }
      } catch (_) {}
    }
    if (last == null) return {'success': false, 'message': 'تعذّر الاتصال بالخادم'};
    try {
      final data = jsonDecode(last.body) as Map<String, dynamic>;
      return _normalize(data, last.statusCode);
    } catch (_) {
      return {'success': false, 'message': 'تعذّر الاتصال بالخادم', 'status': last.statusCode};
    }
  }

  Future<Map<String, dynamic>> updateProfileMultipart({
    required String token,
    required Map<String, String> fields,
    Map<String, File> files = const {},
  }) async {
    http.Response? last;
    for (final uri in _candidatesForPath('profile/')) {
      try {
        final request = http.MultipartRequest('POST', uri);
        request.headers.addAll(_authHeaders(token));
        request.fields['_method'] = 'PUT';
        request.fields.addAll(fields);
        for (final entry in files.entries) {
          final file = await http.MultipartFile.fromPath(entry.key, entry.value.path);
          request.files.add(file);
        }
        final streamed = await request.send();
        final resp = await http.Response.fromStream(streamed);
        last = resp;
        Map<String, dynamic> data;
        try {
          data = jsonDecode(resp.body) is Map<String, dynamic>
              ? (jsonDecode(resp.body) as Map<String, dynamic>)
              : <String, dynamic>{};
        } catch (_) {
          data = <String, dynamic>{};
        }
        if (resp.statusCode == 200) {
          return data.isNotEmpty ? data : {'success': true, 'data': {}};
        }
        if (resp.statusCode == 422) {
          return _normalize(data, resp.statusCode);
        }
      } catch (_) {}
    }
    if (last == null) return {'success': false, 'message': 'تعذّر الاتصال بالخادم'};
    try {
      final data = jsonDecode(last.body) as Map<String, dynamic>;
      return _normalize(data, last.statusCode);
    } catch (_) {
      return {'success': false, 'message': 'تعذّر الاتصال بالخادم', 'status': last.statusCode};
    }
  }

  void close() => _client.close();
}

