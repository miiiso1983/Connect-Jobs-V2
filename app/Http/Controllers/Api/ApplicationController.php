<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Job;
use App\Models\JobSeeker;
use App\Services\NotificationHelperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class ApplicationController extends Controller
{
    /**
     * Apply for a job (Job Seeker only)
     */
    public function apply(Request $request, $jobId)
    {
        if (auth()->user()->role !== 'jobseeker') {
            return response()->json([
                'success' => false,
                'message' => 'Only job seekers can apply for jobs'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // 5MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $jobSeeker = auth()->user()->jobSeeker;
            
            if (!$jobSeeker) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job seeker profile not found'
                ], 404);
            }

            $job = Job::where('status', 'open')
                ->where('approved_by_admin', true)
                ->findOrFail($jobId);

            // Check if already applied
            $existingApplication = Application::where('job_id', $job->id)
                ->where('job_seeker_id', $jobSeeker->id)
                ->first();

            if ($existingApplication) {
                return response()->json([
                    'success' => false,
                    'message' => 'You have already applied for this job'
                ], 409);
            }

            $applicationData = [
                'job_id' => $job->id,
                'job_seeker_id' => $jobSeeker->id,
                'applied_at' => now(),
                'status' => 'pending',
                'matching_percentage' => $this->calculateMatchingPercentage($job, $jobSeeker),
            ];

            // Handle CV file upload
            if ($request->hasFile('cv_file')) {
                $file = $request->file('cv_file');
                $filename = time() . '_' . $jobSeeker->id . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('applications', $filename, 'public');
                $applicationData['cv_file'] = $path;
            } elseif ($jobSeeker->cv_file) {
                // Use existing CV from profile
                $applicationData['cv_file'] = $jobSeeker->cv_file;
            }

            $application = Application::create($applicationData);

            // Send notification to company about new job application
            try {
                $notificationService = app(NotificationHelperService::class);
                $application->load(['job.company', 'jobSeeker.user']);
                $notificationService->notifyCompanyNewJobApplication($application);
            } catch (\Exception $e) {
                // Log error but don't fail application submission
                \Log::error('Failed to send company notification for new job application: ' . $e->getMessage());
            }

            return response()->json([
                'success' => true,
                'data' => $application->load(['job.company.user', 'jobSeeker.user']),
                'message' => 'Application submitted successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to submit application',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get job seeker's applications
     */
    public function myApplications(Request $request)
    {
        if (auth()->user()->role !== 'jobseeker') {
            return response()->json([
                'success' => false,
                'message' => 'Only job seekers can access this endpoint'
            ], 403);
        }

        try {
            $jobSeeker = auth()->user()->jobSeeker;
            
            $query = Application::where('job_seeker_id', $jobSeeker->id)
                ->with(['job.company.user']);

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('date_from')) {
                $query->whereDate('applied_at', '>=', $request->date_from);
            }

            if ($request->filled('date_to')) {
                $query->whereDate('applied_at', '<=', $request->date_to);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'applied_at');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = min($request->get('per_page', 15), 50);
            $applications = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $applications,
                'message' => 'Applications retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve applications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get applications for company's jobs
     */
    public function jobApplications(Request $request, $jobId)
    {
        if (auth()->user()->role !== 'company') {
            return response()->json([
                'success' => false,
                'message' => 'Only companies can access this endpoint'
            ], 403);
        }

        try {
            $company = auth()->user()->company;
            
            // Verify job belongs to company
            $job = Job::where('company_id', $company->id)->findOrFail($jobId);

            $query = Application::where('job_id', $job->id)
                ->with(['jobSeeker.user']);

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('min_matching')) {
                $query->where('matching_percentage', '>=', $request->min_matching);
            }

            if ($request->filled('speciality')) {
                $query->whereHas('jobSeeker', function($q) use ($request) {
                    $q->where('speciality', $request->speciality);
                });
            }

            if ($request->filled('province')) {
                $query->whereHas('jobSeeker', function($q) use ($request) {
                    $q->where('province', $request->province);
                });
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'matching_percentage');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = min($request->get('per_page', 15), 50);
            $applications = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => [
                    'job' => $job,
                    'applications' => $applications
                ],
                'message' => 'Applications retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve applications',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update application status (Company only)
     */
    public function updateStatus(Request $request, $applicationId)
    {
        if (auth()->user()->role !== 'company') {
            return response()->json([
                'success' => false,
                'message' => 'Only companies can update application status'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:pending,reviewed,shortlisted,rejected,hired',
            'notes' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $company = auth()->user()->company;
            
            $application = Application::whereHas('job', function($query) use ($company) {
                $query->where('company_id', $company->id);
            })->findOrFail($applicationId);

            $application->update([
                'status' => $request->status,
                'notes' => $request->notes,
                'reviewed_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'data' => $application->load(['job.company.user', 'jobSeeker.user']),
                'message' => 'Application status updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update application status',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Withdraw application (Job Seeker only)
     */
    public function withdraw($applicationId)
    {
        if (auth()->user()->role !== 'jobseeker') {
            return response()->json([
                'success' => false,
                'message' => 'Only job seekers can withdraw applications'
            ], 403);
        }

        try {
            $jobSeeker = auth()->user()->jobSeeker;
            
            $application = Application::where('job_seeker_id', $jobSeeker->id)
                ->findOrFail($applicationId);

            // Can only withdraw pending applications
            if ($application->status !== 'pending') {
                return response()->json([
                    'success' => false,
                    'message' => 'Can only withdraw pending applications'
                ], 400);
            }

            $application->delete();

            return response()->json([
                'success' => true,
                'message' => 'Application withdrawn successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to withdraw application',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Calculate matching percentage between job and job seeker
     */
    private function calculateMatchingPercentage(Job $job, JobSeeker $jobSeeker)
    {
        $score = 0;
        $totalCriteria = 0;

        // Province match (20%)
        $totalCriteria += 20;
        if ($job->province === $jobSeeker->province) {
            $score += 20;
        }

        // Speciality match (30%)
        $totalCriteria += 30;
        if ($job->speciality === $jobSeeker->speciality) {
            $score += 30;
        }

        // Districts match (15%)
        $totalCriteria += 15;
        if ($job->districts && $jobSeeker->districts) {
            $jobDistricts = is_array($job->districts) ? $job->districts : json_decode($job->districts, true);
            $seekerDistricts = is_array($jobSeeker->districts) ? $jobSeeker->districts : json_decode($jobSeeker->districts, true);
            
            if ($jobDistricts && $seekerDistricts) {
                $commonDistricts = array_intersect($jobDistricts, $seekerDistricts);
                if (count($commonDistricts) > 0) {
                    $score += 15;
                }
            }
        }

        // Specialities match (25%)
        $totalCriteria += 25;
        if ($job->specialities && $jobSeeker->specialities) {
            $jobSpecialities = is_array($job->specialities) ? $job->specialities : json_decode($job->specialities, true);
            $seekerSpecialities = is_array($jobSeeker->specialities) ? $jobSeeker->specialities : json_decode($jobSeeker->specialities, true);
            
            if ($jobSpecialities && $seekerSpecialities) {
                $commonSpecialities = array_intersect($jobSpecialities, $seekerSpecialities);
                if (count($commonSpecialities) > 0) {
                    $matchPercentage = (count($commonSpecialities) / count($jobSpecialities)) * 25;
                    $score += min($matchPercentage, 25);
                }
            }
        }

        // Profile completion bonus (10%)
        $totalCriteria += 10;
        if ($jobSeeker->profile_completed) {
            $score += 10;
        }

        return min(round(($score / $totalCriteria) * 100), 100);
    }
}
