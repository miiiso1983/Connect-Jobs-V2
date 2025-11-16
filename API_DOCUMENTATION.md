# Connect Jobs API Documentation for Flutter Developers

## Base Configuration

### Production URL
```
https://www.connect-job.com/api/v1/
```

### Development URL
```
http://127.0.0.1:8000/api/v1/
```

### Authentication
All protected endpoints require a Bearer token in the Authorization header:
```
Authorization: Bearer {token}
```

### Standard Headers
```json
{
  "Content-Type": "application/json",
  "Accept": "application/json"
}
```

---

## Response Format

### Success Response
```json
{
  "success": true,
  "data": { ... },
  "message": "Success message (optional)"
}
```

### Error Response
```json
{
  "success": false,
  "message": "Error message",
  "errors": { ... } // Validation errors (422 status)
}
```

---

## 1. Authentication Endpoints

### 1.1 Register
**POST** `/auth/register`

**Public** ✅

**Request Body:**
```json
{
  "name": "string (required)",
  "email": "string (required, email)",
  "password": "string (required, min:8)",
  "password_confirmation": "string (required)",
  "role": "jobseeker|company (required)",
  "phone": "string (optional)",
  "province": "string (optional)"
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "user": { ... },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  },
  "message": "Registration successful"
}
```

---

### 1.2 Login
**POST** `/auth/login`

**Public** ✅

**Request Body:**
```json
{
  "email": "string (required)",
  "password": "string (required)"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "jobseeker|company",
      "phone": "1234567890",
      "province": "Baghdad"
    },
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
  }
}
```

---

### 1.3 Get Current User
**GET** `/auth/me`

**Protected** 🔒

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "jobseeker",
    ...
  }
}
```

---

### 1.4 Logout
**POST** `/auth/logout`

**Protected** 🔒

**Response (200):**
```json
{
  "success": true,
  "message": "Successfully logged out"
}
```

---

### 1.5 Refresh Token
**POST** `/auth/refresh`

**Public** ✅

**Headers:**
```
Authorization: Bearer {old_token}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "token": "new_token_here"
  }
}
```

---

### 1.6 Register FCM Token
**POST** `/auth/register-fcm-token`

**Protected** 🔒

**Request Body:**
```json
{
  "fcm_token": "string (required)",
  "device_id": "string (optional)",
  "device_type": "android|ios (optional)"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "FCM token registered successfully"
}
```

---

### 1.7 Unregister FCM Token
**DELETE** `/auth/unregister-fcm-token`

**Protected** 🔒

**Request Body:**
```json
{
  "fcm_token": "string (required)"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "FCM token unregistered successfully"
}
```

---

## 2. Jobs Endpoints

### 2.1 List Jobs
**GET** `/jobs`

**Public** ✅ (Can be accessed with or without authentication)

**Query Parameters:**
- `search` (string, optional) - Search in job title, description
- `province` (string, optional) - Filter by province
- `speciality` (string, optional) - Filter by speciality
- `sort_by` (string, optional, default: "id") - Sort field
- `sort_order` (string, optional, default: "desc") - asc|desc
- `page` (integer, optional, default: 1) - Page number

**Example Request:**
```
GET /jobs?search=developer&province=Baghdad&page=1
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "title": "Senior Developer",
        "description": "Job description...",
        "province": "Baghdad",
        "speciality": "IT",
        "salary_range": "1000-2000",
        "requirements": "Requirements...",
        "company": {
          "id": 5,
          "name": "Tech Company",
          "email": "company@example.com"
        },
        "created_at": "2024-01-01T00:00:00.000000Z",
        "is_favorite": false
      }
    ],
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 75
  }
}
```

---

### 2.2 Get Job Details
**GET** `/jobs/{id}`

**Public** ✅

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "title": "Senior Developer",
    "description": "Full job description...",
    "province": "Baghdad",
    "speciality": "IT",
    "salary_range": "1000-2000",
    "requirements": "Requirements...",
    "benefits": "Benefits...",
    "company": {
      "id": 5,
      "name": "Tech Company",
      "email": "company@example.com",
      "phone": "1234567890"
    },
    "created_at": "2024-01-01T00:00:00.000000Z",
    "is_favorite": false,
    "has_applied": false
  }
}
```

