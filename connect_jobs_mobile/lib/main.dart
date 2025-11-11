import 'package:flutter/material.dart';
import 'package:flutter/foundation.dart';
import 'dart:convert';
import 'package:http/http.dart' as http;
import 'utils/app_config.dart';
import 'services/auth_service.dart';
import 'services/favorites_service.dart';
import 'services/jobs_service.dart';
import 'services/company_service.dart';
import 'services/applications_service.dart';
import 'theme/app_theme.dart';
import 'services/profile_service.dart';

import 'screens/admin_webview_screen.dart';


import 'dart:io';

import 'package:image_picker/image_picker.dart';
import 'package:file_picker/file_picker.dart';

import 'package:pdf/widgets.dart' as pw;
import 'package:pdf/pdf.dart' show PdfColors;
import 'package:printing/printing.dart' show PdfGoogleFonts, networkImage;
import 'package:file_saver/file_saver.dart';


import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';

import 'package:flutter_local_notifications/flutter_local_notifications.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:hive_flutter/hive_flutter.dart';
import 'services/cache/jobs_cache.dart';



@pragma('vm:entry-point')
Future<void> _firebaseMessagingBackgroundHandler(RemoteMessage message) async {
  // Ensure Firebase is initialized for background isolates
  try { await Firebase.initializeApp(); } catch (_) {}
}

final FlutterLocalNotificationsPlugin _fln = FlutterLocalNotificationsPlugin();
const AndroidNotificationChannel _defaultAndroidChannel = AndroidNotificationChannel(
  'default_channel',
  'General',
  description: 'General notifications',
  importance: Importance.defaultImportance,
);

/// Store FCM token locally using Hive for later use when user logs in
Future<void> _storeFCMTokenLocally(String token) async {
  try {
    final box = await Hive.openBox('app_data');
    await box.put('fcm_token', token);
    await box.close();
  } catch (e) {
    debugPrint('Error storing FCM token locally: $e');
  }
}



void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  // Initialize Firebase using explicit options for both platforms
  await Firebase.initializeApp(
    options: (Platform.isIOS || Platform.isMacOS)
        ? const FirebaseOptions(
            apiKey: 'AIzaSyAsN-KvkiIK2X7RBbXT0OsXaoCWPt6Fh4o',
            appId: '1:577210737623:ios:af500400fb288d9470a192',
            messagingSenderId: '577210737623',
            projectId: 'connect-job-c6a8f',
            storageBucket: 'connect-job-c6a8f.firebasestorage.app',
            iosBundleId: 'com.job.connectjob',
          )
        : const FirebaseOptions(
            apiKey: 'AIzaSyBHszAXbSPPyDLt9U09In-QdS07GW36QEg',
            appId: '1:577210737623:android:61e19f2080cb43af70a192',
            messagingSenderId: '577210737623',
            projectId: 'connect-job-c6a8f',
            storageBucket: 'connect-job-c6a8f.firebasestorage.app',

          ),
  );

  // Android 13+ runtime notification permission (request before any notifications)
  try {
    if (Platform.isAndroid) {
      final notifStatus = await Permission.notification.status;
      if (!notifStatus.isGranted) {
        await Permission.notification.request();
      }
    }
  } catch (_) {}

  // Request notification permissions (iOS) and obtain the FCM token
  try {
    await FirebaseMessaging.instance.requestPermission(
      alert: true,
      badge: true,
      sound: true,
    );
    final token = await FirebaseMessaging.instance.getToken();
    if (token != null) {

      // Store token locally for later use when user logs in
      await _storeFCMTokenLocally(token);
      debugPrint('FCM token: $token');
    }
  } catch (_) {}
  // Local notifications + FCM handlers
  await FirebaseMessaging.instance.setForegroundNotificationPresentationOptions(
    alert: true, badge: true, sound: true,
  );

  const InitializationSettings initSettings = InitializationSettings(
    android: AndroidInitializationSettings('@mipmap/ic_launcher'),
    iOS: DarwinInitializationSettings(),
  );
  await _fln.initialize(initSettings);

  await _fln
      .resolvePlatformSpecificImplementation<AndroidFlutterLocalNotificationsPlugin>()
      ?.createNotificationChannel(_defaultAndroidChannel);

  FirebaseMessaging.onBackgroundMessage(_firebaseMessagingBackgroundHandler);

  FirebaseMessaging.onMessage.listen((RemoteMessage message) async {
    final notif = message.notification;
    if (notif != null) {
      await _fln.show(
        notif.hashCode,
        notif.title,
        notif.body,
        NotificationDetails(
          android: AndroidNotificationDetails(
            _defaultAndroidChannel.id,
            _defaultAndroidChannel.name,
            channelDescription: _defaultAndroidChannel.description,
            importance: Importance.defaultImportance,
            priority: Priority.defaultPriority,
          ),
          iOS: DarwinNotificationDetails(),
        ),
      );
    }
  });

  FirebaseMessaging.onMessageOpenedApp.listen((RemoteMessage message) {
    debugPrint('Notification clicked: \\${message.messageId}');
  });


  // Initialize local cache (Hive)
  try {
    await Hive.initFlutter();
    await JobsCache.instance.init();
  } catch (_) {}


  runApp(const ConnectJobsApp());
}

