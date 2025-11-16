# دليل API تطبيق Connect Jobs - للمطورين

## 📋 نظرة عامة

هذا الدليل يوفر توثيقاً شاملاً لجميع نقاط النهاية (Endpoints) المتاحة في API تطبيق Connect Jobs.

---

## 🌐 عناوين الخوادم

### الإنتاج (Production)
```
https://www.connect-job.com/api/v1/
```

### التطوير (Development)
```
http://127.0.0.1:8000/api/v1/
```

---

## 🔐 المصادقة (Authentication)

جميع النقاط المحمية تتطلب رمز Bearer في الهيدر:

```dart
headers: {
  'Authorization': 'Bearer YOUR_TOKEN_HERE',
  'Accept': 'application/json',
  'Content-Type': 'application/json',
}
```

---

## 📚 الملفات المتوفرة

| الملف | الوصف |
|------|-------|
| `API_DOCUMENTATION.md` | توثيق كامل لجميع نقاط النهاية (بالإنجليزية) |
| `API_EXAMPLES.md` | أمثلة عملية بكود Flutter |
| `API_README.md` | دليل البدء السريع |
| `API_GUIDE_AR.md` | هذا الملف - دليل سريع بالعربية |
| `Connect_Jobs_API.postman_collection.json` | مجموعة Postman للاختبار |

---

## 🚀 البدء السريع

### 1. تسجيل الدخول

```dart
import 'services/auth_service.dart';

final authService = AuthService();
final result = await authService.login(
  email: 'user@example.com',
  password: 'password123',
);

if (result['success'] == true) {
  final token = result['data']['token'];
  final user = result['data']['user'];
  
  // احفظ الرمز
  final box = await Hive.openBox('auth');
  await box.put('token', token);
  await box.put('user', user);
}
```

### 2. جلب قائمة الوظائف

```dart
import 'services/jobs_service.dart';

final jobsService = JobsService();
final result = await jobsService.listJobs(
  token: token,
  search: 'مطور',
  province: 'بغداد',
  page: 1,
);

if (result['success'] == true) {
  final jobs = result['data']['data'] as List;
  // اعرض الوظائف
}
```

### 3. التقديم على وظيفة

```dart
import 'services/applications_service.dart';

final applicationsService = ApplicationsService();
final result = await applicationsService.applyToJob(
  token: token,
  jobId: 5,
);

if (result['success'] == true) {
  print('تم التقديم بنجاح!');
  print('نسبة التطابق: ${result['data']['matching_percentage']}%');
}
```

---

## 📱 نقاط النهاية الرئيسية

### 🔑 المصادقة

| النقطة | الطريقة | الوصف |
|--------|---------|-------|
| `/auth/register` | POST | تسجيل مستخدم جديد |
| `/auth/login` | POST | تسجيل الدخول |
| `/auth/me` | GET | معلومات المستخدم الحالي |
| `/auth/logout` | POST | تسجيل الخروج |
| `/auth/register-fcm-token` | POST | تسجيل رمز الإشعارات |

### 💼 الوظائف

| النقطة | الطريقة | الوصف |
|--------|---------|-------|
| `/jobs` | GET | قائمة الوظائف (عامة) |
| `/jobs/{id}` | GET | تفاصيل وظيفة |
| `/jobs/` | POST | إنشاء وظيفة (شركة فقط) |
| `/jobs/{id}` | PUT | تحديث وظيفة (شركة فقط) |
| `/jobs/my-jobs` | GET | وظائفي (شركة فقط) |
| `/jobs/dashboard-stats` | GET | إحصائيات (شركة فقط) |

### 📝 الطلبات

| النقطة | الطريقة | الوصف |
|--------|---------|-------|
| `/applications/apply/{jobId}` | POST | التقديم على وظيفة (باحث فقط) |
| `/applications/my-applications` | GET | طلباتي (باحث فقط) |
| `/jobs/{jobId}/applications` | GET | طلبات الوظيفة (شركة فقط) |
| `/applications/{id}/status` | PUT | تحديث حالة الطلب (شركة فقط) |

