import 'dart:convert';
import 'dart:io';
import 'dart:async';

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
    void Function(double progress)? onProgress,
    int retries = 2,
    Duration initialBackoff = const Duration(seconds: 1),
  }) async {
    // Prepare request
    Future<http.Response> sendOnce() async {
      final request = http.MultipartRequest('POST', _path('profile/'));
      request.headers.addAll(_authHeaders(token));
      request.fields['_method'] = 'PUT';
      request.fields.addAll(fields);

      // Compute total bytes across all files (best-effort)
      int totalBytes = 0;
      for (final f in files.values) {
        try { totalBytes += await f.length(); } catch (_) {}
      }
      int sentBytes = 0;

      for (final entry in files.entries) {
        final file = entry.value;
        final length = await file.length();
        final Stream<List<int>> stream = file.openRead().transform<List<int>>(
          StreamTransformer<List<int>, List<int>>.fromHandlers(
            handleData: (List<int> chunk, EventSink<List<int>> sink) {
              sentBytes += chunk.length;
              if (onProgress != null && totalBytes > 0) {
                final double p = sentBytes / totalBytes;
                // Clamp 0..1
                onProgress(p < 0 ? 0 : (p > 1 ? 1 : p));
              }
              sink.add(chunk);
            },
          ),
        );
        final multipart = http.MultipartFile(
          entry.key,
          stream,
          length,
          filename: file.path.split('/').last,
        );
        request.files.add(multipart);
      }

      final streamed = await request.send();
      return http.Response.fromStream(streamed);
    }

    int attempt = 0;
    while (true) {
      try {
        final resp = await sendOnce();
        Map<String, dynamic> data;
        try {
          data = jsonDecode(resp.body) is Map<String, dynamic>
              ? (jsonDecode(resp.body) as Map<String, dynamic>)
              : <String, dynamic>{};
        } catch (_) {
          data = <String, dynamic>{};
        }
        final normalized = _normalize(data, resp.statusCode);
        // Consider 5xx/connection issues for retry; 4xx shouldn't retry
        final shouldRetry = resp.statusCode >= 500 && attempt < retries;
        if (!shouldRetry) {
          // Ensure final progress emits 1.0 if success
          if (normalized['success'] == true && onProgress != null) {
            onProgress(1.0);
          }
          return normalized;
        }
      } catch (_) {
        if (attempt >= retries) {
          return {'success': false, 'message': 'تعذّر الاتصال بالخادم'};
        }
      }
      // Backoff
      final delay = initialBackoff * (1 << attempt);
      await Future.delayed(delay);
      attempt += 1;
    }
  }

  void close() => _client.close();
}