---

### 2.3 Create Job (Company Only)
**POST** `/jobs/`

**Protected** 🔒 **Role:** Company

**Request Body:**
```json
{
  "title": "string (required)",
  "description": "string (required)",
  "province": "string (required)",
  "speciality": "string (required)",
  "salary_range": "string (optional)",
  "requirements": "string (optional)",
  "benefits": "string (optional)",
  "districts": ["district1", "district2"] // array (optional)
}
```

**Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 10,
    "title": "Senior Developer",
    ...
  },
  "message": "Job created successfully"
}
```

---

### 2.4 Update Job (Company Only)
**PUT** `/jobs/{id}`

**Protected** 🔒 **Role:** Company

**Request Body:** (Same as Create Job)

**Response (200):**
```json
{
  "success": true,
  "data": { ... },
  "message": "Job updated successfully"
}
```

---

### 2.5 Delete Job (Company Only)
**DELETE** `/jobs/{id}`

**Protected** 🔒 **Role:** Company

**Response (200):**
```json
{
  "success": true,
  "message": "Job deleted successfully"
}
```

---

### 2.6 My Jobs (Company Only)
**GET** `/jobs/my-jobs`

**Protected** 🔒 **Role:** Company

**Query Parameters:**
- `page` (integer, optional)
- `search` (string, optional)

**Response (200):**
```json
{
  "success": true,
  "data": {
    "data": [ ... ],
    "current_page": 1,
    "last_page": 3,
    "total": 45
  }
}
```

---

### 2.7 Dashboard Stats (Company Only)
**GET** `/jobs/dashboard-stats`

**Protected** 🔒 **Role:** Company

**Response (200):**
```json
{
  "success": true,
  "data": {
    "total_jobs": 10,
    "active_jobs": 8,
    "total_applications": 150,
    "pending_applications": 50,
    "accepted_applications": 30,
    "rejected_applications": 20
  }
}
```

---

## 3. Applications Endpoints

### 3.1 Apply to Job (Job Seeker Only)
**POST** `/applications/apply/{jobId}`

**Protected** 🔒 **Role:** Job Seeker

**Response (201):**
```json
{
  "success": true,
  "data": {
    "id": 100,
    "job_id": 5,
    "job_seeker_id": 10,
    "status": "pending",
    "matching_percentage": 85,
    "created_at": "2024-01-01T00:00:00.000000Z"
  },
  "message": "Application submitted successfully"
}
```

**Error (400):**
```json
{
  "success": false,
  "message": "You have already applied to this job"
}
```

---

### 3.2 My Applications (Job Seeker Only)
**GET** `/applications/my-applications`

**Protected** 🔒 **Role:** Job Seeker

**Query Parameters:**
- `page` (integer, optional)
- `status` (string, optional) - pending|accepted|rejected

**Response (200):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 100,
        "job": {
          "id": 5,
          "title": "Senior Developer",
          "company": {
            "name": "Tech Company"
          }
        },
        "status": "pending",
        "matching_percentage": 85,
        "applied_at": "2024-01-01T00:00:00.000000Z"
      }
    ],
    "current_page": 1,
    "total": 10
  }
}
```

---

### 3.3 Withdraw Application (Job Seeker Only)
**DELETE** `/applications/{applicationId}/withdraw`

**Protected** 🔒 **Role:** Job Seeker

**Response (200):**
```json
{
  "success": true,
  "message": "Application withdrawn successfully"
}
```

---

### 3.4 Job Applications (Company Only)
**GET** `/jobs/{jobId}/applications`

**Protected** 🔒 **Role:** Company

