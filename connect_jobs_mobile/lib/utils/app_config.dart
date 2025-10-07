/// Centralized app configuration (base URLs, environment flags, etc.)
class AppConfig {
  // Development/local
  static const String devBaseUrl = 'http://127.0.0.1:8000/api/v1/';
  // Production (provided by you)
  static const String prodBaseUrl = 'https://www.connect-job.com/api/v1/';
  static const String stageBaseUrl = prodBaseUrl;

  /// Current base url. You can wire this to flavors or --dart-define later.
  static const String baseUrl = prodBaseUrl;

  /// API paths (keep here so we can adjust quickly if backend differs)
  static const String authLoginPath = 'auth/login';
  static const String registerJobSeekerPath = 'auth/register/jobseeker';
  static const String registerCompanyPath = 'auth/register/company';
}