### 👤 الملف الشخصي

| النقطة | الطريقة | الوصف |
|--------|---------|-------|
| `/profile/` | GET | عرض الملف الشخصي |
| `/profile/` | PUT | تحديث الملف الشخصي |
| `/profile/change-password` | POST | تغيير كلمة المرور |
| `/master-settings` | GET | الإعدادات العامة (المحافظات، التخصصات) |

### ⭐ المفضلة

| النقطة | الطريقة | الوصف |
|--------|---------|-------|
| `/favorites/` | GET | قائمة المفضلة |
| `/favorites/{jobId}` | POST | إضافة للمفضلة |
| `/favorites/{jobId}` | DELETE | إزالة من المفضلة |

---

## 🎯 الأدوار (Roles)

التطبيق يدعم دورين:

### 1. باحث عن عمل (jobseeker)
- تصفح الوظائف
- التقديم على الوظائف
- إدارة الملف الشخصي
- رفع السيرة الذاتية
- عرض حالة الطلبات

### 2. شركة (company)
- نشر الوظائف
- إدارة الوظائف
- عرض المتقدمين
- قبول/رفض الطلبات
- عرض الإحصائيات

---

## 📊 هيكل الاستجابة

### استجابة ناجحة
```json
{
  "success": true,
  "data": {
    // البيانات هنا
  },
  "message": "رسالة اختيارية"
}
```

### استجابة خطأ
```json
{
  "success": false,
  "message": "رسالة الخطأ",
  "errors": {
    // أخطاء التحقق (في حالة 422)
  }
}
```

---

## 🛠️ الخدمات المتوفرة (Services)

جميع الخدمات موجودة في مجلد `lib/services/`:

```dart
// المصادقة
AuthService authService = AuthService();

// الوظائف
JobsService jobsService = JobsService();

// الطلبات
ApplicationsService applicationsService = ApplicationsService();

// الملف الشخصي
ProfileService profileService = ProfileService();

// المفضلة
FavoritesService favoritesService = FavoritesService();

// الشركة
CompanyService companyService = CompanyService();

// الإشعارات
NotificationService notificationService = NotificationService();
```

---

## 💡 نصائح مهمة

### 1. حفظ الرمز بشكل آمن
```dart
// استخدم Hive أو flutter_secure_storage
final box = await Hive.openBox('auth');
await box.put('token', token);
```

### 2. معالجة انتهاء صلاحية الرمز
```dart
if (response.statusCode == 401) {
  // الرمز منتهي الصلاحية
  // أعد توجيه المستخدم لتسجيل الدخول
  Navigator.pushReplacementNamed(context, '/login');
}
```

### 3. الدعم دون اتصال
```dart
// الوظائف تُحفظ تلقائياً للعمل دون اتصال
final jobs = await jobsService.listJobs(token: token);
// إذا فشل الاتصال، يتم إرجاع البيانات المحفوظة
```

### 4. رفع الملفات
```dart
// استخدم updateProfileMultipart لرفع الصور والسيرة الذاتية
await profileService.updateProfileMultipart(
  token: token,
  fields: {'name': 'أحمد'},
  files: {
    'profile_image': imageFile,
    'cv_file': cvFile,
  },
  onProgress: (progress) {
    print('التقدم: ${(progress * 100).toInt()}%');
  },
);
```

### 5. تسجيل رمز الإشعارات
```dart
// بعد تسجيل الدخول مباشرة
final fcmToken = await notificationService.getFcmToken();
if (fcmToken != null) {
  await notificationService.registerFcmToken(
    token: authToken,
    fcmToken: fcmToken,
  );
}
```

---

## 🔍 البحث والفلترة

