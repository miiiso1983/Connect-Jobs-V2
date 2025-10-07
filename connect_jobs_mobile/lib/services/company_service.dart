import 'dart:convert';
import 'package:http/http.dart' as http;
import '../utils/app_config.dart';

class CompanyService {
  final http.Client _client;
  CompanyService({http.Client? client}) : _client = client ?? http.Client();

  Future<Map<String, dynamic>> dashboardStats({required String token}) async {
    final resp = await _client.get(
      Uri.parse('${AppConfig.baseUrl}jobs/dashboard-stats'),
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
    );
    final body = _safeDecode(resp.body);
    return {
      'statusCode': resp.statusCode,
      'success': body['success'] == true || resp.statusCode == 200,
      'data': body['data'],
      'message': body['message'],
    };
  }

  Future<Map<String, dynamic>> myJobs({required String token}) async {
    final resp = await _client.get(
      Uri.parse('${AppConfig.baseUrl}jobs/my-jobs'),
      headers: {
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
    );
    final body = _safeDecode(resp.body);
    return {
      'statusCode': resp.statusCode,
      'success': body['success'] == true || resp.statusCode == 200,
      'data': body['data'],
      'message': body['message'],
    };
  }

  Map<String, dynamic> _safeDecode(String src) {
    try {
      final d = jsonDecode(src);
      return d is Map<String, dynamic> ? d : <String, dynamic>{};
    } catch (_) {
      return <String, dynamic>{};
    }
  }

  void close() => _client.close();
}

