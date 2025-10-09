import 'dart:convert';
import 'package:hive_flutter/hive_flutter.dart';

class JobsCache {
  static const String jobsListBoxName = 'jobs_list_cache_v1';

  static final JobsCache instance = JobsCache._internal();
  JobsCache._internal();

  Box<String>? _listBox;

  Future<void> init() async {
    _listBox ??= await Hive.openBox<String>(jobsListBoxName);
  }

  String _keyFromParams({
    String? search,
    String? province,
    String? speciality,
    String sortBy = 'id',
    String sortOrder = 'desc',
    int? page,
  }) {
    final qp = <String, dynamic>{
      if (search != null && search.isNotEmpty) 'search': search,
      if (province != null) 'province': province,
      if (speciality != null) 'speciality': speciality,
      'sort_by': sortBy,
      'sort_order': sortOrder,
      if (page != null) 'page': page,
    };
    return jsonEncode(qp); // compact cache key
  }

  Future<void> saveList({
    required Map<String, dynamic> payload,
    String? search,
    String? province,
    String? speciality,
    String sortBy = 'id',
    String sortOrder = 'desc',
    int? page,
  }) async {
    await init();
    final key = _keyFromParams(
      search: search,
      province: province,
      speciality: speciality,
      sortBy: sortBy,
      sortOrder: sortOrder,
      page: page,
    );
    final value = jsonEncode({
      'ts': DateTime.now().millisecondsSinceEpoch,
      'data': payload,
    });
    await _listBox!.put(key, value);
  }

  Map<String, dynamic>? getList({
    String? search,
    String? province,
    String? speciality,
    String sortBy = 'id',
    String sortOrder = 'desc',
    int? page,
    Duration maxAge = const Duration(minutes: 30),
  }) {
    if (_listBox == null) return null;
    final key = _keyFromParams(
      search: search,
      province: province,
      speciality: speciality,
      sortBy: sortBy,
      sortOrder: sortOrder,
      page: page,
    );
    final raw = _listBox!.get(key);
    if (raw == null) return null;
    try {
      final obj = jsonDecode(raw) as Map<String, dynamic>;
      final ts = (obj['ts'] as num?)?.toInt() ?? 0;
      final isFresh = DateTime.now().millisecondsSinceEpoch - ts <= maxAge.inMilliseconds;
      if (!isFresh) return null;
      final data = obj['data'];
      if (data is Map<String, dynamic>) return data;
    } catch (_) {}
    return null;
  }

  Future<void> clearForParams({
    String? search,
    String? province,
    String? speciality,
    String sortBy = 'id',
    String sortOrder = 'desc',
    int? page,
  }) async {
    await init();
    final key = _keyFromParams(
      search: search,
      province: province,
      speciality: speciality,
      sortBy: sortBy,
      sortOrder: sortOrder,
      page: page,
    );
    await _listBox!.delete(key);
  }

  Future<void> clearAll() async {
    await init();
    await _listBox!.clear();
  }
}

