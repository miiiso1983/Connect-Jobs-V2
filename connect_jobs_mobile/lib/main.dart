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
	  // Simple fade-in animation for a modern feel.
	  // Use a post-frame callback (instead of Future.delayed) to avoid pending timers in widget tests.
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
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: AppTheme.backgroundGradient,
        ),
        child: SafeArea(
          child: Center(
            child: SingleChildScrollView(
              padding: const EdgeInsets.all(AppTheme.spacingL),
              child: AnimatedOpacity(
                duration: const Duration(milliseconds: 600),
                opacity: _animateIn ? 1.0 : 0.0,
                child: AnimatedSlide(
                  duration: const Duration(milliseconds: 600),
                  offset: _animateIn ? Offset.zero : const Offset(0, 0.1),
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      // Logo with gradient background
                      Container(
                        width: 110,
                        height: 110,
                        decoration: BoxDecoration(
                          gradient: AppTheme.primaryGradient,
                          shape: BoxShape.circle,
                          boxShadow: [
                            BoxShadow(
                              color: AppTheme.primaryNavy.withValues(alpha: 0.3),
                              blurRadius: 20,
                              offset: const Offset(0, 8),
                            ),
                          ],
                        ),
                        child: Padding(
                          padding: const EdgeInsets.all(12),
                          child: Image.network(
                            '${AppConfig.baseUrl.replaceFirst('api/v1/', '')}images/brand/logo.png',
                            width: 80,
                            height: 80,
                            fit: BoxFit.contain,
                            errorBuilder: (_, __, ___) => const Icon(
                              Icons.work_rounded,
                              size: 50,
                              color: Colors.white,
                            ),
                          ),
                        ),
                      ),
                      const SizedBox(height: AppTheme.spacingL),

                      // App Title
                      const Text(
                        'Connect Jobs',
                        style: TextStyle(
                          fontSize: 32,
                          fontWeight: FontWeight.bold,
                          color: AppTheme.primaryNavy,
                          letterSpacing: -0.5,
                        ),
                      ),
                      const SizedBox(height: AppTheme.spacingXS),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 6),
                        decoration: BoxDecoration(
                          color: AppTheme.secondaryGold.withValues(alpha: 0.15),
                          borderRadius: BorderRadius.circular(AppTheme.radiusRound),
                        ),
                        child: const Text(
                          'منصة التوظيف الطبي',
                          style: TextStyle(
                            fontSize: 14,
                            fontWeight: FontWeight.w500,
                            color: AppTheme.primaryNavy,
                          ),
                        ),
                      ),
                      const SizedBox(height: AppTheme.spacingXL),

                      // Login Form Card
                      Container(
                        decoration: BoxDecoration(
                          color: AppTheme.surfaceWhite,
                          borderRadius: BorderRadius.circular(AppTheme.radiusXLarge),
                          boxShadow: AppTheme.mediumShadow,
                        ),
                        child: Padding(
                          padding: const EdgeInsets.all(AppTheme.spacingL),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              // Welcome text
                              const Text(
                                'مرحباً بعودتك! 👋',
                                style: TextStyle(
                                  fontSize: 20,
                                  fontWeight: FontWeight.bold,
                                  color: AppTheme.textPrimary,
                                ),
                              ),
                              const SizedBox(height: 4),
                              const Text(
                                'سجّل دخولك للوصول إلى حسابك',
                                style: TextStyle(
                                  fontSize: 14,
                                  color: AppTheme.textSecondary,
                                ),
                              ),
                              const SizedBox(height: AppTheme.spacingL),

                              // Email Field
                              _buildInputField(
                                controller: _emailController,
                                label: 'البريد الإلكتروني',
                                hint: 'example@email.com',
                                icon: Icons.email_outlined,
                                keyboardType: TextInputType.emailAddress,
                              ),
                              const SizedBox(height: AppTheme.spacingM),

                              // Password Field
                              _buildInputField(
                                controller: _passwordController,
                                label: 'كلمة المرور',
                                hint: '••••••••',
                                icon: Icons.lock_outline,
                                isPassword: true,
                                obscureText: _obscure,
                                onToggleObscure: () => setState(() => _obscure = !_obscure),
                              ),
                              const SizedBox(height: AppTheme.spacingL),

                              // Error Message
                              if (_errorMessage.isNotEmpty)
                                Container(
                                  width: double.infinity,
                                  padding: const EdgeInsets.all(12),
                                  margin: const EdgeInsets.only(bottom: AppTheme.spacingM),
                                  decoration: BoxDecoration(
                                    color: AppTheme.accentRed.withValues(alpha: 0.1),
                                    borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
                                    border: Border.all(color: AppTheme.accentRed.withValues(alpha: 0.3)),
                                  ),
                                  child: Row(
                                    children: [
                                      const Icon(Icons.error_outline, color: AppTheme.accentRed, size: 20),
                                      const SizedBox(width: 8),
                                      Expanded(
                                        child: Text(
                                          _errorMessage,
                                          style: const TextStyle(color: AppTheme.accentRed, fontSize: 13),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),

                              // Login Button with gradient
                              SizedBox(
                                width: double.infinity,
                                child: Container(
                                  decoration: BoxDecoration(
                                    gradient: _isLoading ? null : AppTheme.primaryGradient,
                                    borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
                                    boxShadow: _isLoading ? null : [
                                      BoxShadow(
                                        color: AppTheme.primaryNavy.withValues(alpha: 0.3),
                                        blurRadius: 12,
                                        offset: const Offset(0, 4),
                                      ),
                                    ],
                                  ),
                                  child: ElevatedButton(
                                    onPressed: _isLoading ? null : _login,
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: Colors.transparent,
                                      shadowColor: Colors.transparent,
                                      padding: const EdgeInsets.symmetric(vertical: 16),
                                      shape: RoundedRectangleBorder(
                                        borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
                                      ),
                                    ),
                                    child: _isLoading
                                        ? const SizedBox(
                                            width: 22,
                                            height: 22,
                                            child: CircularProgressIndicator(
                                              strokeWidth: 2.5,
                                              color: Colors.white,
                                            ),
                                          )
                                        : const Row(
                                            mainAxisAlignment: MainAxisAlignment.center,
                                            children: [
                                              Icon(Icons.login_rounded, color: Colors.white),
                                              SizedBox(width: 8),
                                              Text(
                                                'تسجيل الدخول',
                                                style: TextStyle(
                                                  fontSize: 16,
                                                  fontWeight: FontWeight.w600,
                                                  color: Colors.white,
                                                ),
                                              ),
                                            ],
                                          ),
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),

                      const SizedBox(height: AppTheme.spacingL),

                      // Divider with text
                      Row(
                        children: [
                          Expanded(child: Container(height: 1, color: AppTheme.borderLight)),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 16),
                            child: const Text(
                              'أو',
                              style: TextStyle(color: AppTheme.textMuted, fontSize: 14),
                            ),
                          ),
                          Expanded(child: Container(height: 1, color: AppTheme.borderLight)),
                        ],
                      ),

                      const SizedBox(height: AppTheme.spacingL),

                      // Register Options
                      Row(
                        children: [
                          Expanded(
                            child: _buildSecondaryButton(
                              icon: Icons.person_add_alt_1_rounded,
                              label: 'باحث عن عمل',
                              onPressed: () => Navigator.push(
                                context,
                                MaterialPageRoute(builder: (_) => const RegisterJobSeekerScreen()),
                              ),
                            ),
                          ),
                          const SizedBox(width: AppTheme.spacingM),
                          Expanded(
                            child: _buildSecondaryButton(
                              icon: Icons.business_rounded,
                              label: 'شركة',
                              onPressed: () => Navigator.push(
                                context,
                                MaterialPageRoute(builder: (_) => const RegisterCompanyScreen()),
                              ),
                            ),
                          ),
                        ],
                      ),

                      const SizedBox(height: AppTheme.spacingM),

                      // Guest Browse Button
                      Container(
                        width: double.infinity,
                        decoration: BoxDecoration(
                          border: Border.all(color: AppTheme.secondaryGold, width: 1.5),
                          borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
                        ),
                        child: TextButton.icon(
                          onPressed: () => Navigator.pushReplacement(
                            context,
                            MaterialPageRoute(
                              builder: (_) => JobsScreen(
                                token: '',
                                user: const {'role': 'guest'},
                              ),
                            ),
                          ),
                          icon: const Icon(Icons.visibility_rounded, color: AppTheme.secondaryGoldDark),
                          label: const Text(
                            'تصفح الوظائف كضيف',
                            style: TextStyle(
                              color: AppTheme.secondaryGoldDark,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                          style: TextButton.styleFrom(
                            padding: const EdgeInsets.symmetric(vertical: 14),
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
      ),
    );
  }

  Widget _buildInputField({
    required TextEditingController controller,
    required String label,
    required String hint,
    required IconData icon,
    TextInputType? keyboardType,
    bool isPassword = false,
    bool obscureText = false,
    VoidCallback? onToggleObscure,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: AppTheme.textPrimary,
          ),
        ),
        const SizedBox(height: 8),
        TextField(
          controller: controller,
          keyboardType: keyboardType,
          obscureText: isPassword && obscureText,
          style: const TextStyle(fontSize: 15),
          decoration: InputDecoration(
            hintText: hint,
            prefixIcon: Container(
              margin: const EdgeInsets.only(left: 12, right: 8),
              child: Icon(icon, color: AppTheme.primaryNavy, size: 22),
            ),
            prefixIconConstraints: const BoxConstraints(minWidth: 48),
            suffixIcon: isPassword
                ? IconButton(
                    icon: Icon(
                      obscureText ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                      color: AppTheme.textMuted,
                    ),
                    onPressed: onToggleObscure,
                  )
                : null,
            filled: true,
            fillColor: AppTheme.surfaceLight,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
              borderSide: BorderSide.none,
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
              borderSide: const BorderSide(color: AppTheme.borderLight),
            ),
            focusedBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
              borderSide: const BorderSide(color: AppTheme.primaryNavy, width: 2),
            ),
            contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
          ),
        ),
      ],
    );
  }

  Widget _buildSecondaryButton({
    required IconData icon,
    required String label,
    required VoidCallback onPressed,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: AppTheme.surfaceWhite,
        borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
        border: Border.all(color: AppTheme.borderLight),
        boxShadow: [
          BoxShadow(
            color: AppTheme.primaryNavy.withValues(alpha: 0.05),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onPressed,
          borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
          child: Padding(
            padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 12),
            child: Column(
              children: [
                Container(
                  width: 44,
                  height: 44,
                  decoration: BoxDecoration(
                    color: AppTheme.primaryNavy.withValues(alpha: 0.08),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(icon, color: AppTheme.primaryNavy, size: 24),
                ),
                const SizedBox(height: 8),
                Text(
                  label,
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w600,
                    color: AppTheme.textPrimary,
                  ),
                ),
              ],
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

  bool _isLoggingOut = false;

  Future<void> _logout() async {
    if (_isLoggingOut) return;
    setState(() => _isLoggingOut = true);

	  // Navigate immediately, then unregister FCM in background.
	  // Use pushAndRemoveUntil to fully reset navigation stack (avoids occasional black screen on iOS).
	  if (!mounted) return;
	  Navigator.pushAndRemoveUntil(
	    context,
	    MaterialPageRoute(builder: (context) => const LoginScreen()),
	    (route) => false,
	  );

    // Unregister FCM token in background (don't wait)
    try {
      final auth = AuthService();
      // ignore: unawaited_futures
      auth.unregisterFCMTokenOnLogout(widget.token).then((_) {}, onError: (e) {
        debugPrint('Failed to unregister FCM token on logout: $e');
      });
    } catch (e) {
      debugPrint('Failed to unregister FCM token on logout: $e');
    }
  }

  void _showSearchDialog() {
    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      backgroundColor: Colors.transparent,
      builder: (context) => StatefulBuilder(
        builder: (context, setModalState) => Container(
          decoration: const BoxDecoration(
            color: AppTheme.surfaceWhite,
            borderRadius: BorderRadius.vertical(top: Radius.circular(AppTheme.radiusXLarge)),
          ),
          padding: EdgeInsets.only(
            bottom: MediaQuery.of(context).viewInsets.bottom,
          ),
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(AppTheme.spacingL),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Handle bar
                Center(
                  child: Container(
                    width: 40,
                    height: 4,
                    decoration: BoxDecoration(
                      color: AppTheme.borderLight,
                      borderRadius: BorderRadius.circular(2),
                    ),
                  ),
                ),
                const SizedBox(height: AppTheme.spacingM),

                // Title
                Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: AppTheme.primaryNavy.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.search_rounded, color: AppTheme.primaryNavy),
                    ),
                    const SizedBox(width: 12),
                    const Text(
                      'البحث والفلترة',
                      style: TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: AppTheme.textPrimary,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: AppTheme.spacingL),

                // Search field
                TextField(
                  controller: _searchController,
                  decoration: InputDecoration(
                    hintText: 'ابحث عن وظيفة...',
                    prefixIcon: const Icon(Icons.search_rounded, color: AppTheme.primaryNavy),
                    filled: true,
                    fillColor: AppTheme.surfaceLight,
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
                      borderSide: BorderSide.none,
                    ),
                    enabledBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
                      borderSide: const BorderSide(color: AppTheme.borderLight),
                    ),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
                      borderSide: const BorderSide(color: AppTheme.primaryNavy, width: 2),
                    ),
                  ),
                ),
                const SizedBox(height: AppTheme.spacingM),

                // Province filter
                _buildFilterDropdown<String>(
                  label: 'المحافظة',
                  icon: Icons.location_on_rounded,
                  value: _selectedProvince,
                  items: [
                    const DropdownMenuItem<String>(value: null, child: Text('جميع المحافظات')),
                    ..._provinces.map((p) => DropdownMenuItem<String>(value: p, child: Text(p))),
                  ],
                  onChanged: (v) => setModalState(() => _selectedProvince = v),
                ),
                const SizedBox(height: AppTheme.spacingM),

                // Speciality filter
                _buildFilterDropdown<String>(
                  label: 'التخصص',
                  icon: Icons.medical_services_rounded,
                  value: _selectedSpeciality,
                  items: [
                    const DropdownMenuItem<String>(value: null, child: Text('جميع التخصصات')),
                    ..._specialities.map((s) => DropdownMenuItem<String>(value: s, child: Text(_getSpecialityName(s)))),
                  ],
                  onChanged: (v) => setModalState(() => _selectedSpeciality = v),
                ),
                const SizedBox(height: AppTheme.spacingM),

                // Sort options
                _buildFilterDropdown<String>(
                  label: 'ترتيب النتائج',
                  icon: Icons.sort_rounded,
                  value: '$_sortBy-$_sortOrder',
                  items: const [
                    DropdownMenuItem<String>(value: 'id-desc', child: Text('الأحدث أولاً')),
                    DropdownMenuItem<String>(value: 'id-asc', child: Text('الأقدم أولاً')),
                    DropdownMenuItem<String>(value: 'title-asc', child: Text('العنوان (أ-ي)')),
                    DropdownMenuItem<String>(value: 'title-desc', child: Text('العنوان (ي-أ)')),
                  ],
                  onChanged: (v) {
                    if (v != null) {
                      final parts = v.split('-');
                      setModalState(() {
                        _sortBy = parts[0];
                        _sortOrder = parts[1];
                      });
                    }
                  },
                ),
                const SizedBox(height: AppTheme.spacingL),

                // Action buttons
                Row(
                  children: [
                    Expanded(
                      child: OutlinedButton.icon(
                        onPressed: () {
                          setModalState(() {
                            _searchController.clear();
                            _selectedProvince = null;
                            _selectedSpeciality = null;
                            _sortBy = 'id';
                            _sortOrder = 'desc';
                          });
                        },
                        icon: const Icon(Icons.clear_all_rounded),
                        label: const Text('مسح الكل'),
                        style: OutlinedButton.styleFrom(
                          foregroundColor: AppTheme.textSecondary,
                          side: const BorderSide(color: AppTheme.borderLight),
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppTheme.radiusMedium)),
                        ),
                      ),
                    ),
                    const SizedBox(width: AppTheme.spacingM),
                    Expanded(
                      flex: 2,
                      child: Container(
                        decoration: BoxDecoration(
                          gradient: AppTheme.primaryGradient,
                          borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
                          boxShadow: [
                            BoxShadow(
                              color: AppTheme.primaryNavy.withValues(alpha: 0.3),
                              blurRadius: 8,
                              offset: const Offset(0, 2),
                            ),
                          ],
                        ),
                        child: ElevatedButton.icon(
                          onPressed: () {
                            Navigator.pop(context);
                            setState(() {});
                            _loadJobs();
                          },
                          icon: const Icon(Icons.search_rounded, color: Colors.white),
                          label: const Text('بحث', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600)),
                          style: ElevatedButton.styleFrom(
                            backgroundColor: Colors.transparent,
                            shadowColor: Colors.transparent,
                            padding: const EdgeInsets.symmetric(vertical: 14),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppTheme.radiusMedium)),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: AppTheme.spacingM),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildFilterDropdown<T>({
    required String label,
    required IconData icon,
    required T? value,
    required List<DropdownMenuItem<T>> items,
    required ValueChanged<T?> onChanged,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            Icon(icon, size: 16, color: AppTheme.textMuted),
            const SizedBox(width: 6),
            Text(label, style: const TextStyle(fontSize: 13, color: AppTheme.textSecondary, fontWeight: FontWeight.w500)),
          ],
        ),
        const SizedBox(height: 8),
        Container(
          decoration: BoxDecoration(
            color: AppTheme.surfaceLight,
            borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
            border: Border.all(color: AppTheme.borderLight),
          ),
          child: DropdownButtonFormField<T>(
            value: value,
            items: items,
            onChanged: onChanged,
            decoration: const InputDecoration(
              border: InputBorder.none,
              contentPadding: EdgeInsets.symmetric(horizontal: 16, vertical: 12),
            ),
            dropdownColor: AppTheme.surfaceWhite,
            borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
          ),
        ),
      ],
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

  bool get _isGuest => widget.user['role'] == 'guest';

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: AppTheme.backgroundGradient,
        ),
        child: SafeArea(
          child: Column(
            children: [
              // Custom AppBar with gradient
              Container(
                decoration: BoxDecoration(
                  gradient: AppTheme.primaryGradient,
                  boxShadow: [
                    BoxShadow(
                      color: AppTheme.primaryNavy.withValues(alpha: 0.2),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 8),
                  child: Row(
                    children: [
                      // Title with icon
                      Expanded(
                        child: Row(
                          children: [
                            Container(
                              width: 40,
                              height: 40,
                              decoration: BoxDecoration(
                                color: Colors.white.withValues(alpha: 0.2),
                                borderRadius: BorderRadius.circular(10),
                              ),
                              child: const Icon(Icons.work_rounded, color: Colors.white, size: 22),
                            ),
                            const SizedBox(width: 12),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    _isGuest ? 'تصفح الوظائف' : 'الوظائف المتاحة',
                                    style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 18,
                                      fontWeight: FontWeight.bold,
                                    ),
                                  ),
                                  if (!isLoading)
                                    Text(
                                      _isGuest ? 'وضع الضيف' : '${jobs.length} وظيفة',
                                      style: TextStyle(
                                        color: Colors.white.withValues(alpha: 0.8),
                                        fontSize: 12,
                                      ),
                                    ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                      // Action buttons
                      _buildAppBarButton(Icons.search_rounded, 'بحث', _showSearchDialog),
                      if (!_isGuest)
                        _buildAppBarButton(Icons.person_rounded, 'الملف', () {
                          Navigator.push(context, MaterialPageRoute(
                            builder: (_) => ProfileScreen(token: widget.token, user: widget.user),
                          ));
                        }),
                      if (widget.user['role'] == 'company')
                        _buildAppBarButton(Icons.dashboard_rounded, 'لوحة', () {
                          Navigator.push(context, MaterialPageRoute(
                            builder: (_) => CompanyDashboardScreen(token: widget.token, user: widget.user),
                          ));
                        }),
                      if (widget.user['role'] == 'jobseeker') ...[
                        _buildAppBarButton(Icons.favorite_rounded, 'المفضلة', () {
                          Navigator.push(context, MaterialPageRoute(
                            builder: (_) => FavoritesScreen(token: widget.token, user: widget.user),
                          ));
                        }),
                        _buildAppBarButton(Icons.assignment_rounded, 'طلباتي', () {
                          Navigator.push(context, MaterialPageRoute(
                            builder: (_) => MyApplicationsScreen(token: widget.token, user: widget.user),
                          ));
                        }),
                      ],
                      _buildAppBarButton(
                        _isGuest ? Icons.login_rounded : Icons.logout_rounded,
                        _isGuest ? 'دخول' : 'خروج',
                        _isGuest
                          ? () => Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const LoginScreen()))
                          : _logout,
                      ),
                    ],
                  ),
                ),
              ),

              // Active filters indicator
              if (_hasActiveFilters())
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                  decoration: BoxDecoration(
                    color: AppTheme.secondaryGold.withValues(alpha: 0.1),
                    border: Border(
                      bottom: BorderSide(color: AppTheme.secondaryGold.withValues(alpha: 0.3)),
                    ),
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(6),
                        decoration: BoxDecoration(
                          color: AppTheme.secondaryGold.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(8),
                        ),
                        child: const Icon(Icons.filter_list_rounded, size: 16, color: AppTheme.secondaryGoldDark),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          _getActiveFiltersText(),
                          style: const TextStyle(color: AppTheme.secondaryGoldDark, fontSize: 12, fontWeight: FontWeight.w500),
                        ),
                      ),
                      TextButton.icon(
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
                        icon: const Icon(Icons.close_rounded, size: 16),
                        label: const Text('مسح'),
                        style: TextButton.styleFrom(
                          foregroundColor: AppTheme.secondaryGoldDark,
                          padding: const EdgeInsets.symmetric(horizontal: 8),
                        ),
                      ),
                    ],
                  ),
                ),

              // Main content
              Expanded(
                child: isLoading
                    ? _buildLoadingState()
                    : errorMessage.isNotEmpty
                        ? _buildErrorState()
                        : jobs.isEmpty
                            ? _buildEmptyState()
                            : RefreshIndicator(
                                onRefresh: _loadJobs,
                                color: AppTheme.primaryNavy,
                                child: ListView.builder(
                                  padding: const EdgeInsets.all(AppTheme.spacingM),
                                  itemCount: jobs.length + ((_currentPage < _lastPage) ? 1 : 0),
                                  itemBuilder: (context, index) {
                                    if (index >= jobs.length) {
                                      return _buildLoadMoreButton();
                                    }
                                    return _buildJobCard(jobs[index]);
                                  },
                                ),
                              ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildAppBarButton(IconData icon, String tooltip, VoidCallback onPressed) {
    return Tooltip(
      message: tooltip,
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: onPressed,
          borderRadius: BorderRadius.circular(10),
          child: Container(
            width: 40,
            height: 40,
            decoration: BoxDecoration(
              color: Colors.white.withValues(alpha: 0.1),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Icon(icon, color: Colors.white, size: 20),
          ),
        ),
      ),
    );
  }

  Widget _buildLoadingState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            width: 80,
            height: 80,
            decoration: BoxDecoration(
              color: AppTheme.primaryNavy.withValues(alpha: 0.1),
              shape: BoxShape.circle,
            ),
            child: const Center(
              child: CircularProgressIndicator(
                color: AppTheme.primaryNavy,
                strokeWidth: 3,
              ),
            ),
          ),
          const SizedBox(height: AppTheme.spacingM),
          const Text(
            'جاري تحميل الوظائف...',
            style: TextStyle(
              color: AppTheme.textSecondary,
              fontSize: 14,
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.spacingXL),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 80,
              height: 80,
              decoration: BoxDecoration(
                color: AppTheme.accentRed.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.error_outline_rounded, size: 40, color: AppTheme.accentRed),
            ),
            const SizedBox(height: AppTheme.spacingM),
            Text(
              errorMessage,
              style: const TextStyle(color: AppTheme.accentRed, fontSize: 14),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: AppTheme.spacingL),
            ElevatedButton.icon(
              onPressed: _loadJobs,
              icon: const Icon(Icons.refresh_rounded),
              label: const Text('إعادة المحاولة'),
              style: ElevatedButton.styleFrom(
                backgroundColor: AppTheme.primaryNavy,
                foregroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppTheme.radiusMedium)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.spacingXL),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              width: 100,
              height: 100,
              decoration: BoxDecoration(
                color: AppTheme.secondaryGold.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.search_off_rounded, size: 50, color: AppTheme.secondaryGold),
            ),
            const SizedBox(height: AppTheme.spacingL),
            const Text(
              'لا توجد نتائج مطابقة',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: AppTheme.textPrimary,
              ),
            ),
            const SizedBox(height: AppTheme.spacingS),
            const Text(
              'جرّب تعديل الفلاتر أو استخدام كلمة بحث أكثر عمومية',
              style: TextStyle(fontSize: 13, color: AppTheme.textSecondary),
              textAlign: TextAlign.center,
            ),
            if (_hasActiveFilters()) ...[
              const SizedBox(height: AppTheme.spacingL),
              OutlinedButton.icon(
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
                icon: const Icon(Icons.filter_list_off_rounded),
                label: const Text('مسح الفلاتر'),
                style: OutlinedButton.styleFrom(
                  foregroundColor: AppTheme.primaryNavy,
                  side: const BorderSide(color: AppTheme.primaryNavy),
                  padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 12),
                  shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppTheme.radiusMedium)),
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildLoadMoreButton() {
    return Container(
      margin: const EdgeInsets.only(bottom: AppTheme.spacingM),
      child: Container(
        decoration: BoxDecoration(
          border: Border.all(color: AppTheme.primaryNavy.withValues(alpha: 0.3)),
          borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
        ),
        child: Material(
          color: Colors.transparent,
          child: InkWell(
            onTap: _isLoadingMore ? null : _loadMoreJobs,
            borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
            child: Padding(
              padding: const EdgeInsets.symmetric(vertical: 14),
              child: Center(
                child: _isLoadingMore
                    ? const SizedBox(
                        height: 20,
                        width: 20,
                        child: CircularProgressIndicator(strokeWidth: 2, color: AppTheme.primaryNavy),
                      )
                    : const Row(
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Icon(Icons.expand_more_rounded, color: AppTheme.primaryNavy),
                          SizedBox(width: 8),
                          Text(
                            'تحميل المزيد',
                            style: TextStyle(
                              color: AppTheme.primaryNavy,
                              fontWeight: FontWeight.w600,
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

  Widget _buildJobCard(Map<String, dynamic> job) {
    final jobId = (job['id'] is num) ? (job['id'] as num).toInt() : (int.tryParse('${job['id']}') ?? -1);
    final isFavorite = _favoriteIds.contains(jobId);
    final isOpen = job['status'] == 'open';

    return Container(
      margin: const EdgeInsets.only(bottom: AppTheme.spacingM),
      decoration: BoxDecoration(
        color: AppTheme.surfaceWhite,
        borderRadius: BorderRadius.circular(AppTheme.radiusLarge),
        boxShadow: AppTheme.lightShadow,
        border: Border.all(color: AppTheme.borderLight),
      ),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          onTap: () {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) => JobDetailsScreen(job: job, token: widget.token, user: widget.user),
              ),
            );
          },
          borderRadius: BorderRadius.circular(AppTheme.radiusLarge),
          child: Padding(
            padding: const EdgeInsets.all(AppTheme.spacingM),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Header row: Title + Favorite
                Row(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // Company logo placeholder
                    Container(
                      width: 50,
                      height: 50,
                      decoration: BoxDecoration(
                        color: AppTheme.primaryNavy.withValues(alpha: 0.08),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.business_rounded, color: AppTheme.primaryNavy, size: 26),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            job['title'] ?? '',
                            style: const TextStyle(
                              fontSize: 16,
                              fontWeight: FontWeight.bold,
                              color: AppTheme.textPrimary,
                            ),
                            maxLines: 2,
                            overflow: TextOverflow.ellipsis,
                          ),
                          const SizedBox(height: 4),
                          Text(
                            job['company']?['company_name'] ?? 'شركة غير محددة',
                            style: const TextStyle(
                              fontSize: 13,
                              color: AppTheme.primaryNavy,
                              fontWeight: FontWeight.w500,
                            ),
                          ),
                        ],
                      ),
                    ),
                    if (widget.user['role'] == 'jobseeker')
                      GestureDetector(
                        onTap: () => _toggleFavorite(jobId),
                        child: Container(
                          width: 38,
                          height: 38,
                          decoration: BoxDecoration(
                            color: isFavorite
                                ? AppTheme.accentRed.withValues(alpha: 0.1)
                                : AppTheme.surfaceLight,
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: Icon(
                            isFavorite ? Icons.favorite_rounded : Icons.favorite_border_rounded,
                            color: AppTheme.accentRed,
                            size: 20,
                          ),
                        ),
                      ),
                  ],
                ),
                const SizedBox(height: AppTheme.spacingM),

                // Location and Speciality row
                Row(
                  children: [
                    _buildInfoChip(Icons.location_on_rounded, job['province'] ?? ''),
                    const SizedBox(width: 8),
                    Expanded(
                      child: _buildInfoChip(Icons.medical_services_rounded, _getSpecialityName(job['speciality'] ?? '')),
                    ),
                  ],
                ),
                const SizedBox(height: AppTheme.spacingM),

                // Description
                Text(
                  job['description'] ?? '',
                  style: const TextStyle(fontSize: 13, color: AppTheme.textSecondary, height: 1.4),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: AppTheme.spacingM),

                // Footer: Status + View button
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                      decoration: BoxDecoration(
                        color: isOpen
                            ? AppTheme.accentGreen.withValues(alpha: 0.1)
                            : AppTheme.accentRed.withValues(alpha: 0.1),
                        borderRadius: BorderRadius.circular(AppTheme.radiusRound),
                      ),
                      child: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Icon(
                            isOpen ? Icons.check_circle_rounded : Icons.cancel_rounded,
                            size: 14,
                            color: isOpen ? AppTheme.accentGreen : AppTheme.accentRed,
                          ),
                          const SizedBox(width: 4),
                          Text(
                            isOpen ? 'متاحة' : 'مغلقة',
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: isOpen ? AppTheme.accentGreen : AppTheme.accentRed,
                            ),
                          ),
                        ],
                      ),
                    ),
                    Container(
                      decoration: BoxDecoration(
                        gradient: AppTheme.primaryGradient,
                        borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
                        boxShadow: [
                          BoxShadow(
                            color: AppTheme.primaryNavy.withValues(alpha: 0.2),
                            blurRadius: 8,
                            offset: const Offset(0, 2),
                          ),
                        ],
                      ),
                      child: Material(
                        color: Colors.transparent,
                        child: InkWell(
                          onTap: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (_) => JobDetailsScreen(job: job, token: widget.token, user: widget.user),
                              ),
                            );
                          },
                          borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
                          child: const Padding(
                            padding: EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                            child: Row(
                              children: [
                                Text(
                                  'التفاصيل',
                                  style: TextStyle(
                                    color: Colors.white,
                                    fontSize: 13,
                                    fontWeight: FontWeight.w600,
                                  ),
                                ),
                                SizedBox(width: 4),
                                Icon(Icons.arrow_forward_rounded, color: Colors.white, size: 16),
                              ],
                            ),
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildInfoChip(IconData icon, String text) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 6),
      decoration: BoxDecoration(
        color: AppTheme.surfaceLight,
        borderRadius: BorderRadius.circular(8),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 14, color: AppTheme.textMuted),
          const SizedBox(width: 4),
          Flexible(
            child: Text(
              text,
              style: const TextStyle(fontSize: 12, color: AppTheme.textSecondary),
              overflow: TextOverflow.ellipsis,
            ),
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
  bool get _isGuest => widget.user['role'] == 'guest';

  void _showLoginPrompt() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (context) => Container(
        decoration: const BoxDecoration(
          color: AppTheme.surfaceWhite,
          borderRadius: BorderRadius.vertical(top: Radius.circular(AppTheme.radiusXLarge)),
        ),
        padding: const EdgeInsets.all(AppTheme.spacingL),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: AppTheme.borderLight,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            const SizedBox(height: AppTheme.spacingL),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppTheme.secondaryGold.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.login_rounded, size: 40, color: AppTheme.secondaryGold),
            ),
            const SizedBox(height: AppTheme.spacingM),
            const Text(
              'تسجيل الدخول مطلوب',
              style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: AppTheme.textPrimary),
            ),
            const SizedBox(height: AppTheme.spacingS),
            const Text(
              'يجب عليك تسجيل الدخول أو إنشاء حساب للتقديم على الوظائف',
              style: TextStyle(color: AppTheme.textSecondary),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: AppTheme.spacingL),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => Navigator.pop(context),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      side: const BorderSide(color: AppTheme.borderLight),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppTheme.radiusMedium)),
                    ),
                    child: const Text('إلغاء'),
                  ),
                ),
                const SizedBox(width: AppTheme.spacingM),
                Expanded(
                  flex: 2,
                  child: Container(
                    decoration: BoxDecoration(
                      gradient: AppTheme.primaryGradient,
                      borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
                    ),
                    child: ElevatedButton.icon(
                      onPressed: () {
                        Navigator.pop(context);
                        Navigator.pushReplacement(context, MaterialPageRoute(builder: (_) => const LoginScreen()));
                      },
                      icon: const Icon(Icons.login_rounded, color: Colors.white),
                      label: const Text('تسجيل الدخول', style: TextStyle(color: Colors.white)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.transparent,
                        shadowColor: Colors.transparent,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppTheme.radiusMedium)),
                      ),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: AppTheme.spacingM),
          ],
        ),
      ),
    );
  }

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
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (context) => Container(
        decoration: const BoxDecoration(
          color: AppTheme.surfaceWhite,
          borderRadius: BorderRadius.vertical(top: Radius.circular(AppTheme.radiusXLarge)),
        ),
        padding: const EdgeInsets.all(AppTheme.spacingL),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 40,
              height: 4,
              decoration: BoxDecoration(
                color: AppTheme.borderLight,
                borderRadius: BorderRadius.circular(2),
              ),
            ),
            const SizedBox(height: AppTheme.spacingL),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: AppTheme.accentGreen.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.send_rounded, size: 40, color: AppTheme.accentGreen),
            ),
            const SizedBox(height: AppTheme.spacingM),
            const Text(
              'تأكيد التقديم',
              style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: AppTheme.textPrimary),
            ),
            const SizedBox(height: AppTheme.spacingS),
            Text(
              'هل أنت متأكد من التقديم على وظيفة "${widget.job['title']}"؟',
              style: const TextStyle(color: AppTheme.textSecondary),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: AppTheme.spacingS),
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: AppTheme.surfaceLight,
                borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
              ),
              child: const Row(
                children: [
                  Icon(Icons.info_outline_rounded, size: 18, color: AppTheme.textMuted),
                  SizedBox(width: 8),
                  Expanded(
                    child: Text(
                      'سيتم استخدام ملفك الشخصي الحالي للتقديم',
                      style: TextStyle(fontSize: 12, color: AppTheme.textMuted),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: AppTheme.spacingL),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => Navigator.pop(context),
                    style: OutlinedButton.styleFrom(
                      padding: const EdgeInsets.symmetric(vertical: 14),
                      side: const BorderSide(color: AppTheme.borderLight),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppTheme.radiusMedium)),
                    ),
                    child: const Text('إلغاء'),
                  ),
                ),
                const SizedBox(width: AppTheme.spacingM),
                Expanded(
                  flex: 2,
                  child: Container(
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [AppTheme.accentGreen, Color(0xFF1B8B6A)],
                      ),
                      borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
                    ),
                    child: ElevatedButton.icon(
                      onPressed: () {
                        Navigator.pop(context);
                        _applyForJob();
                      },
                      icon: const Icon(Icons.check_rounded, color: Colors.white),
                      label: const Text('تأكيد التقديم', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600)),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: Colors.transparent,
                        shadowColor: Colors.transparent,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppTheme.radiusMedium)),
                      ),
                    ),
                  ),
                ),
              ],
            ),
            const SizedBox(height: AppTheme.spacingM),
          ],
        ),
      ),
    );
  }


  @override
  Widget build(BuildContext context) {
    final isOpen = widget.job['status'] == 'open';

    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(
          gradient: AppTheme.backgroundGradient,
        ),
        child: SafeArea(
          child: Column(
            children: [
              // Header with gradient
              Container(
                decoration: BoxDecoration(
                  gradient: AppTheme.primaryGradient,
                  boxShadow: [
                    BoxShadow(
                      color: AppTheme.primaryNavy.withValues(alpha: 0.2),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Padding(
                  padding: const EdgeInsets.all(AppTheme.spacingM),
                  child: Row(
                    children: [
                      GestureDetector(
                        onTap: () => Navigator.pop(context),
                        child: Container(
                          width: 40,
                          height: 40,
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.arrow_back_rounded, color: Colors.white),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Text(
                          'تفاصيل الوظيفة',
                          style: const TextStyle(
                            color: Colors.white,
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: isOpen
                              ? AppTheme.accentGreen.withValues(alpha: 0.2)
                              : AppTheme.accentRed.withValues(alpha: 0.2),
                          borderRadius: BorderRadius.circular(AppTheme.radiusRound),
                        ),
                        child: Row(
                          mainAxisSize: MainAxisSize.min,
                          children: [
                            Icon(
                              isOpen ? Icons.check_circle_rounded : Icons.cancel_rounded,
                              size: 14,
                              color: Colors.white,
                            ),
                            const SizedBox(width: 4),
                            Text(
                              isOpen ? 'متاحة' : 'مغلقة',
                              style: const TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.w600,
                                color: Colors.white,
                              ),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              ),

              // Content
              Expanded(
                child: SingleChildScrollView(
                  padding: const EdgeInsets.all(AppTheme.spacingM),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      // Job Title Card
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(AppTheme.spacingL),
                        decoration: BoxDecoration(
                          color: AppTheme.surfaceWhite,
                          borderRadius: BorderRadius.circular(AppTheme.radiusLarge),
                          boxShadow: AppTheme.lightShadow,
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Container(
                                  width: 60,
                                  height: 60,
                                  decoration: BoxDecoration(
                                    gradient: AppTheme.primaryGradient,
                                    borderRadius: BorderRadius.circular(14),
                                  ),
                                  child: const Icon(Icons.business_rounded, color: Colors.white, size: 30),
                                ),
                                const SizedBox(width: 14),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        widget.job['title'] ?? '',
                                        style: const TextStyle(
                                          fontSize: 20,
                                          fontWeight: FontWeight.bold,
                                          color: AppTheme.textPrimary,
                                        ),
                                      ),
                                      const SizedBox(height: 6),
                                      Text(
                                        widget.job['company']?['company_name'] ?? 'شركة غير محددة',
                                        style: const TextStyle(
                                          fontSize: 15,
                                          color: AppTheme.primaryNavy,
                                          fontWeight: FontWeight.w500,
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: AppTheme.spacingM),
                            Wrap(
                              spacing: 8,
                              runSpacing: 8,
                              children: [
                                _buildTag(Icons.location_on_rounded, widget.job['province'] ?? '', AppTheme.primaryNavy),
                                _buildTag(Icons.medical_services_rounded, widget.job['speciality'] ?? '', AppTheme.secondaryGold),
                              ],
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: AppTheme.spacingM),

                      // Company Info
                      _buildInfoCard(
                        title: 'معلومات الشركة',
                        icon: Icons.business_rounded,
                        children: [
                          _buildInfoRow(Icons.apartment_rounded, 'اسم الشركة', widget.job['company']?['company_name'] ?? 'غير محدد'),
                          _buildInfoRow(Icons.location_city_rounded, 'المحافظة', widget.job['company']?['province'] ?? 'غير محدد'),
                          _buildInfoRow(Icons.category_rounded, 'القطاع', widget.job['company']?['industry'] ?? 'غير محدد'),
                        ],
                      ),
                      const SizedBox(height: AppTheme.spacingM),

                      // Job Details
                      _buildInfoCard(
                        title: 'تفاصيل الوظيفة',
                        icon: Icons.work_rounded,
                        children: [
                          _buildInfoRow(Icons.medical_services_rounded, 'التخصص', widget.job['speciality'] ?? 'غير محدد'),
                          _buildInfoRow(Icons.location_on_rounded, 'المحافظة', widget.job['province'] ?? 'غير محدد'),
                        ],
                      ),
                      const SizedBox(height: AppTheme.spacingM),

                      // Description
                      _buildInfoCard(
                        title: 'وصف الوظيفة',
                        icon: Icons.description_rounded,
                        children: [
                          Text(
                            widget.job['description'] ?? 'لا يوجد وصف متاح',
                            style: const TextStyle(
                              fontSize: 14,
                              color: AppTheme.textSecondary,
                              height: 1.6,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: AppTheme.spacingM),

                      // Requirements
                      _buildInfoCard(
                        title: 'متطلبات الوظيفة',
                        icon: Icons.checklist_rounded,
                        children: [
                          Text(
                            widget.job['requirements'] ?? 'لا توجد متطلبات محددة',
                            style: const TextStyle(
                              fontSize: 14,
                              color: AppTheme.textSecondary,
                              height: 1.6,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: AppTheme.spacingXL),
                    ],
                  ),
                ),
              ),

              // Apply Button
              Container(
                padding: const EdgeInsets.all(AppTheme.spacingM),
                decoration: BoxDecoration(
                  color: AppTheme.surfaceWhite,
                  boxShadow: [
                    BoxShadow(
                      color: AppTheme.primaryNavy.withValues(alpha: 0.1),
                      blurRadius: 10,
                      offset: const Offset(0, -4),
                    ),
                  ],
                ),
                child: Container(
                  width: double.infinity,
                  decoration: BoxDecoration(
                    gradient: isOpen
                        ? (_isGuest ? AppTheme.primaryGradient : const LinearGradient(colors: [AppTheme.accentGreen, Color(0xFF1B8B6A)]))
                        : null,
                    color: isOpen ? null : AppTheme.textMuted,
                    borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
                    boxShadow: isOpen
                        ? [
                            BoxShadow(
                              color: (_isGuest ? AppTheme.primaryNavy : AppTheme.accentGreen).withValues(alpha: 0.3),
                              blurRadius: 12,
                              offset: const Offset(0, 4),
                            ),
                          ]
                        : null,
                  ),
                  child: ElevatedButton(
                    onPressed: isOpen && !_isApplying
                        ? (_isGuest ? _showLoginPrompt : _showApplicationDialog)
                        : null,
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.transparent,
                      shadowColor: Colors.transparent,
                      disabledBackgroundColor: Colors.transparent,
                      padding: const EdgeInsets.symmetric(vertical: 16),
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppTheme.radiusMedium)),
                    ),
                    child: _isApplying
                        ? const SizedBox(
                            width: 24,
                            height: 24,
                            child: CircularProgressIndicator(strokeWidth: 2.5, color: Colors.white),
                          )
                        : Row(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(
                                !isOpen ? Icons.block_rounded : (_isGuest ? Icons.login_rounded : Icons.send_rounded),
                                color: Colors.white,
                              ),
                              const SizedBox(width: 8),
                              Text(
                                !isOpen
                                    ? 'الوظيفة مغلقة'
                                    : (_isGuest ? 'سجّل الدخول للتقديم' : 'تقديم على الوظيفة'),
                                style: const TextStyle(
                                  fontSize: 16,
                                  fontWeight: FontWeight.w600,
                                  color: Colors.white,
                                ),
                              ),
                            ],
                          ),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildTag(IconData icon, String text, Color color) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: color.withValues(alpha: 0.1),
        borderRadius: BorderRadius.circular(AppTheme.radiusRound),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 16, color: color),
          const SizedBox(width: 6),
          Text(
            text,
            style: TextStyle(fontSize: 13, color: color, fontWeight: FontWeight.w500),
          ),
        ],
      ),
    );
  }

  Widget _buildInfoCard({
    required String title,
    required IconData icon,
    required List<Widget> children,
  }) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(AppTheme.spacingM),
      decoration: BoxDecoration(
        color: AppTheme.surfaceWhite,
        borderRadius: BorderRadius.circular(AppTheme.radiusLarge),
        boxShadow: AppTheme.lightShadow,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: AppTheme.primaryNavy.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, size: 20, color: AppTheme.primaryNavy),
              ),
              const SizedBox(width: 10),
              Text(
                title,
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: AppTheme.textPrimary,
                ),
              ),
            ],
          ),
          const SizedBox(height: AppTheme.spacingM),
          ...children,
        ],
      ),
    );
  }

  Widget _buildInfoRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Row(
        children: [
          Icon(icon, size: 18, color: AppTheme.textMuted),
          const SizedBox(width: 10),
          Text(
            '$label: ',
            style: const TextStyle(fontSize: 14, color: AppTheme.textMuted),
          ),
          Expanded(
            child: Text(
              value,
              style: const TextStyle(fontSize: 14, color: AppTheme.textPrimary, fontWeight: FontWeight.w500),
            ),
          ),
        ],
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
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              // Header
              Container(
                decoration: BoxDecoration(
                  gradient: AppTheme.primaryGradient,
                  boxShadow: [BoxShadow(color: AppTheme.primaryNavy.withValues(alpha: 0.2), blurRadius: 8, offset: const Offset(0, 2))],
                ),
                child: Padding(
                  padding: const EdgeInsets.all(AppTheme.spacingM),
                  child: Row(
                    children: [
                      GestureDetector(
                        onTap: () => Navigator.pop(context),
                        child: Container(
                          width: 40, height: 40,
                          decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(10)),
                          child: const Icon(Icons.arrow_back_rounded, color: Colors.white),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('طلباتي', style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                            Text('${applications.length} طلب', style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 13)),
                          ],
                        ),
                      ),
                      GestureDetector(
                        onTap: _loadApplications,
                        child: Container(
                          width: 40, height: 40,
                          decoration: BoxDecoration(color: AppTheme.secondaryGold, borderRadius: BorderRadius.circular(10)),
                          child: const Icon(Icons.refresh_rounded, color: AppTheme.primaryNavy, size: 22),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              // Content
              Expanded(
                child: isLoading
                    ? _buildLoadingState()
                    : errorMessage.isNotEmpty
                        ? _buildErrorState()
                        : applications.isEmpty
                            ? _buildEmptyState()
                            : ListView.builder(
                                padding: const EdgeInsets.all(AppTheme.spacingM),
                                itemCount: applications.length,
                                itemBuilder: (context, index) => _buildApplicationCard(applications[index]),
                              ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildLoadingState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(color: AppTheme.surfaceWhite, borderRadius: BorderRadius.circular(AppTheme.radiusLarge), boxShadow: AppTheme.lightShadow),
            child: const CircularProgressIndicator(color: AppTheme.primaryNavy),
          ),
          const SizedBox(height: AppTheme.spacingM),
          const Text('جاري تحميل الطلبات...', style: TextStyle(color: AppTheme.textSecondary)),
        ],
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.spacingL),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(color: AppTheme.accentRed.withValues(alpha: 0.1), shape: BoxShape.circle),
              child: const Icon(Icons.error_outline_rounded, size: 50, color: AppTheme.accentRed),
            ),
            const SizedBox(height: AppTheme.spacingM),
            Text(errorMessage, style: const TextStyle(color: AppTheme.accentRed, fontSize: 14), textAlign: TextAlign.center),
            const SizedBox(height: AppTheme.spacingL),
            Container(
              decoration: BoxDecoration(gradient: AppTheme.primaryGradient, borderRadius: BorderRadius.circular(AppTheme.radiusMedium)),
              child: ElevatedButton.icon(
                onPressed: _loadApplications,
                icon: const Icon(Icons.refresh_rounded, color: Colors.white),
                label: const Text('إعادة المحاولة', style: TextStyle(color: Colors.white)),
                style: ElevatedButton.styleFrom(backgroundColor: Colors.transparent, shadowColor: Colors.transparent, padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.spacingL),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(color: AppTheme.primaryNavy.withValues(alpha: 0.1), shape: BoxShape.circle),
              child: const Icon(Icons.assignment_outlined, size: 60, color: AppTheme.primaryNavy),
            ),
            const SizedBox(height: AppTheme.spacingL),
            const Text('لم تتقدم لأي وظيفة بعد', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppTheme.textPrimary)),
            const SizedBox(height: AppTheme.spacingS),
            const Text('ابدأ بتصفح الوظائف المتاحة وتقدم للفرص المناسبة', style: TextStyle(color: AppTheme.textSecondary), textAlign: TextAlign.center),
          ],
        ),
      ),
    );
  }

  Widget _buildApplicationCard(dynamic application) {
    final job = application['job'];
    final status = application['status'] ?? 'pending';
    final statusColor = _getStatusColor(status);

    return Container(
      margin: const EdgeInsets.only(bottom: AppTheme.spacingM),
      decoration: BoxDecoration(
        color: AppTheme.surfaceWhite,
        borderRadius: BorderRadius.circular(AppTheme.radiusLarge),
        boxShadow: AppTheme.lightShadow,
        border: Border.all(color: AppTheme.borderLight),
      ),
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.spacingM),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 50, height: 50,
                  decoration: BoxDecoration(gradient: AppTheme.primaryGradient, borderRadius: BorderRadius.circular(12)),
                  child: const Icon(Icons.work_rounded, color: Colors.white, size: 24),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(job['title'] ?? '', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.textPrimary)),
                      const SizedBox(height: 4),
                      Text(job['company']?['company_name'] ?? 'شركة غير محددة', style: const TextStyle(fontSize: 14, color: AppTheme.primaryNavy, fontWeight: FontWeight.w500)),
                    ],
                  ),
                ),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  decoration: BoxDecoration(color: statusColor.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(AppTheme.radiusRound)),
                  child: Text(_getStatusText(status), style: TextStyle(fontSize: 12, color: statusColor, fontWeight: FontWeight.w600)),
                ),
              ],
            ),
            const SizedBox(height: AppTheme.spacingS),
            Wrap(
              spacing: 12,
              children: [
                _buildInfoChip(Icons.location_on_rounded, job['province'] ?? ''),
                _buildInfoChip(Icons.calendar_today_rounded, application['applied_at']?.substring(0, 10) ?? ''),
                if (application['matching_percentage'] != null)
                  _buildInfoChip(Icons.percent_rounded, '${application['matching_percentage']}% تطابق'),
              ],
            ),
            if (application['notes'] != null && application['notes'].isNotEmpty) ...[
              const SizedBox(height: AppTheme.spacingS),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(color: AppTheme.surfaceLight, borderRadius: BorderRadius.circular(AppTheme.radiusMedium)),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Row(
                      children: [
                        Icon(Icons.comment_rounded, size: 14, color: AppTheme.textMuted),
                        SizedBox(width: 6),
                        Text('ملاحظات الشركة:', style: TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: AppTheme.textMuted)),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(application['notes'], style: const TextStyle(fontSize: 13, color: AppTheme.textSecondary)),
                  ],
                ),
              ),
            ],
          ],
        ),
      ),
    );
  }

  Widget _buildInfoChip(IconData icon, String text) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 14, color: AppTheme.textMuted),
        const SizedBox(width: 4),
        Text(text, style: const TextStyle(fontSize: 12, color: AppTheme.textMuted)),
      ],
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
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              // Header
              Container(
                decoration: BoxDecoration(
                  gradient: AppTheme.primaryGradient,
                  boxShadow: [BoxShadow(color: AppTheme.primaryNavy.withValues(alpha: 0.2), blurRadius: 8, offset: const Offset(0, 2))],
                ),
                child: Padding(
                  padding: const EdgeInsets.all(AppTheme.spacingM),
                  child: Row(
                    children: [
                      GestureDetector(
                        onTap: () => Navigator.pop(context),
                        child: Container(
                          width: 40, height: 40,
                          decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(10)),
                          child: const Icon(Icons.arrow_back_rounded, color: Colors.white),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('المفضلة', style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                            Text('${favorites.length} وظيفة محفوظة', style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 13)),
                          ],
                        ),
                      ),
                      GestureDetector(
                        onTap: _loadFavorites,
                        child: Container(
                          width: 40, height: 40,
                          decoration: BoxDecoration(color: AppTheme.secondaryGold, borderRadius: BorderRadius.circular(10)),
                          child: const Icon(Icons.refresh_rounded, color: AppTheme.primaryNavy, size: 22),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              // Content
              Expanded(
                child: isLoading
                    ? _buildLoadingState()
                    : errorMessage.isNotEmpty
                        ? _buildErrorState()
                        : favorites.isEmpty
                            ? _buildEmptyState()
                            : ListView.builder(
                                padding: const EdgeInsets.all(AppTheme.spacingM),
                                itemCount: favorites.length,
                                itemBuilder: (context, index) => _buildFavoriteCard(favorites[index]),
                              ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildLoadingState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(color: AppTheme.surfaceWhite, borderRadius: BorderRadius.circular(AppTheme.radiusLarge), boxShadow: AppTheme.lightShadow),
            child: const CircularProgressIndicator(color: AppTheme.primaryNavy),
          ),
          const SizedBox(height: AppTheme.spacingM),
          const Text('جاري تحميل المفضلة...', style: TextStyle(color: AppTheme.textSecondary)),
        ],
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.spacingL),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(color: AppTheme.accentRed.withValues(alpha: 0.1), shape: BoxShape.circle),
              child: const Icon(Icons.error_outline_rounded, size: 50, color: AppTheme.accentRed),
            ),
            const SizedBox(height: AppTheme.spacingM),
            Text(errorMessage, style: const TextStyle(color: AppTheme.accentRed, fontSize: 14), textAlign: TextAlign.center),
            const SizedBox(height: AppTheme.spacingL),
            Container(
              decoration: BoxDecoration(gradient: AppTheme.primaryGradient, borderRadius: BorderRadius.circular(AppTheme.radiusMedium)),
              child: ElevatedButton.icon(
                onPressed: _loadFavorites,
                icon: const Icon(Icons.refresh_rounded, color: Colors.white),
                label: const Text('إعادة المحاولة', style: TextStyle(color: Colors.white)),
                style: ElevatedButton.styleFrom(backgroundColor: Colors.transparent, shadowColor: Colors.transparent, padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12)),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.spacingL),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(color: AppTheme.accentRed.withValues(alpha: 0.1), shape: BoxShape.circle),
              child: const Icon(Icons.favorite_border_rounded, size: 60, color: AppTheme.accentRed),
            ),
            const SizedBox(height: AppTheme.spacingL),
            const Text('لا توجد وظائف في المفضلة', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppTheme.textPrimary)),
            const SizedBox(height: AppTheme.spacingS),
            const Text('احفظ الوظائف التي تهمك لتسهيل الوصول إليها لاحقاً', style: TextStyle(color: AppTheme.textSecondary), textAlign: TextAlign.center),
          ],
        ),
      ),
    );
  }

  Widget _buildFavoriteCard(dynamic favorite) {
    final job = favorite['job'];
    final isOpen = job['status'] == 'open';

    return Container(
      margin: const EdgeInsets.only(bottom: AppTheme.spacingM),
      decoration: BoxDecoration(
        color: AppTheme.surfaceWhite,
        borderRadius: BorderRadius.circular(AppTheme.radiusLarge),
        boxShadow: AppTheme.lightShadow,
        border: Border.all(color: AppTheme.borderLight),
      ),
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.spacingM),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: 50, height: 50,
                  decoration: BoxDecoration(gradient: AppTheme.primaryGradient, borderRadius: BorderRadius.circular(12)),
                  child: const Icon(Icons.work_rounded, color: Colors.white, size: 24),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(job['title'] ?? '', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.textPrimary)),
                      const SizedBox(height: 4),
                      Text(job['company']?['company_name'] ?? 'شركة غير محددة', style: const TextStyle(fontSize: 14, color: AppTheme.primaryNavy, fontWeight: FontWeight.w500)),
                    ],
                  ),
                ),
                GestureDetector(
                  onTap: () => _showRemoveConfirmation(job['id']),
                  child: Container(
                    width: 40, height: 40,
                    decoration: BoxDecoration(color: AppTheme.accentRed.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(10)),
                    child: const Icon(Icons.favorite_rounded, color: AppTheme.accentRed, size: 22),
                  ),
                ),
              ],
            ),
            const SizedBox(height: AppTheme.spacingS),
            Wrap(
              spacing: 12,
              children: [
                _buildInfoChip(Icons.location_on_rounded, job['province'] ?? ''),
                _buildInfoChip(Icons.category_rounded, job['speciality'] ?? ''),
              ],
            ),
            if (job['description'] != null && job['description'].isNotEmpty) ...[
              const SizedBox(height: AppTheme.spacingS),
              Text(job['description'], style: const TextStyle(fontSize: 13, color: AppTheme.textSecondary, height: 1.4), maxLines: 2, overflow: TextOverflow.ellipsis),
            ],
            const SizedBox(height: AppTheme.spacingM),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 5),
                  decoration: BoxDecoration(
                    color: isOpen ? AppTheme.accentGreen.withValues(alpha: 0.1) : AppTheme.accentRed.withValues(alpha: 0.1),
                    borderRadius: BorderRadius.circular(AppTheme.radiusRound),
                  ),
                  child: Text(isOpen ? 'متاحة' : 'مغلقة', style: TextStyle(fontSize: 12, color: isOpen ? AppTheme.accentGreen : AppTheme.accentRed, fontWeight: FontWeight.w600)),
                ),
                Container(
                  decoration: BoxDecoration(gradient: AppTheme.primaryGradient, borderRadius: BorderRadius.circular(AppTheme.radiusMedium)),
                  child: ElevatedButton(
                    onPressed: () => Navigator.push(context, MaterialPageRoute(builder: (_) => JobDetailsScreen(job: job, token: widget.token, user: widget.user))),
                    style: ElevatedButton.styleFrom(backgroundColor: Colors.transparent, shadowColor: Colors.transparent, padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10)),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Text('عرض التفاصيل', style: TextStyle(color: Colors.white, fontSize: 13)),
                        SizedBox(width: 6),
                        Icon(Icons.arrow_forward_rounded, color: Colors.white, size: 16),
                      ],
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  void _showRemoveConfirmation(int jobId) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (context) => Container(
        padding: const EdgeInsets.all(AppTheme.spacingL),
        decoration: const BoxDecoration(
          color: AppTheme.surfaceWhite,
          borderRadius: BorderRadius.vertical(top: Radius.circular(AppTheme.radiusXLarge)),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(width: 40, height: 4, decoration: BoxDecoration(color: AppTheme.borderLight, borderRadius: BorderRadius.circular(2))),
            const SizedBox(height: AppTheme.spacingL),
            Container(
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(color: AppTheme.accentRed.withValues(alpha: 0.1), shape: BoxShape.circle),
              child: const Icon(Icons.favorite_rounded, color: AppTheme.accentRed, size: 32),
            ),
            const SizedBox(height: AppTheme.spacingM),
            const Text('إزالة من المفضلة', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppTheme.textPrimary)),
            const SizedBox(height: AppTheme.spacingS),
            const Text('هل تريد إزالة هذه الوظيفة من قائمة المفضلة؟', style: TextStyle(color: AppTheme.textSecondary), textAlign: TextAlign.center),
            const SizedBox(height: AppTheme.spacingL),
            Row(
              children: [
                Expanded(
                  child: OutlinedButton(
                    onPressed: () => Navigator.pop(context),
                    style: OutlinedButton.styleFrom(padding: const EdgeInsets.symmetric(vertical: 14), side: const BorderSide(color: AppTheme.borderLight)),
                    child: const Text('إلغاء', style: TextStyle(color: AppTheme.textSecondary)),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: Container(
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(colors: [AppTheme.accentRed, Color(0xFFB23A3A)]),
                      borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
                    ),
                    child: ElevatedButton(
                      onPressed: () {
                        Navigator.pop(context);
                        _removeFavorite(jobId);
                      },
                      style: ElevatedButton.styleFrom(backgroundColor: Colors.transparent, shadowColor: Colors.transparent, padding: const EdgeInsets.symmetric(vertical: 14)),
                      child: const Text('إزالة', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600)),
                    ),
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildInfoChip(IconData icon, String text) {
    return Row(
      mainAxisSize: MainAxisSize.min,
      children: [
        Icon(icon, size: 14, color: AppTheme.textMuted),
        const SizedBox(width: 4),
        Text(text, style: const TextStyle(fontSize: 12, color: AppTheme.textMuted)),
      ],
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
      body: Container(
        decoration: const BoxDecoration(
          gradient: AppTheme.backgroundGradient,
        ),
        child: SafeArea(
          child: Column(
            children: [
              // Header with gradient
              Container(
                decoration: BoxDecoration(
                  gradient: AppTheme.primaryGradient,
                  boxShadow: [
                    BoxShadow(
                      color: AppTheme.primaryNavy.withValues(alpha: 0.2),
                      blurRadius: 8,
                      offset: const Offset(0, 2),
                    ),
                  ],
                ),
                child: Padding(
                  padding: const EdgeInsets.all(AppTheme.spacingM),
                  child: Row(
                    children: [
                      GestureDetector(
                        onTap: () => Navigator.pop(context),
                        child: Container(
                          width: 40,
                          height: 40,
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.2),
                            borderRadius: BorderRadius.circular(10),
                          ),
                          child: const Icon(Icons.arrow_back_rounded, color: Colors.white),
                        ),
                      ),
                      const SizedBox(width: 12),
                      const Expanded(
                        child: Text(
                          'الملف الشخصي',
                          style: TextStyle(
                            color: Colors.white,
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                      ),
                      if (widget.user['role'] == 'jobseeker' || widget.user['role'] == 'company')
                        GestureDetector(
                          onTap: () {
                            Navigator.push(
                              context,
                              MaterialPageRoute(
                                builder: (context) => EditProfileScreen(
                                  token: widget.token,
                                  user: widget.user,
                                  profileData: profileData,
                                ),
                              ),
                            ).then((_) => _loadProfile());
                          },
                          child: Container(
                            padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                            decoration: BoxDecoration(
                              color: AppTheme.secondaryGold,
                              borderRadius: BorderRadius.circular(10),
                            ),
                            child: const Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                Icon(Icons.edit_rounded, size: 18, color: AppTheme.primaryNavy),
                                SizedBox(width: 6),
                                Text('تعديل', style: TextStyle(color: AppTheme.primaryNavy, fontWeight: FontWeight.w600, fontSize: 14)),
                              ],
                            ),
                          ),
                        ),
                    ],
                  ),
                ),
              ),

              // Content
              Expanded(
                child: isLoading
                    ? _buildLoadingState()
                    : errorMessage.isNotEmpty
                        ? _buildErrorState()
                        : profileData == null
                            ? _buildEmptyState()
                            : SingleChildScrollView(
                                padding: const EdgeInsets.all(AppTheme.spacingM),
                                child: widget.user['role'] == 'jobseeker'
                                    ? _buildJobSeekerProfile()
                                    : _buildCompanyProfile(),
                              ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildLoadingState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              color: AppTheme.surfaceWhite,
              borderRadius: BorderRadius.circular(AppTheme.radiusLarge),
              boxShadow: AppTheme.lightShadow,
            ),
            child: const CircularProgressIndicator(color: AppTheme.primaryNavy),
          ),
          const SizedBox(height: AppTheme.spacingM),
          const Text('جاري تحميل الملف الشخصي...', style: TextStyle(color: AppTheme.textSecondary)),
        ],
      ),
    );
  }

  Widget _buildErrorState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.spacingL),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppTheme.accentRed.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.error_outline_rounded, size: 50, color: AppTheme.accentRed),
            ),
            const SizedBox(height: AppTheme.spacingM),
            Text(
              errorMessage,
              style: const TextStyle(color: AppTheme.accentRed, fontSize: 14),
              textAlign: TextAlign.center,
            ),
            const SizedBox(height: AppTheme.spacingL),
            Container(
              decoration: BoxDecoration(
                gradient: AppTheme.primaryGradient,
                borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
              ),
              child: ElevatedButton.icon(
                onPressed: _loadProfile,
                icon: const Icon(Icons.refresh_rounded, color: Colors.white),
                label: const Text('إعادة المحاولة', style: TextStyle(color: Colors.white)),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.transparent,
                  shadowColor: Colors.transparent,
                  padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 12),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Padding(
        padding: const EdgeInsets.all(AppTheme.spacingL),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppTheme.primaryNavy.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.person_off_rounded, size: 50, color: AppTheme.primaryNavy),
            ),
            const SizedBox(height: AppTheme.spacingM),
            const Text('لا توجد بيانات', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppTheme.textPrimary)),
            const SizedBox(height: AppTheme.spacingS),
            const Text('لم يتم العثور على بيانات الملف الشخصي', style: TextStyle(color: AppTheme.textSecondary)),
          ],
        ),
      ),
    );
  }

  Widget _buildJobSeekerProfile() {
    final jobSeeker = profileData!['job_seeker'];
    if (jobSeeker == null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppTheme.secondaryGold.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.person_add_rounded, size: 50, color: AppTheme.secondaryGold),
            ),
            const SizedBox(height: AppTheme.spacingM),
            const Text('لم يتم إنشاء الملف الشخصي بعد', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.textPrimary)),
          ],
        ),
      );
    }

    final List<String> subsList = _toStringList(jobSeeker['specialities']);

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Profile Header Card
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(AppTheme.spacingL),
          decoration: BoxDecoration(
            gradient: AppTheme.primaryGradient,
            borderRadius: BorderRadius.circular(AppTheme.radiusLarge),
            boxShadow: [
              BoxShadow(
                color: AppTheme.primaryNavy.withValues(alpha: 0.3),
                blurRadius: 12,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Column(
            children: [
              Container(
                padding: const EdgeInsets.all(4),
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(color: AppTheme.secondaryGold, width: 3),
                ),
                child: CircleAvatar(
                  radius: 45,
                  backgroundColor: Colors.white,
                  backgroundImage: jobSeeker['profile_image'] != null ? NetworkImage(jobSeeker['profile_image']) : null,
                  child: jobSeeker['profile_image'] == null
                      ? Text(
                          (jobSeeker['full_name'] ?? 'U')[0].toUpperCase(),
                          style: const TextStyle(fontSize: 32, fontWeight: FontWeight.bold, color: AppTheme.primaryNavy),
                        )
                      : null,
                ),
              ),
              const SizedBox(height: AppTheme.spacingM),
              Text(
                jobSeeker['full_name'] ?? 'غير محدد',
                style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.white),
              ),
              const SizedBox(height: 4),
              Text(
                jobSeeker['job_title'] ?? 'غير محدد',
                style: TextStyle(fontSize: 15, color: Colors.white.withValues(alpha: 0.9)),
              ),
              const SizedBox(height: AppTheme.spacingS),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.2),
                  borderRadius: BorderRadius.circular(AppTheme.radiusRound),
                ),
                child: Row(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Icon(Icons.location_on_rounded, size: 16, color: AppTheme.secondaryGold),
                    const SizedBox(width: 6),
                    Text(
                      jobSeeker['province'] ?? 'غير محدد',
                      style: const TextStyle(color: Colors.white, fontSize: 13),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
        const SizedBox(height: AppTheme.spacingM),

        // Contact Information
        _buildProfileInfoCard(
          'معلومات الاتصال',
          Icons.contact_phone_rounded,
          [
            _buildProfileInfoRow(Icons.email_rounded, 'البريد الإلكتروني', profileData!['email'] ?? 'غير محدد'),
            _buildProfileInfoRow(Icons.person_rounded, 'الاسم الكامل', jobSeeker['full_name'] ?? 'غير محدد'),
          ],
        ),
        const SizedBox(height: AppTheme.spacingM),

        // Professional Information
        _buildProfileInfoCard(
          'المعلومات المهنية',
          Icons.work_rounded,
          [
            _buildProfileInfoRow(Icons.badge_rounded, 'المسمى الوظيفي', jobSeeker['job_title'] ?? 'غير محدد'),
            _buildProfileInfoRow(Icons.medical_services_rounded, 'التخصص الرئيسي', jobSeeker['speciality'] ?? 'غير محدد'),
            if (subsList.isNotEmpty) _buildProfileInfoRow(Icons.category_rounded, 'التخصصات الفرعية', subsList.join(', ')),
            _buildProfileInfoRow(Icons.school_rounded, 'مستوى التعليم', jobSeeker['education_level'] ?? 'غير محدد'),
            _buildProfileInfoRow(Icons.trending_up_rounded, 'مستوى الخبرة', jobSeeker['experience_level'] ?? 'غير محدد'),
          ],
        ),
        const SizedBox(height: AppTheme.spacingL),

        // Account Management Section
        _buildDeleteAccountButton(),
      ],
    );
  }

  Widget _buildCompanyProfile() {
    final company = profileData!['company'];
    if (company == null) {
      return Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                color: AppTheme.secondaryGold.withValues(alpha: 0.1),
                shape: BoxShape.circle,
              ),
              child: const Icon(Icons.business_rounded, size: 50, color: AppTheme.secondaryGold),
            ),
            const SizedBox(height: AppTheme.spacingM),
            const Text('لم يتم إنشاء الملف الشخصي بعد', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.textPrimary)),
          ],
        ),
      );
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        // Company Header Card
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(AppTheme.spacingL),
          decoration: BoxDecoration(
            gradient: AppTheme.primaryGradient,
            borderRadius: BorderRadius.circular(AppTheme.radiusLarge),
            boxShadow: [
              BoxShadow(
                color: AppTheme.primaryNavy.withValues(alpha: 0.3),
                blurRadius: 12,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Column(
            children: [
              Container(
                padding: const EdgeInsets.all(4),
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(color: AppTheme.secondaryGold, width: 3),
                ),
                child: CircleAvatar(
                  radius: 45,
                  backgroundColor: Colors.white,
                  backgroundImage: company['profile_image'] != null ? NetworkImage(company['profile_image']) : null,
                  child: company['profile_image'] == null
                      ? Text(
                          (company['company_name'] ?? 'C')[0].toUpperCase(),
                          style: const TextStyle(fontSize: 32, fontWeight: FontWeight.bold, color: AppTheme.primaryNavy),
                        )
                      : null,
                ),
              ),
              const SizedBox(height: AppTheme.spacingM),
              Text(
                company['company_name'] ?? 'غير محدد',
                style: const TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: Colors.white),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 4),
              Text(
                company['scientific_office_name'] ?? '',
                style: TextStyle(fontSize: 15, color: Colors.white.withValues(alpha: 0.9)),
                textAlign: TextAlign.center,
              ),
              if (company['province'] != null) ...[
                const SizedBox(height: AppTheme.spacingS),
                Container(
                  padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6),
                  decoration: BoxDecoration(
                    color: Colors.white.withValues(alpha: 0.2),
                    borderRadius: BorderRadius.circular(AppTheme.radiusRound),
                  ),
                  child: Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      const Icon(Icons.location_on_rounded, size: 16, color: AppTheme.secondaryGold),
                      const SizedBox(width: 6),
                      Text(company['province'], style: const TextStyle(color: Colors.white, fontSize: 13)),
                    ],
                  ),
                ),
              ],
            ],
          ),
        ),
        const SizedBox(height: AppTheme.spacingM),

        // Company Information
        _buildProfileInfoCard(
          'معلومات الشركة',
          Icons.business_rounded,
          [
            _buildProfileInfoRow(Icons.apartment_rounded, 'اسم الشركة', company['company_name'] ?? 'غير محدد'),
            _buildProfileInfoRow(Icons.category_rounded, 'القطاع', company['industry'] ?? 'غير محدد'),
            _buildProfileInfoRow(Icons.email_rounded, 'البريد الإلكتروني', profileData!['email'] ?? 'غير محدد'),
          ],
        ),
        const SizedBox(height: AppTheme.spacingL),

        // Account Management Section
        _buildDeleteAccountButton(),
      ],
    );
  }

  Widget _buildDeleteAccountButton() {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(AppTheme.spacingM),
      decoration: BoxDecoration(
        color: AppTheme.accentRed.withValues(alpha: 0.08),
        borderRadius: BorderRadius.circular(AppTheme.radiusLarge),
        border: Border.all(color: AppTheme.accentRed.withValues(alpha: 0.3)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: AppTheme.accentRed.withValues(alpha: 0.15),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: const Icon(Icons.warning_amber_rounded, color: AppTheme.accentRed, size: 22),
              ),
              const SizedBox(width: 10),
              const Text(
                'إدارة الحساب',
                style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: AppTheme.accentRed),
              ),
            ],
          ),
          const SizedBox(height: AppTheme.spacingS),
          const Text(
            'حذف الحساب سيؤدي إلى إزالة جميع بياناتك بشكل نهائي ولا يمكن التراجع عن هذا الإجراء.',
            style: TextStyle(color: AppTheme.textSecondary, fontSize: 13, height: 1.5),
          ),
          const SizedBox(height: AppTheme.spacingM),
          Container(
            width: double.infinity,
            decoration: BoxDecoration(
              gradient: const LinearGradient(colors: [AppTheme.accentRed, Color(0xFFB23A3A)]),
              borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
              boxShadow: [
                BoxShadow(
                  color: AppTheme.accentRed.withValues(alpha: 0.3),
                  blurRadius: 8,
                  offset: const Offset(0, 3),
                ),
              ],
            ),
            child: ElevatedButton.icon(
              onPressed: _showDeleteAccountDialog,
              icon: const Icon(Icons.delete_forever_rounded, color: Colors.white),
              label: const Text('حذف الحساب نهائياً', style: TextStyle(color: Colors.white, fontWeight: FontWeight.w600)),
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.transparent,
                shadowColor: Colors.transparent,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(AppTheme.radiusMedium)),
              ),
            ),
          ),
        ],
      ),
    );
  }

  void _showDeleteAccountDialog() {
    final passwordController = TextEditingController();
    bool isDeleting = false;
    String? errorMessage;

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (dialogContext) => StatefulBuilder(
        builder: (context, setDialogState) => AlertDialog(
          title: Row(
            children: [
              Icon(Icons.warning, color: Colors.red[700]),
              const SizedBox(width: 8),
              const Text('تأكيد حذف الحساب'),
            ],
          ),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: Colors.red[50],
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Text(
                    '⚠️ تحذير: هذا الإجراء لا يمكن التراجع عنه!\n\nسيتم حذف:\n• جميع بياناتك الشخصية\n• جميع طلبات التوظيف\n• جميع المفضلات\n• السيرة الذاتية والصور',
                    style: TextStyle(color: Colors.red[700], fontSize: 13),
                  ),
                ),
                const SizedBox(height: 16),
                const Text('أدخل كلمة المرور للتأكيد:'),
                const SizedBox(height: 8),
                TextField(
                  controller: passwordController,
                  obscureText: true,
                  enabled: !isDeleting,
                  decoration: InputDecoration(
                    labelText: 'كلمة المرور',
                    border: const OutlineInputBorder(),
                    prefixIcon: const Icon(Icons.lock),
                    errorText: errorMessage,
                  ),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: isDeleting ? null : () => Navigator.pop(dialogContext),
              child: const Text('إلغاء'),
            ),
            ElevatedButton(
              onPressed: isDeleting
                  ? null
                  : () async {
                      if (passwordController.text.isEmpty) {
                        setDialogState(() => errorMessage = 'الرجاء إدخال كلمة المرور');
                        return;
                      }
                      setDialogState(() {
                        isDeleting = true;
                        errorMessage = null;
                      });

                      final auth = AuthService();
                      final result = await auth.deleteAccount(
                        authToken: widget.token,
                        password: passwordController.text,
                      );
                      auth.close();

                      if (!context.mounted) return;

                      if (result['success'] == true) {
                        Navigator.pop(dialogContext);
                        // Navigate to login screen
                        Navigator.pushAndRemoveUntil(
                          context,
                          MaterialPageRoute(builder: (_) => const LoginScreen()),
                          (route) => false,
                        );
                        ScaffoldMessenger.of(context).showSnackBar(
                          SnackBar(
                            content: Text(result['message'] ?? 'تم حذف الحساب بنجاح'),
                            backgroundColor: Colors.green,
                          ),
                        );
                      } else {
                        setDialogState(() {
                          isDeleting = false;
                          errorMessage = result['message'] ?? 'فشل في حذف الحساب';
                        });
                      }
                    },
              style: ElevatedButton.styleFrom(
                backgroundColor: Colors.red,
                foregroundColor: Colors.white,
              ),
              child: isDeleting
                  ? const SizedBox(
                      width: 20,
                      height: 20,
                      child: CircularProgressIndicator(
                        strokeWidth: 2,
                        valueColor: AlwaysStoppedAnimation<Color>(Colors.white),
                      ),
                    )
                  : const Text('حذف الحساب'),
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildProfileInfoCard(String title, IconData icon, List<Widget> children) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(AppTheme.spacingM),
      decoration: BoxDecoration(
        color: AppTheme.surfaceWhite,
        borderRadius: BorderRadius.circular(AppTheme.radiusLarge),
        boxShadow: AppTheme.lightShadow,
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(8),
                decoration: BoxDecoration(
                  color: AppTheme.primaryNavy.withValues(alpha: 0.1),
                  borderRadius: BorderRadius.circular(10),
                ),
                child: Icon(icon, size: 20, color: AppTheme.primaryNavy),
              ),
              const SizedBox(width: 10),
              Text(
                title,
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.textPrimary),
              ),
            ],
          ),
          const SizedBox(height: AppTheme.spacingM),
          ...children,
        ],
      ),
    );
  }

  Widget _buildProfileInfoRow(IconData icon, String label, String value) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, size: 18, color: AppTheme.textMuted),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(label, style: const TextStyle(fontSize: 12, color: AppTheme.textMuted)),
                const SizedBox(height: 2),
                Text(value, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w500, color: AppTheme.textPrimary)),
              ],
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

  // Education (job seeker)
  final _universityNameController = TextEditingController();
  final _collegeNameController = TextEditingController();
  final _departmentNameController = TextEditingController();
  final _graduationYearController = TextEditingController();
  bool _isFreshGraduate = false;

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

  @override
  void dispose() {
    _fullNameController.dispose();
    _jobTitleController.dispose();
    _summaryController.dispose();
    _qualificationsController.dispose();
    _experiencesController.dispose();
    _skillsController.dispose();
    _languagesController.dispose();
    _universityNameController.dispose();
    _collegeNameController.dispose();
    _departmentNameController.dispose();
    _graduationYearController.dispose();

    _companyNameController.dispose();
    _companyIndustryController.dispose();
    _companyLocationController.dispose();
    _companyWebsiteController.dispose();
    _companyDescController.dispose();
    super.dispose();
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

          _universityNameController.text = jobSeeker['university_name'] ?? '';
          _collegeNameController.text = jobSeeker['college_name'] ?? '';
          _departmentNameController.text = jobSeeker['department_name'] ?? '';
          final gy = jobSeeker['graduation_year'];
          _graduationYearController.text = (gy == null || '$gy' == 'null') ? '' : '$gy';
          final fg = jobSeeker['is_fresh_graduate'];
          _isFreshGraduate = (fg == true) || (fg == 1) || ('$fg' == '1');
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
              // Education fields (send nulls to allow clearing)
              'university_name': _universityNameController.text.trim().isEmpty ? null : _universityNameController.text.trim(),
              'college_name': _collegeNameController.text.trim().isEmpty ? null : _collegeNameController.text.trim(),
              'department_name': _departmentNameController.text.trim().isEmpty ? null : _departmentNameController.text.trim(),
              'graduation_year': _graduationYearController.text.trim().isEmpty ? null : int.tryParse(_graduationYearController.text.trim()),
              'is_fresh_graduate': _isFreshGraduate,
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

        // Education fields
        fields['university_name'] = _universityNameController.text.trim();
        fields['college_name'] = _collegeNameController.text.trim();
        fields['department_name'] = _departmentNameController.text.trim();
        fields['graduation_year'] = _graduationYearController.text.trim();
        fields['is_fresh_graduate'] = _isFreshGraduate ? '1' : '0';

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
        return lines.map((l) => pw.Bullet(text: l, style: pw.TextStyle(font: regular, fontSize: 18, fontFallback: [latin]))).toList();
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
                      section('نبذة عني', [pw.Text(summary, style: pw.TextStyle(fontSize: 18, fontFallback: [latin]))]),
                    section('الخبرات المهنية', bulletFromText(experiences)),
                  ]),
                ),
                pw.SizedBox(width: 18),
                pw.Expanded(
                  flex: 2,
                  child: pw.Column(crossAxisAlignment: pw.CrossAxisAlignment.start, children: [
                    section('بيانات التواصل', [
                      if (email.isNotEmpty) pw.Text('البريد: $email', style: pw.TextStyle(font: regular, fontSize: 18, fontFallback: [latin])),
                      if (phone.isNotEmpty) pw.Text('رقم الموبايل: $phone', style: pw.TextStyle(font: regular, fontSize: 18, fontFallback: [latin])),
                      if (province.toString().isNotEmpty) pw.Text('المحافظة: $province', style: pw.TextStyle(font: regular, fontSize: 18, fontFallback: [latin])),
                      pw.Text('امتلاك السيارة: ${hasCar ? 'نعم' : 'لا'}', style: pw.TextStyle(font: regular, fontSize: 18, fontFallback: [latin])),
                    ]),
                    if (districts.isNotEmpty)
                      section('المناطق', districts.map((d) => pw.Bullet(text: d, style: pw.TextStyle(font: regular, fontSize: 18, fontFallback: [latin]))).toList()),
                    if (specialities.isNotEmpty)
                      section('التخصصات', specialities.map((s) => pw.Bullet(text: s, style: pw.TextStyle(font: regular, fontSize: 18, fontFallback: [latin]))).toList()),
                    section('التعليم', education.toString().isEmpty ? [] : [pw.Text(education, style: pw.TextStyle(fontSize: 18, fontFallback: [latin]))]),
                    section('المهارات', bulletFromText(skills)),
                    section('اللغات', bulletFromText(languages)),
                    section('المؤهلات', bulletFromText(qualifications)),
                    section('التخصص', speciality.toString().isEmpty ? [] : [pw.Text(speciality, style: pw.TextStyle(fontSize: 18, fontFallback: [latin]))]),
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

              // Education Information (Job Seeker)
              if (!_isCompany)
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(16),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text(
                          'المؤهل الدراسي',
                          style: TextStyle(
                            fontSize: 18,
                            fontWeight: FontWeight.bold,
                          ),
                        ),
                        const SizedBox(height: 16),
                        TextFormField(
                          controller: _universityNameController,
                          decoration: const InputDecoration(
                            labelText: 'اسم الجامعة',
                            border: OutlineInputBorder(),
                          ),
                        ),
                        const SizedBox(height: 16),
                        TextFormField(
                          controller: _collegeNameController,
                          decoration: const InputDecoration(
                            labelText: 'اسم الكلية',
                            border: OutlineInputBorder(),
                          ),
                        ),
                        const SizedBox(height: 16),
                        TextFormField(
                          controller: _departmentNameController,
                          decoration: const InputDecoration(
                            labelText: 'اسم القسم',
                            border: OutlineInputBorder(),
                          ),
                        ),
                        const SizedBox(height: 16),
                        TextFormField(
                          controller: _graduationYearController,
                          keyboardType: TextInputType.number,
                          decoration: const InputDecoration(
                            labelText: 'سنة التخرج',
                            border: OutlineInputBorder(),
                            hintText: 'مثال: 2024',
                          ),
                          validator: (value) {
                            final v = (value ?? '').trim();
                            if (v.isEmpty) return null;
                            final y = int.tryParse(v);
                            if (y == null) return 'يرجى إدخال سنة صحيحة';
                            if (y < 1950 || y > 2100) return 'يرجى إدخال سنة بين 1950 و 2100';
                            return null;
                          },
                        ),
                        const SizedBox(height: 8),
                        SwitchListTile.adaptive(
                          contentPadding: EdgeInsets.zero,
                          title: const Text('هل أنت خريج جديد؟'),
                          value: _isFreshGraduate,
                          onChanged: (v) => setState(() => _isFreshGraduate = v),
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

  Widget _statCard(String label, dynamic value, Color color, IconData icon) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.all(AppTheme.spacingM),
        decoration: BoxDecoration(
          color: AppTheme.surfaceWhite,
          borderRadius: BorderRadius.circular(AppTheme.radiusLarge),
          boxShadow: AppTheme.lightShadow,
        ),
        child: Column(
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(color: color.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(12)),
              child: Icon(icon, size: 22, color: color),
            ),
            const SizedBox(height: 10),
            Text('$value', style: TextStyle(fontSize: 22, fontWeight: FontWeight.bold, color: color)),
            const SizedBox(height: 4),
            Text(label, style: const TextStyle(color: AppTheme.textMuted, fontSize: 12)),
          ],
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
      borderRadius: BorderRadius.circular(AppTheme.radiusLarge),
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(AppTheme.radiusLarge),
          gradient: gradient,
          boxShadow: [BoxShadow(color: gradient.colors[0].withValues(alpha: 0.3), blurRadius: 8, offset: const Offset(0, 3))],
        ),
        padding: const EdgeInsets.all(AppTheme.spacingM),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.end,
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(12)),
              child: Icon(icon, size: 28, color: Colors.white),
            ),
            Text(title, textAlign: TextAlign.right, style: const TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.bold)),
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
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              // Header
              Container(
                decoration: BoxDecoration(
                  gradient: AppTheme.primaryGradient,
                  boxShadow: [BoxShadow(color: AppTheme.primaryNavy.withValues(alpha: 0.2), blurRadius: 8, offset: const Offset(0, 2))],
                ),
                child: Padding(
                  padding: const EdgeInsets.all(AppTheme.spacingM),
                  child: Row(
                    children: [
                      GestureDetector(
                        onTap: () => Navigator.pop(context),
                        child: Container(
                          width: 40, height: 40,
                          decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(10)),
                          child: const Icon(Icons.arrow_back_rounded, color: Colors.white),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('لوحة تحكم الشركة', style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                            Text(widget.user['name'] ?? '', style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 13)),
                          ],
                        ),
                      ),
                      GestureDetector(
                        onTap: _loadDashboard,
                        child: Container(
                          width: 40, height: 40,
                          decoration: BoxDecoration(color: AppTheme.secondaryGold, borderRadius: BorderRadius.circular(10)),
                          child: const Icon(Icons.refresh_rounded, color: AppTheme.primaryNavy, size: 22),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              // Content
              Expanded(
                child: isLoading
                    ? Center(
                        child: Container(
                          padding: const EdgeInsets.all(20),
                          decoration: BoxDecoration(color: AppTheme.surfaceWhite, borderRadius: BorderRadius.circular(AppTheme.radiusLarge), boxShadow: AppTheme.lightShadow),
                          child: const CircularProgressIndicator(color: AppTheme.primaryNavy),
                        ),
                      )
                    : SingleChildScrollView(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            if (errorMessage.isNotEmpty)
                              Padding(
                                padding: const EdgeInsets.all(AppTheme.spacingM),
                                child: Container(
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(color: AppTheme.accentRed.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(AppTheme.radiusMedium)),
                                  child: Row(
                                    children: [
                                      const Icon(Icons.warning_rounded, color: AppTheme.accentRed, size: 20),
                                      const SizedBox(width: 8),
                                      const Expanded(child: Text('فشل في تحميل بعض البيانات', style: TextStyle(color: AppTheme.accentRed, fontSize: 13))),
                                    ],
                                  ),
                                ),
                              ),
                            const SizedBox(height: AppTheme.spacingS),
                Padding(
                      padding: const EdgeInsets.symmetric(horizontal: AppTheme.spacingM),
                      child: Row(
                        children: [
                          _statCard('إجمالي الوظائف', stats?['total_jobs'] ?? 0, AppTheme.primaryNavy, Icons.work_rounded),
                          const SizedBox(width: AppTheme.spacingS),
                          _statCard('الوظائف النشطة', stats?['active_jobs'] ?? 0, AppTheme.accentGreen, Icons.check_circle_rounded),
                        ],
                      ),
                    ),
                    const SizedBox(height: AppTheme.spacingS),
                    Padding(
                      padding: const EdgeInsets.symmetric(horizontal: AppTheme.spacingM),
                      child: Row(
                        children: [
                          _statCard('طلبات التقديم', stats?['total_applications'] ?? 0, const Color(0xFF9333EA), Icons.inbox_rounded),
                          const SizedBox(width: AppTheme.spacingS),
                          _statCard('طلبات قيد المراجعة', stats?['pending_applications'] ?? 0, Colors.orange, Icons.pending_actions_rounded),
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

                            const SizedBox(height: AppTheme.spacingM),
                            // وظائفي section
                            Padding(
                              padding: const EdgeInsets.symmetric(horizontal: AppTheme.spacingM),
                              child: Row(
                                children: [
                                  Container(
                                    padding: const EdgeInsets.all(8),
                                    decoration: BoxDecoration(color: AppTheme.primaryNavy.withValues(alpha: 0.1), borderRadius: BorderRadius.circular(10)),
                                    child: const Icon(Icons.list_alt_rounded, size: 20, color: AppTheme.primaryNavy),
                                  ),
                                  const SizedBox(width: 10),
                                  const Text('وظائفي', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: AppTheme.textPrimary)),
                                ],
                              ),
                            ),
                            const SizedBox(height: AppTheme.spacingS),
                            jobs.isEmpty
                                ? Container(
                                    margin: const EdgeInsets.all(AppTheme.spacingM),
                                    padding: const EdgeInsets.all(AppTheme.spacingL),
                                    decoration: BoxDecoration(color: AppTheme.surfaceWhite, borderRadius: BorderRadius.circular(AppTheme.radiusLarge), boxShadow: AppTheme.lightShadow),
                                    child: const Center(child: Text('لا توجد وظائف', style: TextStyle(color: AppTheme.textMuted))),
                                  )
                                : ListView.builder(
                                    shrinkWrap: true,
                                    physics: const NeverScrollableScrollPhysics(),
                                    padding: const EdgeInsets.symmetric(horizontal: AppTheme.spacingM),
                                    itemCount: jobs.length,
                                    itemBuilder: (context, i) {
                                      final job = jobs[i];
                                      final isOpen = job['status'] == 'open';
                                      return Container(
                                        margin: const EdgeInsets.only(bottom: AppTheme.spacingS),
                                        decoration: BoxDecoration(
                                          color: AppTheme.surfaceWhite,
                                          borderRadius: BorderRadius.circular(AppTheme.radiusMedium),
                                          boxShadow: AppTheme.lightShadow,
                                        ),
                                        child: ListTile(
                                          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                                          leading: Container(
                                            width: 45, height: 45,
                                            decoration: BoxDecoration(gradient: AppTheme.primaryGradient, borderRadius: BorderRadius.circular(10)),
                                            child: const Icon(Icons.work_rounded, color: Colors.white, size: 22),
                                          ),
                                          title: Text(job['title'] ?? '', style: const TextStyle(fontWeight: FontWeight.w600, color: AppTheme.textPrimary)),
                                          subtitle: Row(
                                            children: [
                                              const Icon(Icons.people_rounded, size: 14, color: AppTheme.textMuted),
                                              const SizedBox(width: 4),
                                              Text('${job['applications_count'] ?? 0}', style: const TextStyle(color: AppTheme.textMuted, fontSize: 12)),
                                              const SizedBox(width: 12),
                                              Container(
                                                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                                                decoration: BoxDecoration(
                                                  color: isOpen ? AppTheme.accentGreen.withValues(alpha: 0.1) : AppTheme.accentRed.withValues(alpha: 0.1),
                                                  borderRadius: BorderRadius.circular(AppTheme.radiusRound),
                                                ),
                                                child: Text(isOpen ? 'نشطة' : 'مغلقة', style: TextStyle(color: isOpen ? AppTheme.accentGreen : AppTheme.accentRed, fontSize: 11, fontWeight: FontWeight.w500)),
                                              ),
                                            ],
                                          ),
                                          trailing: Container(
                                            width: 32, height: 32,
                                            decoration: BoxDecoration(color: AppTheme.surfaceLight, borderRadius: BorderRadius.circular(8)),
                                            child: const Icon(Icons.chevron_left_rounded, color: AppTheme.textMuted),
                                          ),
                                          onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => ApplicantsScreen(token: widget.token, user: widget.user, jobId: job['id'], jobTitle: job['title'] ?? ''))),
                                        ),
                                      );
                                    },
                                  ),
                            const SizedBox(height: AppTheme.spacingM),
                          ],
                        ),
                      ),
              ),
            ],
          ),
        ),
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
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              // Header
              Container(
                decoration: BoxDecoration(
                  gradient: AppTheme.primaryGradient,
                  boxShadow: [BoxShadow(color: AppTheme.primaryNavy.withValues(alpha: 0.2), blurRadius: 8, offset: const Offset(0, 2))],
                ),
                child: Padding(
                  padding: const EdgeInsets.all(AppTheme.spacingM),
                  child: Row(
                    children: [
                      GestureDetector(
                        onTap: () => Navigator.pop(context),
                        child: Container(
                          width: 40, height: 40,
                          decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(10)),
                          child: const Icon(Icons.arrow_back_rounded, color: Colors.white),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('لوحة تحكم الأدمن', style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                            Text(user['name'] ?? 'المدير', style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 13)),
                          ],
                        ),
                      ),
                      Container(
                        width: 40, height: 40,
                        decoration: BoxDecoration(color: AppTheme.secondaryGold, borderRadius: BorderRadius.circular(10)),
                        child: const Icon(Icons.admin_panel_settings_rounded, color: AppTheme.primaryNavy, size: 22),
                      ),
                    ],
                  ),
                ),
              ),
              // Content
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.all(AppTheme.spacingM),
                  child: GridView.count(
                    crossAxisCount: 2,
                    crossAxisSpacing: AppTheme.spacingS,
                    mainAxisSpacing: AppTheme.spacingS,
                    children: [
                      _adminCard(context, title: 'الشركات', icon: Icons.apartment_rounded, gradient: const LinearGradient(colors: [Color(0xFF4F46E5), Color(0xFF3730A3)]),
                        onTap: () => _open(context, 'إدارة الشركات', '${AppConfig.baseUrl.replaceFirst('api/v1/', '')}admin/companies')),
                      _adminCard(context, title: 'وظائف قيد المراجعة', icon: Icons.fact_check_rounded, gradient: const LinearGradient(colors: [Color(0xFF10B981), Color(0xFF059669)]),
                        onTap: () => _open(context, 'الوظائف قيد المراجعة', '${AppConfig.baseUrl.replaceFirst('api/v1/', '')}admin/jobs/pending')),
                      _adminCard(context, title: 'الباحثون عن عمل', icon: Icons.people_alt_rounded, gradient: const LinearGradient(colors: [Color(0xFFF59E0B), Color(0xFFD97706)]),
                        onTap: () => _open(context, 'إدارة الباحثين', '${AppConfig.baseUrl.replaceFirst('api/v1/', '')}admin/jobseekers')),
                      _adminCard(context, title: 'قاعدة بيانات الباحثين', icon: Icons.manage_search_rounded, gradient: const LinearGradient(colors: [Color(0xFF0EA5E9), Color(0xFF0369A1)]),
                        onTap: () => _open(context, 'قاعدة بيانات الباحثين', '${AppConfig.baseUrl.replaceFirst('api/v1/', '')}admin/seekers')),
                      _adminCard(context, title: 'الإعدادات', icon: Icons.settings_rounded, gradient: const LinearGradient(colors: [Color(0xFF9333EA), Color(0xFF7E22CE)]),
                        onTap: () => _open(context, 'الإعدادات', '${AppConfig.baseUrl.replaceFirst('api/v1/', '')}admin/settings')),
                      _adminCard(context, title: 'الأقضية والمناطق', icon: Icons.map_rounded, gradient: const LinearGradient(colors: [Color(0xFF14B8A6), Color(0xFF0F766E)]),
                        onTap: () => _open(context, 'الأقضية والمناطق', '${AppConfig.baseUrl.replaceFirst('api/v1/', '')}admin/districts')),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _adminCard(BuildContext context, {required String title, required IconData icon, required LinearGradient gradient, required VoidCallback onTap}) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(AppTheme.radiusLarge),
      child: Container(
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(AppTheme.radiusLarge),
          gradient: gradient,
          boxShadow: [BoxShadow(color: gradient.colors[0].withValues(alpha: 0.3), blurRadius: 8, offset: const Offset(0, 3))],
        ),
        padding: const EdgeInsets.all(AppTheme.spacingM),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.end,
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(12)),
              child: Icon(icon, size: 28, color: Colors.white),
            ),
            Text(title, textAlign: TextAlign.right, style: const TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.bold)),
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
    Navigator.push(context, MaterialPageRoute(builder: (_) => AdminWebViewScreen(title: title, url: bridged)));
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


