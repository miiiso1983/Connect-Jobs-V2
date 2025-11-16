# Connect Jobs API Documentation

## 📚 Documentation Files

This repository contains comprehensive API documentation for Flutter developers working on the Connect Jobs mobile application.

### Files Overview

1. **API_DOCUMENTATION.md** - Complete API Reference
   - All available endpoints
   - Request/response formats
   - Authentication details
   - Error codes
   - Query parameters

2. **API_EXAMPLES.md** - Practical Code Examples
   - Complete Flutter code samples
   - Real-world workflows
   - Error handling patterns
   - Best practices

3. **API_README.md** - This file
   - Quick start guide
   - Documentation overview

---

## 🚀 Quick Start

### 1. Base URL Configuration

The API has two environments:

**Production:**
```dart
const String baseUrl = 'https://www.connect-job.com/api/v1/';
```

**Development:**
```dart
const String baseUrl = 'http://127.0.0.1:8000/api/v1/';
```

Configure in `lib/utils/app_config.dart`

---

### 2. Authentication Flow

```dart
// 1. Login
final authService = AuthService();
final result = await authService.login(
  email: 'user@example.com',
  password: 'password123',
);

// 2. Extract token
if (result['success'] == true) {
  final token = result['data']['token'];
  final user = result['data']['user'];
  
  // 3. Save token
  final box = await Hive.openBox('auth');
  await box.put('token', token);
  await box.put('user', user);
}
```

---

### 3. Making API Calls

All service classes are available in `lib/services/`:

```dart
// Jobs
final jobsService = JobsService();
final jobs = await jobsService.listJobs(token: token);

// Applications
final applicationsService = ApplicationsService();
await applicationsService.applyToJob(token: token, jobId: 5);

// Profile
final profileService = ProfileService();
final profile = await profileService.getProfile(token: token);

// Favorites
final favoritesService = FavoritesService();
await favoritesService.addFavorite(token: token, jobId: 5);
```

---

## 📖 How to Use This Documentation

### For New Developers

1. **Start with API_DOCUMENTATION.md**
   - Read the "Authentication Endpoints" section
   - Understand the response format
   - Review error codes

2. **Move to API_EXAMPLES.md**
   - Follow the "Authentication Flow" example
   - Implement login/registration
   - Test with your credentials

3. **Implement Features**
   - Use the relevant service class
   - Refer to endpoint documentation
   - Copy code examples as needed

### For Experienced Developers

- Use **API_DOCUMENTATION.md** as a quick reference
- Jump to specific endpoint sections
- Check query parameters and filters
- Review response structures

---

## 🔑 Key Concepts

### 1. Token-Based Authentication

All protected endpoints require a Bearer token:

```dart
headers: {
  'Authorization': 'Bearer $token',
  'Accept': 'application/json',
}
```

### 2. Role-Based Access

Two user roles:
- **jobseeker** - Can browse jobs, apply, manage profile
- **company** - Can post jobs, review applications, manage listings

Check user role before showing UI:
```dart
if (user['role'] == 'jobseeker') {
  // Show job seeker UI
} else if (user['role'] == 'company') {
  // Show company UI
}
```

### 3. Response Format

All responses follow this structure:
```json
{
  "success": true|false,
  "data": { ... },
  "message": "Optional message"
}
```

Always check `success` before accessing `data`:
```dart
if (result['success'] == true) {
  final data = result['data'];
  // Process data
} else {
  final error = result['message'];
  // Show error
}
```

---

## 🛠️ Available Services

| Service | File | Purpose |
|---------|------|---------|
| AuthService | `lib/services/auth_service.dart` | Login, register, logout |
| JobsService | `lib/services/jobs_service.dart` | Browse, search jobs |
| ApplicationsService | `lib/services/applications_service.dart` | Apply, view applications |
| ProfileService | `lib/services/profile_service.dart` | View/update profile |
| FavoritesService | `lib/services/favorites_service.dart` | Manage favorites |
| CompanyService | `lib/services/company_service.dart` | Company dashboard stats |
| NotificationService | `lib/services/notification_service.dart` | FCM push notifications |

---

## 📱 Common Workflows

### Job Seeker Flow
1. Register/Login → Get token
2. Browse jobs → `JobsService.listJobs()`
3. View job details → `GET /jobs/{id}`
4. Apply to job → `ApplicationsService.applyToJob()`
5. Check application status → `GET /applications/my-applications`

### Company Flow
1. Register/Login → Get token
2. Create job posting → `POST /jobs/`
3. View applications → `GET /jobs/{jobId}/applications`
4. Update application status → `PUT /applications/{id}/status`
5. View dashboard stats → `GET /jobs/dashboard-stats`

---

## ⚠️ Important Notes

### 1. Token Storage
Store tokens securely using `hive` or `flutter_secure_storage`:
```dart
final box = await Hive.openBox('auth');
await box.put('token', token);
```

### 2. Token Expiration
Handle 401 errors by refreshing token or redirecting to login:
```dart
if (response.statusCode == 401) {
  // Token expired
  await ApiErrorHandler.handleUnauthorized(context);
}
```

### 3. Offline Support
Jobs are cached locally for offline access:
```dart
// JobsService automatically caches responses
final jobs = await jobsService.listJobs(token: token);
// Returns cached data if network fails
```

### 4. File Uploads
Use multipart for profile images and CVs:
```dart
await profileService.updateProfileMultipart(
  token: token,
  fields: {'name': 'John'},
  files: {'profile_image': imageFile},
  onProgress: (progress) => print('$progress%'),
);
```

### 5. FCM Notifications
Register FCM token after login:
```dart
final fcmToken = await notificationService.getFcmToken();
await notificationService.registerFcmToken(
  token: authToken,
  fcmToken: fcmToken,
);
```

---

## 🐛 Debugging Tips

### Enable Logging
```dart
// In development, log all requests
if (kDebugMode) {
  print('Request: ${uri.toString()}');
  print('Headers: $headers');
  print('Body: $body');
}
```

### Test Endpoints
Use the health check endpoint to verify API connectivity:
```dart
final response = await http.get(
  Uri.parse('${AppConfig.baseUrl}health'),
);
// Should return: {"success": true, "message": "API is working"}
```

### Common Issues

**401 Unauthorized**
- Token expired or invalid
- Missing Authorization header
- Solution: Re-login or refresh token

**422 Validation Error**
- Invalid request data
- Check `errors` field in response
- Solution: Fix input validation

**404 Not Found**
- Wrong endpoint URL
- Resource doesn't exist
- Solution: Check API_DOCUMENTATION.md for correct path

---

## 📞 Support

For API issues or questions:
1. Check **API_DOCUMENTATION.md** for endpoint details
2. Review **API_EXAMPLES.md** for code samples
3. Test with Postman/curl to isolate issues
4. Check Laravel logs on server: `storage/logs/laravel.log`

---

## 📝 Changelog

### Version 1.0.0 (2024-11-15)
- Initial API documentation
- Complete endpoint reference
- Flutter code examples
- Service class documentation

---

**Last Updated:** 2024-11-15  
**API Version:** 1.0.0  
**Base URL:** https://www.connect-job.com/api/v1/

