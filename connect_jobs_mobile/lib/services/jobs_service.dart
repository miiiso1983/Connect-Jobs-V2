import 'dart:convert';
import 'package:http/http.dart' as http;
import '../utils/app_config.dart';

class JobsService {
  final http.Client _client;
  JobsService({http.Client? client}) : _client = client ?? http.Client();

  Future<Map<String, dynamic>> listJobs({
    required String token,
    String? search,
    String? province,
    String? speciality,
    String sortBy = 'id',
    String sortOrder = 'desc',
  }) async {
    final query = <String, String>{
      'sort_by': sortBy,
      'sort_order': sortOrder,
      if (search != null && search.isNotEmpty) 'search': search,
      if (province != null) 'province': province,
      if (speciality != null) 'speciality': speciality,
    };

    final uri = Uri.parse('${AppConfig.baseUrl}jobs').replace(queryParameters: query);
    final resp = await _client.get(uri, headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    });
    final body = _safeDecode(resp.body);
    return {
      'statusCode': resp.statusCode,
      'success': body['success'] == true || resp.statusCode == 200,
      'data': body['data'],
      'message': body['message'] ?? (resp.statusCode == 200 ? null : 'Failed to load jobs'),
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