### البحث في الوظائف
```dart
final jobs = await jobsService.listJobs(
  token: token,
  search: 'مطور فلاتر',      // البحث في العنوان والوصف
  province: 'بغداد',         // فلترة حسب المحافظة
  speciality: 'تقنية المعلومات',  // فلترة حسب التخصص
  sortBy: 'created_at',      // الترتيب حسب
  sortOrder: 'desc',         // تنازلي
  page: 1,                   // رقم الصفحة
);
```

### فلترة الطلبات (للشركات)
```dart
final applications = await applicationsService.listApplications(
  token: token,
  jobId: 5,
  status: 'pending',         // قيد الانتظار فقط
  minMatching: 70,           // نسبة تطابق 70% فأكثر
  province: 'بغداد',
  sortBy: 'matching_percentage',
  sortOrder: 'desc',
);
```

---

## ⚠️ رموز الأخطاء الشائعة

| الرمز | الوصف | الحل |
|------|-------|-----|
| 200 | نجاح | - |
| 201 | تم الإنشاء بنجاح | - |
| 400 | طلب خاطئ | تحقق من البيانات المرسلة |
| 401 | غير مصرح | الرمز منتهي أو غير صحيح |
| 403 | ممنوع | ليس لديك صلاحية |
| 404 | غير موجود | المورد غير موجود |
| 422 | خطأ في التحقق | تحقق من حقل `errors` |
| 500 | خطأ في الخادم | حاول لاحقاً |

---

## 🧪 الاختبار

### استخدام Postman
1. استورد ملف `Connect_Jobs_API.postman_collection.json`
2. عدّل متغير `baseUrl` إلى عنوان الخادم
3. سجل دخول من نقطة Login
4. الرمز سيُحفظ تلقائياً في متغير `token`
5. جرب باقي النقاط

### فحص الاتصال
```dart
final response = await http.get(
  Uri.parse('${AppConfig.baseUrl}health'),
);
// يجب أن يرجع: {"success": true, "message": "API is working"}
```

---

## 📖 أمثلة عملية

### مثال كامل: تسجيل دخول وعرض الوظائف

```dart
import 'package:flutter/material.dart';
import 'services/auth_service.dart';
import 'services/jobs_service.dart';

class JobsScreen extends StatefulWidget {
  @override
  _JobsScreenState createState() => _JobsScreenState();
}

class _JobsScreenState extends State<JobsScreen> {
  final authService = AuthService();
  final jobsService = JobsService();
  
  String? token;
  List<dynamic> jobs = [];
  bool isLoading = false;

  @override
  void initState() {
    super.initState();
    _init();
  }

  Future<void> _init() async {
    // 1. تسجيل الدخول
    final loginResult = await authService.login(
      email: 'user@example.com',
      password: 'password123',
    );
    
    if (loginResult['success'] == true) {
      token = loginResult['data']['token'];
      
      // 2. جلب الوظائف
      await _loadJobs();
    }
  }

  Future<void> _loadJobs() async {
    setState(() { isLoading = true; });
    
    final result = await jobsService.listJobs(
      token: token!,
      sortBy: 'created_at',
      sortOrder: 'desc',
    );
    
    if (result['success'] == true) {
      setState(() {
        jobs = result['data']['data'] as List;
        isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('الوظائف المتاحة')),
      body: isLoading
          ? Center(child: CircularProgressIndicator())
          : ListView.builder(
              itemCount: jobs.length,
              itemBuilder: (context, index) {
                final job = jobs[index];
                return ListTile(
                  title: Text(job['title']),
                  subtitle: Text(job['company']['name']),
                  onTap: () {
                    // عرض تفاصيل الوظيفة
                  },
                );
              },
            ),
    );
  }
}
```

---

## 📞 الدعم

للمزيد من التفاصيل:
1. راجع `API_DOCUMENTATION.md` للتوثيق الكامل
2. راجع `API_EXAMPLES.md` لأمثلة متقدمة
3. استخدم Postman Collection للاختبار
4. تحقق من ملفات الخدمات في `lib/services/`

---

**آخر تحديث:** 2024-11-15  
**إصدار API:** 1.0.0  
**الخادم:** https://www.connect-job.com/api/v1/

