# Connect Jobs API Documentation - Index

## 📚 Complete Documentation Package for Flutter Developers

Welcome to the Connect Jobs API documentation. This package contains everything you need to integrate with the Connect Jobs backend API.

---

## 📁 Documentation Files

### 1. **API_DOCUMENTATION.md** (Primary Reference)
**Language:** English  
**Size:** ~950 lines  
**Purpose:** Complete API endpoint reference

**Contents:**
- All API endpoints with detailed descriptions
- Request/response formats for each endpoint
- Authentication requirements
- Query parameters and filters
- Error codes and handling
- Response examples with actual JSON structures

**Best for:**
- Looking up specific endpoint details
- Understanding request/response formats
- Checking available query parameters
- Reference during development

**Quick Links:**
- [Authentication Endpoints](API_DOCUMENTATION.md#1-authentication-endpoints)
- [Jobs Endpoints](API_DOCUMENTATION.md#2-jobs-endpoints)
- [Applications Endpoints](API_DOCUMENTATION.md#3-applications-endpoints)
- [Profile Endpoints](API_DOCUMENTATION.md#4-profile-endpoints)
- [Favorites Endpoints](API_DOCUMENTATION.md#5-favorites-endpoints)

---

### 2. **API_EXAMPLES.md** (Code Samples)
**Language:** English (with Dart/Flutter code)  
**Size:** ~600 lines  
**Purpose:** Practical Flutter implementation examples

**Contents:**
- Complete authentication flow with token storage
- Job seeker workflows (browse, search, apply)
- Company workflows (post jobs, review applications)
- Profile management with file uploads
- Error handling patterns
- Retry logic with exponential backoff

**Best for:**
- Learning how to implement features
- Copy-paste ready code snippets
- Understanding best practices
- Real-world usage patterns

**Quick Links:**
- [Authentication Flow](API_EXAMPLES.md#authentication-flow)
- [Job Seeker Workflows](API_EXAMPLES.md#job-seeker-workflows)
- [Company Workflows](API_EXAMPLES.md#company-workflows)
- [Profile Management](API_EXAMPLES.md#profile-management)
- [Error Handling](API_EXAMPLES.md#error-handling)

---

### 3. **API_README.md** (Quick Start Guide)
**Language:** English  
**Size:** ~250 lines  
**Purpose:** Getting started guide and overview

**Contents:**
- Quick start instructions
- Base URL configuration
- Authentication flow overview
- Available services overview
- Common workflows
- Important notes and tips
- Debugging guide

**Best for:**
- First-time setup
- Understanding the overall structure
- Quick reference for common tasks
- Troubleshooting common issues

---

### 4. **API_GUIDE_AR.md** (Arabic Quick Guide)
**Language:** Arabic (العربية)  
**Size:** ~350 lines  
**Purpose:** Quick reference guide in Arabic

**Contents:**
- نظرة عامة على API
- نقاط النهاية الرئيسية
- أمثلة سريعة بالعربية
- الأدوار والصلاحيات
- نصائح مهمة
- رموز الأخطاء الشائعة

**Best for:**
- Arabic-speaking developers
- Quick reference in native language
- Understanding roles and permissions
- Common error codes

---

### 5. **Connect_Jobs_API.postman_collection.json** (Postman Collection)
**Format:** JSON (Postman Collection v2.1)  
**Size:** ~680 lines  
**Purpose:** Ready-to-use Postman collection for API testing

**Contents:**
- All API endpoints organized by category
- Pre-configured requests with example data
- Automatic token extraction and storage
- Environment variables for easy switching
- Test scripts for common scenarios

**Best for:**
- Testing API endpoints without writing code
- Exploring API responses
- Debugging issues
- Sharing API examples with team

**How to use:**
1. Open Postman
2. Import `Connect_Jobs_API.postman_collection.json`
3. Set `baseUrl` variable to your server
4. Login to get token (auto-saved)
5. Test other endpoints

---

### 6. **API_INDEX.md** (This File)
**Language:** English  
**Purpose:** Navigation and overview of all documentation

---

## 🎯 Quick Navigation by Task

### I want to...

#### **Understand the API structure**
→ Start with [API_README.md](API_README.md)

#### **Look up a specific endpoint**
→ Go to [API_DOCUMENTATION.md](API_DOCUMENTATION.md)

#### **Implement a feature in Flutter**
→ Check [API_EXAMPLES.md](API_EXAMPLES.md)

#### **Test endpoints without coding**
→ Use [Connect_Jobs_API.postman_collection.json](Connect_Jobs_API.postman_collection.json)

#### **Read in Arabic**
→ See [API_GUIDE_AR.md](API_GUIDE_AR.md)

---

## 🚀 Getting Started (5-Minute Setup)

### Step 1: Configure Base URL
Edit `lib/utils/app_config.dart`:
```dart
static const String baseUrl = 'https://www.connect-job.com/api/v1/';
```

### Step 2: Login
```dart
final authService = AuthService();
final result = await authService.login(
  email: 'your@email.com',
  password: 'yourpassword',
);
final token = result['data']['token'];
```

### Step 3: Make API Calls
```dart
final jobsService = JobsService();
final jobs = await jobsService.listJobs(token: token);
```

**For detailed examples, see [API_EXAMPLES.md](API_EXAMPLES.md)**

---

## 📖 Documentation by User Role

### For Job Seekers (jobseeker role)

**Key Endpoints:**
- Browse jobs: `GET /jobs`
- Apply to job: `POST /applications/apply/{jobId}`
- My applications: `GET /applications/my-applications`
- Update profile: `PUT /profile/`
- Manage favorites: `POST /favorites/{jobId}`

**See:**
- [Job Seeker Workflows in API_EXAMPLES.md](API_EXAMPLES.md#job-seeker-workflows)
- [Applications Endpoints in API_DOCUMENTATION.md](API_DOCUMENTATION.md#3-applications-endpoints)

---

### For Companies (company role)

**Key Endpoints:**
- Create job: `POST /jobs/`
- My jobs: `GET /jobs/my-jobs`
- View applications: `GET /jobs/{jobId}/applications`
- Update application status: `PUT /applications/{id}/status`
- Dashboard stats: `GET /jobs/dashboard-stats`

**See:**
- [Company Workflows in API_EXAMPLES.md](API_EXAMPLES.md#company-workflows)
- [Jobs Endpoints in API_DOCUMENTATION.md](API_DOCUMENTATION.md#2-jobs-endpoints)

---

## 🛠️ Available Services

All service classes are located in `lib/services/`:

| Service | File | Purpose |
|---------|------|---------|
| AuthService | `auth_service.dart` | Login, register, logout, FCM tokens |
| JobsService | `jobs_service.dart` | Browse and search jobs |
| ApplicationsService | `applications_service.dart` | Apply to jobs, manage applications |
| ProfileService | `profile_service.dart` | View/update profile, upload files |
| FavoritesService | `favorites_service.dart` | Manage favorite jobs |
| CompanyService | `company_service.dart` | Company-specific features |
| NotificationService | `notification_service.dart` | Push notifications |

**For usage examples, see [API_EXAMPLES.md](API_EXAMPLES.md)**

---

## 🔍 Search This Documentation

### By Feature

- **Authentication:** [API_DOCUMENTATION.md#1-authentication-endpoints](API_DOCUMENTATION.md#1-authentication-endpoints)
- **Jobs:** [API_DOCUMENTATION.md#2-jobs-endpoints](API_DOCUMENTATION.md#2-jobs-endpoints)
- **Applications:** [API_DOCUMENTATION.md#3-applications-endpoints](API_DOCUMENTATION.md#3-applications-endpoints)
- **Profile:** [API_DOCUMENTATION.md#4-profile-endpoints](API_DOCUMENTATION.md#4-profile-endpoints)
- **Favorites:** [API_DOCUMENTATION.md#5-favorites-endpoints](API_DOCUMENTATION.md#5-favorites-endpoints)

### By HTTP Method

- **GET requests:** Browse, list, view endpoints
- **POST requests:** Create, apply, add endpoints
- **PUT requests:** Update endpoints
- **DELETE requests:** Remove, delete endpoints

### By Access Level

- **Public (✅):** No authentication required
- **Protected (🔒):** Requires Bearer token
- **Role-specific:** Requires specific role (jobseeker/company)

---

## 📊 API Statistics

- **Total Endpoints:** 25+
- **Public Endpoints:** 4
- **Protected Endpoints:** 21+
- **Roles:** 2 (jobseeker, company)
- **Base URL:** https://www.connect-job.com/api/v1/
- **API Version:** 1.0.0

---

## 💡 Best Practices

1. **Always check `success` field** before accessing `data`
2. **Store tokens securely** using Hive or flutter_secure_storage
3. **Handle 401 errors** by redirecting to login
4. **Use pagination** for list endpoints
5. **Implement retry logic** for network failures
6. **Cache data locally** for offline support
7. **Register FCM token** after login for notifications

**For detailed examples, see [API_EXAMPLES.md#error-handling](API_EXAMPLES.md#error-handling)**

---

## 🐛 Troubleshooting

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

**For more debugging tips, see [API_README.md#debugging-tips](API_README.md#debugging-tips)**

---

## 📞 Support & Resources

- **API Documentation:** [API_DOCUMENTATION.md](API_DOCUMENTATION.md)
- **Code Examples:** [API_EXAMPLES.md](API_EXAMPLES.md)
- **Quick Start:** [API_README.md](API_README.md)
- **Arabic Guide:** [API_GUIDE_AR.md](API_GUIDE_AR.md)
- **Postman Collection:** [Connect_Jobs_API.postman_collection.json](Connect_Jobs_API.postman_collection.json)

---

## 📝 Changelog

### Version 1.0.0 (2024-11-15)
- Initial API documentation release
- Complete endpoint reference
- Flutter code examples
- Postman collection
- Arabic quick guide

---

**Last Updated:** 2024-11-15  
**API Version:** 1.0.0  
**Documentation Version:** 1.0.0  
**Base URL:** https://www.connect-job.com/api/v1/