class ConnectJobsApp extends StatelessWidget {
  const ConnectJobsApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Connect Jobs',
      theme: AppTheme.light.copyWith(
        textTheme: AppTheme.light.textTheme.apply(fontFamily: 'Arial'),
      ),
      home: const LoginScreen(),
      debugShowCheckedModeBanner: false,
    );
  }
}

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _isLoading = false;
  String _errorMessage = '';

  bool _obscure = true;
  bool _animateIn = false;

  @override
  void initState() {
    super.initState();
    // Simple fade-in animation for a modern feel
    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (mounted) setState(() => _animateIn = true);
    });
  }

  Future<void> _login() async {
    setState(() {
      _isLoading = true;
      _errorMessage = '';
    });

    try {
      final auth = AuthService();
      final data = await auth.login(
        email: _emailController.text,
        password: _passwordController.text,
      );

      if (data['success'] == true) {
        if (!mounted) return;
        final String token = (data['data']?['token'] as String?) ?? '';
        final Map<String, dynamic> user = (data['data']?['user'] as Map<String, dynamic>?) ?? {};
        final String role = (user['role'] as String?) ?? '';

        // Register FCM token with backend after successful login
        if (token.isNotEmpty) {
          try {
            await auth.registerFCMTokenAfterLogin(token);
          } catch (e) {
            debugPrint('Failed to register FCM token after login: $e');
            // Don't block login flow if FCM registration fails
          }
        }

        Widget home;
        if (role == 'admin') {
          home = AdminDashboardScreen(token: token, user: user);
        } else if (role == 'company') {
          home = CompanyDashboardScreen(token: token, user: user);
        } else {
          home = JobSeekerDashboardScreen(token: token, user: user);
        }

        if (!mounted) return;
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(builder: (context) => home),
        );
      } else {
        setState(() {
          _errorMessage = data['message'] ?? 'فشل في تسجيل الدخول';
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'خطأ في الاتصال: $e';
      });
    }

    setState(() {
      _isLoading = false;
    });
  }


  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            begin: Alignment.topRight,
            end: Alignment.bottomLeft,
            colors: [Color(0xFFF8FAFF), Color(0xFFEFF3FF)],
          ),
        ),
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(24),
              child: AnimatedOpacity(
                duration: const Duration(milliseconds: 500),
                opacity: _animateIn ? 1.0 : 0.0,
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    // Logo
                    CircleAvatar(
                      radius: 56,
                      backgroundColor: scheme.primary,
                      child: Padding(
                        padding: const EdgeInsets.all(8.0),
                        child: Image.network(
                          '${AppConfig.baseUrl.replaceFirst('api/v1/', '')}images/brand/logo.png',
                          width: 88,
                          height: 88,
                          fit: BoxFit.contain,
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    // Title
                    Text(
                      'Connect Jobs',
                      style: TextStyle(
                        fontSize: 28,
                        fontWeight: FontWeight.bold,
                        color: scheme.primary,
                      ),
                    ),
                    const SizedBox(height: 6),
                    Text(
                      'منصة التوظيف الطبي',
                      style: TextStyle(
                        fontSize: 15,
                        color: scheme.onSurface.withValues(alpha: 0.6),
                      ),
                    ),
                    const SizedBox(height: 20),

                    // Form Card
                    Card(
                      elevation: 8,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                      child: Padding(
                        padding: const EdgeInsets.all(16.0),
                        child: Column(
                          children: [
                            TextField(
                              controller: _emailController,
                              keyboardType: TextInputType.emailAddress,
                              decoration: InputDecoration(
                                labelText: 'البريد الإلكتروني',
                                prefixIcon: const Icon(Icons.email_outlined),
                                filled: true,
                                fillColor: Theme.of(context).colorScheme.surface.withValues(alpha: 0.95),
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(14),
                                  borderSide: BorderSide.none,
                                ),
                              ),
                            ),
                            const SizedBox(height: 12),
                            TextField(
                              controller: _passwordController,
                              obscureText: _obscure,
                              decoration: InputDecoration(
                                labelText: 'كلمة المرور',
                                prefixIcon: const Icon(Icons.lock_outline),
                                suffixIcon: IconButton(
                                  icon: Icon(_obscure ? Icons.visibility : Icons.visibility_off),
                                  onPressed: () => setState(() => _obscure = !_obscure),
                                ),
                                filled: true,
                                fillColor: Theme.of(context).colorScheme.surface.withValues(alpha: 0.95),
                                border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(14),
                                  borderSide: BorderSide.none,
                                ),
                              ),
                            ),
                            const SizedBox(height: 16),

                            // Error Message
                            if (_errorMessage.isNotEmpty)
                              Container(
                                width: double.infinity,
                                padding: const EdgeInsets.all(12),
                                margin: const EdgeInsets.only(bottom: 8),
                                decoration: BoxDecoration(
                                  color: Colors.red[50],
                                  borderRadius: BorderRadius.circular(12),
                                  border: Border.all(color: Colors.red[200]!),
                                ),
                                child: Text(
                                  _errorMessage,
                                  textAlign: TextAlign.right,
                                  style: TextStyle(color: Colors.red[700]),
                                ),
                              ),

                            // Login Button
                            SizedBox(
                              width: double.infinity,
                              child: FilledButton.icon(
                                onPressed: _isLoading ? null : _login,
                                icon: _isLoading
                                    ? const SizedBox(
                                        width: 18,
                                        height: 18,
                                        child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
                                      )
                                    : const Icon(Icons.login),
                                label: const Text('تسجيل الدخول'),
                                style: FilledButton.styleFrom(
                                  padding: const EdgeInsets.symmetric(vertical: 14),
                                ),
                              ),
                            ),

                            const SizedBox(height: 12),
                            Row(
                              children: [
                                Expanded(child: Divider(color: Colors.grey[300])),
                                const Padding(
                                  padding: EdgeInsets.symmetric(horizontal: 8.0),
                                  child: Text('أو'),
                                ),
                                Expanded(child: Divider(color: Colors.grey[300])),
                              ],
                            ),
                            const SizedBox(height: 12),
                            Row(
                              children: [
                                Expanded(
                                  child: OutlinedButton.icon(
                                    onPressed: () => Navigator.push(
                                      context,
                                      MaterialPageRoute(builder: (_) => const RegisterJobSeekerScreen()),
                                    ),
                                    icon: const Icon(Icons.person_add_alt_1),
                                    label: const Text('إنشاء حساب كباحث عن عمل'),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 8),
                            Row(
                              children: [
                                Expanded(
                                  child: OutlinedButton.icon(
                                    onPressed: () => Navigator.push(
                                      context,
                                      MaterialPageRoute(builder: (_) => const RegisterCompanyScreen()),
                                    ),
                                    icon: const Icon(Icons.apartment),
                                    label: const Text('إنشاء حساب كشركة'),
                                  ),
                                ),
                              ],
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }
}

class RegisterJobSeekerScreen extends StatefulWidget {
  const RegisterJobSeekerScreen({super.key});
  @override
  State<RegisterJobSeekerScreen> createState() => _RegisterJobSeekerScreenState();
}

class _RegisterJobSeekerScreenState extends State<RegisterJobSeekerScreen> {

  @override
  Widget build(BuildContext context) {
    final site = AppConfig.baseUrl.replaceFirst('api/v1/', '');
    final url = '${site}register?role=jobseeker';
    return AdminWebViewScreen(
      title: 'إنشاء حساب - باحث عن عمل',
      url: url,
    );
  }
}

class RegisterCompanyScreen extends StatefulWidget {
  const RegisterCompanyScreen({super.key});
  @override
  State<RegisterCompanyScreen> createState() => _RegisterCompanyScreenState();
}

class _RegisterCompanyScreenState extends State<RegisterCompanyScreen> {

  @override
  Widget build(BuildContext context) {
    final site = AppConfig.baseUrl.replaceFirst('api/v1/', '');
    final url = '${site}register?role=company';
    return AdminWebViewScreen(
      title: 'إنشاء حساب - شركة',
      url: url,
    );
  }
}


class JobsScreen extends StatefulWidget {
  final String token;
  final Map<String, dynamic> user;

  const JobsScreen({super.key, required this.token, required this.user});

  @override
  State<JobsScreen> createState() => _JobsScreenState();
}

class _JobsScreenState extends State<JobsScreen> {
  List<dynamic> jobs = [];
  bool isLoading = true;
  String errorMessage = '';

  // Search and Filter variables
  final TextEditingController _searchController = TextEditingController();
  String? _selectedProvince;
  String? _selectedSpeciality;
  String _sortBy = 'id';
  String _sortOrder = 'desc';
  int _currentPage = 1;
  int _lastPage = 1;
  bool _isLoadingMore = false;
  final Set<int> _favoriteIds = <int>{};



  // Available options for filters
  final List<String> _provinces = [
    'بغداد', 'البصرة', 'أربيل', 'الموصل', 'النجف', 'كربلاء', 'الأنبار', 'ديالى', 'صلاح الدين', 'واسط', 'ميسان', 'ذي قار', 'المثنى', 'القادسية', 'بابل', 'كركوك', 'السليمانية', 'دهوك'
  ];

  final List<String> _specialities = [
    'General Practitioner', 'Pediatrics', 'Cardiologist', 'Nurses', 'Pharmacist', 'General Surgery', 'Radiology', 'Obstetrics and Gynecology', 'Medical Laboratory', 'Dentistry'
  ];

  @override
  void initState() {
    super.initState();
    // Preload favorites for jobseekers (optional best-effort)
    if (widget.user['role'] == 'jobseeker') {
      _loadFavorites();
    }

    _loadJobs();
  }

  Future<void> _refreshJobs() async {
    // Clear cache for current filters then reload
    try {
      await JobsCache.instance.clearForParams(
        search: _searchController.text.isNotEmpty ? _searchController.text : null,
        province: _selectedProvince,
        speciality: _selectedSpeciality,
        sortBy: _sortBy,
        sortOrder: _sortOrder,
        page: null,
      );
    } catch (_) {}
    await _loadJobs();
  }

  Future<void> _loadJobs() async {
    setState(() {
      isLoading = true;
      errorMessage = '';
    });

    try {
      // Build query parameters
      Map<String, String> queryParams = {
        'sort_by': _sortBy,
        'sort_order': _sortOrder,
      };

      if (_searchController.text.isNotEmpty) {
        queryParams['search'] = _searchController.text;
      }

      if (_selectedProvince != null) {
        queryParams['province'] = _selectedProvince!;
      }

      if (_selectedSpeciality != null) {
        queryParams['speciality'] = _selectedSpeciality!;
      }

      final res = await JobsService().listJobs(
        token: widget.token,
        search: _searchController.text.isNotEmpty ? _searchController.text : null,
        province: _selectedProvince,
        speciality: _selectedSpeciality,
        sortBy: _sortBy,
        sortOrder: _sortOrder,
      );

      if (res['success'] == true) {
        final data = res['data'] as Map<String, dynamic>?;
        setState(() {
          jobs = (data?['data'] as List?) ?? [];
          _currentPage = (data?['current_page'] as int?) ?? 1;
          _lastPage = (data?['last_page'] as int?) ?? 1;
          isLoading = false;
        });
      } else {
        setState(() {
          errorMessage = (res['message'] as String?) ?? 'فشل في تحميل الوظائف';
          isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        errorMessage = 'خطأ في الاتصال: $e';
        isLoading = false;
      });
    }
  }

  Future<void> _loadMoreJobs() async {
    if (_isLoadingMore || _currentPage >= _lastPage) return;
    setState(() {
      _isLoadingMore = true;
    });
    try {
      final res = await JobsService().listJobs(
        token: widget.token,
        search: _searchController.text.isNotEmpty ? _searchController.text : null,
        province: _selectedProvince,
        speciality: _selectedSpeciality,
        sortBy: _sortBy,
        sortOrder: _sortOrder,
        page: _currentPage + 1,
      );
      if (res['success'] == true) {
        final data = res['data'] as Map<String, dynamic>?;
        final List<dynamic> more = (data?['data'] as List?) ?? [];
        setState(() {
          jobs = [...jobs, ...more];
          _currentPage = (data?['current_page'] as int?) ?? _currentPage;
          _lastPage = (data?['last_page'] as int?) ?? _lastPage;
        });
      }
    } catch (_) {
      // ignore errors for load more; keep current list
    } finally {
      if (mounted) {
        setState(() {
          _isLoadingMore = false;
        });
      }
    }
  }

  Future<void> _loadFavorites() async {
    if (widget.user['role'] != 'jobseeker') return;
    try {
      final res = await FavoritesService().listFavorites(token: widget.token, page: 1);
      final data = res['data'];
      final Set<int> ids = <int>{};
      if (data is Map<String, dynamic>) {
        final list = (data['data'] as List?) ?? const [];
        for (final item in list) {
          if (item is Map<String, dynamic>) {
            final job = item['job'];
            if (job is Map<String, dynamic>) {
              final id = job['id'];
              if (id is num) {
                ids.add(id.toInt());
              } else if (id != null) {
                final p = int.tryParse('$id');
                if (p != null) ids.add(p);
              }
            } else {
              final id = item['id'];
              if (id is num) {
                ids.add(id.toInt());
              } else if (id != null) {
                final p = int.tryParse('$id');
                if (p != null) ids.add(p);
              }
            }
          }
        }
      } else if (data is List) {
        for (final item in data) {
          if (item is Map<String, dynamic>) {
            final job = item['job'];
            if (job is Map<String, dynamic>) {
              final id = job['id'];
              if (id is num) {
                ids.add(id.toInt());
              } else if (id != null) {
                final p = int.tryParse('$id');
                if (p != null) ids.add(p);
              }
            } else {
              final id = item['id'];
              if (id is num) {
                ids.add(id.toInt());
              } else if (id != null) {
                final p = int.tryParse('$id');
                if (p != null) ids.add(p);
              }
            }
          }
        }
      }
      if (!mounted) return;
      setState(() {
        _favoriteIds
          ..clear()
          ..addAll(ids);
      });
    } catch (_) {
      // ignore failures silently; keep icons default
    }
  }

  Future<void> _logout() async {
    try {
      // Unregister FCM token before logout
      final auth = AuthService();
      await auth.unregisterFCMTokenOnLogout(widget.token);
    } catch (e) {
      debugPrint('Failed to unregister FCM token on logout: $e');
      // Don't block logout flow if FCM unregistration fails
    }

    if (!mounted) return;
    Navigator.pushReplacement(
      context,
      MaterialPageRoute(builder: (context) => const LoginScreen()),
    );
  }

  void _showSearchDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('البحث والفلترة'),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // Search field
              TextField(
                controller: _searchController,
                decoration: const InputDecoration(
                  labelText: 'البحث في الوظائف',
                  hintText: 'ادخل كلمة البحث...',
                  prefixIcon: Icon(Icons.search),
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 16),

              // Province filter
              DropdownButtonFormField<String>(
                value: _selectedProvince,
                decoration: const InputDecoration(
                  labelText: 'المحافظة',
                  border: OutlineInputBorder(),
                ),
                items: [
                  const DropdownMenuItem<String>(
                    value: null,
                    child: Text('جميع المحافظات'),
                  ),
                  ..._provinces.map((province) => DropdownMenuItem<String>(
                    value: province,
                    child: Text(province),
                  )),
                ],
                onChanged: (value) {
                  setState(() {
                    _selectedProvince = value;
                  });
                },
              ),
              const SizedBox(height: 16),

              // Speciality filter
              DropdownButtonFormField<String>(
                value: _selectedSpeciality,
                decoration: const InputDecoration(
                  labelText: 'التخصص',
                  border: OutlineInputBorder(),
                ),
                items: [
                  const DropdownMenuItem<String>(
                    value: null,
                    child: Text('جميع التخصصات'),
                  ),
                  ..._specialities.map((speciality) => DropdownMenuItem<String>(
                    value: speciality,
                    child: Text(_getSpecialityName(speciality)),
                  )),
                ],
                onChanged: (value) {
                  setState(() {
                    _selectedSpeciality = value;
                  });
                },
              ),
              const SizedBox(height: 16),

              // Sort options
              DropdownButtonFormField<String>(
                value: '$_sortBy-$_sortOrder',
                decoration: const InputDecoration(
                  labelText: 'ترتيب النتائج',
                  border: OutlineInputBorder(),
                ),
                items: const [
                  DropdownMenuItem<String>(
                    value: 'id-desc',
                    child: Text('الأحدث أولاً'),
                  ),
                  DropdownMenuItem<String>(
                    value: 'id-asc',
                    child: Text('الأقدم أولاً'),
                  ),
                  DropdownMenuItem<String>(
                    value: 'title-asc',
                    child: Text('العنوان (أ-ي)'),
                  ),
                  DropdownMenuItem<String>(
                    value: 'title-desc',
                    child: Text('العنوان (ي-أ)'),
                  ),
                ],
                onChanged: (value) {
                  if (value != null) {
                    final parts = value.split('-');
                    setState(() {
                      _sortBy = parts[0];
                      _sortOrder = parts[1];
                    });
                  }
                },
              ),
            ],
          ),
        ),
        actions: [
          TextButton(
            onPressed: () {
              setState(() {
                _searchController.clear();
                _selectedProvince = null;
                _selectedSpeciality = null;
                _sortBy = 'id';
                _sortOrder = 'desc';
              });
              Navigator.pop(context);
              _loadJobs();
            },
            child: const Text('مسح الفلاتر'),
          ),
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إلغاء'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _loadJobs();
            },
            child: const Text('بحث'),
          ),
        ],
      ),
    );
  }

  String _getSpecialityName(String speciality) {
    switch (speciality) {
      case 'General Practitioner':
        return 'طبيب عام';
      case 'Pediatrics':
        return 'طبيب أطفال';
      case 'Cardiologist':
        return 'طبيب قلب';
      case 'Nurses':
        return 'ممرض/ممرضة';
      case 'Pharmacist':
        return 'صيدلي';
      case 'General Surgery':
        return 'طبيب جراحة عامة';
      case 'Radiology':
        return 'أخصائي أشعة';
      case 'Obstetrics and Gynecology':
        return 'طبيب نساء وولادة';
      case 'Medical Laboratory':
        return 'فني مختبر طبي';
      case 'Dentistry':
        return 'طبيب أسنان';
      default:
        return speciality;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('الوظائف المتاحة (${jobs.length})'),
        backgroundColor: Theme.of(context).colorScheme.primary,
        foregroundColor: Theme.of(context).colorScheme.onPrimary,
        actions: [
          // 1) Search
          IconButton(
            icon: const Icon(Icons.search),
            onPressed: _showSearchDialog,
          ),
          // 2) Profile (always visible, placed early to avoid truncation on small screens)
          IconButton(
            icon: const Icon(Icons.person),
            tooltip: 'الملف الشخصي',
            onPressed: () {
              Navigator.push(
                context,
                MaterialPageRoute(
                  builder: (context) => ProfileScreen(
                    token: widget.token,
                    user: widget.user,
                  ),
                ),
              );
            },
          ),
          // 3) Company dashboard
          if (widget.user['role'] == 'company')
            IconButton(
              icon: const Icon(Icons.dashboard),
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => CompanyDashboardScreen(
                      token: widget.token,
                      user: widget.user,
                    ),
                  ),
                );
              },
            ),
          // 4) Jobseeker quick actions
          if (widget.user['role'] == 'jobseeker') ...[
            IconButton(
              icon: const Icon(Icons.favorite),
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => FavoritesScreen(
                      token: widget.token,
                      user: widget.user,
                    ),
                  ),
                );
              },
            ),
            IconButton(
              icon: const Icon(Icons.assignment),
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => MyApplicationsScreen(
                      token: widget.token,
                      user: widget.user,
                    ),
                  ),
                );
              },
            ),
          ],
          // 5) Refresh
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _refreshJobs,
          ),
          // 6) Logout
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: _logout,
          ),
        ],
      ),
      body: Column(
        children: [
          // Active filters indicator
          if (_hasActiveFilters())
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              color: Theme.of(context).colorScheme.primary.withValues(alpha: 0.08),
              child: Row(
                children: [
                  Icon(Icons.filter_list, size: 16, color: Theme.of(context).colorScheme.primary),
                  const SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      _getActiveFiltersText(),
                      style: TextStyle(color: Theme.of(context).colorScheme.primary, fontSize: 12),
                    ),
                  ),
                  TextButton(
                    onPressed: () {
                      setState(() {
                        _searchController.clear();
                        _selectedProvince = null;

                        _selectedSpeciality = null;
                        _sortBy = 'id';
                        _sortOrder = 'desc';
                      });
                      _loadJobs();
                    },
                    child: const Text('مسح', style: TextStyle(fontSize: 12)),
                  ),
                ],
              ),
            ),

          // Main content
          Expanded(
            child: isLoading
                ? const Center(child: CircularProgressIndicator())
                : errorMessage.isNotEmpty
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text(
                              errorMessage,
                              style: const TextStyle(color: Colors.red),
                              textAlign: TextAlign.center,
                            ),
                            const SizedBox(height: 16),
                            ElevatedButton(
                              onPressed: _loadJobs,
                              child: const Text('إعادة المحاولة'),
                            ),
                          ],
                        ),
                      )
                    : jobs.isEmpty
                        ? Center(
                            child: Column(
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                const Text('لا توجد نتائج مطابقة'),
                                const SizedBox(height: 8),
                                const Text('جرّب تعديل الفلاتر أو استخدام كلمة بحث أكثر عمومية', style: TextStyle(fontSize: 12, color: Colors.grey)),
                                const SizedBox(height: 8),
                                if (_searchController.text.isNotEmpty || _selectedProvince != null || _selectedSpeciality != null)
                                  OutlinedButton(
                                    onPressed: () {
                                      setState(() {
                                        _searchController.clear();
                                        _selectedProvince = null;
                                        _selectedSpeciality = null;
                                        _sortBy = 'id';
                                        _sortOrder = 'desc';
                                      });
                                      _loadJobs();
                                    },
                                    child: const Text('مسح الفلاتر'),
                                  ),
                              ],
                            ),
                          )
                        : RefreshIndicator(
                          onRefresh: _loadJobs,
                          child: ListView.builder(
                      padding: const EdgeInsets.all(16),
                      itemCount: jobs.length + ((_currentPage < _lastPage) ? 1 : 0),
                      itemBuilder: (context, index) {
                        if (index >= jobs.length) {
                          return Padding(
                            padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
                            child: SizedBox(
                              width: double.infinity,
                              child: OutlinedButton(
                                onPressed: _isLoadingMore ? null : _loadMoreJobs,
                                child: _isLoadingMore
                                    ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2))
                                    : const Text('\u062a\u062d\u0645\u064a\u0644 \u0627\u0644\u0645\u0632\u064a\u062f'),
                              ),
                            ),
                          );
                        }

                        final job = jobs[index];
                        return Card(
                          margin: const EdgeInsets.only(bottom: 16),
                          elevation: 2,
                          child: Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Expanded(
                                      child: Text(

                                        job['title'] ?? '',
                                        style: const TextStyle(
                                          fontSize: 18,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ),
                                    if (widget.user['role'] == 'jobseeker')
                                      IconButton(
                                        icon: Icon(
                                          _favoriteIds.contains((job['id'] is num) ? (job['id'] as num).toInt() : (int.tryParse('${job['id']}') ?? -1))
                                              ? Icons.favorite
                                              : Icons.favorite_border,
                                          color: Colors.red[400],
                                          size: 20,
                                        ),
                                        onPressed: () => _toggleFavorite((job['id'] is num) ? (job['id'] as num).toInt() : (int.tryParse('${job['id']}') ?? -1)),
                                        padding: EdgeInsets.zero,
                                        constraints: const BoxConstraints(),
                                      ),
                                  ],
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  job['company']?['company_name'] ?? 'شركة غير محددة',
                                  style: TextStyle(
                                    fontSize: 14,
                                    color: Theme.of(context).colorScheme.primary,
                                  ),
                                ),
                                const SizedBox(height: 8),
                                Row(
                                  children: [
                                    Icon(Icons.location_on, size: 16, color: Colors.grey[600]),
                                    const SizedBox(width: 4),
                                    Text(
                                      job['province'] ?? '',
                                      style: TextStyle(color: Colors.grey[600]),
                                    ),
                                    const SizedBox(width: 16),
                                    Icon(Icons.work, size: 16, color: Colors.grey[600]),
                                    const SizedBox(width: 4),
                                    Expanded(
                                      child: Text(
                                        job['speciality'] ?? '',
                                        style: TextStyle(color: Colors.grey[600]),
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 12),
                                Text(
                                  job['description'] ?? '',
                                  style: const TextStyle(fontSize: 14),
                                  maxLines: 3,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 12),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 8,
                                        vertical: 4,
                                      ),
                                      decoration: BoxDecoration(

                                        color: job['status'] == 'open' ? Colors.green[50] : Colors.red[50],
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      child: Text(
                                        job['status'] == 'open' ? 'متاحة' : 'مغلقة',
                                        style: TextStyle(
                                          fontSize: 12,
                                          color: job['status'] == 'open' ? Colors.green[700] : Colors.red[700],
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ),
                                    ElevatedButton(
                                      onPressed: () {
                                        Navigator.push(
                                          context,
                                          MaterialPageRoute(
                                            builder: (context) => JobDetailsScreen(
                                              job: job,
                                              token: widget.token,
                                              user: widget.user,
                                            ),
                                          ),
                                        );
                                      },
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: Theme.of(context).colorScheme.primary,
                                        foregroundColor: Theme.of(context).colorScheme.onPrimary,
                                      ),
                                      child: const Text('عرض التفاصيل'),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    )),
          ),
        ],
      ),
    );
  }

  bool _hasActiveFilters() {
    return _searchController.text.isNotEmpty ||
           _selectedProvince != null ||
           _selectedSpeciality != null ||
           _sortBy != 'id' ||
           _sortOrder != 'desc';
  }

  String _getActiveFiltersText() {
    List<String> filters = [];

    if (_searchController.text.isNotEmpty) {
      filters.add('البحث: ${_searchController.text}');
    }

    if (_selectedProvince != null) {
      filters.add('المحافظة: $_selectedProvince');
    }

    if (_selectedSpeciality != null) {
      filters.add('التخصص: ${_getSpecialityName(_selectedSpeciality!)}');
    }

    if (_sortBy != 'id' || _sortOrder != 'desc') {
      String sortText = _sortBy == 'title' ? 'العنوان' : 'التاريخ';
      String orderText = _sortOrder == 'asc' ? 'تصاعدي' : 'تنازلي';
      filters.add('الترتيب: $sortText ($orderText)');
    }

    return filters.join(' • ');
  }

  Future<void> _toggleFavorite(int jobId) async {
    if (jobId <= 0) return;
    final favs = FavoritesService();
    final bool isFav = _favoriteIds.contains(jobId);
    try {
      if (isFav) {
        final rem = await favs.removeFavorite(token: widget.token, jobId: jobId);
        if (rem['success'] == true) {
          if (!mounted) return;
          setState(() { _favoriteIds.remove(jobId); });
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('تم إزالة الوظيفة من المفضلة')),
          );
        } else {
          if (!mounted) return;
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text((rem['message'] as String?) ?? 'تعذر إزالة المفضلة')),
          );
        }
      } else {
        final add = await favs.addFavorite(token: widget.token, jobId: jobId);
        if (add['success'] == true || add['statusCode'] == 409) {
          // 409 = موجودة مسبقًا
          if (!mounted) return;
          setState(() { _favoriteIds.add(jobId); });
          ScaffoldMessenger.of(context).showSnackBar(
            const SnackBar(content: Text('تم إضافة الوظيفة للمفضلة')),
          );
        } else {
          if (!mounted) return;
          ScaffoldMessenger.of(context).showSnackBar(
            SnackBar(content: Text((add['message'] as String?) ?? 'تعذر إضافة إلى المفضلة')),
          );
        }
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('خطأ في الاتصال: $e')),
      );
    }
  }
}

