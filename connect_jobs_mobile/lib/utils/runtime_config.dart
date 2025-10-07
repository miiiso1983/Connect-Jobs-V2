import 'app_config.dart';

/// RuntimeConfig holds API endpoints that can be changed at runtime
/// without requiring a rebuild. Values are kept in-memory only for now
/// (session-only). Defaults come from AppConfig.
class RuntimeConfig {
  static String baseUrl = AppConfig.baseUrl; // should end with '/'
  static String authLoginPath = AppConfig.authLoginPath;
  static String registerJobSeekerPath = AppConfig.registerJobSeekerPath;
  static String registerCompanyPath = AppConfig.registerCompanyPath;

  static void apply({
    String? newBaseUrl,
    String? newAuthLoginPath,
    String? newRegisterJobSeekerPath,
    String? newRegisterCompanyPath,
  }) {
    if (newBaseUrl != null && newBaseUrl.trim().isNotEmpty) {
      baseUrl = _ensureTrailingSlash(newBaseUrl.trim());
    }
    if (newAuthLoginPath != null && newAuthLoginPath.trim().isNotEmpty) {
      authLoginPath = newAuthLoginPath.trim();
    }
    if (newRegisterJobSeekerPath != null && newRegisterJobSeekerPath.trim().isNotEmpty) {
      registerJobSeekerPath = newRegisterJobSeekerPath.trim();
    }
    if (newRegisterCompanyPath != null && newRegisterCompanyPath.trim().isNotEmpty) {
      registerCompanyPath = newRegisterCompanyPath.trim();
    }
  }

  static void resetToDefaults() {
    baseUrl = AppConfig.baseUrl;
    authLoginPath = AppConfig.authLoginPath;
    registerJobSeekerPath = AppConfig.registerJobSeekerPath;
    registerCompanyPath = AppConfig.registerCompanyPath;
  }

  static String _ensureTrailingSlash(String url) {
    return url.endsWith('/') ? url : '$url/';
  }
}