// ========================= CV Verification (Mobile) =========================
class CvVerificationScreen extends StatefulWidget {
  final String token;
  const CvVerificationScreen({super.key, required this.token});

  @override
  State<CvVerificationScreen> createState() => _CvVerificationScreenState();
}

class _CvVerificationScreenState extends State<CvVerificationScreen> {
  bool isLoading = true;
  bool isRequesting = false;
  String errorMessage = '';
  Map<String, dynamic>? statusData;

  Uri _path(String path) => Uri.parse('${AppConfig.baseUrl}$path');
  Map<String, String> _headers() => {
        'Accept': 'application/json',
        'Authorization': 'Bearer ${widget.token}',
      };

  String _wrapSessionUrl(String absoluteUrl) {
    final site = AppConfig.baseUrl.replaceFirst('api/v1/', '');
    String redirect = absoluteUrl;
    if (absoluteUrl.startsWith(site)) {
      redirect = absoluteUrl.substring(site.length);
      if (!redirect.startsWith('/')) redirect = '/$redirect';
    }
    final encodedToken = Uri.encodeComponent(widget.token);
    final dest = Uri.encodeComponent(redirect);
    return '${site}mobile/session-login?token=$encodedToken&redirect=$dest';
  }

  void _openWeb(BuildContext context, String title, String url) {
    final bridged = _wrapSessionUrl(url);
    Navigator.push(context, MaterialPageRoute(builder: (_) => AdminWebViewScreen(title: title, url: bridged)));
  }