class JobDetailsScreen extends StatefulWidget {
  final Map<String, dynamic> job;
  final String token;
  final Map<String, dynamic> user;

  const JobDetailsScreen({super.key, required this.job, required this.token, required this.user});

  @override
  State<JobDetailsScreen> createState() => _JobDetailsScreenState();
}

class _JobDetailsScreenState extends State<JobDetailsScreen> {
  bool _isApplying = false;

  Future<void> _applyForJob() async {
    if (widget.user['role'] != 'jobseeker') {
      _showMessage('يمكن للباحثين عن العمل فقط التقديم على الوظائف', isError: true);
      return;
    }

    setState(() {
      _isApplying = true;
    });

    try {
      final jobId = (widget.job['id'] is num) ? (widget.job['id'] as num).toInt() : int.tryParse('${widget.job['id']}') ?? 0;
      final res = await ApplicationsService().applyToJob(
        token: widget.token,
        jobId: jobId,
      );

      if (res['success'] == true && (res['statusCode'] == 201 || res['statusCode'] == 200)) {
        _showMessage('تم التقديم على الوظيفة بنجاح!', isError: false);
      } else {
        _showMessage((res['message'] as String?) ?? 'فشل في التقديم على الوظيفة', isError: true);
      }
    } catch (e) {
      _showMessage('خطأ في الاتصال: $e', isError: true);
    }

    setState(() {
      _isApplying = false;
    });
  }

  void _showMessage(String message, {required bool isError}) {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text(isError ? 'خطأ' : 'نجح'),
        content: Text(message),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('موافق'),
          ),
        ],
      ),
    );
  }

  void _showApplicationDialog() {
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('تأكيد التقديم'),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text('هل أنت متأكد من التقديم على وظيفة "${widget.job['title']}"؟'),
            const SizedBox(height: 16),
            const Text(
              'ملاحظة: سيتم استخدام ملفك الشخصي الحالي للتقديم.',
              style: TextStyle(fontSize: 12, color: Colors.grey),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: const Text('إلغاء'),
          ),
          ElevatedButton(
            onPressed: () {
              Navigator.pop(context);
              _applyForJob();
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.green,
              foregroundColor: Colors.white,
            ),
            child: const Text('تأكيد التقديم'),
          ),
        ],
      ),
    );
  }


  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text(widget.job['title'] ?? ''),
        backgroundColor: Theme.of(context).colorScheme.primary,
        foregroundColor: Theme.of(context).colorScheme.onPrimary,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Job Title
            Text(
              widget.job['title'] ?? '',
              style: const TextStyle(
                fontSize: 24,
                fontWeight: FontWeight.bold,
              ),
            ),
            const SizedBox(height: 16),

            // Quick tags
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: [
                if ((widget.job['province'] ?? '').toString().isNotEmpty)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    decoration: BoxDecoration(
                      color: Theme.of(context).colorScheme.primary.withValues(alpha: 0.08),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Text(
                      '\u0627\u0644\u0645\u062d\u0627\u0641\u0638\u0629: ${widget.job['province']}',
                      style: TextStyle(color: Theme.of(context).colorScheme.primary),
                    ),
                  ),
                if ((widget.job['speciality'] ?? '').toString().isNotEmpty)
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                    decoration: BoxDecoration(
                      color: Theme.of(context).colorScheme.secondary.withValues(alpha: 0.08),
                      borderRadius: BorderRadius.circular(16),
                    ),
                    child: Text(
                      '\u0627\u0644\u062a\u062e\u0635\u0635: ${widget.job['speciality']}',
                      style: TextStyle(color: Theme.of(context).colorScheme.secondary),
                    ),
                  ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
                  decoration: BoxDecoration(
                    color: (widget.job['status'] == 'open' ? Colors.green[50] : Colors.red[50]),
                    borderRadius: BorderRadius.circular(16),
                  ),
                  child: Text(
                    widget.job['status'] == 'open' ? '\u0645\u062a\u0627\u062d\u0629' : '\u0645\u063a\u0644\u0642\u0629',
                    style: TextStyle(
                      color: widget.job['status'] == 'open' ? Colors.green[700] : Colors.red[700],
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: 16),


            // Company Info
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'معلومات الشركة',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Theme.of(context).colorScheme.primary,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      'اسم الشركة: ${widget.job['company']?['company_name'] ?? 'غير محدد'}',
                      style: const TextStyle(fontSize: 16),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'المحافظة: ${widget.job['company']?['province'] ?? 'غير محدد'}',
                      style: const TextStyle(fontSize: 16),
                    ),
                    const SizedBox(height: 4),
                    Text(
                      'القطاع: ${widget.job['company']?['industry'] ?? 'غير محدد'}',
                      style: const TextStyle(fontSize: 16),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Job Details
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'تفاصيل الوظيفة',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Theme.of(context).colorScheme.primary,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Icon(Icons.work, color: Colors.grey[600]),
                        const SizedBox(width: 8),
                        Text(
                          'التخصص: ${widget.job['speciality'] ?? 'غير محدد'}',
                          style: const TextStyle(fontSize: 16),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Icon(Icons.location_on, color: Colors.grey[600]),
                        const SizedBox(width: 8),
                        Text(
                          'المحافظة: ${widget.job['province'] ?? 'غير محدد'}',
                          style: const TextStyle(fontSize: 16),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    Row(
                      children: [
                        Icon(Icons.info, color: Colors.grey[600]),
                        const SizedBox(width: 8),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                          decoration: BoxDecoration(
                            color: widget.job['status'] == 'open' ? Colors.green[50] : Colors.red[50],
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(
                            widget.job['status'] == 'open' ? 'متاحة' : 'مغلقة',
                            style: TextStyle(
                              color: widget.job['status'] == 'open' ? Colors.green[700] : Colors.red[700],
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Job Description
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'وصف الوظيفة',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Theme.of(context).colorScheme.primary,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      widget.job['description'] ?? 'لا يوجد وصف متاح',
                      style: const TextStyle(fontSize: 16),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),

            // Job Requirements
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'متطلبات الوظيفة',
                      style: TextStyle(
                        fontSize: 18,
                        fontWeight: FontWeight.bold,
                        color: Theme.of(context).colorScheme.primary,
                      ),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      widget.job['requirements'] ?? 'لا توجد متطلبات محددة',
                      style: const TextStyle(fontSize: 16),
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 24),

            // Apply Button
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: (widget.job['status'] == 'open' && !_isApplying) ? _showApplicationDialog : null,
                style: ElevatedButton.styleFrom(
                  backgroundColor: widget.job['status'] == 'open'
                      ? Theme.of(context).colorScheme.secondary
                      : Colors.grey,
                  foregroundColor: Theme.of(context).colorScheme.onSecondary,
                  padding: const EdgeInsets.symmetric(vertical: 16),
                ),
                child: _isApplying
                    ? const CircularProgressIndicator(color: Colors.white)
                    : Text(
                        widget.job['status'] == 'open' ? 'تقديم على الوظيفة' : 'الوظيفة مغلقة',
                        style: const TextStyle(fontSize: 16),
                      ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class MyApplicationsScreen extends StatefulWidget {
  final String token;
  final Map<String, dynamic> user;

  const MyApplicationsScreen({super.key, required this.token, required this.user});

  @override
  State<MyApplicationsScreen> createState() => _MyApplicationsScreenState();
}

class _MyApplicationsScreenState extends State<MyApplicationsScreen> {
  List<dynamic> applications = [];
  bool isLoading = true;
  String errorMessage = '';

  @override
  void initState() {
    super.initState();
    _loadApplications();
  }

  Future<void> _loadApplications() async {
    setState(() {
      isLoading = true;
      errorMessage = '';
    });

    try {
      final response = await http.get(
        Uri.parse('${AppConfig.baseUrl}applications/my-applications'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer ${widget.token}',
        },
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        if (data['success'] == true) {
          setState(() {
            applications = data['data']['data'] ?? [];
            isLoading = false;
          });
        } else {
          setState(() {
            errorMessage = data['message'] ?? 'فشل في تحميل الطلبات';
            isLoading = false;
          });
        }
      } else {
        setState(() {
          errorMessage = 'خطأ في الاتصال بالخادم';
          isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        errorMessage = 'خطأ في الاتصال: $e';
        isLoading = false;
      });
    }
  }

  String _getStatusText(String status) {
    switch (status) {
      case 'pending':
        return 'قيد المراجعة';
      case 'reviewed':
        return 'تمت المراجعة';
      case 'shortlisted':
        return 'مرشح للمقابلة';
      case 'rejected':
        return 'مرفوض';
      case 'hired':
        return 'تم التوظيف';
      default:
        return status;
    }
  }

  Color _getStatusColor(String status) {
    switch (status) {
      case 'pending':
        return Colors.orange;
      case 'reviewed':
        return Colors.blue;
      case 'shortlisted':
        return Colors.green;
      case 'rejected':
        return Colors.red;
      case 'hired':
        return Colors.purple;
      default:
        return Colors.grey;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('طلباتي (${applications.length})'),
        backgroundColor: Colors.blue,
        foregroundColor: Colors.white,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadApplications,
          ),
        ],
      ),
      body: isLoading
          ? const Center(child: CircularProgressIndicator())
          : errorMessage.isNotEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        errorMessage,
                        style: const TextStyle(color: Colors.red),
                        textAlign: TextAlign.center,
                      ),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadApplications,
                        child: const Text('إعادة المحاولة'),
                      ),
                    ],
                  ),
                )
              : applications.isEmpty
                  ? const Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.assignment, size: 64, color: Colors.grey),
                          SizedBox(height: 16),
                          Text(
                            'لم تتقدم لأي وظيفة بعد',
                            style: TextStyle(fontSize: 18, color: Colors.grey),
                          ),
                        ],
                      ),
                    )
                  : ListView.builder(
                      padding: const EdgeInsets.all(16),
                      itemCount: applications.length,
                      itemBuilder: (context, index) {
                        final application = applications[index];
                        final job = application['job'];
                        return Card(
                          margin: const EdgeInsets.only(bottom: 16),
                          elevation: 2,
                          child: Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Expanded(
                                      child: Text(
                                        job['title'] ?? '',
                                        style: const TextStyle(
                                          fontSize: 18,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ),
                                    Container(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 8,
                                        vertical: 4,
                                      ),
                                      decoration: BoxDecoration(
                                        color: _getStatusColor(application['status']).withValues(alpha: 0.1),
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      child: Text(
                                        _getStatusText(application['status']),
                                        style: TextStyle(
                                          fontSize: 12,
                                          color: _getStatusColor(application['status']),
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  job['company']?['company_name'] ?? 'شركة غير محددة',
                                  style: TextStyle(
                                    fontSize: 14,
                                    color: Colors.blue[700],
                                  ),
                                ),
                                const SizedBox(height: 8),
                                Row(
                                  children: [
                                    Icon(Icons.location_on, size: 16, color: Colors.grey[600]),
                                    const SizedBox(width: 4),
                                    Text(
                                      job['province'] ?? '',
                                      style: TextStyle(color: Colors.grey[600]),
                                    ),
                                    const SizedBox(width: 16),
                                    Icon(Icons.calendar_today, size: 16, color: Colors.grey[600]),
                                    const SizedBox(width: 4),
                                    Text(
                                      application['applied_at']?.substring(0, 10) ?? '',
                                      style: TextStyle(color: Colors.grey[600]),
                                    ),
                                  ],
                                ),
                                if (application['matching_percentage'] != null) ...[
                                  const SizedBox(height: 8),
                                  Row(
                                    children: [
                                      Icon(Icons.percent, size: 16, color: Colors.grey[600]),
                                      const SizedBox(width: 4),
                                      Text(
                                        'نسبة التطابق: ${application['matching_percentage']}%',
                                        style: TextStyle(color: Colors.grey[600]),
                                      ),
                                    ],
                                  ),
                                ],
                                if (application['notes'] != null && application['notes'].isNotEmpty) ...[
                                  const SizedBox(height: 8),
                                  Container(
                                    padding: const EdgeInsets.all(8),
                                    decoration: BoxDecoration(
                                      color: Colors.grey[100],
                                      borderRadius: BorderRadius.circular(8),
                                    ),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        const Text(
                                          'ملاحظات الشركة:',
                                          style: TextStyle(fontWeight: FontWeight.bold),
                                        ),
                                        const SizedBox(height: 4),
                                        Text(application['notes']),
                                      ],
                                    ),
                                  ),
                                ],
                              ],
                            ),
                          ),
                        );
                      },
                    ),
    );
  }
}

