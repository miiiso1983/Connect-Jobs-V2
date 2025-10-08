import 'dart:convert';
import 'package:http/http.dart' as http;
import '../utils/app_config.dart';

class ApplicationsService {
  final http.Client _client;
  ApplicationsService({http.Client? client}) : _client = client ?? http.Client();

  Future<Map<String, dynamic>> listApplications({
    required String token,
    required int jobId,
    String? status,
    int? minMatching,
    String? speciality,
    String? province,
    String sortBy = 'matching_percentage',
    String sortOrder = 'desc',
  }) async {
    final query = <String, String>{
      'sort_by': sortBy,
      'sort_order': sortOrder,
      if (status != null) 'status': status,
      if (minMatching != null) 'min_matching': minMatching.toString(),
      if (speciality != null) 'speciality': speciality,
      if (province != null) 'province': province,
    };

    final uri = Uri.parse('${AppConfig.baseUrl}jobs/$jobId/applications').replace(queryParameters: query);
    final resp = await _client.get(uri, headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    });
    final body = _safeDecode(resp.body);
    return {
      'statusCode': resp.statusCode,
      'success': body['success'] == true || resp.statusCode == 200,
      'data': body['data'],
      'message': body['message'] ?? (resp.statusCode == 200 ? null : 'Failed to load applications'),
    };
  }

  Future<Map<String, dynamic>> applyToJob({
    required String token,
    required int jobId,
  }) async {
    final uri = Uri.parse('${AppConfig.baseUrl}applications/apply/$jobId');
    final resp = await _client.post(
      uri,
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'Authorization': 'Bearer $token',
      },
      body: jsonEncode({}),
    );
    final body = _safeDecode(resp.body);
    return {
      'statusCode': resp.statusCode,
      'success': body['success'] == true || resp.statusCode == 201,
      'data': body['data'],
      'message': body['message'] ?? (resp.statusCode == 201 ? null : 'Failed to apply'),
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