  Map<String, dynamic> _normalize(Map<String, dynamic> raw, int status) {
    if (raw.containsKey('success')) return raw;
    if (status >= 200 && status < 300) return {'success': true, 'data': raw};
    return {'success': false, 'message': (raw['message'] as String?) ?? 'فشل الطلب', 'status': status, 'data': raw};
  }

  Future<void> _loadStatus() async {
    setState(() {
      isLoading = true;
      errorMessage = '';
    });
    try {
      final resp = await http.get(_path('cv-verification/'), headers: _headers());
      Map<String, dynamic> data;
      try {
        data = jsonDecode(resp.body) is Map<String, dynamic> ? (jsonDecode(resp.body) as Map<String, dynamic>) : <String, dynamic>{};
      } catch (_) {
        data = <String, dynamic>{};
      }
      final normalized = _normalize(data, resp.statusCode);
      if (!mounted) return;
      if (normalized['success'] == true) {
        setState(() {
          statusData = (normalized['data'] as Map<String, dynamic>?) ?? {};
          isLoading = false;
        });
      } else {
        setState(() {
          errorMessage = (normalized['message'] as String?) ?? 'فشل في تحميل الحالة';
          isLoading = false;
        });
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        errorMessage = 'خطأ في الاتصال: $e';
        isLoading = false;
      });
    }
  }

  Future<void> _submitRequest() async {
    if (isRequesting) return;
    setState(() {
      isRequesting = true;
      errorMessage = '';
    });
    try {
      final resp = await http.post(_path('cv-verification/request'), headers: _headers());
      Map<String, dynamic> data;
      try {
        data = jsonDecode(resp.body) is Map<String, dynamic> ? (jsonDecode(resp.body) as Map<String, dynamic>) : <String, dynamic>{};
      } catch (_) {
        data = <String, dynamic>{};
      }
      final normalized = _normalize(data, resp.statusCode);
      if (!mounted) return;
      if (normalized['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('تم إرسال طلب التوثيق بنجاح')));
        await _loadStatus();
      } else {
        setState(() {
          errorMessage = (normalized['message'] as String?) ?? 'فشل في إرسال الطلب';
        });
      }
    } catch (e) {
      if (!mounted) return;
      setState(() {
        errorMessage = 'خطأ في الاتصال: $e';
      });
    } finally {
      if (mounted) {
        setState(() {
          isRequesting = false;
        });
      }
    }
  }

  @override
  void initState() {
    super.initState();
    _loadStatus();
  }

  @override
  Widget build(BuildContext context) {
    final scheme = Theme.of(context).colorScheme;
    final site = AppConfig.baseUrl.replaceFirst('api/v1/', '');

    final data = statusData ?? {};
    final cvVerified = data['cv_verified'] == true;
    final hasCv = data['has_cv'] == true;
    final latest = data['latest_request'];
    String? latestStatus;
    String? adminNotes;
    if (latest is Map) {
      latestStatus = (latest['status'] as String?);
      adminNotes = (latest['admin_notes'] as String?);
    }

    Widget statusWidget() {
      if (cvVerified) {
        return Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(color: Colors.green.withValues(alpha: 0.08), borderRadius: BorderRadius.circular(12)),
          child: const Row(children: [Icon(Icons.verified_rounded, color: Colors.green), SizedBox(width: 8), Expanded(child: Text('السيرة الذاتية موثّقة'))]),
        );
      }
      if (!hasCv) {
        return Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(color: Colors.orange.withValues(alpha: 0.08), borderRadius: BorderRadius.circular(12)),
          child: const Row(children: [Icon(Icons.upload_file_rounded, color: Colors.orange), SizedBox(width: 8), Expanded(child: Text('يرجى رفع السيرة الذاتية (CV) أولاً'))]),
        );
      }
      if (latestStatus == 'pending') {
        return Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(color: scheme.primary.withValues(alpha: 0.08), borderRadius: BorderRadius.circular(12)),
          child: Row(children: [Icon(Icons.hourglass_top_rounded, color: scheme.primary), const SizedBox(width: 8), const Expanded(child: Text('طلب التوثيق قيد المراجعة'))]),
        );
      }
      if (latestStatus == 'rejected') {
        return Container(
          padding: const EdgeInsets.all(12),
          decoration: BoxDecoration(color: Colors.red.withValues(alpha: 0.08), borderRadius: BorderRadius.circular(12)),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            const Row(children: [Icon(Icons.cancel_rounded, color: Colors.red), SizedBox(width: 8), Expanded(child: Text('تم رفض طلب التوثيق'))]),
            if ((adminNotes ?? '').trim().isNotEmpty) ...[
              const SizedBox(height: 8),
              Text('ملاحظات الإدارة: $adminNotes', style: TextStyle(color: scheme.onSurface.withValues(alpha: 0.8))),
            ],
          ]),
        );
      }
      return Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(color: scheme.surfaceContainerHighest.withValues(alpha: 0.6), borderRadius: BorderRadius.circular(12)),
        child: const Row(children: [Icon(Icons.info_outline_rounded), SizedBox(width: 8), Expanded(child: Text('لم يتم إرسال طلب توثيق بعد.'))]),
      );
    }

    Widget actionWidget() {
      if (cvVerified) {
        return const SizedBox.shrink();
      }
      if (!hasCv) {
        return SizedBox(
          width: double.infinity,
          child: ElevatedButton.icon(
            onPressed: () => _openWeb(context, 'الملف الشخصي', '${site}jobseeker/profile'),
            icon: const Icon(Icons.edit_rounded),
            label: const Text('رفع/تعديل السيرة الذاتية'),
          ),
        );
      }
      final disabled = (latestStatus == 'pending') || isRequesting;
      return SizedBox(
        width: double.infinity,
        child: ElevatedButton.icon(
          onPressed: disabled ? null : _submitRequest,
          icon: isRequesting
              ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
              : const Icon(Icons.verified_user_rounded),
          label: Text(latestStatus == 'rejected' ? 'إعادة إرسال طلب التوثيق' : 'طلب توثيق السيرة الذاتية'),
        ),
      );
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('توثيق السيرة الذاتية'),
        backgroundColor: scheme.primary,
        foregroundColor: scheme.onPrimary,
        actions: [
          IconButton(onPressed: isLoading ? null : _loadStatus, icon: const Icon(Icons.refresh_rounded)),
        ],
      ),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: isLoading
            ? const Center(child: CircularProgressIndicator())
            : Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  if (errorMessage.isNotEmpty)
                    Container(
                      padding: const EdgeInsets.all(12),
                      margin: const EdgeInsets.only(bottom: 12),
                      decoration: BoxDecoration(color: Colors.red[50], borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.red[200]!)),
                      child: Text(errorMessage, style: TextStyle(color: Colors.red[700])),
                    ),
                  Text(
                    'هذه الميزة متاحة للصيادلة فقط (إذا كان المسمى الوظيفي يحتوي على صيدل أو pharmac).',
                    style: TextStyle(color: scheme.onSurface.withValues(alpha: 0.75)),
                    textAlign: TextAlign.right,
                  ),
                  const SizedBox(height: 12),
                  statusWidget(),
                  const SizedBox(height: 16),
                  actionWidget(),
                ],
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
    Navigator.push(context, MaterialPageRoute(builder: (_) => AdminWebViewScreen(title: title, url: bridged)));
  }

  Widget _jsCard(BuildContext context, {required String title, required IconData icon, required LinearGradient gradient, required VoidCallback onTap}) {
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(AppTheme.radiusLarge),
      child: Container(
        decoration: BoxDecoration(
          gradient: gradient,
          borderRadius: BorderRadius.circular(AppTheme.radiusLarge),
          boxShadow: [BoxShadow(color: gradient.colors[0].withValues(alpha: 0.3), blurRadius: 8, offset: const Offset(0, 3))],
        ),
        padding: const EdgeInsets.all(AppTheme.spacingM),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.end,
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(12)),
              child: Icon(icon, size: 28, color: Colors.white),
            ),
            Text(title, textAlign: TextAlign.right, style: const TextStyle(color: Colors.white, fontSize: 14, fontWeight: FontWeight.bold)),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final site = AppConfig.baseUrl.replaceFirst('api/v1/', '');
    return Scaffold(
      body: Container(
        decoration: const BoxDecoration(gradient: AppTheme.backgroundGradient),
        child: SafeArea(
          child: Column(
            children: [
              // Header
              Container(
                decoration: BoxDecoration(
                  gradient: AppTheme.primaryGradient,
                  boxShadow: [BoxShadow(color: AppTheme.primaryNavy.withValues(alpha: 0.2), blurRadius: 8, offset: const Offset(0, 2))],
                ),
                child: Padding(
                  padding: const EdgeInsets.all(AppTheme.spacingM),
                  child: Row(
                    children: [
                      GestureDetector(
                        onTap: () => Navigator.pop(context),
                        child: Container(
                          width: 40, height: 40,
                          decoration: BoxDecoration(color: Colors.white.withValues(alpha: 0.2), borderRadius: BorderRadius.circular(10)),
                          child: const Icon(Icons.arrow_back_rounded, color: Colors.white),
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('لوحة الباحث عن عمل', style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold)),
                            Text(user['name'] ?? '', style: TextStyle(color: Colors.white.withValues(alpha: 0.8), fontSize: 13)),
                          ],
                        ),
                      ),
                      Container(
                        width: 40, height: 40,
                        decoration: BoxDecoration(color: AppTheme.secondaryGold, borderRadius: BorderRadius.circular(10)),
                        child: const Icon(Icons.person_rounded, color: AppTheme.primaryNavy, size: 22),
                      ),
                    ],
                  ),
                ),
              ),
              // Content
              Expanded(
                child: Padding(
                  padding: const EdgeInsets.all(AppTheme.spacingM),
                  child: GridView.count(
                    crossAxisCount: 2,
                    crossAxisSpacing: AppTheme.spacingS,
                    mainAxisSpacing: AppTheme.spacingS,
                    children: [
                      _jsCard(context, title: 'لوحتي', icon: Icons.dashboard_rounded, gradient: const LinearGradient(colors: [Color(0xFF4F46E5), Color(0xFF3730A3)]),
                        onTap: () => _open(context, 'لوحة الباحث', '${site}jobseeker')),
                      _jsCard(context, title: 'الملف الشخصي', icon: Icons.account_circle_rounded, gradient: const LinearGradient(colors: [Color(0xFF9333EA), Color(0xFF7E22CE)]),
                        onTap: () => _open(context, 'الملف الشخصي', '${site}jobseeker/profile')),
                      _jsCard(
                        context,
                        title: 'توثيق CV',
                        icon: Icons.verified_user_rounded,
                        gradient: const LinearGradient(colors: [Color(0xFFF59E0B), Color(0xFFD97706)]),
                        onTap: () => Navigator.push(context, MaterialPageRoute(builder: (_) => CvVerificationScreen(token: token))),
                      ),
                      _jsCard(context, title: 'الإشعارات', icon: Icons.notifications_active_rounded, gradient: const LinearGradient(colors: [Color(0xFF10B981), Color(0xFF059669)]),
                        onTap: () => _open(context, 'الإشعارات', '${site}notifications')),
                      _jsCard(context, title: 'تصفح الوظائف', icon: Icons.search_rounded, gradient: const LinearGradient(colors: [Color(0xFF0EA5E9), Color(0xFF0369A1)]),
                        onTap: () => _open(context, 'الوظائف', '${site}jobs')),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