class FavoritesScreen extends StatefulWidget {
  final String token;
  final Map<String, dynamic> user;

  const FavoritesScreen({super.key, required this.token, required this.user});

  @override
  State<FavoritesScreen> createState() => _FavoritesScreenState();
}

class _FavoritesScreenState extends State<FavoritesScreen> {
  List<dynamic> favorites = [];
  bool isLoading = true;
  String errorMessage = '';

  @override
  void initState() {
    super.initState();
    _loadFavorites();
  }

  Future<void> _loadFavorites() async {
    setState(() {
      isLoading = true;
      errorMessage = '';
    });

    try {
      final res = await FavoritesService().listFavorites(token: widget.token);
      if (res['success'] == true) {
        final data = res['data'] as Map<String, dynamic>?;
        setState(() {
          favorites = (data?['data'] as List?) ?? [];
          isLoading = false;
        });
      } else {
        setState(() {
          errorMessage = (res['message'] as String?) ?? 'فشل في تحميل المفضلة';
          isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        errorMessage = 'خطأ في الاتصال: $e';
        isLoading = false;
      });
    }
  }

  Future<void> _removeFavorite(int jobId) async {
    try {
      final res = await FavoritesService().removeFavorite(token: widget.token, jobId: jobId);
      if (res['success'] == true) {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('تم إزالة الوظيفة من المفضلة')),
        );
        _loadFavorites();
      } else {
        if (!mounted) return;
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('فشل في إزالة الوظيفة')),
        );
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('خطأ في الاتصال: $e')),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('المفضلة (${favorites.length})'),
        backgroundColor: Theme.of(context).colorScheme.primary,
        foregroundColor: Theme.of(context).colorScheme.onPrimary,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: _loadFavorites,
          ),
        ],
      ),
      body: isLoading
          ? const Center(child: CircularProgressIndicator())
          : errorMessage.isNotEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        errorMessage,
                        style: const TextStyle(color: Colors.red),
                        textAlign: TextAlign.center,
                      ),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadFavorites,
                        child: const Text('إعادة المحاولة'),
                      ),
                    ],
                  ),
                )
              : favorites.isEmpty
                  ? const Center(
                      child: Column(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.favorite_border, size: 64, color: Colors.grey),
                          SizedBox(height: 16),
                          Text(
                            'لا توجد وظائف في المفضلة',
                            style: TextStyle(fontSize: 18, color: Colors.grey),
                          ),
                        ],
                      ),
                    )
                  : ListView.builder(
                      padding: const EdgeInsets.all(16),
                      itemCount: favorites.length,
                      itemBuilder: (context, index) {
                        final favorite = favorites[index];
                        final job = favorite['job'];
                        return Card(
                          margin: const EdgeInsets.only(bottom: 16),
                          elevation: 2,
                          child: Padding(
                            padding: const EdgeInsets.all(16),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Expanded(
                                      child: Text(
                                        job['title'] ?? '',
                                        style: const TextStyle(
                                          fontSize: 18,
                                          fontWeight: FontWeight.bold,
                                        ),
                                      ),
                                    ),
                                    IconButton(
                                      icon: const Icon(Icons.favorite, color: Colors.red),
                                      onPressed: () => _removeFavorite(job['id']),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 8),
                                Text(
                                  job['company']?['company_name'] ?? 'شركة غير محددة',
                                  style: TextStyle(
                                    fontSize: 14,
                                    color: Colors.blue[700],
                                  ),
                                ),
                                const SizedBox(height: 8),
                                Row(
                                  children: [
                                    Icon(Icons.location_on, size: 16, color: Colors.grey[600]),
                                    const SizedBox(width: 4),
                                    Text(
                                      job['province'] ?? '',
                                      style: TextStyle(color: Colors.grey[600]),
                                    ),
                                    const SizedBox(width: 16),
                                    Icon(Icons.work, size: 16, color: Colors.grey[600]),
                                    const SizedBox(width: 4),
                                    Expanded(
                                      child: Text(
                                        job['speciality'] ?? '',
                                        style: TextStyle(color: Colors.grey[600]),
                                      ),
                                    ),
                                  ],
                                ),
                                const SizedBox(height: 12),
                                Text(
                                  job['description'] ?? '',
                                  style: const TextStyle(fontSize: 14),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                ),
                                const SizedBox(height: 12),
                                Row(
                                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                                  children: [
                                    Container(
                                      padding: const EdgeInsets.symmetric(
                                        horizontal: 8,
                                        vertical: 4,
                                      ),
                                      decoration: BoxDecoration(
                                        color: job['status'] == 'open' ? Colors.green : Colors.red,
                                        borderRadius: BorderRadius.circular(12),
                                      ),
                                      child: Text(
                                        job['status'] == 'open' ? 'متاحة' : 'مغلقة',
                                        style: const TextStyle(
                                          color: Colors.white,
                                          fontSize: 12,
                                        ),
                                      ),
                                    ),
                                    ElevatedButton(
                                      onPressed: () {
                                        Navigator.push(
                                          context,
                                          MaterialPageRoute(
                                            builder: (context) => JobDetailsScreen(
                                              job: job,
                                              token: widget.token,
                                              user: widget.user,
                                            ),
                                          ),
                                        );
                                      },
                                      style: ElevatedButton.styleFrom(
                                        backgroundColor: Colors.blue,
                                        foregroundColor: Colors.white,
                                      ),
                                      child: const Text('عرض التفاصيل'),
                                    ),
                                  ],
                                ),
                              ],
                            ),
                          ),
                        );
                      },
                    ),
    );
  }
}

class ProfileScreen extends StatefulWidget {
  final String token;
  final Map<String, dynamic> user;

  const ProfileScreen({super.key, required this.token, required this.user});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  Map<String, dynamic>? profileData;
  bool isLoading = true;
  String errorMessage = '';
  List<String> _toStringList(dynamic value) {
    if (value == null) return [];
    if (value is List) {
      return value.map((e) => (e ?? '').toString()).where((s) => s.isNotEmpty).toList();
    }
    if (value is String) {
      final s = value.trim();
      if (s.isEmpty) return [];
      return s.split(RegExp(r'[;,]')).map((e) => e.trim()).where((e) => e.isNotEmpty).toList();
    }
    return [value.toString()];
  }


  @override
  void initState() {
    super.initState();
    _loadProfile();
  }

  Future<void> _loadProfile() async {
    setState(() {
      isLoading = true;
      errorMessage = '';
    });

    final service = ProfileService();
    try {
      final res = await service.getProfile(token: widget.token);
      if (!mounted) return;
      if (res['success'] == true) {
        setState(() {
          final data = (res['data'] as Map<String, dynamic>?) ?? {};
          profileData = data;
          isLoading = false;
        });
      } else {
        setState(() {
          errorMessage = (res['message'] as String?) ?? 'فشل في تحميل الملف الشخصي';
          isLoading = false;
        });
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        errorMessage = 'خطأ في الاتصال: $e';
        isLoading = false;
      });
    } finally {
      service.close();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('الملف الشخصي'),
        backgroundColor: Theme.of(context).colorScheme.primary,
        foregroundColor: Theme.of(context).colorScheme.onPrimary,
        actions: [
          if (widget.user['role'] == 'jobseeker' || widget.user['role'] == 'company')
            IconButton(
              icon: const Icon(Icons.edit),
              onPressed: () {
                Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (context) => EditProfileScreen(
                      token: widget.token,
                      user: widget.user,
                      profileData: profileData,
                    ),
                  ),
                ).then((_) => _loadProfile()); // Reload after edit
              },
            ),
        ],
      ),
      body: isLoading
          ? const Center(child: CircularProgressIndicator())
          : errorMessage.isNotEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        errorMessage,
                        style: const TextStyle(color: Colors.red),
                        textAlign: TextAlign.center,
                      ),
                      const SizedBox(height: 16),
                      ElevatedButton(
                        onPressed: _loadProfile,
                        child: const Text('إعادة المحاولة'),
                      ),
                    ],
                  ),
                )
              : profileData == null
                  ? const Center(child: Text('لا توجد بيانات'))
                  : SingleChildScrollView(
                      padding: const EdgeInsets.all(16),
                      child: widget.user['role'] == 'jobseeker'
                          ? _buildJobSeekerProfile()
                          : _buildCompanyProfile(),
                    ),
    );
  }

  Widget _buildJobSeekerProfile() {
    final jobSeeker = profileData!['job_seeker'];
    if (jobSeeker == null) {
      return const Center(child: Text('لم يتم إنشاء الملف الشخصي بعد'));
    }

    final List<String> subsList = _toStringList(jobSeeker['specialities']);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Profile Image and Basic Info
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                CircleAvatar(
                  radius: 40,
                  backgroundImage: jobSeeker['profile_image'] != null
                      ? NetworkImage(jobSeeker['profile_image'])
                      : null,
                  child: jobSeeker['profile_image'] == null
                      ? Text(
                          (jobSeeker['full_name'] ?? 'U')[0].toUpperCase(),
                          style: const TextStyle(fontSize: 24),
                        )
                      : null,
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        jobSeeker['full_name'] ?? 'غير محدد',
                        style: const TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        jobSeeker['job_title'] ?? 'غير محدد',
                        style: TextStyle(
                          fontSize: 16,
                          color: Colors.grey[600],
                        ),
                      ),
                      const SizedBox(height: 4),
                      Row(
                        children: [
                          Icon(Icons.location_on, size: 16, color: Colors.grey[600]),
                          const SizedBox(width: 4),
                          Text(
                            jobSeeker['province'] ?? 'غير محدد',
                            style: TextStyle(color: Colors.grey[600]),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 16),

        // Contact Information
        _buildInfoCard(
          'معلومات الاتصال',
          Icons.contact_phone,
          [
            _buildInfoRow('البريد الإلكتروني', profileData!['email'] ?? 'غير محدد'),
            _buildInfoRow('الاسم الكامل', jobSeeker['full_name'] ?? 'غير محدد'),
          ],
        ),
        const SizedBox(height: 16),

        // Professional Information
        _buildInfoCard(
          'المعلومات المهنية',
          Icons.work,
          [
            _buildInfoRow('المسمى الوظيفي', jobSeeker['job_title'] ?? 'غير محدد'),
            _buildInfoRow('التخصص الرئيسي', jobSeeker['speciality'] ?? 'غير محدد'),
            if (subsList.isNotEmpty)
              _buildInfoRow('التخصصات الفرعية', subsList.join(', ')),
            _buildInfoRow('مستوى التعليم', jobSeeker['education_level'] ?? 'غير محدد'),
            _buildInfoRow('مستوى الخبرة', jobSeeker['experience_level'] ?? 'غير محدد'),
          ],
        ),
      ],
    );
  }

  Widget _buildCompanyProfile() {
    final company = profileData!['company'];
    if (company == null) {
      return const Center(child: Text('لم يتم إنشاء الملف الشخصي بعد'));
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Company Logo and Basic Info
        Card(
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                CircleAvatar(
                  radius: 40,
                  backgroundImage: company['profile_image'] != null
                      ? NetworkImage(company['profile_image'])
                      : null,
                  child: company['profile_image'] == null
                      ? Text(
                          (company['company_name'] ?? 'C')[0].toUpperCase(),
                          style: const TextStyle(fontSize: 24),
                        )
                      : null,
                ),
                const SizedBox(width: 16),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        company['company_name'] ?? 'غير محدد',
                        style: const TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 4),
                      Text(
                        company['scientific_office_name'] ?? 'غير محدد',
                        style: TextStyle(
                          fontSize: 16,
                          color: Colors.grey[600],
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildInfoCard(String title, IconData icon, List<Widget> children) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(icon, color: Colors.blue),
                const SizedBox(width: 8),
                Text(
                  title,
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 12),
            ...children,
          ],
        ),
      ),
    );
  }

  Widget _buildInfoRow(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 120,
            child: Text(
              '$label:',
              style: const TextStyle(
                fontWeight: FontWeight.bold,
                color: Colors.grey,
              ),
            ),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontSize: 14),
            ),
          ),
        ],
      ),
    );
  }
}

