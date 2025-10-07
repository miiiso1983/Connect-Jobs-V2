class AppConstants {
  // API Configuration
  static const String baseUrl = 'http://127.0.0.1:8000/api/v1';
  
  // API Endpoints
  static const String loginEndpoint = '/auth/login';
  static const String logoutEndpoint = '/auth/logout';
  static const String refreshEndpoint = '/auth/refresh';
  static const String userEndpoint = '/auth/user';
  static const String jobsEndpoint = '/jobs';
  static const String applicationsEndpoint = '/applications';
  static const String profileEndpoint = '/profile';
  static const String masterSettingsEndpoint = '/master-settings';
  
  // Pagination
  static const int defaultPageSize = 15;
  static const int maxPageSize = 50;
  
  // User Roles
  static const String roleAdmin = 'admin';
  static const String roleCompany = 'company';
  static const String roleJobSeeker = 'jobseeker';
  
  // User Status
  static const String statusActive = 'active';
  static const String statusInactive = 'inactive';
  static const String statusSuspended = 'suspended';
  
  // Job Status
  static const String jobStatusDraft = 'draft';
  static const String jobStatusOpen = 'open';
  static const String jobStatusClosed = 'closed';
  static const String jobStatusPaused = 'paused';
  
  // Application Status
  static const String applicationStatusPending = 'pending';
  static const String applicationStatusAccepted = 'accepted';
  static const String applicationStatusRejected = 'rejected';
  static const String applicationStatusWithdrawn = 'withdrawn';
  
  // Storage Keys
  static const String tokenKey = 'auth_token';
  static const String userKey = 'user_data';
  static const String refreshTokenKey = 'refresh_token';
  
  // Error Messages
  static const String networkError = 'خطأ في الاتصال بالشبكة';
  static const String serverError = 'خطأ في الخادم';
  static const String unknownError = 'حدث خطأ غير متوقع';
  static const String invalidCredentials = 'بيانات الدخول غير صحيحة';
  static const String sessionExpired = 'انتهت صلاحية الجلسة';
  static const String noInternetConnection = 'لا يوجد اتصال بالإنترنت';
  
  // Success Messages
  static const String loginSuccess = 'تم تسجيل الدخول بنجاح';
  static const String logoutSuccess = 'تم تسجيل الخروج بنجاح';
  static const String profileUpdated = 'تم تحديث الملف الشخصي بنجاح';
  static const String applicationSubmitted = 'تم تقديم الطلب بنجاح';
  
  // Validation Messages
  static const String fieldRequired = 'هذا الحقل مطلوب';
  static const String invalidEmail = 'البريد الإلكتروني غير صحيح';
  static const String passwordTooShort = 'كلمة المرور قصيرة جداً';
  static const String passwordsDoNotMatch = 'كلمات المرور غير متطابقة';
  
  // App Info
  static const String appName = 'Connect Jobs';
  static const String appVersion = '1.0.0';
  static const String appDescription = 'تطبيق ربط المواهب بالفرص';
  
  // Timeouts
  static const Duration defaultTimeout = Duration(seconds: 30);
  static const Duration shortTimeout = Duration(seconds: 10);
  static const Duration longTimeout = Duration(minutes: 2);
  
  // UI Constants
  static const double defaultPadding = 16.0;
  static const double smallPadding = 8.0;
  static const double largePadding = 24.0;
  static const double defaultBorderRadius = 8.0;
  static const double cardElevation = 2.0;
  
  // Animation Durations
  static const Duration shortAnimation = Duration(milliseconds: 200);
  static const Duration mediumAnimation = Duration(milliseconds: 300);
  static const Duration longAnimation = Duration(milliseconds: 500);
}
