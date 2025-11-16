# 📚 Connect Jobs API Documentation Package

## Welcome Flutter Developers! 👋

This is a **complete API documentation package** for the Connect Jobs mobile application. Everything you need to integrate with the backend API is here.

---

## 📦 What's Included?

This package contains **6 comprehensive files** totaling over **2,000 lines** of documentation:

| File | Size | Language | Purpose |
|------|------|----------|---------|
| **API_DOCUMENTATION.md** | 16 KB | English | Complete endpoint reference |
| **API_EXAMPLES.md** | 23 KB | English | Flutter code examples |
| **API_README.md** | 7.1 KB | English | Quick start guide |
| **API_GUIDE_AR.md** | 12 KB | Arabic | Quick guide in Arabic |
| **API_INDEX.md** | 9.5 KB | English | Navigation & overview |
| **Connect_Jobs_API.postman_collection.json** | 18 KB | JSON | Postman collection |

**Total:** ~85 KB of documentation

---

## 🚀 Quick Start (Choose Your Path)

### Path 1: I'm New to This Project
1. Start with **API_README.md** - Get the big picture
2. Read **API_INDEX.md** - Understand what's available
3. Try **Connect_Jobs_API.postman_collection.json** - Test the API
4. Implement using **API_EXAMPLES.md** - Copy working code

### Path 2: I Need a Specific Endpoint
1. Open **API_DOCUMENTATION.md**
2. Use Cmd+F to search for the endpoint
3. Copy the request format
4. Check **API_EXAMPLES.md** for implementation

### Path 3: I Want to Implement a Feature
1. Check **API_EXAMPLES.md** for the workflow
2. Copy the code example
3. Refer to **API_DOCUMENTATION.md** for details
4. Test with **Postman collection**

### Path 4: أنا أتحدث العربية
1. ابدأ بـ **API_GUIDE_AR.md**
2. استخدم **Connect_Jobs_API.postman_collection.json** للاختبار
3. راجع **API_EXAMPLES.md** للأمثلة البرمجية

---

## 📖 File Descriptions

### 1. API_DOCUMENTATION.md (The Bible)
**950+ lines of complete API reference**

Contains:
- ✅ All 25+ endpoints documented
- ✅ Request/response formats
- ✅ Authentication details
- ✅ Query parameters
- ✅ Error codes
- ✅ Real JSON examples

**Use when:** You need exact endpoint details

---

### 2. API_EXAMPLES.md (The Cookbook)
**600+ lines of Flutter code**

Contains:
- ✅ Complete authentication flow
- ✅ Job seeker workflows
- ✅ Company workflows
- ✅ Profile management
- ✅ File upload examples
- ✅ Error handling patterns

**Use when:** You want to implement a feature

---

### 3. API_README.md (The Quick Start)
**250+ lines of getting started guide**

Contains:
- ✅ 5-minute setup
- ✅ Configuration guide
- ✅ Common workflows
- ✅ Debugging tips
- ✅ Best practices

**Use when:** You're setting up for the first time

---

### 4. API_GUIDE_AR.md (الدليل العربي)
**350+ lines in Arabic**

يحتوي على:
- ✅ نظرة عامة على API
- ✅ نقاط النهاية الرئيسية
- ✅ أمثلة سريعة
- ✅ نصائح مهمة
- ✅ رموز الأخطاء

**استخدمه عندما:** تريد مرجع سريع بالعربية

---

### 5. API_INDEX.md (The Map)
**Navigation guide for all documentation**

Contains:
- ✅ Overview of all files
- ✅ Quick navigation links
- ✅ Search by feature
- ✅ Troubleshooting guide

**Use when:** You're not sure where to look

---

### 6. Connect_Jobs_API.postman_collection.json (The Tester)
**Ready-to-use Postman collection**

Contains:
- ✅ All endpoints pre-configured
- ✅ Example requests
- ✅ Auto token management
- ✅ Environment variables

**Use when:** You want to test without coding

---

## 🎯 Common Tasks

### Task: Login and Get Token
**File:** API_EXAMPLES.md → Authentication Flow
```dart
final authService = AuthService();
final result = await authService.login(
  email: 'user@example.com',
  password: 'password123',
);
final token = result['data']['token'];
```

### Task: Browse Jobs
**File:** API_EXAMPLES.md → Job Seeker Workflows
```dart
final jobsService = JobsService();
final jobs = await jobsService.listJobs(
  token: token,
  search: 'developer',
  province: 'Baghdad',
);
```

### Task: Apply to Job
**File:** API_EXAMPLES.md → Apply to Job
```dart
final applicationsService = ApplicationsService();
await applicationsService.applyToJob(
  token: token,
  jobId: 5,
);
```

### Task: Upload Profile Image
**File:** API_EXAMPLES.md → Profile Management
```dart
await profileService.updateProfileMultipart(
  token: token,
  files: {'profile_image': imageFile},
);
```