class EditProfileScreen extends StatefulWidget {
  final String token;
  final Map<String, dynamic> user;
  final Map<String, dynamic>? profileData;

  const EditProfileScreen({super.key, required this.token, required this.user, this.profileData});

  @override
  State<EditProfileScreen> createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends State<EditProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  bool isLoading = false;
  String errorMessage = '';

  // Form controllers
  final _fullNameController = TextEditingController();
  final _jobTitleController = TextEditingController();
  final _summaryController = TextEditingController();
  final _qualificationsController = TextEditingController();
  final _experiencesController = TextEditingController();
  final _skillsController = TextEditingController();
  final _languagesController = TextEditingController();

  // Company form controllers
  final _companyNameController = TextEditingController();
  final _companyIndustryController = TextEditingController();
  final _companyLocationController = TextEditingController();
  final _companyWebsiteController = TextEditingController();
  final _companyDescController = TextEditingController();

  // Selected files (optional)
  File? _selectedProfileImage;
  File? _selectedCVFile;

  String? _selectedProvince;
  String? _selectedSpeciality;
  String? _selectedEducationLevel;
  String? _selectedExperienceLevel;
  String? _selectedGender;
  bool _ownCar = false;

  bool get _isCompany => (widget.user['role'] == 'company');

  // Available options
  final List<String> _provinces = [
    'بغداد', 'البصرة', 'أربيل', 'الموصل', 'النجف', 'كربلاء', 'الأنبار', 'ديالى', 'صلاح الدين', 'واسط', 'ميسان', 'ذي قار', 'المثنى', 'القادسية', 'بابل', 'كركوك', 'السليمانية', 'دهوك'
  ];

  final List<String> _specialities = [
    'General Practitioner', 'Pediatrics', 'Cardiologist', 'Nurses', 'Pharmacist', 'General Surgery', 'Radiology', 'Obstetrics and Gynecology', 'Medical Laboratory', 'Dentistry'
  ];

  final List<String> _educationLevels = [
    'دبلوم', 'بكالوريوس', 'ماجستير', 'دكتوراه'
  ];

  final List<String> _experienceLevels = [
    'مبتدئ', '1-3 سنوات', '3-5 سنوات', '5-10 سنوات', 'أكثر من 10 سنوات'
  ];

  @override
  void initState() {
    super.initState();
    _initializeForm();
  }

  void _initializeForm() {
    if (widget.profileData != null) {
      if (widget.user['role'] == 'jobseeker') {
        final jobSeeker = widget.profileData!['job_seeker'];
        if (jobSeeker != null) {
          _fullNameController.text = jobSeeker['full_name'] ?? '';
          _jobTitleController.text = jobSeeker['job_title'] ?? '';
          _summaryController.text = jobSeeker['summary'] ?? '';
          _qualificationsController.text = jobSeeker['qualifications'] ?? '';
          _experiencesController.text = jobSeeker['experiences'] ?? '';
          _skillsController.text = jobSeeker['skills'] ?? '';
          _languagesController.text = jobSeeker['languages'] ?? '';
          _selectedProvince = jobSeeker['province'];
          _selectedSpeciality = jobSeeker['speciality'];
          _selectedEducationLevel = jobSeeker['education_level'];
          _selectedExperienceLevel = jobSeeker['experience_level'];
          _selectedGender = jobSeeker['gender'];
          _ownCar = jobSeeker['own_car'] ?? false;
        }
      } else if (_isCompany) {
        final company = widget.profileData!['company'];
        if (company != null) {
          _companyNameController.text = company['company_name'] ?? '';
          _companyIndustryController.text = company['industry'] ?? '';
          _companyLocationController.text = company['location'] ?? '';
          _companyWebsiteController.text = company['website'] ?? '';
          _companyDescController.text = company['description'] ?? '';
        }
      }
    }
  }

