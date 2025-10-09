<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\JobSeeker;
use App\Models\Company;
use App\Models\Job;
use App\Models\CvAccessLog;
use App\Services\NotificationHelperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class CvController extends Controller
{
    /**
     * Download CV file with access tracking
     */
    public function download(Request $request, $jobSeekerId, $jobId = null)
    {
        // Ensure user is a company
        if (auth()->user()->role !== 'company') {
            return response()->json([
                'success' => false,
                'message' => 'Only companies can download CVs'
            ], 403);
        }

        try {
            $company = auth()->user()->company;
            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company profile not found'
                ], 404);
            }

            $jobSeeker = JobSeeker::findOrFail($jobSeekerId);
            
            // Get CV file path
            $cvFile = null;
            
            // If job ID is provided, try to get CV from application first
            if ($jobId) {
                $job = Job::findOrFail($jobId);
                
                // Verify the job belongs to this company
                if ($job->company_id !== $company->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only download CVs for your own job postings'
                    ], 403);
                }
                
                // Get application CV if exists
                $application = $jobSeeker->applications()
                    ->where('job_id', $jobId)
                    ->first();
                
                if ($application && $application->cv_file) {
                    $cvFile = $application->cv_file;
                }
            }
            
            // Fallback to job seeker's profile CV
            if (!$cvFile && $jobSeeker->cv_file) {
                $cvFile = $jobSeeker->cv_file;
            }
            
            if (!$cvFile) {
                return response()->json([
                    'success' => false,
                    'message' => 'CV file not found'
                ], 404);
            }

            // Check if file exists in storage
            if (!Storage::disk('public')->exists($cvFile)) {
                return response()->json([
                    'success' => false,
                    'message' => 'CV file not found in storage'
                ], 404);
            }

            // Log the CV access
            $this->logCvAccess($jobSeeker, $company, $jobId, CvAccessLog::TYPE_DOWNLOAD, $request);

            // Send notification to job seeker
            try {
                $notificationService = app(NotificationHelperService::class);
                $job = $jobId ? Job::find($jobId) : null;
                $notificationService->notifyJobSeekerCvDownloaded($jobSeeker, $company, $job);
            } catch (\Exception $e) {
                // Log error but don't fail download
                \Log::error('Failed to send CV download notification: ' . $e->getMessage());
            }

            // Get file content and return as download
            $filePath = Storage::disk('public')->path($cvFile);
            $fileName = $jobSeeker->full_name . '_CV_' . now()->format('Y-m-d') . '.' . pathinfo($cvFile, PATHINFO_EXTENSION);

            return Response::download($filePath, $fileName);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to download CV',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * View CV file with access tracking (returns file URL)
     */
    public function view(Request $request, $jobSeekerId, $jobId = null)
    {
        // Ensure user is a company
        if (auth()->user()->role !== 'company') {
            return response()->json([
                'success' => false,
                'message' => 'Only companies can view CVs'
            ], 403);
        }

        try {
            $company = auth()->user()->company;
            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company profile not found'
                ], 404);
            }

            $jobSeeker = JobSeeker::findOrFail($jobSeekerId);
            
            // Get CV file path
            $cvFile = null;
            
            // If job ID is provided, try to get CV from application first
            if ($jobId) {
                $job = Job::findOrFail($jobId);
                
                // Verify the job belongs to this company
                if ($job->company_id !== $company->id) {
                    return response()->json([
                        'success' => false,
                        'message' => 'You can only view CVs for your own job postings'
                    ], 403);
                }
                
                // Get application CV if exists
                $application = $jobSeeker->applications()
                    ->where('job_id', $jobId)
                    ->first();
                
                if ($application && $application->cv_file) {
                    $cvFile = $application->cv_file;
                }
            }
            
            // Fallback to job seeker's profile CV
            if (!$cvFile && $jobSeeker->cv_file) {
                $cvFile = $jobSeeker->cv_file;
            }
            
            if (!$cvFile) {
                return response()->json([
                    'success' => false,
                    'message' => 'CV file not found'
                ], 404);
            }

            // Check if file exists in storage
            if (!Storage::disk('public')->exists($cvFile)) {
                return response()->json([
                    'success' => false,
                    'message' => 'CV file not found in storage'
                ], 404);
            }

            // Log the CV access
            $this->logCvAccess($jobSeeker, $company, $jobId, CvAccessLog::TYPE_VIEW, $request);

            // Send notification to job seeker
            try {
                $notificationService = app(NotificationHelperService::class);
                $job = $jobId ? Job::find($jobId) : null;
                $notificationService->notifyJobSeekerCvDownloaded($jobSeeker, $company, $job);
            } catch (\Exception $e) {
                // Log error but don't fail view
                \Log::error('Failed to send CV view notification: ' . $e->getMessage());
            }

            // Return file URL
            $fileUrl = Storage::disk('public')->url($cvFile);

            return response()->json([
                'success' => true,
                'data' => [
                    'cv_url' => $fileUrl,
                    'job_seeker' => [
                        'id' => $jobSeeker->id,
                        'full_name' => $jobSeeker->full_name,
                        'job_title' => $jobSeeker->job_title,
                    ],
                    'access_logged' => true,
                ],
                'message' => 'CV accessed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to access CV',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Log CV access
     */
    private function logCvAccess(JobSeeker $jobSeeker, Company $company, $jobId, string $accessType, Request $request): void
    {
        CvAccessLog::create([
            'job_seeker_id' => $jobSeeker->id,
            'company_id' => $company->id,
            'job_id' => $jobId,
            'access_type' => $accessType,
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);
    }

    /**
     * Get CV access logs for a job seeker (for job seekers to see who accessed their CV)
     */
    public function getAccessLogs(Request $request)
    {
        if (auth()->user()->role !== 'jobseeker') {
            return response()->json([
                'success' => false,
                'message' => 'Only job seekers can access this endpoint'
            ], 403);
        }

        try {
            $jobSeeker = auth()->user()->jobSeeker;
            if (!$jobSeeker) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job seeker profile not found'
                ], 404);
            }

            $logs = CvAccessLog::where('job_seeker_id', $jobSeeker->id)
                ->with(['company.user', 'job'])
                ->orderBy('created_at', 'desc')
                ->paginate(15);

            return response()->json([
                'success' => true,
                'data' => $logs,
                'message' => 'CV access logs retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve CV access logs',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
