# Connect Jobs API - Flutter Code Examples

## Table of Contents
1. [Authentication Flow](#authentication-flow)
2. [Job Seeker Workflows](#job-seeker-workflows)
3. [Company Workflows](#company-workflows)
4. [Profile Management](#profile-management)
5. [Error Handling](#error-handling)

---

## Authentication Flow

### Complete Login Flow with Token Storage

```dart
import 'package:flutter/material.dart';
import 'package:hive_flutter/hive_flutter.dart';
import 'services/auth_service.dart';
import 'services/notification_service.dart';

class LoginScreen extends StatefulWidget {
  @override
  _LoginScreenState createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _authService = AuthService();
  final _notificationService = NotificationService();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  bool _isLoading = false;
  String? _errorMessage;

  Future<void> _handleLogin() async {
    setState(() {
      _isLoading = true;
      _errorMessage = null;
    });

    try {
      // 1. Login
      final result = await _authService.login(
        email: _emailController.text.trim(),
        password: _passwordController.text,
      );

      if (result['success'] == true) {
        final token = result['data']['token'] as String;
        final user = result['data']['user'] as Map<String, dynamic>;
        
        // 2. Save token to Hive
        final box = await Hive.openBox('auth');
        await box.put('token', token);
        await box.put('user', user);
        
        // 3. Register FCM token for notifications
        final fcmToken = await _notificationService.getFcmToken();
        if (fcmToken != null) {
          await _notificationService.registerFcmToken(
            token: token,
            fcmToken: fcmToken,
          );
        }
        
        // 4. Navigate based on role
        if (user['role'] == 'jobseeker') {
          Navigator.pushReplacementNamed(context, '/jobseeker-home');
        } else if (user['role'] == 'company') {
          Navigator.pushReplacementNamed(context, '/company-dashboard');
        }
      } else {
        setState(() {
          _errorMessage = result['message'] ?? 'فشل تسجيل الدخول';
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'خطأ في الاتصال: $e';
      });
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Padding(
        padding: EdgeInsets.all(16),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            TextField(
              controller: _emailController,
              decoration: InputDecoration(labelText: 'البريد الإلكتروني'),
              keyboardType: TextInputType.emailAddress,
            ),
            SizedBox(height: 16),
            TextField(
              controller: _passwordController,
              decoration: InputDecoration(labelText: 'كلمة المرور'),
              obscureText: true,
            ),
            if (_errorMessage != null) ...[
              SizedBox(height: 16),
              Text(_errorMessage!, style: TextStyle(color: Colors.red)),
            ],
            SizedBox(height: 24),
            ElevatedButton(
              onPressed: _isLoading ? null : _handleLogin,
              child: _isLoading
                  ? CircularProgressIndicator()
                  : Text('تسجيل الدخول'),
            ),
          ],
        ),
      ),
    );
  }
}
```

---

### Registration Flow

```dart
Future<void> registerJobSeeker({
  required String name,
  required String email,
  required String password,
  required String phone,
  required String province,
}) async {
  final authService = AuthService();
  
  final result = await authService.register(
    name: name,
    email: email,
    password: password,
    passwordConfirmation: password,
    role: 'jobseeker',
    phone: phone,
    province: province,
  );
  
  if (result['success'] == true) {
    final token = result['data']['token'];
    final user = result['data']['user'];
    
    // Save to storage
    final box = await Hive.openBox('auth');
    await box.put('token', token);
    await box.put('user', user);
    
    // Navigate to home
    Navigator.pushReplacementNamed(context, '/jobseeker-home');
  } else {
    // Show error
    showDialog(
      context: context,
      builder: (context) => AlertDialog(
        title: Text('خطأ'),
        content: Text(result['message'] ?? 'فشل التسجيل'),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('حسناً'),
          ),
        ],
      ),
    );
  }
}
```

---

## Job Seeker Workflows

### Browse and Search Jobs

```dart
import 'services/jobs_service.dart';

class JobsListScreen extends StatefulWidget {
  final String token;
  
  JobsListScreen({required this.token});
  
  @override
  _JobsListScreenState createState() => _JobsListScreenState();
}

class _JobsListScreenState extends State<JobsListScreen> {
  final _jobsService = JobsService();
  List<dynamic> _jobs = [];
  bool _isLoading = false;
  String? _searchQuery;
  String? _selectedProvince;
  int _currentPage = 1;
  int _totalPages = 1;

  @override
  void initState() {
    super.initState();
    _loadJobs();
  }

  Future<void> _loadJobs({bool loadMore = false}) async {
    if (_isLoading) return;
    
    setState(() {
      _isLoading = true;
    });

    try {
      final result = await _jobsService.listJobs(
        token: widget.token,
        search: _searchQuery,
        province: _selectedProvince,
        page: loadMore ? _currentPage + 1 : 1,
        sortBy: 'created_at',
        sortOrder: 'desc',
      );

      if (result['success'] == true) {
        final data = result['data'];
        setState(() {
          if (loadMore) {
            _jobs.addAll(data['data'] as List);
            _currentPage++;
          } else {
            _jobs = data['data'] as List;
            _currentPage = data['current_page'] ?? 1;
          }
          _totalPages = data['last_page'] ?? 1;
        });
      }
    } catch (e) {
      print('Error loading jobs: $e');
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  void _onSearch(String query) {
    setState(() {
      _searchQuery = query.isEmpty ? null : query;
    });
    _loadJobs();
  }

  void _onProvinceFilter(String? province) {
    setState(() {
      _selectedProvince = province;
    });
    _loadJobs();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('الوظائف المتاحة'),
        actions: [
          IconButton(
            icon: Icon(Icons.filter_list),
            onPressed: () => _showFilterDialog(),
          ),
        ],
      ),
      body: Column(
        children: [
          // Search bar
          Padding(
            padding: EdgeInsets.all(16),
            child: TextField(
              decoration: InputDecoration(
                hintText: 'ابحث عن وظيفة...',
                prefixIcon: Icon(Icons.search),
                border: OutlineInputBorder(),
              ),
              onChanged: _onSearch,
            ),
          ),
          
          // Jobs list
          Expanded(
            child: _isLoading && _jobs.isEmpty
                ? Center(child: CircularProgressIndicator())
                : ListView.builder(
                    itemCount: _jobs.length + (_currentPage < _totalPages ? 1 : 0),
                    itemBuilder: (context, index) {
                      if (index == _jobs.length) {
                        // Load more button
                        return Padding(
                          padding: EdgeInsets.all(16),
                          child: ElevatedButton(
                            onPressed: () => _loadJobs(loadMore: true),
                            child: Text('تحميل المزيد'),
                          ),
                        );
                      }
                      
                      final job = _jobs[index];
                      return JobCard(
                        job: job,
                        token: widget.token,
                        onTap: () => _navigateToJobDetails(job),
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }

  void _showFilterDialog() {
    // Show filter dialog with province selection
  }

  void _navigateToJobDetails(Map<String, dynamic> job) {
    Navigator.push(
      context,
      MaterialPageRoute(
        builder: (context) => JobDetailsScreen(
          jobId: job['id'],
          token: widget.token,
        ),
      ),
    );
  }
}
```

---

### Apply to Job

```dart
import 'services/applications_service.dart';

Future<void> applyToJob({
  required BuildContext context,
  required String token,
  required int jobId,
}) async {
  final applicationsService = ApplicationsService();
  
  // Show loading dialog
  showDialog(
    context: context,
    barrierDismissible: false,
    builder: (context) => Center(child: CircularProgressIndicator()),
  );
  
  try {
    final result = await applicationsService.applyToJob(
      token: token,
      jobId: jobId,
    );
    
    // Close loading dialog
    Navigator.pop(context);
    
    if (result['success'] == true) {
      final matchingPercentage = result['data']['matching_percentage'];
      
      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          title: Text('تم التقديم بنجاح'),
          content: Text(
            'تم تقديم طلبك بنجاح!\n'
            'نسبة التطابق: $matchingPercentage%'
          ),
          actions: [
            TextButton(
              onPressed: () {
                Navigator.pop(context);
                Navigator.pushNamed(context, '/my-applications');
              },
              child: Text('عرض طلباتي'),
            ),
          ],
        ),
      );
    } else {
      showDialog(
        context: context,
        builder: (context) => AlertDialog(
          title: Text('خطأ'),
          content: Text(result['message'] ?? 'فشل التقديم'),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(context),
              child: Text('حسناً'),
            ),
          ],
        ),
      );
    }
  } catch (e) {
    Navigator.pop(context); // Close loading
    print('Error applying: $e');
  }
}
```

---

### View My Applications

```dart
Future<List<dynamic>> loadMyApplications(String token) async {
  final response = await http.get(
    Uri.parse('${AppConfig.baseUrl}applications/my-applications'),
    headers: {
      'Accept': 'application/json',
      'Authorization': 'Bearer $token',
    },
  );
  
  if (response.statusCode == 200) {
    final data = jsonDecode(response.body);
    if (data['success'] == true) {
      return data['data']['data'] as List;
    }
  }
  
  return [];
}

// Widget
class MyApplicationsScreen extends StatelessWidget {
  final String token;
  
  MyApplicationsScreen({required this.token});
  
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('طلباتي')),
      body: FutureBuilder<List<dynamic>>(
        future: loadMyApplications(token),
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return Center(child: CircularProgressIndicator());
          }
          
          if (!snapshot.hasData || snapshot.data!.isEmpty) {
            return Center(child: Text('لا توجد طلبات'));
          }
          
          return ListView.builder(
            itemCount: snapshot.data!.length,
            itemBuilder: (context, index) {
              final app = snapshot.data![index];
              return ApplicationCard(application: app);
            },
          );
        },
      ),
    );
  }
}
```

---

## Company Workflows

### View Job Applications with Filtering

```dart
import 'services/applications_service.dart';

class JobApplicationsScreen extends StatefulWidget {
  final String token;
  final int jobId;
  
  JobApplicationsScreen({required this.token, required this.jobId});
  
  @override
  _JobApplicationsScreenState createState() => _JobApplicationsScreenState();
}

class _JobApplicationsScreenState extends State<JobApplicationsScreen> {
  final _applicationsService = ApplicationsService();
  List<dynamic> _applications = [];
  bool _isLoading = false;
  String? _statusFilter;
  int? _minMatchingFilter;

  @override
  void initState() {
    super.initState();
    _loadApplications();
  }

  Future<void> _loadApplications() async {
    setState(() {
      _isLoading = true;
    });

    try {
      final result = await _applicationsService.listApplications(
        token: widget.token,
        jobId: widget.jobId,
        status: _statusFilter,
        minMatching: _minMatchingFilter,
        sortBy: 'matching_percentage',
        sortOrder: 'desc',
      );

      if (result['success'] == true) {
        setState(() {
          _applications = result['data'] as List;
        });
      }
    } catch (e) {
      print('Error: $e');
    } finally {
      setState(() {
        _isLoading = false;
      });
    }
  }

  Future<void> _updateApplicationStatus(int applicationId, String status) async {
    try {
      final response = await http.put(
        Uri.parse('${AppConfig.baseUrl}applications/$applicationId/status'),
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
          'Authorization': 'Bearer ${widget.token}',
        },
        body: jsonEncode({'status': status}),
      );

      if (response.statusCode == 200) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('تم تحديث حالة الطلب')),
        );
        _loadApplications(); // Reload
      }
    } catch (e) {
      print('Error: $e');
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('المتقدمون للوظيفة'),
        actions: [
          PopupMenuButton<String>(
            onSelected: (value) {
              setState(() {
                _statusFilter = value == 'all' ? null : value;
              });
              _loadApplications();
            },
            itemBuilder: (context) => [
              PopupMenuItem(value: 'all', child: Text('الكل')),
              PopupMenuItem(value: 'pending', child: Text('قيد الانتظار')),
              PopupMenuItem(value: 'accepted', child: Text('مقبول')),
              PopupMenuItem(value: 'rejected', child: Text('مرفوض')),
            ],
          ),
        ],
      ),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : ListView.builder(
              itemCount: _applications.length,
              itemBuilder: (context, index) {
                final app = _applications[index];
                final jobSeeker = app['job_seeker'];
                
                return Card(
                  margin: EdgeInsets.all(8),
                  child: ListTile(
                    title: Text(jobSeeker['name']),
                    subtitle: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text('نسبة التطابق: ${app['matching_percentage']}%'),
                        Text('الحالة: ${app['status']}'),
                      ],
                    ),
                    trailing: Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        IconButton(
                          icon: Icon(Icons.check, color: Colors.green),
                          onPressed: () => _updateApplicationStatus(
                            app['id'],
                            'accepted',
                          ),
                        ),
                        IconButton(
                          icon: Icon(Icons.close, color: Colors.red),
                          onPressed: () => _updateApplicationStatus(
                            app['id'],
                            'rejected',
                          ),
                        ),
                      ],
                    ),
                    onTap: () {
                      // View applicant details
                    },
                  ),
                );
              },
            ),
    );
  }
}
```

---

## Profile Management

### Update Profile with Image Upload

```dart
import 'dart:io';
import 'package:image_picker/image_picker.dart';
import 'services/profile_service.dart';

class EditProfileScreen extends StatefulWidget {
  final String token;
  
  EditProfileScreen({required this.token});
  
  @override
  _EditProfileScreenState createState() => _EditProfileScreenState();
}

class _EditProfileScreenState extends State<EditProfileScreen> {
  final _profileService = ProfileService();
  final _nameController = TextEditingController();
  final _phoneController = TextEditingController();
  File? _selectedImage;
  File? _selectedCV;
  bool _isSaving = false;
  double _uploadProgress = 0.0;

  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final pickedFile = await picker.pickImage(source: ImageSource.gallery);
    
    if (pickedFile != null) {
      setState(() {
        _selectedImage = File(pickedFile.path);
      });
    }
  }

  Future<void> _pickCV() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: ['pdf'],
    );
    
    if (result != null) {
      setState(() {
        _selectedCV = File(result.files.single.path!);
      });
    }
  }

  Future<void> _saveProfile() async {
    setState(() {
      _isSaving = true;
      _uploadProgress = 0.0;
    });

    try {
      final fields = {
        'name': _nameController.text,
        'phone': _phoneController.text,
      };

      final files = <String, File>{};
      if (_selectedImage != null) {
        files['profile_image'] = _selectedImage!;
      }
      if (_selectedCV != null) {
        files['cv_file'] = _selectedCV!;
      }

      final result = await _profileService.updateProfileMultipart(
        token: widget.token,
        fields: fields,
        files: files,
        onProgress: (progress) {
          setState(() {
            _uploadProgress = progress;
          });
        },
      );

      if (result['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text('تم تحديث الملف الشخصي')),
        );
        Navigator.pop(context);
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(result['message'] ?? 'فشل التحديث')),
        );
      }
    } catch (e) {
      print('Error: $e');
    } finally {
      setState(() {
        _isSaving = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text('تعديل الملف الشخصي')),
      body: SingleChildScrollView(
        padding: EdgeInsets.all(16),
        child: Column(
          children: [
            // Profile image picker
            GestureDetector(
              onTap: _pickImage,
              child: CircleAvatar(
                radius: 50,
                backgroundImage: _selectedImage != null
                    ? FileImage(_selectedImage!)
                    : null,
                child: _selectedImage == null
                    ? Icon(Icons.camera_alt, size: 40)
                    : null,
              ),
            ),
            SizedBox(height: 24),
            
            TextField(
              controller: _nameController,
              decoration: InputDecoration(labelText: 'الاسم'),
            ),
            SizedBox(height: 16),
            
            TextField(
              controller: _phoneController,
              decoration: InputDecoration(labelText: 'رقم الهاتف'),
            ),
            SizedBox(height: 16),
            
            // CV picker
            ElevatedButton.icon(
              onPressed: _pickCV,
              icon: Icon(Icons.upload_file),
              label: Text(_selectedCV != null ? 'تم اختيار السيرة الذاتية' : 'رفع السيرة الذاتية'),
            ),
            
            if (_isSaving) ...[
              SizedBox(height: 24),
              LinearProgressIndicator(value: _uploadProgress),
              Text('${(_uploadProgress * 100).toInt()}%'),
            ],
            
            SizedBox(height: 24),
            ElevatedButton(
              onPressed: _isSaving ? null : _saveProfile,
              child: Text('حفظ'),
            ),
          ],
        ),
      ),
    );
  }
}
```

---

## Error Handling

### Global Error Handler

```dart
class ApiErrorHandler {
  static String getErrorMessage(dynamic error, int? statusCode) {
    if (statusCode == 401) {
      return 'انتهت صلاحية الجلسة. يرجى تسجيل الدخول مرة أخرى';
    } else if (statusCode == 403) {
      return 'ليس لديك صلاحية للوصول إلى هذا المورد';
    } else if (statusCode == 404) {
      return 'المورد المطلوب غير موجود';
    } else if (statusCode == 422) {
      if (error is Map && error.containsKey('errors')) {
        final errors = error['errors'] as Map;
        return errors.values
            .expand((e) => e is List ? e : [e])
            .join('\n');
      }
      return error['message'] ?? 'بيانات غير صحيحة';
    } else if (statusCode != null && statusCode >= 500) {
      return 'خطأ في الخادم. يرجى المحاولة لاحقاً';
    }
    
    return error['message'] ?? 'حدث خطأ غير متوقع';
  }

  static Future<void> handleUnauthorized(BuildContext context) async {
    // Clear stored token
    final box = await Hive.openBox('auth');
    await box.clear();
    
    // Navigate to login
    Navigator.pushNamedAndRemoveUntil(
      context,
      '/login',
      (route) => false,
    );
  }
}
```

---

### Retry Logic with Exponential Backoff

```dart
Future<T> retryWithBackoff<T>({
  required Future<T> Function() operation,
  int maxRetries = 3,
  Duration initialDelay = const Duration(seconds: 1),
}) async {
  int attempt = 0;
  
  while (true) {
    try {
      return await operation();
    } catch (e) {
      attempt++;
      
      if (attempt >= maxRetries) {
        rethrow;
      }
      
      final delay = initialDelay * (1 << attempt); // Exponential backoff
      await Future.delayed(delay);
    }
  }
}

// Usage
final jobs = await retryWithBackoff(
  operation: () => jobsService.listJobs(token: token),
  maxRetries: 3,
);
```

---

**For more examples, check the service files in `lib/services/` directory.**