  Future<void> _saveProfile() async {
    if (!_formKey.currentState!.validate()) {
      return;
    }

    // إذا هناك ملفات محددة استخدم Multipart وإلا أرسل JSON عادي
    if (_selectedProfileImage != null || _selectedCVFile != null) {
      await _saveProfileMultipart();
      return;
    }

    setState(() { isLoading = true; errorMessage = ''; });
    final service = ProfileService();
    try {
      final payload = _isCompany
          ? <String, dynamic>{
              if (_companyNameController.text.isNotEmpty) 'company_name': _companyNameController.text,
              if (_companyIndustryController.text.isNotEmpty) 'industry': _companyIndustryController.text,
              if (_companyLocationController.text.isNotEmpty) 'location': _companyLocationController.text,
              if (_companyWebsiteController.text.isNotEmpty) 'website': _companyWebsiteController.text,
              if (_companyDescController.text.isNotEmpty) 'description': _companyDescController.text,
            }
          : <String, dynamic>{
              'full_name': _fullNameController.text,
              if (_jobTitleController.text.isNotEmpty) 'job_title': _jobTitleController.text,
              if (_selectedSpeciality != null) 'speciality': _selectedSpeciality,
              if (_selectedProvince != null) 'province': _selectedProvince,
              if (_selectedEducationLevel != null) 'education_level': _selectedEducationLevel,
              if (_selectedExperienceLevel != null) 'experience_level': _selectedExperienceLevel,
              if (_selectedGender != null) 'gender': _selectedGender,
              'own_car': _ownCar,
              if (_summaryController.text.isNotEmpty) 'summary': _summaryController.text,
              if (_qualificationsController.text.isNotEmpty) 'qualifications': _qualificationsController.text,
              if (_experiencesController.text.isNotEmpty) 'experiences': _experiencesController.text,
              if (_skillsController.text.isNotEmpty) 'skills': _skillsController.text,
              if (_languagesController.text.isNotEmpty) 'languages': _languagesController.text,
            };

      final res = await service.updateProfileJson(token: widget.token, body: payload);
      if (!mounted) return;
      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('تم حفظ الملف الشخصي بنجاح')),
        );
        Navigator.pop(context);
      } else {
        setState(() { errorMessage = (res['message'] as String?) ?? 'فشل في حفظ الملف الشخصي'; });
      }
    } catch (e) {
      if (!mounted) return;
      setState(() { errorMessage = 'خطأ في الاتصال: $e'; });
    } finally {
      service.close();
      setState(() { isLoading = false; });
    }
  }

  Future<void> _saveProfileMultipart() async {
    setState(() { isLoading = true; errorMessage = ''; });
    final service = ProfileService();
    try {
      final fields = <String, String>{};
      if (_isCompany) {
        if (_companyNameController.text.isNotEmpty) fields['company_name'] = _companyNameController.text;
        if (_companyIndustryController.text.isNotEmpty) fields['industry'] = _companyIndustryController.text;
        if (_companyLocationController.text.isNotEmpty) fields['location'] = _companyLocationController.text;
        if (_companyWebsiteController.text.isNotEmpty) fields['website'] = _companyWebsiteController.text;
        if (_companyDescController.text.isNotEmpty) fields['description'] = _companyDescController.text;
      } else {
        fields['full_name'] = _fullNameController.text;
        if (_jobTitleController.text.isNotEmpty) fields['job_title'] = _jobTitleController.text;
        if (_selectedSpeciality != null) fields['speciality'] = _selectedSpeciality!;
        if (_selectedProvince != null) fields['province'] = _selectedProvince!;
        if (_selectedEducationLevel != null) fields['education_level'] = _selectedEducationLevel!;
        if (_selectedExperienceLevel != null) fields['experience_level'] = _selectedExperienceLevel!;
        if (_selectedGender != null) fields['gender'] = _selectedGender!;
        fields['own_car'] = _ownCar ? '1' : '0';
        if (_summaryController.text.isNotEmpty) fields['summary'] = _summaryController.text;
        if (_qualificationsController.text.isNotEmpty) fields['qualifications'] = _qualificationsController.text;
        if (_experiencesController.text.isNotEmpty) fields['experiences'] = _experiencesController.text;
        if (_skillsController.text.isNotEmpty) fields['skills'] = _skillsController.text;
        if (_languagesController.text.isNotEmpty) fields['languages'] = _languagesController.text;
      }

      final files = <String, File>{};
      if (_selectedProfileImage != null) files['profile_image'] = _selectedProfileImage!;
      if (_selectedCVFile != null) files['cv_file'] = _selectedCVFile!;

      // Show progress dialog
      double dlgProgress = 0.0;
      if (!mounted) return;
      StateSetter? dialogSet;
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (ctx) {
          return StatefulBuilder(
            builder: (ctx, setDlg) {
              dialogSet = setDlg;
              return AlertDialog(
                title: const Text('جاري رفع الملفات'),
                content: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    LinearProgressIndicator(value: dlgProgress > 0 && dlgProgress < 1 ? dlgProgress : null),
                    const SizedBox(height: 12),
                    Text(
                      dlgProgress > 0 ? '${(dlgProgress * 100).toStringAsFixed(0)}%' : 'يتم التحضير...'
                    ),
                  ],
                ),
              );
            },
          );
        },
      );

      // Start upload while updating the dialog's state
      final res = await service.updateProfileMultipart(
        token: widget.token,
        fields: fields,
        files: files,
        onProgress: (p) {
          dlgProgress = p;
          // Update dialog UI
          if (mounted && dialogSet != null) {
            dialogSet!(() {});
          }
        },
      );

      // Close dialog if still mounted and open
      if (mounted) {
        final nav = Navigator.of(context, rootNavigator: true);
        if (nav.canPop()) nav.pop();
      }

      if (!mounted) return;
      if (res['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          const SnackBar(content: Text('تم حفظ الملف الشخصي بنجاح')),
        );
        Navigator.pop(context);
      } else {
        setState(() { errorMessage = (res['message'] as String?) ?? 'فشل في حفظ الملف الشخصي'; });
      }
    } catch (e) {
      if (!mounted) return;
      // Ensure dialog closed on error
      if (Navigator.of(context, rootNavigator: true).canPop()) {
        Navigator.of(context, rootNavigator: true).pop();
      }
      setState(() { errorMessage = 'خطأ في الاتصال: $e'; });
    } finally {
      service.close();
      setState(() { isLoading = false; });
    }
  }

  Future<void> _pickProfileImage() async {
    try {
      final ImagePicker picker = ImagePicker();
      final XFile? picked = await picker.pickImage(source: ImageSource.gallery, imageQuality: 85);
      if (picked != null) {
        setState(() { _selectedProfileImage = File(picked.path); });
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('تعذر اختيار الصورة: $e')));
    }
  }

  Future<void> _pickCV() async {
    try {
      if (!mounted) return;

      final result = await FilePicker.platform.pickFiles(
        type: FileType.custom,
        allowedExtensions: ['pdf','doc','docx'],
      );
      if (result != null && result.files.single.path != null) {
        setState(() { _selectedCVFile = File(result.files.single.path!); });
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('تعذر اختيار الملف: $e')));
    }
  }

  Future<void> _exportCvAsPdf() async {
    try {
      final js = widget.profileData?['job_seeker'] ?? {};
      final fullName = _fullNameController.text.isNotEmpty ? _fullNameController.text : (js['full_name'] ?? widget.user['name'] ?? '');
      final email = (widget.profileData?['email'] as String?) ?? '';
      final phone = (js['phone']?.toString() ?? '');
      final jobTitle = _jobTitleController.text.isNotEmpty ? _jobTitleController.text : (js['job_title'] ?? '');
      final province = _selectedProvince ?? js['province'] ?? '';
      final speciality = _selectedSpeciality ?? js['speciality'] ?? '';
      final education = _selectedEducationLevel ?? js['education_level'] ?? '';
      final summary = _summaryController.text;
      final qualifications = _qualificationsController.text;
      final experiences = _experiencesController.text;
      final skills = _skillsController.text;
      final languages = _languagesController.text;

      final List<String> districts = (js['districts'] is List)
          ? (js['districts'] as List).map((e) => '$e').where((e) => e.trim().isNotEmpty).toList().cast<String>()
          : <String>[];
      final List<String> specialities = (js['specialities'] is List)
          ? (js['specialities'] as List).map((e) => '$e').where((e) => e.trim().isNotEmpty).toList().cast<String>()
          : <String>[];
      final bool hasCar = _ownCar || (js['own_car'] == true) || (js['own_car'] == 1) || ('${js['own_car']}' == '1');
      final String imageUrl = (js['profile_image'] ?? '').toString();
      pw.ImageProvider? avatar;
      if (imageUrl.isNotEmpty) {
        try { avatar = await networkImage(imageUrl); } catch (_) {}
      }

      final doc = pw.Document();
      final regular = await PdfGoogleFonts.notoNaskhArabicRegular();
      final bold = await PdfGoogleFonts.notoNaskhArabicBold();
      final latin = await PdfGoogleFonts.robotoRegular();
      final latinBold = await PdfGoogleFonts.robotoBold();

      pw.Widget section(String title, List<pw.Widget> children) {
        if (children.isEmpty) return pw.SizedBox();
        return pw.Column(crossAxisAlignment: pw.CrossAxisAlignment.start, children: [
          pw.SizedBox(height: 10),
          pw.Container(
            padding: const pw.EdgeInsets.symmetric(vertical: 6, horizontal: 8),
            decoration: pw.BoxDecoration(
              color: PdfColors.blue,
              borderRadius: pw.BorderRadius.circular(4),
            ),
            child: pw.Text(
              title,
              style: pw.TextStyle(font: bold, fontSize: 13, color: PdfColors.white, fontFallback: [latinBold]),
            ),
          ),
          pw.SizedBox(height: 6),
          ...children,
        ]);
      }

      List<pw.Widget> bulletFromText(String text) {
        final lines = text.split('\n').map((e) => e.trim()).where((e) => e.isNotEmpty).toList();
        return lines.map((l) => pw.Bullet(text: l, style: pw.TextStyle(font: regular, fontSize: 11, fontFallback: [latin]))).toList();
      }

      doc.addPage(
        pw.MultiPage(
          pageTheme: pw.PageTheme(
            margin: const pw.EdgeInsets.symmetric(horizontal: 28, vertical: 24),
            textDirection: pw.TextDirection.rtl,
            theme: pw.ThemeData.withFont(base: regular, bold: bold),
          ),
          build: (ctx) => [
            // Header bar
            pw.Container(
              padding: const pw.EdgeInsets.all(14),
              decoration: pw.BoxDecoration(color: PdfColors.blue),
              child: pw.Row(
                mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
                crossAxisAlignment: pw.CrossAxisAlignment.start,
                children: [
                  pw.Column(crossAxisAlignment: pw.CrossAxisAlignment.start, children: [
                    pw.Text(fullName.isEmpty ? 'سيرة ذاتية' : fullName, style: pw.TextStyle(font: bold, fontSize: 22, color: PdfColors.white, fontFallback: [latinBold])),
                    if (jobTitle.toString().isNotEmpty) pw.Text(jobTitle, style: pw.TextStyle(font: regular, fontSize: 12, color: PdfColors.white, fontFallback: [latin])),
                  ]),
                ],
              ),
            ),
            pw.SizedBox(height: 14),
            // Two-column layout
            pw.Row(
              crossAxisAlignment: pw.CrossAxisAlignment.start,
              children: [
                pw.Expanded(
                  flex: 5,
                  child: pw.Column(crossAxisAlignment: pw.CrossAxisAlignment.start, children: [
                    if (avatar != null) ...[
                      pw.Image(avatar, width: 110, height: 110, fit: pw.BoxFit.cover),
                      pw.SizedBox(height: 12),
                    ],
                    if (summary.trim().isNotEmpty)
                      section('نبذة عني', [pw.Text(summary, style: pw.TextStyle(fontSize: 11, fontFallback: [latin]))]),
                    section('الخبرات المهنية', bulletFromText(experiences)),
                  ]),
                ),
                pw.SizedBox(width: 18),
                pw.Expanded(
                  flex: 2,
                  child: pw.Column(crossAxisAlignment: pw.CrossAxisAlignment.start, children: [
                    section('بيانات التواصل', [
                      if (email.isNotEmpty) pw.Text('البريد: $email', style: pw.TextStyle(font: regular, fontSize: 10, fontFallback: [latin])),
                      if (phone.isNotEmpty) pw.Text('رقم الموبايل: $phone', style: pw.TextStyle(font: regular, fontSize: 10, fontFallback: [latin])),
                      if (province.toString().isNotEmpty) pw.Text('المحافظة: $province', style: pw.TextStyle(font: regular, fontSize: 10, fontFallback: [latin])),
                      pw.Text('امتلاك السيارة: ${hasCar ? 'نعم' : 'لا'}', style: pw.TextStyle(font: regular, fontSize: 10, fontFallback: [latin])),
                    ]),
                    if (districts.isNotEmpty)
                      section('المناطق', districts.map((d) => pw.Bullet(text: d, style: pw.TextStyle(font: regular, fontSize: 11, fontFallback: [latin]))).toList()),
                    if (specialities.isNotEmpty)
                      section('التخصصات', specialities.map((s) => pw.Bullet(text: s, style: pw.TextStyle(font: regular, fontSize: 11, fontFallback: [latin]))).toList()),
                    section('التعليم', education.toString().isEmpty ? [] : [pw.Text(education, style: pw.TextStyle(fontSize: 11, fontFallback: [latin]))]),
                    section('المهارات', bulletFromText(skills)),
                    section('اللغات', bulletFromText(languages)),
                    section('المؤهلات', bulletFromText(qualifications)),
                    section('التخصص', speciality.toString().isEmpty ? [] : [pw.Text(speciality, style: pw.TextStyle(fontSize: 11, fontFallback: [latin]))]),
                  ]),
                ),
              ],
            ),
          ],
        ),
      );

      final bytes = await doc.save();
      final safeName = (fullName.isEmpty ? 'cv' : fullName).replaceAll(RegExp(r'[\\/:*?"<>|]'), '_');
      if (kIsWeb) {
        await FileSaver.instance.saveFile(
          name: 'CV_$safeName',
          bytes: bytes,
          fileExtension: 'pdf',
          mimeType: MimeType.pdf,
        );
      } else {
        await FileSaver.instance.saveAs(
          name: 'CV_$safeName',
          bytes: bytes,
          fileExtension: 'pdf',
          mimeType: MimeType.pdf,
        );
      }

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('تم إنشاء وحفظ السيرة الذاتية (PDF) بنجاح')));
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('تعذر إنشاء PDF: $e')));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('تعديل الملف الشخصي'),
        backgroundColor: Theme.of(context).colorScheme.primary,
        foregroundColor: Theme.of(context).colorScheme.onPrimary,
        actions: [
          IconButton(
            icon: const Icon(Icons.save),
            onPressed: isLoading ? null : _saveProfile,
          ),
        ],
      ),
      body: Form(
        key: _formKey,
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            children: [
              // Error Message
              if (errorMessage.isNotEmpty)
                Container(
                  padding: const EdgeInsets.all(12),
                  margin: const EdgeInsets.only(bottom: 16),
                  decoration: BoxDecoration(
                    color: Colors.red[50],
                    borderRadius: BorderRadius.circular(8),
                    border: Border.all(color: Colors.red[300]!),
                  ),
                  child: Text(
                    errorMessage,
                    style: TextStyle(color: Colors.red[700]),
                  ),
                ),

              // Company Information (for companies)
              if (_isCompany)
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'بيانات الشركة',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 16),
                        TextFormField(
                          controller: _companyNameController,
                          decoration: const InputDecoration(
                            labelText: 'اسم الشركة *',
                            border: OutlineInputBorder(),
                          ),
                          validator: (value) {
                            if (_isCompany && (value == null || value.isEmpty)) {
                              return 'اسم الشركة مطلوب';
                            }
                            return null;
                          },
                        ),
                        const SizedBox(height: 16),
                        TextFormField(
                          controller: _companyIndustryController,
                          decoration: const InputDecoration(
                            labelText: 'القطاع/الصناعة',
                            border: OutlineInputBorder(),
                          ),
                        ),
                        const SizedBox(height: 16),
                        TextFormField(
                          controller: _companyLocationController,
                          decoration: const InputDecoration(
                            labelText: 'الموقع/المحافظة',
                            border: OutlineInputBorder(),
                          ),
                        ),
                        const SizedBox(height: 16),
                        TextFormField(
                          controller: _companyWebsiteController,
                          decoration: const InputDecoration(
                            labelText: 'الموقع الإلكتروني',
                            border: OutlineInputBorder(),
                          ),
                        ),
                        const SizedBox(height: 16),
                        TextFormField(
                          controller: _companyDescController,
                          decoration: const InputDecoration(
                            labelText: 'وصف الشركة',
                            border: OutlineInputBorder(),
                          ),
                          maxLines: 3,
                        ),
                        const SizedBox(height: 16),
                        Row(
                          children: [
                            Expanded(
                              child: OutlinedButton.icon(
                                onPressed: _pickProfileImage,
                                icon: const Icon(Icons.image),
                                label: Text(_selectedProfileImage == null
                                    ? 'إرفاق شعار/صورة الشركة'
                                    : 'تم اختيار صورة: ${_selectedProfileImage!.path.split('/').last}'),
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),
                ),


              // Basic Information
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'المعلومات الأساسية',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _fullNameController,
                        decoration: const InputDecoration(
                          labelText: 'الاسم الكامل *',
                          border: OutlineInputBorder(),
                        ),
                        validator: (value) {
                          if (value == null || value.isEmpty) {
                            return 'الاسم الكامل مطلوب';
                          }
                          return null;
                        },
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _jobTitleController,
                        decoration: const InputDecoration(
                          labelText: 'المسمى الوظيفي',
                          border: OutlineInputBorder(),
                        ),
                      ),
                      const SizedBox(height: 16),
                      DropdownButtonFormField<String>(
                        value: _selectedProvince,
                        decoration: const InputDecoration(
                          labelText: 'المحافظة',
                          border: OutlineInputBorder(),
                        ),
                        items: _provinces.map((province) => DropdownMenuItem<String>(
                          value: province,
                          child: Text(province),
                        )).toList(),
                        onChanged: (value) {
                          setState(() {
                            _selectedProvince = value;
                          });
                        },
                      ),
                      const SizedBox(height: 16),
                      DropdownButtonFormField<String>(
                        value: _selectedGender,
                        decoration: const InputDecoration(
                          labelText: 'الجنس',
                          border: OutlineInputBorder(),
                        ),
                        items: const [
                          DropdownMenuItem<String>(
                            value: 'male',
                            child: Text('ذكر'),
                          ),
                          DropdownMenuItem<String>(
                            value: 'female',
                            child: Text('أنثى'),
                          ),
                        ],
                        onChanged: (value) {
                          setState(() {
                            _selectedGender = value;
                          });
                        },
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Checkbox(
                            value: _ownCar,
                            onChanged: (value) {
                              setState(() {
                                _ownCar = value ?? false;
                              });
                            },
                          ),
                          const Text('يملك سيارة'),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),

              // Professional Information
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'المعلومات المهنية',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 16),
                      DropdownButtonFormField<String>(
                        value: _selectedSpeciality,
                        decoration: const InputDecoration(
                          labelText: 'التخصص',
                          border: OutlineInputBorder(),
                        ),
                        items: _specialities.map((speciality) => DropdownMenuItem<String>(
                          value: speciality,
                          child: Text(_getSpecialityName(speciality)),
                        )).toList(),
                        onChanged: (value) {
                          setState(() {
                            _selectedSpeciality = value;
                          });
                        },
                      ),
                      const SizedBox(height: 16),
                      DropdownButtonFormField<String>(
                        value: _selectedEducationLevel,
                        decoration: const InputDecoration(
                          labelText: 'مستوى التعليم',
                          border: OutlineInputBorder(),
                        ),
                        items: _educationLevels.map((level) => DropdownMenuItem<String>(
                          value: level,
                          child: Text(level),
                        )).toList(),
                        onChanged: (value) {
                          setState(() {
                            _selectedEducationLevel = value;
                          });
                        },
                      ),
                      const SizedBox(height: 16),
                      DropdownButtonFormField<String>(
                        value: _selectedExperienceLevel,
                        decoration: const InputDecoration(
                          labelText: 'مستوى الخبرة',
                          border: OutlineInputBorder(),
                        ),
                        items: _experienceLevels.map((level) => DropdownMenuItem<String>(
                          value: level,
                          child: Text(level),
                        )).toList(),
                        onChanged: (value) {
                          setState(() {
                            _selectedExperienceLevel = value;
                          });
                        },
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),

              // Additional Information
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text(
                        'معلومات إضافية',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _summaryController,
                        decoration: const InputDecoration(
                          labelText: 'الملخص المهني',
                          border: OutlineInputBorder(),
                          hintText: 'اكتب ملخصاً مختصراً عن خبراتك ومهاراتك...',
                        ),
                        maxLines: 3,
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _qualificationsController,
                        decoration: const InputDecoration(
                          labelText: 'المؤهلات',
                          border: OutlineInputBorder(),
                          hintText: 'اذكر مؤهلاتك العلمية والشهادات...',
                        ),
                        maxLines: 3,
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _experiencesController,
                        decoration: const InputDecoration(
                          labelText: 'الخبرات',
                          border: OutlineInputBorder(),
                          hintText: 'اذكر خبراتك العملية السابقة...',
                        ),
                        maxLines: 3,
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _skillsController,
                        decoration: const InputDecoration(
                          labelText: 'المهارات',
                          border: OutlineInputBorder(),
                          hintText: 'اذكر مهاراتك التقنية والشخصية...',
                        ),
                        maxLines: 2,
                      ),
                      const SizedBox(height: 16),
                      TextFormField(
                        controller: _languagesController,
                        decoration: const InputDecoration(
                          labelText: 'اللغات',
                          border: OutlineInputBorder(),
                          hintText: 'اذكر اللغات التي تتقنها...',
                        ),
                      ),
                      const SizedBox(height: 16),
                      // Upload controls
                      Row(
                        children: [
                          Expanded(
                            child: OutlinedButton.icon(
                              onPressed: _pickProfileImage,
                              icon: const Icon(Icons.image),
                              label: Text(_selectedProfileImage == null
                                  ? 'إرفاق صورة الملف الشخصي'
                                  : 'تم اختيار صورة: ${_selectedProfileImage!.path.split('/').last}'),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          Expanded(
                            child: OutlinedButton.icon(
                              onPressed: _pickCV,
                              icon: const Icon(Icons.picture_as_pdf),
                              label: Text(_selectedCVFile == null
                                  ? 'إرفاق CV (PDF)'
                                  : 'تم اختيار ملف: ${_selectedCVFile!.path.split('/').last}'),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Row(
                        children: [
                          Expanded(
                            child: OutlinedButton.icon(
                              onPressed: isLoading ? null : _exportCvAsPdf,
                              icon: const Icon(Icons.picture_as_pdf),
                              label: const Text('تصدير السيرة الذاتية كـ PDF'),
                            ),
                          ),
                        ],
                      ),

                    ],
                  ),
                ),
              ),
              const SizedBox(height: 24),

              // Save Button
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: isLoading ? null : _saveProfile,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.blue,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                  ),
                  child: isLoading
                      ? const CircularProgressIndicator(color: Colors.white)
                      : const Text(
                          'حفظ التغييرات',
                          style: TextStyle(fontSize: 16),
                        ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  String _getSpecialityName(String speciality) {
    switch (speciality) {
      case 'General Practitioner':
        return 'طبيب عام';
      case 'Pediatrics':
        return 'طبيب أطفال';
      case 'Cardiologist':
        return 'طبيب قلب';
      case 'Nurses':
        return 'ممرض/ممرضة';
      case 'Pharmacist':
        return 'صيدلي';

      case 'General Surgery':
        return 'طبيب جراحة عامة';
      case 'Radiology':
        return 'أخصائي أشعة';
      case 'Obstetrics and Gynecology':
        return 'طبيب نساء وولادة';
      case 'Medical Laboratory':
        return 'فني مختبر طبي';
      case 'Dentistry':
        return 'طبيب أسنان';
      default:
        return speciality;
    }
  }
  }


class CompanyDashboardScreen extends StatefulWidget {
  final String token;
  final Map<String, dynamic> user;
  const CompanyDashboardScreen({super.key, required this.token, required this.user});
  @override
  State<CompanyDashboardScreen> createState() => _CompanyDashboardScreenState();
}

class _CompanyDashboardScreenState extends State<CompanyDashboardScreen> {
  Map<String, dynamic>? stats;
  List<dynamic> jobs = [];
  bool isLoading = true;
  String errorMessage = '';

  @override
  void initState() {
    super.initState();
    _loadDashboard();
  }

  Future<void> _loadDashboard() async {
    setState(() { isLoading = true; errorMessage = ''; });
    try {
      final company = CompanyService();
      final statsRes = await company.dashboardStats(token: widget.token);
      final jobsRes  = await company.myJobs(token: widget.token);
      if (statsRes['success'] == true && jobsRes['success'] == true) {
        setState(() {
          stats = (statsRes['data'] as Map<String, dynamic>?) ?? {};
          jobs  = ((jobsRes['data'] as Map<String, dynamic>?)?['data'] as List?) ?? [];
          isLoading = false;
        });
      } else {
        setState(() { errorMessage = 'فشل في تحميل البيانات'; isLoading = false; });
      }
    } catch (e) {
      setState(() { errorMessage = 'خطأ في الاتصال: $e'; isLoading = false; });
    }
  }

  Widget _statCard(String label, dynamic value, Color color) {
    return Expanded(
      child: Card(
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Column(
            children: [
              Text('$value', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: color)),
              const SizedBox(height: 4),
              Text(label, style: const TextStyle(color: Colors.grey)),
            ],
          ),
        ),
      ),
    );
  }
  Widget _actionCard(
    BuildContext context, {
    required String title,
    required IconData icon,
    required LinearGradient gradient,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          gradient: gradient,
        ),
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.end,
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Icon(icon, size: 48, color: Colors.white),
            Text(
              title,
              textAlign: TextAlign.right,
              style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
            ),
          ],
        ),
      ),
    );
  }

  String _wrapSessionUrl(String absoluteUrl) {
    final site = AppConfig.baseUrl.replaceFirst('api/v1/', '');
    String redirect = absoluteUrl;
    if (absoluteUrl.startsWith(site)) {
      redirect = absoluteUrl.substring(site.length);
      if (!redirect.startsWith('/')) redirect = '/$redirect';
    }
    final token = Uri.encodeComponent(widget.token);
    final dest = Uri.encodeComponent(redirect);
    return '${site}mobile/session-login?token=$token&redirect=$dest';
  }

  void _openCompany(BuildContext context, String title, String url) {
    final bridged = _wrapSessionUrl(url);
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => AdminWebViewScreen(title: title, url: bridged)),
    );
  }


  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('لوحة تحكم الشركة'),
        backgroundColor: Theme.of(context).colorScheme.primary,
        foregroundColor: Theme.of(context).colorScheme.onPrimary,
        actions: [
          IconButton(icon: const Icon(Icons.refresh), onPressed: _loadDashboard),
        ],
      ),

      body: isLoading
          ? const Center(child: CircularProgressIndicator())
          : Column(
              children: [
                if (errorMessage.isNotEmpty)
                  const Padding(
                    padding: EdgeInsets.all(12),
                    child: Text('فشل في تحميل البيانات، سيتم عرض الواجهة دون إحصاءات', style: TextStyle(color: Colors.red)),
                  ),
                Padding(
                      padding: const EdgeInsets.all(12),
                      child: Row(
                        children: [
                          _statCard('إجمالي الوظائف', stats?['total_jobs'] ?? 0, Colors.blue),
                          _statCard('الوظائف النشطة', stats?['active_jobs'] ?? 0, Colors.green),
                        ],
                      ),
                    ),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 12),
                      child: Row(
                        children: [
                          _statCard('طلبات التقديم', stats?['total_applications'] ?? 0, Colors.deepPurple),
                          _statCard('طلبات قيد المراجعة', stats?['pending_applications'] ?? 0, Colors.orange),
                        ],
                      ),
                    ),
                    // روابط الشركة كما في الموقع داخل WebView
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 12),
                      child: GridView.count(
                        shrinkWrap: true,
                        physics: const NeverScrollableScrollPhysics(),
                        crossAxisCount: 2,
                        crossAxisSpacing: 12,
                        mainAxisSpacing: 12,
                        childAspectRatio: 1.6,
                        children: [
                          _actionCard(
                            context,
                            title: 'إدارة الوظائف',
                            icon: Icons.work_outline,
                            gradient: const LinearGradient(colors: [Color(0xFF0D2660), Color(0xFF102E66)]),
                            onTap: () {
                              final site = AppConfig.baseUrl.replaceFirst('api/v1/', '');
                              _openCompany(context, 'إدارة الوظائف', '${site}company/jobs');
                            },
                          ),
                          _actionCard(
                            context,
                            title: 'وظيفة جديدة',
                            icon: Icons.add_box_outlined,
                            gradient: const LinearGradient(colors: [Color(0xFFE7C66A), Color(0xFFC5A74F)]),
                            onTap: () {
                              final site = AppConfig.baseUrl.replaceFirst('api/v1/', '');
                              _openCompany(context, 'وظيفة جديدة', '${site}company/jobs/create');
                            },
                          ),
                          _actionCard(
                            context,
                            title: 'الطلبات',
                            icon: Icons.inbox_outlined,
                            gradient: const LinearGradient(colors: [Color(0xFF10B981), Color(0xFF059669)]),
                            onTap: () {
                              final site = AppConfig.baseUrl.replaceFirst('api/v1/', '');
                              _openCompany(context, 'طلبات التقديم', '${site}company/applicants');
                            },
                          ),
                          _actionCard(
                            context,
                            title: 'قاعدة الباحثين',
                            icon: Icons.manage_search,
                            gradient: const LinearGradient(colors: [Color(0xFF0EA5E9), Color(0xFF0369A1)]),
                            onTap: () {
                              final site = AppConfig.baseUrl.replaceFirst('api/v1/', '');
                              _openCompany(context, 'قاعدة الباحثين', '${site}company/seekers');
                            },
                          ),
                          _actionCard(
                            context,
                            title: 'ملف الشركة',
                            icon: Icons.account_circle_outlined,
                            gradient: const LinearGradient(colors: [Color(0xFF9333EA), Color(0xFF7E22CE)]),
                            onTap: () {
                              final site = AppConfig.baseUrl.replaceFirst('api/v1/', '');
                              _openCompany(context, 'ملف الشركة', '${site}company/profile');
                            },
                          ),
                        ],
                      ),
                    ),

                    const Padding(
                      padding: EdgeInsets.fromLTRB(12, 12, 12, 0),
                      child: Align(
                        alignment: Alignment.centerRight,
                        child: Text('وظائفي', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                      ),
                    ),
                    Expanded(

                      child: jobs.isEmpty
                          ? const Center(child: Text('لا توجد وظائف'))
                          : ListView.builder(
                    // Gradient header like website

                              padding: const EdgeInsets.all(12),
                              itemCount: jobs.length,
                              itemBuilder: (context, i) {
                                final job = jobs[i];
                                return Card(
                                  child: ListTile(
                                    title: Text(job['title'] ?? ''),
                                    subtitle: Text('المتقدمون: ${job['applications_count'] ?? 0} • الحالة: ${job['status'] ?? ''}'),
                                    trailing: const Icon(Icons.chevron_left),
                                    onTap: () {
                                      Navigator.push(
                                        context,
                                        MaterialPageRoute(
                                          builder: (context) => ApplicantsScreen(
                                            token: widget.token,
                                            user: widget.user,
                                            jobId: job['id'],
                                            jobTitle: job['title'] ?? '',
                                          ),
                                        ),
                                      );
                                    },
                                  ),
                                );
                              },
                            ),
                    ),
                  ],
                ),
    );
  }
}


