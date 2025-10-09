import 'dart:convert';
import 'package:http/http.dart' as http;
import '../utils/app_config.dart';
import 'notification_service.dart';
import 'package:hive_flutter/hive_flutter.dart';
import 'package:flutter/foundation.dart';

class AuthService {
  final http.Client _client;
  final NotificationService _notificationService;

  AuthService({http.Client? client})
    : _client = client ?? http.Client(),
      _notificationService = NotificationService(client: client);

  Uri _loginUri() => Uri.parse('${AppConfig.baseUrl}${AppConfig.authLoginPath}');

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
    try {
      return await _post(_loginUri(), payload);
    } catch (_) {
      return http.Response('{"success":false}', 500);
    }
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

  Uri _path(String path) => Uri.parse('${AppConfig.baseUrl}$path');

  Future<Map<String, dynamic>> _postJson(String path, Map<String, dynamic> payload) async {
    try {
      final resp = await _post(_path(path), payload);
      Map<String, dynamic> data;
      try {
        data = jsonDecode(resp.body) is Map<String, dynamic>
            ? (jsonDecode(resp.body) as Map<String, dynamic>)
            : <String, dynamic>{};
      } catch (_) {
        data = <String, dynamic>{};
      }
      if (resp.statusCode == 200 || resp.statusCode == 201) {
        return data.isNotEmpty ? data : <String, dynamic>{'success': true, 'data': {}};
      }
      if (resp.statusCode == 422) {
        return data.isNotEmpty ? data : <String, dynamic>{'success': false, 'message': 'بيانات غير صحيحة'};
      }
      return <String, dynamic>{'success': false, 'message': data['message'] ?? 'فشل الطلب', 'status': resp.statusCode};
    } catch (_) {
      return <String, dynamic>{'success': false, 'message': 'تعذّر الاتصال بالخادم'};
    }
  }

  Future<Map<String, dynamic>> registerJobSeeker({
    required String name,
    String? fullName,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? province,
    String? jobTitle,
    String? speciality,
    String? gender,
  }) async {
    final payload = {
      'name': name,
      'full_name': fullName ?? name,
      'email': email,
      'password': password,
      'password_confirmation': passwordConfirmation,
      'role': 'jobseeker',
      if (province != null && province.isNotEmpty) 'province': province,
      if (jobTitle != null && jobTitle.isNotEmpty) 'job_title': jobTitle,
      if (speciality != null && speciality.isNotEmpty) 'speciality': speciality,
      if (gender != null && gender.isNotEmpty) 'gender': gender,
    };
    final res = await _postJson(AppConfig.registerJobSeekerPath, payload);
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
    String? industry,
    String? province,
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
      if (industry != null && industry.isNotEmpty) 'industry': industry,
      if (province != null && province.isNotEmpty) 'province': province,
    };
    final res = await _postJson(AppConfig.registerCompanyPath, payload);
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

  /// Register FCM token with backend after successful login
  Future<Map<String, dynamic>> registerFCMTokenAfterLogin(String authToken) async {
    try {
      // Get stored FCM token
      final box = await Hive.openBox('app_data');
      final fcmToken = box.get('fcm_token') as String?;
      await box.close();

      if (fcmToken == null || fcmToken.isEmpty) {
        debugPrint('No FCM token found to register');
        return {'success': false, 'message': 'No FCM token available'};
      }

      // Register token with backend
      final result = await _notificationService.registerFCMToken(
        authToken: authToken,
        fcmToken: fcmToken,
      );

      if (result['success'] == true) {
        debugPrint('FCM token registered successfully with backend');
        // Start listening for token refresh
        _notificationService.listenForTokenRefresh(authToken: authToken);
      } else {
        debugPrint('Failed to register FCM token: ${result['message']}');
      }

      return result;
    } catch (e) {
      debugPrint('Error registering FCM token after login: $e');
      return {'success': false, 'message': 'Error: $e'};
    }
  }

  /// Unregister FCM token on logout
  Future<Map<String, dynamic>> unregisterFCMTokenOnLogout(String authToken) async {
    try {
      // Get stored FCM token
      final box = await Hive.openBox('app_data');
      final fcmToken = box.get('fcm_token') as String?;
      await box.close();

      if (fcmToken == null || fcmToken.isEmpty) {
        debugPrint('No FCM token found to unregister');
        return {'success': true, 'message': 'No FCM token to unregister'};
      }

      // Unregister token from backend
      final result = await _notificationService.unregisterFCMToken(
        authToken: authToken,
        fcmToken: fcmToken,
      );

      if (result['success'] == true) {
        debugPrint('FCM token unregistered successfully from backend');
      } else {
        debugPrint('Failed to unregister FCM token: ${result['message']}');
      }

      return result;
    } catch (e) {
      debugPrint('Error unregistering FCM token on logout: $e');
      return {'success': false, 'message': 'Error: $e'};
    }
  }

  void close() {
    _client.close();
    _notificationService.close();
  }
}

