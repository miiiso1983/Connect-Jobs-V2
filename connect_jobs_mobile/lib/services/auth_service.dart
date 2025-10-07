import 'dart:convert';
import 'package:http/http.dart' as http;
import '../utils/runtime_config.dart';

class AuthService {
  final http.Client _client;
  AuthService({http.Client? client}) : _client = client ?? http.Client();

  List<Uri> _loginCandidates() {
    final baseUri = Uri.parse(RuntimeConfig.baseUrl);
    final domain = '${baseUri.scheme}://${baseUri.host}${baseUri.hasPort ? ':${baseUri.port}' : ''}/';
    return [
      Uri.parse('${RuntimeConfig.baseUrl}${RuntimeConfig.authLoginPath}'),
      Uri.parse('${domain}api/v1/${RuntimeConfig.authLoginPath}'),
      Uri.parse('${domain}api/${RuntimeConfig.authLoginPath}'),
      Uri.parse(domain + RuntimeConfig.authLoginPath),
    ];
  }

  Future<http.Response> _post(Uri uri, Map<String, dynamic> payload) {
    return _client.post(
      uri,
      headers: const {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode(payload),
    );
  }

  Future<http.Response> loginRaw({required String email, required String password}) async {
    final payload = {'email': email, 'password': password};
    http.Response? last;
    for (final uri in _loginCandidates()) {
      try {
        final resp = await _post(uri, payload);
        if (resp.statusCode == 200 || resp.statusCode == 401 || resp.statusCode == 422) {
          return resp;
        }
        last = resp;
      } catch (_) {
        // ignore and try next
      }
    }
    return last ?? http.Response('{"success":false}', 500);
  }

  Future<Map<String, dynamic>> login({required String email, required String password}) async {
    http.Response resp = await loginRaw(email: email, password: password);

    // If 404 here, we've already tried multiple candidates; handle message below
    if (resp.statusCode == 404) {
      // no-op
    }

    Map<String, dynamic> data;
    try {
      final decoded = jsonDecode(resp.body);
      data = decoded is Map<String, dynamic> ? decoded : <String, dynamic>{};
    } catch (_) {
      data = <String, dynamic>{};
    }
    if (resp.statusCode == 200) {
      return data.isNotEmpty ? data : <String, dynamic>{'success': true, 'data': data};
    }
    String msg;
    if (resp.statusCode == 404) {
      msg = 'المسار غير موجود لمسار تسجيل الدخول. يرجى تأكيد إعدادات الـ API.';
    } else {
      msg = (data['message'] as String?) ?? 'فشل تسجيل الدخول';
    }
    return <String, dynamic>{'success': false, 'message': msg};
  }


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

  Future<Map<String, dynamic>> _postJsonToCandidates(String path, Map<String, dynamic> payload) async {
    http.Response? last;
    for (final uri in _candidatesForPath(path)) {
      try {
        final resp = await _post(uri, payload);
        last = resp;
        Map<String, dynamic> data;
        try {
          data = jsonDecode(resp.body) is Map<String, dynamic> ? (jsonDecode(resp.body) as Map<String, dynamic>) : <String, dynamic>{};
        } catch (_) {
          data = <String, dynamic>{};
        }
        if (resp.statusCode == 200 || resp.statusCode == 201) {
          return data.isNotEmpty ? data : <String, dynamic>{'success': true, 'data': {}};
        }
        if (resp.statusCode == 422) {
          // Validation errors; bubble up to UI
          return data.isNotEmpty ? data : <String, dynamic>{'success': false, 'message': 'بيانات غير صحيحة'};
        }
      } catch (_) {
        // try next candidate
      }
    }
    return <String, dynamic>{'success': false, 'message': 'تعذّر الاتصال بالخادم', 'status': last?.statusCode ?? 0};
  }

  Future<Map<String, dynamic>> registerJobSeeker({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    final payload = {
      'name': name,
      'email': email,
      'password': password,
      'password_confirmation': passwordConfirmation,
      'role': 'jobseeker',
    };
    final res = await _postJsonToCandidates(RuntimeConfig.registerJobSeekerPath, payload);
    return _normalizeAuthResponse(res);
  }

  Future<Map<String, dynamic>> registerCompany({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? officeName,
    String? jobTitle,
    String? phone,
  }) async {
    final payload = {
      'name': name,
      'email': email,
      'password': password,
      'password_confirmation': passwordConfirmation,
      'role': 'company',
      if (officeName != null && officeName.isNotEmpty) 'office_name': officeName,
      if (jobTitle != null && jobTitle.isNotEmpty) 'job_title': jobTitle,
      if (phone != null && phone.isNotEmpty) 'phone': phone,
    };
    final res = await _postJsonToCandidates(RuntimeConfig.registerCompanyPath, payload);
    return _normalizeAuthResponse(res);
  }

  Map<String, dynamic> _normalizeAuthResponse(Map<String, dynamic> raw) {
    // Normalize Laravel-style responses { success, message, errors, data }
    if (raw.containsKey('success')) return raw;
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
    // Assume success if token or user present
    if ((raw['token'] ?? raw['access_token']) != null || raw['user'] != null) {
      return {'success': true, 'data': raw};
    }
    return {'success': raw['success'] == true, 'data': raw['data'], 'message': raw['message']};
  }

  void close() => _client.close();
}