---

## 🔑 Key Concepts

### Base URL
```
Production: https://www.connect-job.com/api/v1/
Development: http://127.0.0.1:8000/api/v1/
```

### Authentication
All protected endpoints require:
```dart
headers: {
  'Authorization': 'Bearer YOUR_TOKEN',
  'Accept': 'application/json',
}
```

### Response Format
```json
{
  "success": true|false,
  "data": { ... },
  "message": "Optional message"
}
```

### Roles
- **jobseeker** - Browse jobs, apply, manage profile
- **company** - Post jobs, review applications

---

## 📱 Available Services

Located in `lib/services/`:

- **AuthService** - Login, register, logout
- **JobsService** - Browse, search jobs
- **ApplicationsService** - Apply, manage applications
- **ProfileService** - View/update profile
- **FavoritesService** - Manage favorites
- **CompanyService** - Company features
- **NotificationService** - Push notifications

---

## 🧪 Testing with Postman

1. **Import Collection**
   - Open Postman
   - Import `Connect_Jobs_API.postman_collection.json`

2. **Configure**
   - Set `baseUrl` variable to your server
   - Default: `https://www.connect-job.com/api/v1`

3. **Login**
   - Run "Login" request
   - Token auto-saved to `{{token}}` variable

4. **Test Endpoints**
   - All other requests will use the saved token
   - Modify request bodies as needed

---

## 💡 Pro Tips

1. **Always check `success` field** before accessing `data`
2. **Store tokens securely** using Hive or flutter_secure_storage
3. **Handle 401 errors** by redirecting to login
4. **Use pagination** for list endpoints (default: 15 items/page)
5. **Implement offline caching** - JobsService does this automatically
6. **Register FCM token** after login for push notifications

---

## 🐛 Troubleshooting

### Error 401 (Unauthorized)
- **Cause:** Token expired or invalid
- **Solution:** Re-login to get new token
- **See:** API_README.md → Debugging Tips

### Error 422 (Validation Error)
- **Cause:** Invalid request data
- **Solution:** Check `errors` field in response
- **See:** API_DOCUMENTATION.md → Error Codes

### Error 404 (Not Found)
- **Cause:** Wrong URL or resource doesn't exist
- **Solution:** Check API_DOCUMENTATION.md for correct endpoint
- **See:** API_INDEX.md → Search by Feature

---

## 📞 Need Help?

1. **Search this documentation** - Use Cmd+F in any file
2. **Check API_INDEX.md** - Navigate to the right section
3. **Try Postman** - Test endpoints without code
4. **Review examples** - API_EXAMPLES.md has working code
5. **Read in Arabic** - API_GUIDE_AR.md for Arabic speakers

---

## 📊 Documentation Stats

- **Total Files:** 6
- **Total Size:** ~85 KB
- **Total Lines:** 2,000+
- **Endpoints Documented:** 25+
- **Code Examples:** 15+
- **Languages:** English + Arabic

---

## 🎓 Learning Path

### Beginner
1. API_README.md (Quick start)
2. API_GUIDE_AR.md (If Arabic speaker)
3. Postman Collection (Test endpoints)

### Intermediate
1. API_DOCUMENTATION.md (Endpoint reference)
2. API_EXAMPLES.md (Implementation examples)
3. API_INDEX.md (Navigation)

### Advanced
1. Deep dive into service classes (`lib/services/`)
2. Implement custom error handling
3. Add offline caching
4. Optimize API calls

---

## ✅ Checklist for New Developers

- [ ] Read API_README.md
- [ ] Configure base URL in `lib/utils/app_config.dart`
- [ ] Import Postman collection and test login
- [ ] Implement authentication flow from API_EXAMPLES.md
- [ ] Test job listing
- [ ] Implement profile management
- [ ] Add error handling
- [ ] Register FCM token for notifications
- [ ] Test offline caching
- [ ] Review all service classes

---

## 📝 Version Info

- **Documentation Version:** 1.0.0
- **API Version:** 1.0.0
- **Last Updated:** 2024-11-15
- **Base URL:** https://www.connect-job.com/api/v1/

---

## 🙏 Thank You!

This documentation was created to make your development experience smooth and efficient. If you find any issues or have suggestions, please let us know!

**Happy Coding! 🚀**

---

**Start Here:** [API_INDEX.md](API_INDEX.md) → Navigate to what you need  
**Quick Start:** [API_README.md](API_README.md) → Get up and running  
**Full Reference:** [API_DOCUMENTATION.md](API_DOCUMENTATION.md) → All endpoints  
**Code Examples:** [API_EXAMPLES.md](API_EXAMPLES.md) → Working Flutter code  
**بالعربية:** [API_GUIDE_AR.md](API_GUIDE_AR.md) → دليل سريع بالعربية

