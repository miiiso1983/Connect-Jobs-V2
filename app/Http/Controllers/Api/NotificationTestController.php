<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\JobSeeker;
use App\Models\Job;
use App\Models\Application;
use App\Models\PushNotification;
use App\Services\FcmNotificationService;
use App\Services\NotificationHelperService;
use Illuminate\Http\Request;

class NotificationTestController extends Controller
{
    private FcmNotificationService $fcmService;
    private NotificationHelperService $notificationHelper;

    public function __construct(FcmNotificationService $fcmService, NotificationHelperService $notificationHelper)
    {
        $this->fcmService = $fcmService;
        $this->notificationHelper = $notificationHelper;
    }

    /**
     * Test Firebase connection
     */
    public function testConnection()
    {
        $isConnected = $this->fcmService->testConnection();
        
        return response()->json([
            'success' => $isConnected,
            'message' => $isConnected ? 'Firebase connection successful' : 'Firebase connection failed',
            'firebase_configured' => $isConnected,
        ]);
    }

    /**
     * Send test notification to current user
     */
    public function sendTestNotification(Request $request)
    {
        $user = auth()->user();
        
        $result = $this->notificationHelper->sendTestNotification($user);
        
        return response()->json([
            'success' => $result,
            'message' => $result ? 'Test notification sent successfully' : 'Failed to send test notification',
            'user_id' => $user->id,
            'user_role' => $user->role,
        ]);
    }

    /**
     * Test admin notification for new company registration
     */
    public function testAdminNotification(Request $request)
    {
        // Find a company to use for testing
        $company = Company::with('user')->first();
        
        if (!$company) {
            return response()->json([
                'success' => false,
                'message' => 'No company found for testing'
            ], 404);
        }

        $result = $this->notificationHelper->notifyAdminsNewCompanyRegistration($company->user, $company);
        
        return response()->json([
            'success' => $result,
            'message' => $result ? 'Admin notification sent successfully' : 'Failed to send admin notification',
            'company_name' => $company->company_name,
            'test_type' => 'admin_new_company',
        ]);
    }

    /**
     * Test company notification for new job application
     */
    public function testCompanyNotification(Request $request)
    {
        // Find an application to use for testing
        $application = Application::with(['job.company', 'jobSeeker.user'])->first();
        
        if (!$application) {
            return response()->json([
                'success' => false,
                'message' => 'No application found for testing'
            ], 404);
        }

        $result = $this->notificationHelper->notifyCompanyNewJobApplication($application);
        
        return response()->json([
            'success' => $result,
            'message' => $result ? 'Company notification sent successfully' : 'Failed to send company notification',
            'job_title' => $application->job->title,
            'jobseeker_name' => $application->jobSeeker->full_name,
            'test_type' => 'company_new_application',
        ]);
    }

    /**
     * Test job seeker notification for CV download
     */
    public function testJobSeekerNotification(Request $request)
    {
        // Find a job seeker and company to use for testing
        $jobSeeker = JobSeeker::with('user')->first();
        $company = Company::first();
        
        if (!$jobSeeker || !$company) {
            return response()->json([
                'success' => false,
                'message' => 'Job seeker or company not found for testing'
            ], 404);
        }

        $result = $this->notificationHelper->notifyJobSeekerCvDownloaded($jobSeeker, $company);
        
        return response()->json([
            'success' => $result,
            'message' => $result ? 'Job seeker notification sent successfully' : 'Failed to send job seeker notification',
            'jobseeker_name' => $jobSeeker->full_name,
            'company_name' => $company->company_name,
            'test_type' => 'jobseeker_cv_downloaded',
        ]);
    }

    /**
     * Get notification statistics
     */
    public function getStats()
    {
        $stats = $this->notificationHelper->getNotificationStats();
        
        return response()->json([
            'success' => true,
            'data' => $stats,
            'message' => 'Notification statistics retrieved successfully'
        ]);
    }

    /**
     * Get FCM token information for current user
     */
    public function getFcmTokenInfo()
    {
        $user = auth()->user();
        $fcmTokens = $user->activeFcmTokens()->get();
        
        return response()->json([
            'success' => true,
            'data' => [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'active_fcm_tokens' => $fcmTokens->count(),
                'tokens' => $fcmTokens->map(function ($token) {
                    return [
                        'id' => $token->id,
                        'device_type' => $token->device_type,
                        'device_id' => $token->device_id,
                        'last_used_at' => $token->last_used_at,
                        'created_at' => $token->created_at,
                    ];
                }),
            ],
            'message' => 'FCM token information retrieved successfully'
        ]);
    }

    /**
     * Get recent notifications for current user
     */
    public function getRecentNotifications()
    {
        $user = auth()->user();
        $notifications = PushNotification::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return response()->json([
            'success' => true,
            'data' => $notifications->map(function ($notification) {
                return [
                    'id' => $notification->id,
                    'type' => $notification->type,
                    'type_name' => $notification->type_name,
                    'title' => $notification->title,
                    'body' => $notification->body,
                    'status' => $notification->status,
                    'sent_at' => $notification->sent_at,
                    'created_at' => $notification->created_at,
                    'data' => $notification->data,
                ];
            }),
            'message' => 'Recent notifications retrieved successfully'
        ]);
    }

    /**
     * Clear all notifications for current user (for testing)
     */
    public function clearNotifications()
    {
        $user = auth()->user();
        $deleted = PushNotification::where('user_id', $user->id)->delete();
        
        return response()->json([
            'success' => true,
            'message' => "Cleared {$deleted} notifications for user {$user->id}",
            'deleted_count' => $deleted,
        ]);
    }
}
