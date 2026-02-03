import 'dart:convert';
import 'package:http/http.dart' as http;
import '../utils/app_config.dart';
import 'cache/jobs_cache.dart';

class JobsService {
  final http.Client _client;
  JobsService({http.Client? client}) : _client = client ?? http.Client();

  Future<Map<String, dynamic>> listJobs({
    String? token,
    String? search,
    String? province,
    String? speciality,
    String sortBy = 'id',
    String sortOrder = 'desc',
    int? page,
  }) async {
    final query = <String, String>{
      'sort_by': sortBy,
      'sort_order': sortOrder,
      if (search != null && search.isNotEmpty) 'search': search,
      if (province != null) 'province': province,
      if (speciality != null) 'speciality': speciality,
      if (page != null) 'page': page.toString(),
    };

    // Try network first
    try {
      final uri = Uri.parse('${AppConfig.baseUrl}jobs').replace(queryParameters: query);
      final headers = <String, String>{
        'Accept': 'application/json',
      };
      // Add authorization header only if token is provided
      if (token != null && token.isNotEmpty) {
        headers['Authorization'] = 'Bearer $token';
      }
      final resp = await _client.get(uri, headers: headers);
      final body = _safeDecode(resp.body);
      final success = body['success'] == true || resp.statusCode == 200;
      final result = {
        'statusCode': resp.statusCode,
        'success': success,
        'data': body['data'],
        'message': body['message'] ?? (resp.statusCode == 200 ? null : 'Failed to load jobs'),
      };
      // Save to cache on success
      if (success && body['data'] != null) {
        await JobsCache.instance.saveList(
          payload: result,
          search: search,
          province: province,
          speciality: speciality,
          sortBy: sortBy,
          sortOrder: sortOrder,
          page: page,
        );
      }
      return result;
    } catch (_) {
      // ignore and try cache
    }

    // Fallback to cache
    final cached = JobsCache.instance.getList(
      search: search,
      province: province,
      speciality: speciality,
      sortBy: sortBy,
      sortOrder: sortOrder,
      page: page,
    );
    if (cached != null) {
      return {
        'statusCode': 200,
        'success': true,
        'data': cached['data'],
        'message': null,
        'cached': true,
      };
    }

    // Final fallback
    return {
      'statusCode': 503,
      'success': false,
      'data': null,
      'message': 'تعذّر تحميل الوظائف (لا يوجد اتصال ولا بيانات مخزنة)'
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
