import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import '../utils/app_config.dart';

/// Service to handle FCM token registration and notification management
class NotificationService {
  final http.Client _client;
  
  NotificationService({http.Client? client}) : _client = client ?? http.Client();

  /// Register FCM token with the backend for the authenticated user
  Future<Map<String, dynamic>> registerFCMToken({
    required String authToken,
    required String fcmToken,
    String? deviceType,
  }) async {
    try {
      final response = await _client.post(
        Uri.parse('${AppConfig.baseUrl}${AppConfig.registerFcmTokenPath}'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $authToken',
        },
        body: jsonEncode({
          'fcm_token': fcmToken,
          'device_type': deviceType ?? _getDeviceType(),
        }),
      );

      final data = _safeDecode(response.body);
      return {
        'statusCode': response.statusCode,
        'success': response.statusCode == 200 && (data['success'] == true),
        'data': data['data'],
        'message': data['message'] ?? (response.statusCode == 200 ? 'Token registered successfully' : 'Failed to register token'),
      };
    } catch (e) {
      debugPrint('Error registering FCM token: $e');
      return {
        'statusCode': 500,
        'success': false,
        'data': null,
        'message': 'Network error: $e',
      };
    }
  }

  /// Unregister FCM token from the backend
  Future<Map<String, dynamic>> unregisterFCMToken({
    required String authToken,
    required String fcmToken,
  }) async {
    try {
      final response = await _client.delete(
        Uri.parse('${AppConfig.baseUrl}${AppConfig.unregisterFcmTokenPath}'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer $authToken',
        },
        body: jsonEncode({
          'fcm_token': fcmToken,
        }),
      );

      final data = _safeDecode(response.body);
      return {
        'statusCode': response.statusCode,
        'success': response.statusCode == 200 && (data['success'] == true),
        'data': data['data'],
        'message': data['message'] ?? (response.statusCode == 200 ? 'Token unregistered successfully' : 'Failed to unregister token'),
      };
    } catch (e) {
      debugPrint('Error unregistering FCM token: $e');
      return {
        'statusCode': 500,
        'success': false,
        'data': null,
        'message': 'Network error: $e',
      };
    }
  }

  /// Get current FCM token
  Future<String?> getCurrentFCMToken() async {
    try {
      return await FirebaseMessaging.instance.getToken();
    } catch (e) {
      debugPrint('Error getting FCM token: $e');
      return null;
    }
  }

  /// Listen for FCM token refresh and update backend
  void listenForTokenRefresh({required String authToken}) {
    FirebaseMessaging.instance.onTokenRefresh.listen((newToken) async {
      debugPrint('FCM token refreshed: $newToken');
      await registerFCMToken(authToken: authToken, fcmToken: newToken);
    });
  }

  /// Helper method to determine device type
  String _getDeviceType() {
    if (defaultTargetPlatform == TargetPlatform.iOS) {
      return 'ios';
    } else if (defaultTargetPlatform == TargetPlatform.android) {
      return 'android';
    } else {
      return 'unknown';
    }
  }

  /// Safe JSON decode
  Map<String, dynamic> _safeDecode(String source) {
    try {
      final decoded = jsonDecode(source);
      return decoded is Map<String, dynamic> ? decoded : <String, dynamic>{};
    } catch (_) {
      return <String, dynamic>{};
    }
  }

  /// Close HTTP client
  void close() => _client.close();
}
