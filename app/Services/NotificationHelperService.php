<?php

namespace App\Services;

use App\Models\User;
use App\Models\Company;
use App\Models\JobSeeker;
use App\Models\Job;
use App\Models\Application;
use App\Models\PushNotification;
use Carbon\Carbon;

class NotificationHelperService
{
    private FcmNotificationService $fcmService;

    public function __construct(FcmNotificationService $fcmService)
    {
        $this->fcmService = $fcmService;
    }

    /**
     * Send notification to admins when a new company registers
     */
    public function notifyAdminsNewCompanyRegistration(User $companyUser, Company $company): bool
    {
        $companyName = $company->company_name ?? $companyUser->name;
        $registrationTime = Carbon::now()->format('Y/m/d H:i');

        $title = 'تسجيل شركة جديدة';
        $body = "تم تسجيل شركة جديدة: {$companyName} في {$registrationTime}";
        
        $data = [
            'type' => PushNotification::TYPE_ADMIN_NEW_COMPANY,
            'company_id' => (string) $company->id,
            'company_name' => $companyName,
            'user_id' => (string) $companyUser->id,
            'registration_time' => $registrationTime,
        ];

        $results = $this->fcmService->sendToAdmins(
            $title, 
            $body, 
            $data, 
            PushNotification::TYPE_ADMIN_NEW_COMPANY
        );

        // Return true if at least one admin was notified
        return in_array(true, $results, true);
    }

    /**
     * Send notification to company when a job seeker applies to their job
     */
    public function notifyCompanyNewJobApplication(Application $application): bool
    {
        $job = $application->job;
        $jobSeeker = $application->jobSeeker;
        $company = $job->company;
        $companyUser = $company->user;

        if (!$companyUser || !$jobSeeker) {
            return false;
        }

        $jobSeekerName = $jobSeeker->full_name ?? $jobSeeker->user->name ?? 'باحث عن عمل';
        $jobTitle = $job->title;
        $applicationTime = $application->created_at->format('Y/m/d H:i');

        $title = 'طلب توظيف جديد';
        $body = "تقدم {$jobSeekerName} لوظيفة {$jobTitle} في {$applicationTime}";
        
        $data = [
            'type' => PushNotification::TYPE_COMPANY_NEW_APPLICATION,
            'application_id' => (string) $application->id,
            'job_id' => (string) $job->id,
            'job_title' => $jobTitle,
            'jobseeker_id' => (string) $jobSeeker->id,
            'jobseeker_name' => $jobSeekerName,
            'application_time' => $applicationTime,
        ];

        return $this->fcmService->sendToUser(
            $companyUser, 
            $title, 
            $body, 
            $data, 
            PushNotification::TYPE_COMPANY_NEW_APPLICATION
        );
    }

    /**
     * Send notification to job seeker when a company downloads their CV
     */
    public function notifyJobSeekerCvDownloaded(JobSeeker $jobSeeker, Company $company, ?Job $job = null): bool
    {
        $jobSeekerUser = $jobSeeker->user;
        
        if (!$jobSeekerUser) {
            return false;
        }

        $companyName = $company->company_name ?? $company->user->name ?? 'شركة';
        $downloadTime = Carbon::now()->format('Y/m/d H:i');
        
        $title = 'تم تحميل سيرتك الذاتية';
        
        if ($job) {
            $body = "قامت شركة {$companyName} بتحميل سيرتك الذاتية لوظيفة {$job->title} في {$downloadTime}";
        } else {
            $body = "قامت شركة {$companyName} بتحميل سيرتك الذاتية في {$downloadTime}";
        }
        
        $data = [
            'type' => PushNotification::TYPE_JOBSEEKER_CV_DOWNLOADED,
            'company_id' => (string) $company->id,
            'company_name' => $companyName,
            'jobseeker_id' => (string) $jobSeeker->id,
            'download_time' => $downloadTime,
        ];

        if ($job) {
            $data['job_id'] = (string) $job->id;
            $data['job_title'] = $job->title;
        }

        return $this->fcmService->sendToUser(
            $jobSeekerUser, 
            $title, 
            $body, 
            $data, 
            PushNotification::TYPE_JOBSEEKER_CV_DOWNLOADED
        );
    }

    /**
     * Send a test notification to a specific user
     */
    public function sendTestNotification(User $user): bool
    {
        $title = 'إشعار تجريبي';
        $body = 'هذا إشعار تجريبي للتأكد من عمل النظام بشكل صحيح';
        $data = [
            'type' => 'test',
            'timestamp' => Carbon::now()->toISOString(),
        ];

        return $this->fcmService->sendToUser($user, $title, $body, $data, 'test');
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStats(): array
    {
        return [
            'total_notifications' => PushNotification::count(),
            'sent_notifications' => PushNotification::sent()->count(),
            'failed_notifications' => PushNotification::failed()->count(),
            'pending_notifications' => PushNotification::pending()->count(),
            'notifications_by_type' => [
                'admin_new_company' => PushNotification::ofType(PushNotification::TYPE_ADMIN_NEW_COMPANY)->count(),
                'company_new_application' => PushNotification::ofType(PushNotification::TYPE_COMPANY_NEW_APPLICATION)->count(),
                'jobseeker_cv_downloaded' => PushNotification::ofType(PushNotification::TYPE_JOBSEEKER_CV_DOWNLOADED)->count(),
            ],
            'recent_notifications' => PushNotification::with('user')
                ->latest()
                ->limit(10)
                ->get()
                ->map(function ($notification) {
                    return [
                        'id' => $notification->id,
                        'user' => $notification->user->name ?? 'Unknown',
                        'type' => $notification->type_name,
                        'title' => $notification->title,
                        'status' => $notification->status,
                        'created_at' => $notification->created_at->format('Y-m-d H:i:s'),
                    ];
                }),
        ];
    }
}