class ApplicantsScreen extends StatefulWidget {
  final String token;
  final Map<String, dynamic> user;
  final int jobId;
  final String jobTitle;
  const ApplicantsScreen({super.key, required this.token, required this.user, required this.jobId, required this.jobTitle});
  @override
  State<ApplicantsScreen> createState() => _ApplicantsScreenState();
}

class _ApplicantsScreenState extends State<ApplicantsScreen> {
  List<dynamic> applications = [];
  bool isLoading = true;
  String errorMessage = '';

  // Filters
  String? _selectedStatus;
  int? _minMatching;
  String? _selectedSpeciality;
  String? _selectedProvince;
  String _sortBy = 'matching_percentage';
  String _sortOrder = 'desc';

  final List<String> _statuses = ['pending','reviewed','shortlisted','rejected','hired'];
  final List<String> _specialities = [
    'General Practitioner','Pediatrics','Cardiologist','Nurses','Pharmacist',
    'General Surgery','Radiology','Obstetrics and Gynecology','Medical Laboratory','Dentistry'
  ];
  final List<String> _provinces = [
    'بغداد','البصرة','أربيل','الموصل','النجف','كربلاء','الأنبار','ديالى','صلاح الدين','واسط','ميسان','ذي قار','المثنى','القادسية','بابل','كركوك','السليمانية','دهوك'
  ];

  @override
  void initState() {
    super.initState();
    _loadApplications();
  }

  Future<void> _loadApplications() async {
    setState(() { isLoading = true; errorMessage = ''; });
    try {
      final apps = ApplicationsService();
      final res = await apps.listApplications(
        token: widget.token,
        jobId: widget.jobId,
        status: _selectedStatus,
        minMatching: _minMatching,
        speciality: _selectedSpeciality,
        province: _selectedProvince,
        sortBy: _sortBy,
        sortOrder: _sortOrder,
      );
      if (res['success'] == true) {
        final payload = (res['data'] as Map<String, dynamic>?) ?? {};
        final appsList = ((payload['applications'] ?? {})['data'] as List?) ?? [];
        setState(() {
          applications = appsList;
          isLoading = false;
        });
      } else {
        setState(() { errorMessage = (res['message'] as String?) ?? 'فشل في تحميل المتقدمين'; isLoading = false; });
      }
    } catch (e) {
      setState(() { errorMessage = 'خطأ في الاتصال: $e'; isLoading = false; });
    }
  }

  void _showFilterDialog() {
    final controller = TextEditingController(text: _minMatching?.toString() ?? '');
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: const Text('فلترة المتقدمين'),
        content: SingleChildScrollView(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              DropdownButtonFormField<String>(
                value: _selectedStatus,
                decoration: const InputDecoration(labelText: 'الحالة', border: OutlineInputBorder()),
                items: [
                  const DropdownMenuItem<String>(value: null, child: Text('كل الحالات')),
                  ..._statuses.map((s) => DropdownMenuItem<String>(value: s, child: Text(_statusText(s))))
                ],
                onChanged: (v) => _selectedStatus = v,
              ),
              const SizedBox(height: 12),
              TextField(
                controller: controller,
                keyboardType: TextInputType.number,
                decoration: const InputDecoration(labelText: 'أدنى نسبة تطابق %', border: OutlineInputBorder()),
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                value: _selectedSpeciality,
                decoration: const InputDecoration(labelText: 'التخصص', border: OutlineInputBorder()),
                items: [
                  const DropdownMenuItem<String>(value: null, child: Text('كل التخصصات')),
                  ..._specialities.map((s) => DropdownMenuItem<String>(value: s, child: Text(s)))
                ],
                onChanged: (v) => _selectedSpeciality = v,
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                value: _selectedProvince,
                decoration: const InputDecoration(labelText: 'المحافظة', border: OutlineInputBorder()),
                items: [
                  const DropdownMenuItem<String>(value: null, child: Text('كل المحافظات')),
                  ..._provinces.map((p) => DropdownMenuItem<String>(value: p, child: Text(p)))
                ],
                onChanged: (v) => _selectedProvince = v,
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                value: _sortBy,
                decoration: const InputDecoration(labelText: 'ترتيب حسب', border: OutlineInputBorder()),
                items: const [
                  DropdownMenuItem<String>(value: 'matching_percentage', child: Text('نسبة التطابق')),
                  DropdownMenuItem<String>(value: 'applied_at', child: Text('تاريخ التقديم')),
                ],
                onChanged: (v) => _sortBy = v ?? 'matching_percentage',
              ),
              const SizedBox(height: 12),
              DropdownButtonFormField<String>(
                value: _sortOrder,
                decoration: const InputDecoration(labelText: 'اتجاه الترتيب', border: OutlineInputBorder()),
                items: const [
                  DropdownMenuItem<String>(value: 'desc', child: Text('تنازلي')),
                  DropdownMenuItem<String>(value: 'asc', child: Text('تصاعدي')),
                ],
                onChanged: (v) => _sortOrder = v ?? 'desc',
              ),
            ],
          ),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(context), child: const Text('إلغاء')),
          TextButton(
            onPressed: () {
              setState(() {
                final v = int.tryParse(controller.text);
                _minMatching = v;
              });
              Navigator.pop(context);
              _loadApplications();
            },
            child: const Text('تطبيق'),
          ),
        ],
      ),
    );
  }

  String _statusText(String s) {
    switch (s) {
      case 'pending': return 'قيد المراجعة';
      case 'reviewed': return 'تمت المراجعة';
      case 'shortlisted': return 'مرشح للمقابلة';
      case 'rejected': return 'مرفوض';
      case 'hired': return 'تم التوظيف';
      default: return s;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('المتقدمون - ${widget.jobTitle} (${applications.length})'),
        backgroundColor: Theme.of(context).colorScheme.primary,
        foregroundColor: Theme.of(context).colorScheme.onPrimary,
        actions: [
          IconButton(icon: const Icon(Icons.filter_list), onPressed: _showFilterDialog),
          IconButton(icon: const Icon(Icons.refresh), onPressed: _loadApplications),
        ],
      ),
      body: isLoading
          ? const Center(child: CircularProgressIndicator())
          : errorMessage.isNotEmpty
              ? Center(child: Text(errorMessage, style: const TextStyle(color: Colors.red)))
              : applications.isEmpty
                  ? const Center(child: Text('لا يوجد متقدمون'))
                  : ListView.builder(
                      padding: const EdgeInsets.all(12),
                      itemCount: applications.length,
                      itemBuilder: (context, i) {
                        final app = applications[i];
                        final seeker = app['job_seeker'] ?? app['jobSeeker'] ?? {};
                        final name = seeker['full_name'] ?? seeker['user']?['name'] ?? 'غير معروف';
                        return Card(
                          child: ListTile(
                            title: Text(name),
                            subtitle: Text('النسبة: ${app['matching_percentage'] ?? 0}% • الحالة: ${_statusText(app['status'] ?? 'pending')}'),
                            trailing: const Icon(Icons.chevron_left),
                            onTap: () {
                              Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (context) => ApplicantDetailScreen(
                                    token: widget.token,
                                    application: app,
                                  ),
                                ),
                              ).then((_) => _loadApplications());
                            },
                          ),
                        );
                      },
                    ),
    );
  }
}

