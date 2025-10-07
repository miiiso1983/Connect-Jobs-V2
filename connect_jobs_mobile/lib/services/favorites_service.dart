import 'dart:convert';
import 'package:http/http.dart' as http;
import '../utils/app_config.dart';

class FavoritesService {
  final http.Client _client;
  FavoritesService({http.Client? client}) : _client = client ?? http.Client();

  Future<Map<String, dynamic>> addFavorite({required String token, required int jobId}) async {
    final resp = await _client.post(
      Uri.parse('${AppConfig.baseUrl}favorites/$jobId'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'Authorization': 'Bearer $token',
      },
    );
    final body = _safeDecode(resp.body);
    return {
      'statusCode': resp.statusCode,
      'success': body['success'] == true || resp.statusCode == 201,
      'data': body['data'],
      'message': body['message'],
    };
  }

  Future<Map<String, dynamic>> removeFavorite({required String token, required int jobId}) async {
    final resp = await _client.delete(
      Uri.parse('${AppConfig.baseUrl}favorites/$jobId'),
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

  Future<Map<String, dynamic>> listFavorites({required String token, int page = 1}) async {
    final resp = await _client.get(
      Uri.parse('${AppConfig.baseUrl}favorites/?page=$page'),
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

