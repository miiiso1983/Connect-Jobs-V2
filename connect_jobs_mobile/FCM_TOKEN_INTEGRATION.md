# FCM Token Integration

This document explains how the FCM (Firebase Cloud Messaging) token integration works in the Connect Jobs mobile app.

## Overview

The FCM token integration allows the backend to send push notifications to specific users' devices. When a user logs in, their device's FCM token is automatically registered with the backend and associated with their user account.

## How It Works

### 1. Token Generation and Storage
- When the app starts, Firebase generates an FCM token for the device
- This token is stored locally using Hive for later use
- The token is printed to debug console for development purposes

### 2. Token Registration on Login
- After successful user login, the stored FCM token is automatically sent to the backend
- The backend stores the token in the `user_fcm_tokens` table
- Multiple tokens per user are supported (for multiple devices)
- Token refresh is automatically handled

### 3. Token Unregistration on Logout
- When a user logs out, the FCM token is deactivated on the backend
- This prevents notifications from being sent to logged-out users

## Backend API Endpoints

### Register FCM Token
```
POST /api/v1/auth/register-fcm-token
Authorization: Bearer {token}
Content-Type: application/json

{
  "fcm_token": "string (required, max 500 chars)",
  "device_type": "string (optional: ios|android|web|unknown)",
  "device_id": "string (optional, max 255 chars)"
}
```

### Unregister FCM Token
```
DELETE /api/v1/auth/unregister-fcm-token
Authorization: Bearer {token}
Content-Type: application/json

{
  "fcm_token": "string (required, max 500 chars)"
}
```

### Get User's FCM Tokens
```
GET /api/v1/auth/fcm-tokens
Authorization: Bearer {token}
```

## Database Schema

### user_fcm_tokens Table
- `id`: Primary key
- `user_id`: Foreign key to users table
- `fcm_token`: The FCM token (unique, max 500 chars)
- `device_type`: Device type (ios|android|web|unknown)
- `device_id`: Optional device identifier
- `is_active`: Boolean flag for active tokens
- `last_used_at`: Timestamp of last use
- `created_at`, `updated_at`: Standard timestamps

## Flutter Services

### NotificationService
- `registerFCMToken()`: Register token with backend
- `unregisterFCMToken()`: Unregister token from backend
- `getCurrentFCMToken()`: Get current device FCM token
- `listenForTokenRefresh()`: Handle token refresh events

### AuthService (Enhanced)
- `registerFCMTokenAfterLogin()`: Called after successful login
- `unregisterFCMTokenOnLogout()`: Called before logout

## Usage Examples

### Sending Notifications from Backend
```php
use App\Models\User;
use App\Models\UserFcmToken;

// Get all active FCM tokens for a user
$user = User::find(1);
$tokens = $user->activeFcmTokens()->pluck('fcm_token');

// Send notification to all user's devices
foreach ($tokens as $token) {
    // Use your preferred FCM library to send notification
    // Example: firebase/php-jwt or kreait/firebase-php
}
```

### Manual Token Registration (if needed)
```dart
final notificationService = NotificationService();
final result = await notificationService.registerFCMToken(
  authToken: 'user_jwt_token',
  fcmToken: 'device_fcm_token',
  deviceType: 'android', // or 'ios'
);

if (result['success']) {
  print('Token registered successfully');
} else {
  print('Failed to register token: ${result['message']}');
}
```

## Error Handling

- FCM token operations are designed to fail gracefully
- Login/logout flows are not blocked if FCM operations fail
- Errors are logged to debug console for troubleshooting
- Network errors are handled with appropriate fallbacks

## Security Considerations

- FCM tokens are only accessible to authenticated users
- Tokens are automatically deactivated on logout
- Each token is associated with a specific user account
- Expired or invalid tokens are handled gracefully

## Testing

To test the FCM token integration:

1. Run the app and check debug console for FCM token
2. Login with a user account
3. Check the `user_fcm_tokens` table for the registered token
4. Logout and verify the token is deactivated
5. Test sending notifications using the registered tokens

## Troubleshooting

### Common Issues
1. **Token not registered**: Check network connectivity and authentication
2. **Multiple tokens per device**: This is normal for development/testing
3. **Token refresh**: Handled automatically by the service
4. **Notifications not received**: Verify token is active and valid

### Debug Steps
1. Check debug console for FCM token generation
2. Verify API endpoints are accessible
3. Check database for token records
4. Test with Firebase Console for direct token testing