class ApplicantDetailScreen extends StatefulWidget {
  final String token;
  final Map<String, dynamic> application;
  const ApplicantDetailScreen({super.key, required this.token, required this.application});
  @override
  State<ApplicantDetailScreen> createState() => _ApplicantDetailScreenState();
}

class _ApplicantDetailScreenState extends State<ApplicantDetailScreen> {
  String? _status;
  final _notesController = TextEditingController();
  bool _saving = false;

  final List<String> _statuses = ['pending','reviewed','shortlisted','rejected','hired'];

  Future<void> _updateStatus() async {
    final id = widget.application['id'];
    if (id == null || _status == null) return;
    setState(() { _saving = true; });
    try {
      final resp = await http.put(
        Uri.parse('${AppConfig.baseUrl}applications/$id/status'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer ${widget.token}',
        },
        body: jsonEncode({'status': _status, 'notes': _notesController.text}),
      );
      if (!mounted) return;

      final data = jsonDecode(resp.body);
      if (resp.statusCode == 200 && data['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('تم تحديث الحالة')));
        Navigator.pop(context);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(data['message'] ?? 'فشل التحديث')));
      }
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('خطأ: $e')));
    }
    setState(() { _saving = false; });
  }

  String _statusText(String s) {
    switch (s) {
      case 'pending': return 'قيد المراجعة';
      case 'reviewed': return 'تمت المراجعة';
      case 'shortlisted': return 'مرشح للمقابلة';
      case 'rejected': return 'مرفوض';
      case 'hired': return 'تم التوظيف';
      default: return s;
    }
  }

  @override
  Widget build(BuildContext context) {
    final app = widget.application;
    final seeker = app['job_seeker'] ?? app['jobSeeker'] ?? {};
    final name = seeker['full_name'] ?? seeker['user']?['name'] ?? 'غير معروف';

    return Scaffold(
      appBar: AppBar(
        title: const Text('تفاصيل المتقدم'),
        backgroundColor: Colors.blue,
        foregroundColor: Colors.white,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Card(
              child: ListTile(
                leading: const CircleAvatar(child: Icon(Icons.person)),
                title: Text(name),
                subtitle: Text('${seeker['speciality'] ?? ''} • ${seeker['province'] ?? ''}'),
              ),
            ),
            const SizedBox(height: 12),
            if (app['matching_percentage'] != null)
              Card(
                child: ListTile(
                  leading: const Icon(Icons.percent),
                  title: Text('نسبة التطابق: ${app['matching_percentage']}%'),
                ),
              ),
            if (seeker['cv_file'] != null)
              Card(
                child: ListTile(
                  leading: const Icon(Icons.picture_as_pdf),
                  title: const Text('ملف السيرة الذاتية (CV)'),
                  subtitle: Text('${seeker['cv_file']}'),
                ),
              ),
            const SizedBox(height: 12),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('تحديث الحالة', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 12),
                    DropdownButtonFormField<String>(
                      value: _status,
                      decoration: const InputDecoration(labelText: 'الحالة', border: OutlineInputBorder()),
                      items: _statuses.map((s) => DropdownMenuItem<String>(value: s, child: Text(_statusText(s)))).toList(),
                      onChanged: (v) => setState(() { _status = v; }),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: _notesController,
                      decoration: const InputDecoration(labelText: 'ملاحظات', border: OutlineInputBorder()),
                      maxLines: 3,
                    ),
                    const SizedBox(height: 12),
                    SizedBox(
                      width: double.infinity,
                      child: ElevatedButton(
                        onPressed: _saving ? null : _updateStatus,
                        style: ElevatedButton.styleFrom(backgroundColor: Colors.blue, foregroundColor: Colors.white),
                        child: _saving ? const CircularProgressIndicator(color: Colors.white) : const Text('تحديث'),
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ],


        ),
      ),
    );
  }
}


// ========================= Admin Dashboard (Mobile) =========================
class AdminDashboardScreen extends StatelessWidget {
  final String token;
  final Map<String, dynamic> user;
  const AdminDashboardScreen({super.key, required this.token, required this.user});

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Scaffold(
      appBar: AppBar(
        title: const Text('لوحة تحكم الأدمن'),
        backgroundColor: scheme.primary,
        foregroundColor: scheme.onPrimary,
      ),
      body: Padding(
        padding: const EdgeInsets.all(12),
        child: GridView.count(
          crossAxisCount: 2,
          crossAxisSpacing: 12,
          mainAxisSpacing: 12,
          children: [
            // Use website routes inside WebView for full parity
            _adminCard(
              context,
              title: 'الشركات',
              icon: Icons.apartment,
              gradient: const LinearGradient(colors: [Color(0xFF4F46E5), Color(0xFF3730A3)]),
              onTap: () {
                final site = AppConfig.baseUrl.replaceFirst('api/v1/', '');
                _open(context, 'إدارة الشركات', '${site}admin/companies');
              },
            ),
            _adminCard(
              context,
              title: 'وظائف قيد المراجعة',
              icon: Icons.fact_check,
              gradient: const LinearGradient(colors: [Color(0xFF10B981), Color(0xFF059669)]),
              onTap: () {
                final site = AppConfig.baseUrl.replaceFirst('api/v1/', '');
                _open(context, 'الوظائف قيد المراجعة', '${site}admin/jobs/pending');
              },
            ),
            _adminCard(
              context,
              title: 'الباحثون عن عمل',
              icon: Icons.people_alt,
              gradient: const LinearGradient(colors: [Color(0xFFF59E0B), Color(0xFFD97706)]),
              onTap: () {
                final site = AppConfig.baseUrl.replaceFirst('api/v1/', '');
                _open(context, 'إدارة الباحثين', '${site}admin/jobseekers');
              },
            ),
            _adminCard(
              context,
              title: 'قاعدة بيانات الباحثين',
              icon: Icons.manage_search,
              gradient: const LinearGradient(colors: [Color(0xFF0EA5E9), Color(0xFF0369A1)]),
              onTap: () {
                final site = AppConfig.baseUrl.replaceFirst('api/v1/', '');
                _open(context, 'قاعدة بيانات الباحثين', '${site}admin/seekers');
              },
            ),
            _adminCard(
              context,
              title: 'الإعدادات',
              icon: Icons.settings,
              gradient: const LinearGradient(colors: [Color(0xFF9333EA), Color(0xFF7E22CE)]),
              onTap: () {
                final site = AppConfig.baseUrl.replaceFirst('api/v1/', '');
                _open(context, 'الإعدادات', '${site}admin/settings');
              },
            ),
            _adminCard(
              context,
              title: 'الأقضية والمناطق',
              icon: Icons.map,
              gradient: const LinearGradient(colors: [Color(0xFF14B8A6), Color(0xFF0F766E)]),
              onTap: () {
                final site = AppConfig.baseUrl.replaceFirst('api/v1/', '');
                _open(context, 'الأقضية والمناطق', '${site}admin/districts');
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _adminCard(
    BuildContext context, {
    required String title,
    required IconData icon,
    required LinearGradient gradient,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          gradient: gradient,
        ),
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.end,
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Icon(icon, size: 48, color: Colors.white.withValues(alpha: 0.95)),
            Text(
              title,
              textAlign: TextAlign.right,
              style: const TextStyle(color: Colors.white, fontSize: 16, fontWeight: FontWeight.bold),
            ),
          ],
        ),
      ),
    );
  }

  String _wrapSessionUrl(String absoluteUrl) {
    final site = AppConfig.baseUrl.replaceFirst('api/v1/', '');
    String redirect = absoluteUrl;
    if (absoluteUrl.startsWith(site)) {
      redirect = absoluteUrl.substring(site.length);
      if (!redirect.startsWith('/')) redirect = '/$redirect';
    }
    final encodedToken = Uri.encodeComponent(token);
    final dest = Uri.encodeComponent(redirect);
    return '${site}mobile/session-login?token=$encodedToken&redirect=$dest';
  }

  void _open(BuildContext context, String title, String url) {
    final bridged = _wrapSessionUrl(url);
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => AdminWebViewScreen(title: title, url: bridged)),
    );
  }
}

class AdminPlaceholderScreen extends StatelessWidget {
  final String title;
  const AdminPlaceholderScreen({super.key, required this.title});

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    return Scaffold(
      appBar: AppBar(
        title: Text(title),
        backgroundColor: scheme.primary,
        foregroundColor: scheme.onPrimary,
      ),
      body: Center(
        child: Padding(
          padding: const EdgeInsets.all(24.0),
          child: Text(
            'هذه الشاشة ستتوفر قريبا في التطبيق مع واجهات برمجية للادمن يمكنني ربطها فور توفرها.\nضمن API /api/v1 للادمن حاليا لا توجد نقاط توفرها.',
            textAlign: TextAlign.center,
            style: TextStyle(color: scheme.onSurface.withValues(alpha: 0.8), fontSize: 16),
          ),
        ),
      ),
    );
  }
}


// ========================= Job Seeker Dashboard (Mobile) =========================
class JobSeekerDashboardScreen extends StatelessWidget {
  final String token;
  final Map<String, dynamic> user;
  const JobSeekerDashboardScreen({super.key, required this.token, required this.user});

  String _wrapSessionUrl(String absoluteUrl) {
    final site = AppConfig.baseUrl.replaceFirst('api/v1/', '');
    String redirect = absoluteUrl;
    if (absoluteUrl.startsWith(site)) {
      redirect = absoluteUrl.substring(site.length);
      if (!redirect.startsWith('/')) redirect = '/$redirect';
    }
    final encodedToken = Uri.encodeComponent(token);
    final dest = Uri.encodeComponent(redirect);
    return '${site}mobile/session-login?token=$encodedToken&redirect=$dest';
  }

  void _open(BuildContext context, String title, String url) {
    final bridged = _wrapSessionUrl(url);
    Navigator.push(
      context,
      MaterialPageRoute(builder: (_) => AdminWebViewScreen(title: title, url: bridged)),
    );
  }

  Widget _jsCard(
    BuildContext context, {
    required String title,
    required IconData icon,
    required LinearGradient gradient,
    required VoidCallback onTap,
  }) {
    return InkWell(
      onTap: onTap,
      child: Container(
        decoration: BoxDecoration(
          gradient: gradient,
          borderRadius: BorderRadius.circular(16),
        ),
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.end,
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Icon(icon, size: 48, color: Colors.white.withValues(alpha: 0.95)),
            Text(
              title,
              textAlign: TextAlign.right,
              style: const TextStyle(
                color: Colors.white,
                fontSize: 16,
                fontWeight: FontWeight.bold,
              ),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final site = AppConfig.baseUrl.replaceFirst('api/v1/', '');
    return Scaffold(
      appBar: AppBar(
        title: const Text('لوحة الباحث'),
        backgroundColor: scheme.primary,
        foregroundColor: scheme.onPrimary,
      ),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: GridView.count(
          crossAxisCount: 2,
          crossAxisSpacing: 12,
          mainAxisSpacing: 12,
          children: [
            _jsCard(
              context,
              title: 'لوحتي',
              icon: Icons.dashboard,
              gradient: const LinearGradient(colors: [Color(0xFF4F46E5), Color(0xFF3730A3)]),
              onTap: () => _open(context, 'لوحة الباحث', '${site}jobseeker'),
            ),
            _jsCard(
              context,
              title: 'الملف الشخصي',
              icon: Icons.account_circle_outlined,
              gradient: const LinearGradient(colors: [Color(0xFF9333EA), Color(0xFF7E22CE)]),
              onTap: () => _open(context, 'الملف الشخصي', '${site}jobseeker/profile'),
            ),
            _jsCard(
              context,
              title: 'الإشعارات',
              icon: Icons.notifications_active_outlined,
              gradient: const LinearGradient(colors: [Color(0xFF10B981), Color(0xFF059669)]),
              onTap: () => _open(context, 'الإشعارات', '${site}notifications'),
            ),
            _jsCard(
              context,
              title: 'تصفح الوظائف',
              icon: Icons.search,
              gradient: const LinearGradient(colors: [Color(0xFF0EA5E9), Color(0xFF0369A1)]),
              onTap: () => _open(context, 'الوظائف', '${site}jobs'),
            ),
          ],
        ),
      ),
    );
  }
}
