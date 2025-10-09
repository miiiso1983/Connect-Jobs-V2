# Store Compliance Guide (Apple App Store + Google Play)

This document summarizes the privacy and data safety disclosures for Connect Job to help you complete store submissions.

Last updated: 2025-10-10

---

## 1) App Name (both stores)
- Display Name: Connect Job

---

## 2) App Store Connect (Apple) 

### 2.1 App Privacy (Data Collection)
Select the following categories as "Collected" and "Linked to the user" (used for app functionality):

- Contact Info
  - Email address (account creation/login, communication)
  - Phone number (company accounts; verification/communication)
- User Content
  - Photos or videos (profile photo)
  - Files and documents (CV/resume)
- Identifiers
  - Device ID/Push Token (for notifications)
- Usage Data (optional/minimal)
  - Product interaction (optional; only basic interactions for app functionality)

Not Collected:
- Precise location, contacts, browsing history, health/fitness, financial info, sensitive info.

Data Use Purposes: App functionality, Account management, Communications. Not used for tracking/advertising.

Data Sharing: Not shared with third-party advertisers; only with service providers as necessary to operate the service.

Data Linked to User: Yes (account and content are associated with user account).

Tracking: No (the app does not use AppTrackingTransparency or cross-app tracking). 

### 2.2 Sensitive Permissions
- Push Notifications: Yes. Reason: to deliver job/application updates.
- Photo Library/Camera: Yes. Reason: upload profile photo/CV image if taken by camera.

### 2.3 Encryption
- All network traffic uses HTTPS/TLS.

### 2.4 Privacy Policy URL
- Provide: https://www.connect-job.com/privacy

---

## 3) Google Play Console (Data Safety)

Answer the Data Safety form as follows.

### 3.1 Data Collected
- Personal info
  - Name (account creation)
  - Email address (account creation, login)
  - Phone number (company accounts)
- Files and docs
  - CV/Resume files (user-provided)
- Photos
  - Profile photo (user-provided)
- App activity (optional/minimal)
  - In-app interactions (basic, for app functionality)
- Device or other IDs
  - Push notification token (FCM)

Not collected:
- Precise location, Contacts, Financial info, Health/Fitness, Messages, Web browsing, Device logs beyond standard OS telemetry.

### 3.2 Data Sharing
- Not shared with third parties for advertising. Shared only with service providers to operate the app (e.g., cloud hosting).

### 3.3 Data Handling
- Data is encrypted in transit: Yes (HTTPS/TLS)
- Data deletion: Users can request account deletion; cached data can be cleared in-app; CV/photo files can be removed via profile.
- Data is required for app functionality: Yes (account, files for applications, notifications).

### 3.4 Children
- Not primarily directed to children; no special child-directed features.

### 3.5 Security
- Server-side: passwords hashed; access controlled; production over HTTPS only.
- Client-side: local caching (Hive) for public job listings only; no sensitive secrets stored in plain text.

### 3.6 Privacy Policy URL
- https://www.connect-job.com/privacy

---

## 4) Store Assets Checklist
- iOS screenshots (iPhone 6.7", 6.5", 5.5")
- Android screenshots (phone + 7"/10" tablet optional)
- App icon (provided)
- Short & full description
- Keywords (iOS) / Tags (Android)

---

## 5) Submission Tips
- Test push notifications on a physical device before submission
- Ensure registration WebView flows (company/job seeker) function and stay authenticated
- Verify splash/icon match brand; confirm display name is "Connect Job"
- For iOS, upload APNs key to Firebase and verify entitlements; distribute via TestFlight first