**Query Parameters:**
- `status` (string, optional) - pending|accepted|rejected
- `min_matching` (integer, optional) - Minimum matching percentage
- `speciality` (string, optional)
- `province` (string, optional)
- `sort_by` (string, optional, default: "matching_percentage")
- `sort_order` (string, optional, default: "desc")

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "id": 100,
      "job_seeker": {
        "id": 10,
        "name": "John Doe",
        "email": "john@example.com",
        "phone": "1234567890",
        "province": "Baghdad",
        "speciality": "IT"
      },
      "status": "pending",
      "matching_percentage": 85,
      "applied_at": "2024-01-01T00:00:00.000000Z"
    }
  ]
}
```

---

### 3.5 Update Application Status (Company Only)
**PUT** `/applications/{applicationId}/status`

**Protected** 🔒 **Role:** Company

**Request Body:**
```json
{
  "status": "accepted|rejected (required)"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 100,
    "status": "accepted",
    ...
  },
  "message": "Application status updated successfully"
}
```

---

## 4. Profile Endpoints

### 4.1 Get Profile
**GET** `/profile/`

**Protected** 🔒

**Response (200):**
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "phone": "1234567890",
    "role": "jobseeker",
    "province": "Baghdad",
    "profile_image": "https://example.com/storage/profile-images/image.jpg",
    // Job Seeker specific fields:
    "speciality": "IT",
    "education": "Bachelor's Degree",
    "experience": "5 years experience...",
    "skills": "PHP, Laravel, Flutter...",
    "languages": "Arabic, English",
    "summary": "Professional summary...",
    "districts": ["district1", "district2"],
    "has_car": true,
    "cv_file": "https://example.com/storage/cvs/cv.pdf"
  }
}
```

---

### 4.2 Update Profile (JSON)
**PUT** `/profile/`

**Protected** 🔒

**Content-Type:** `application/json`

**Request Body:**
```json
{
  "name": "string (optional)",
  "phone": "string (optional)",
  "province": "string (optional)",
  // Job Seeker fields:
  "speciality": "string (optional)",
  "education": "string (optional)",
  "experience": "string (optional)",
  "skills": "string (optional)",
  "languages": "string (optional)",
  "summary": "string (optional)",
  "districts": ["array", "optional"],
  "has_car": "boolean (optional)"
}
```

**Response (200):**
```json
{
  "success": true,
  "data": { ... },
  "message": "Profile updated successfully"
}
```

---

### 4.3 Update Profile (Multipart - with files)
**POST** `/profile/` with `_method=PUT`

**Protected** 🔒

**Content-Type:** `multipart/form-data`

**Form Fields:**
- `_method` = "PUT" (required)
- All fields from JSON update
- `profile_image` (file, optional) - Image file
- `cv_file` (file, optional) - PDF file

**Response (200):**
```json
{
  "success": true,
  "data": {
    "profile_image": "new_image_url",
    "cv_file": "new_cv_url",
    ...
  },
  "message": "Profile updated successfully"
}
```

---

### 4.4 Change Password
**POST** `/profile/change-password`

**Protected** 🔒

**Request Body:**
```json
{
  "current_password": "string (required)",
  "new_password": "string (required, min:8)",
  "new_password_confirmation": "string (required)"
}
```

**Response (200):**
```json
{
  "success": true,
  "message": "Password changed successfully"
}
```

---

### 4.5 Delete Profile Image
**DELETE** `/profile/profile-image`

**Protected** 🔒

**Response (200):**
```json
{
  "success": true,
  "message": "Profile image deleted successfully"
}
```

---

### 4.6 Get Master Settings
**GET** `/master-settings`

**Public** ✅

**Response (200):**
```json
{
  "success": true,
  "data": {
    "provinces": ["Baghdad", "Basra", "Erbil", ...],
    "specialities": ["IT", "Engineering", "Medicine", ...],
    "districts": {
      "Baghdad": ["Karrada", "Mansour", ...],
      "Basra": ["Ashar", ...]
    }
  }
}
```

---

## 5. Favorites Endpoints

### 5.1 List Favorites
**GET** `/favorites/`

**Protected** 🔒

**Query Parameters:**
- `page` (integer, optional)

