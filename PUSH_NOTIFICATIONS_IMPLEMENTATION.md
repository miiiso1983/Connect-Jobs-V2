# Push Notifications Implementation - Connect Jobs

## Overview

This document describes the complete push notification system implemented for the Connect Jobs application using Firebase Cloud Messaging (FCM). The system supports three main notification scenarios in Arabic language.

## 🚀 Features Implemented

### 1. **Admin Notification - New Company Registration**
- **Trigger**: When a new company registers on the platform
- **Recipients**: All active admin users with FCM tokens
- **Content**: Company name, registration date/time in Arabic
- **Implementation**: `AuthController@register` method

### 2. **Company Notification - New Job Application**
- **Trigger**: When a job seeker applies to a job posting
- **Recipients**: Company that owns the job posting
- **Content**: Job seeker name, job title, application date/time in Arabic
- **Implementation**: `ApplicationController@apply` method

### 3. **Job Seeker Notification - CV Downloaded**
- **Trigger**: When a company downloads/views a job seeker's CV
- **Recipients**: Job seeker whose CV was accessed
- **Content**: Company name, access date/time in Arabic
- **Implementation**: `CvController@download` and `CvController@view` methods

## 📁 Files Created/Modified

### Backend Files

#### New Files Created:
- `app/Services/FcmNotificationService.php` - Core FCM notification service
- `app/Services/NotificationHelperService.php` - Helper service for specific notification scenarios
- `app/Models/PushNotification.php` - Model for notification history
- `app/Models/CvAccessLog.php` - Model for CV access tracking
- `app/Http/Controllers/Api/CvController.php` - CV download/view with tracking
- `app/Http/Controllers/Api/NotificationTestController.php` - Testing endpoints
- `database/migrations/2025_10_09_000002_create_push_notifications_table.php`
- `database/migrations/2025_10_09_000003_create_cv_access_logs_table.php`

#### Modified Files:
- `app/Http/Controllers/Api/AuthController.php` - Added admin notification trigger
- `app/Http/Controllers/Api/ApplicationController.php` - Added company notification trigger
- `config/services.php` - Added Firebase configuration
- `routes/api.php` - Added CV and notification test routes

### Dependencies Added:
- `kreait/firebase-php` (v6.9.6) - Firebase PHP SDK

## 🔧 Configuration Required

### 1. Firebase Setup
Add these environment variables to your `.env` file:

```env
FIREBASE_PROJECT_ID=your-project-id
FIREBASE_CREDENTIALS_PATH=/path/to/firebase-service-account.json
FIREBASE_DATABASE_URL=https://your-project.firebaseio.com
```

### 2. Service Account JSON
Download the Firebase service account JSON file and place it in your project. Update the `FIREBASE_CREDENTIALS_PATH` accordingly.

## 📊 Database Schema

### Push Notifications Table
```sql
- id (bigint, primary key)
- user_id (foreign key to users)
- type (string: admin_new_company, company_new_application, jobseeker_cv_downloaded)
- title (string, 255 chars)
- body (text)
- data (json, additional payload)
- fcm_tokens (json, tokens sent to)
- status (enum: pending, sent, failed)
- error_message (text, nullable)
- sent_at (timestamp, nullable)
- created_at, updated_at
```

### CV Access Logs Table
```sql
- id (bigint, primary key)
- job_seeker_id (foreign key)
- company_id (foreign key)
- job_id (foreign key, nullable)
- access_type (enum: download, view)
- ip_address (string, 45 chars)
- user_agent (text)
- created_at, updated_at
```

## 🛠 API Endpoints

### CV Access Endpoints
```
GET /api/v1/cv/download/{jobSeekerId}/{jobId?} - Download CV (Company only)
GET /api/v1/cv/view/{jobSeekerId}/{jobId?} - View CV URL (Company only)
GET /api/v1/cv/access-logs - Get CV access logs (Job Seeker only)
```

### Testing Endpoints
```
GET /api/v1/notifications/test/connection - Test Firebase connection
POST /api/v1/notifications/test/send-test - Send test notification
POST /api/v1/notifications/test/admin-notification - Test admin notification
POST /api/v1/notifications/test/company-notification - Test company notification
POST /api/v1/notifications/test/jobseeker-notification - Test job seeker notification
GET /api/v1/notifications/test/stats - Get notification statistics
GET /api/v1/notifications/test/fcm-tokens - Get user's FCM tokens
GET /api/v1/notifications/test/recent - Get recent notifications
DELETE /api/v1/notifications/test/clear - Clear user's notifications
```

## 🧪 Testing Guide

### 1. Test Firebase Connection
```bash
curl -X GET "https://your-domain.com/api/v1/notifications/test/connection" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### 2. Test User's FCM Tokens
```bash
curl -X GET "https://your-domain.com/api/v1/notifications/test/fcm-tokens" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### 3. Send Test Notification
```bash
curl -X POST "https://your-domain.com/api/v1/notifications/test/send-test" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

### 4. Test Specific Scenarios
```bash
# Test admin notification
curl -X POST "https://your-domain.com/api/v1/notifications/test/admin-notification" \
  -H "Authorization: Bearer ADMIN_JWT_TOKEN"

# Test company notification
curl -X POST "https://your-domain.com/api/v1/notifications/test/company-notification" \
  -H "Authorization: Bearer COMPANY_JWT_TOKEN"

# Test job seeker notification
curl -X POST "https://your-domain.com/api/v1/notifications/test/jobseeker-notification" \
  -H "Authorization: Bearer ANY_JWT_TOKEN"
```

## 🔍 Monitoring & Analytics

### Notification Statistics
Access comprehensive statistics via:
```bash
curl -X GET "https://your-domain.com/api/v1/notifications/test/stats" \
  -H "Authorization: Bearer YOUR_JWT_TOKEN"
```

Returns:
- Total notifications sent
- Success/failure rates
- Notifications by type
- Recent notification history

### CV Access Tracking
Job seekers can view who accessed their CV:
```bash
curl -X GET "https://your-domain.com/api/v1/cv/access-logs" \
  -H "Authorization: Bearer JOBSEEKER_JWT_TOKEN"
```

## 🚨 Error Handling

The system includes comprehensive error handling:

1. **Invalid FCM Tokens**: Automatically deactivated
2. **Network Failures**: Logged but don't block main operations
3. **Missing Firebase Config**: Graceful degradation
4. **User Without FCM Tokens**: Logged for monitoring

## 🔒 Security Features

1. **Role-based Access**: Endpoints restricted by user roles
2. **Token Validation**: FCM tokens validated before sending
3. **Access Logging**: All CV access tracked with IP and user agent
4. **Error Logging**: Comprehensive logging for debugging

## 📱 Mobile App Integration

The notification system works with the existing FCM token management:
- Tokens automatically registered on login
- Tokens unregistered on logout
- Multiple device support per user
- Automatic token refresh handling

## 🎯 Next Steps

1. **Firebase Configuration**: Set up Firebase project and service account
2. **Testing**: Use the test endpoints to verify functionality
3. **Monitoring**: Monitor notification delivery rates
4. **Optimization**: Fine-tune notification content and timing

## 📞 Support

For issues or questions about the notification system:
1. Check the logs in `storage/logs/laravel.log`
2. Use the test endpoints to diagnose issues
3. Verify Firebase configuration and credentials
4. Check FCM token registration in the mobile app