**Response (200):**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "job": {
          "id": 5,
          "title": "Senior Developer",
          "company": { ... }
        },
        "created_at": "2024-01-01T00:00:00.000000Z"
      }
    ],
    "current_page": 1,
    "total": 10
  }
}
```

---

### 5.2 Add to Favorites
**POST** `/favorites/{jobId}`

**Protected** 🔒

**Response (201):**
```json
{
  "success": true,
  "message": "Job added to favorites"
}
```

---

### 5.3 Remove from Favorites
**DELETE** `/favorites/{jobId}`

**Protected** 🔒

**Response (200):**
```json
{
  "success": true,
  "message": "Job removed from favorites"
}
```

---

### 5.4 Check if Favorite
**GET** `/favorites/check/{jobId}`

**Protected** 🔒

**Response (200):**
```json
{
  "success": true,
  "data": {
    "is_favorite": true
  }
}
```

---

## 6. CV Access Endpoints (Company Only)

### 6.1 Download CV
**GET** `/cv/download/{jobSeekerId}/{jobId?}`

**Protected** 🔒 **Role:** Company

**Response:** PDF file download

---

### 6.2 View CV
**GET** `/cv/view/{jobSeekerId}/{jobId?}`

**Protected** 🔒 **Role:** Company

**Response:** PDF file inline view

---

### 6.3 Get CV Access Logs (Job Seeker Only)
**GET** `/cv/access-logs`

**Protected** 🔒 **Role:** Job Seeker

**Response (200):**
```json
{
  "success": true,
  "data": [
    {
      "company": {
        "name": "Tech Company"
      },
      "accessed_at": "2024-01-01T00:00:00.000000Z",
      "action": "download"
    }
  ]
}
```

---

## 7. Health Check

### 7.1 API Health
**GET** `/health`

**Public** ✅

**Response (200):**
```json
{
  "success": true,
  "message": "API is working",
  "timestamp": "2024-01-01T00:00:00.000000Z",
  "version": "1.0.0"
}
```

---

## Error Codes

| Code | Description |
|------|-------------|
| 200 | Success |
| 201 | Created |
| 400 | Bad Request |
| 401 | Unauthorized (Invalid/Missing token) |
| 403 | Forbidden (Insufficient permissions) |
| 404 | Not Found |
| 422 | Validation Error |
| 500 | Server Error |

---

## Flutter Service Classes

### Available Services:
1. **AuthService** - `lib/services/auth_service.dart`
2. **JobsService** - `lib/services/jobs_service.dart`
3. **ApplicationsService** - `lib/services/applications_service.dart`
4. **ProfileService** - `lib/services/profile_service.dart`
5. **FavoritesService** - `lib/services/favorites_service.dart`
6. **CompanyService** - `lib/services/company_service.dart`

### Example Usage:

```dart
// Initialize service
final authService = AuthService();

// Login
final result = await authService.login(
  email: 'user@example.com',
  password: 'password123',
);

if (result['success'] == true) {
  final token = result['data']['token'];
  final user = result['data']['user'];
  // Save token and proceed
}

// List jobs
final jobsService = JobsService();
final jobs = await jobsService.listJobs(
  token: token,
  search: 'developer',
  province: 'Baghdad',
  page: 1,
);

// Apply to job
final applicationsService = ApplicationsService();
final application = await applicationsService.applyToJob(
  token: token,
  jobId: 5,
);
```

---

## Notes for Developers

1. **Token Storage**: Store the JWT token securely using `flutter_secure_storage` or `hive`
2. **Token Refresh**: Implement automatic token refresh when receiving 401 errors
3. **Offline Support**: Jobs are cached locally using `JobsCache` for offline access
4. **File Uploads**: Use `ProfileService.updateProfileMultipart()` for uploading images/CVs with progress tracking
5. **FCM Tokens**: Register FCM token after successful login for push notifications
6. **Role-Based UI**: Check user role (`jobseeker` or `company`) to show appropriate screens
7. **Error Handling**: Always check `success` field in responses before accessing `data`

---

**Last Updated:** 2024-11-15
**API Version:** 1.0.0
**Base URL:** https://www.connect-job.com/api/v1/


