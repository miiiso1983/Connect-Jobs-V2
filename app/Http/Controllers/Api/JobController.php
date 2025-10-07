<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Job;
use App\Models\Application;
use App\Models\MasterSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class JobController extends Controller
{
    /**
     * Get all jobs with filters
     */
    public function index(Request $request)
    {
        try {
            $query = Job::with(['company.user'])
                ->where('status', 'open')
                ->where('approved_by_admin', true);

            // Apply filters
            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")
                      ->orWhere('description', 'like', "%{$search}%")
                      ->orWhere('requirements', 'like', "%{$search}%");
                });
            }

            if ($request->filled('province')) {
                $query->where('province', $request->province);
            }

            if ($request->filled('speciality')) {
                $query->where('speciality', $request->speciality);
            }

            if ($request->filled('districts') && is_array($request->districts)) {
                $query->whereJsonContains('districts', $request->districts);
            }

            if ($request->filled('specialities') && is_array($request->specialities)) {
                $query->whereJsonContains('specialities', $request->specialities);
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'id');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = min($request->get('per_page', 15), 50); // Max 50 items per page
            $jobs = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $jobs,
                'message' => 'Jobs retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve jobs',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get single job details
     */
    public function show($id)
    {
        try {
            $job = Job::with(['company.user', 'applications.jobSeeker.user'])
                ->where('status', 'open')
                ->where('approved_by_admin', true)
                ->findOrFail($id);

            // Check if current user has applied (if authenticated)
            $hasApplied = false;
            if (auth()->check() && auth()->user()->role === 'jobseeker') {
                $jobSeeker = auth()->user()->jobSeeker;
                if ($jobSeeker) {
                    $hasApplied = Application::where('job_id', $job->id)
                        ->where('job_seeker_id', $jobSeeker->id)
                        ->exists();
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'job' => $job,
                    'has_applied' => $hasApplied
                ],
                'message' => 'Job retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Job not found',
                'error' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create new job (Company only)
     */
    public function store(Request $request)
    {
        if (auth()->user()->role !== 'company') {
            return response()->json([
                'success' => false,
                'message' => 'Only companies can create jobs'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'speciality' => 'required|string|max:255',
            'description' => 'required|string',
            'requirements' => 'required|string',
            'province' => 'required|string|max:255',
            'districts' => 'nullable|array',
            'specialities' => 'nullable|array',
            'jd_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120', // 5MB max
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
            
            if (!$company) {
                return response()->json([
                    'success' => false,
                    'message' => 'Company profile not found'
                ], 404);
            }

            $jobData = [
                'company_id' => $company->id,
                'title' => $request->title,
                'speciality' => $request->speciality,
                'description' => $request->description,
                'requirements' => $request->requirements,
                'province' => $request->province,
                'districts' => $request->districts ?? [],
                'specialities' => $request->specialities ?? [],
                'status' => 'active',
                'approved_by_admin' => false, // Requires admin approval
            ];

            // Handle file upload
            if ($request->hasFile('jd_file')) {
                $file = $request->file('jd_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('job_descriptions', $filename, 'public');
                $jobData['jd_file'] = $path;
            }

            $job = Job::create($jobData);

            return response()->json([
                'success' => true,
                'data' => $job->load('company.user'),
                'message' => 'Job created successfully. Waiting for admin approval.'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create job',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update job (Company only - own jobs)
     */
    public function update(Request $request, $id)
    {
        if (auth()->user()->role !== 'company') {
            return response()->json([
                'success' => false,
                'message' => 'Only companies can update jobs'
            ], 403);
        }

        try {
            $company = auth()->user()->company;
            $job = Job::where('company_id', $company->id)->findOrFail($id);

            $validator = Validator::make($request->all(), [
                'title' => 'sometimes|required|string|max:255',
                'speciality' => 'sometimes|required|string|max:255',
                'description' => 'sometimes|required|string',
                'requirements' => 'sometimes|required|string',
                'province' => 'sometimes|required|string|max:255',
                'districts' => 'nullable|array',
                'specialities' => 'nullable|array',
                'status' => 'sometimes|in:active,inactive',
                'jd_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation errors',
                    'errors' => $validator->errors()
                ], 422);
            }

            $updateData = $request->only([
                'title', 'speciality', 'description', 'requirements', 
                'province', 'districts', 'specialities', 'status'
            ]);

            // Handle file upload
            if ($request->hasFile('jd_file')) {
                // Delete old file if exists
                if ($job->jd_file) {
                    Storage::disk('public')->delete($job->jd_file);
                }
                
                $file = $request->file('jd_file');
                $filename = time() . '_' . $file->getClientOriginalName();
                $path = $file->storeAs('job_descriptions', $filename, 'public');
                $updateData['jd_file'] = $path;
            }

            // If content is updated, require admin approval again
            if (array_intersect_key($updateData, array_flip(['title', 'description', 'requirements']))) {
                $updateData['approved_by_admin'] = false;
                $updateData['admin_reject_reason'] = null;
            }

            $job->update($updateData);

            return response()->json([
                'success' => true,
                'data' => $job->load('company.user'),
                'message' => 'Job updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update job',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete job (Company only - own jobs)
     */
    public function destroy($id)
    {
        if (auth()->user()->role !== 'company') {
            return response()->json([
                'success' => false,
                'message' => 'Only companies can delete jobs'
            ], 403);
        }

        try {
            $company = auth()->user()->company;
            $job = Job::where('company_id', $company->id)->findOrFail($id);

            // Delete associated file
            if ($job->jd_file) {
                Storage::disk('public')->delete($job->jd_file);
            }

            // Delete applications first
            $job->applications()->delete();
            
            // Delete job
            $job->delete();

            return response()->json([
                'success' => true,
                'message' => 'Job deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete job',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get company's own jobs
     */
    public function myJobs(Request $request)
    {
        if (auth()->user()->role !== 'company') {
            return response()->json([
                'success' => false,
                'message' => 'Only companies can access this endpoint'
            ], 403);
        }

        try {
            $company = auth()->user()->company;
            
            $query = Job::where('company_id', $company->id)
                ->withCount('applications');

            // Apply filters
            if ($request->filled('status')) {
                $query->where('status', $request->status);
            }

            if ($request->filled('approved')) {
                $query->where('approved_by_admin', $request->boolean('approved'));
            }

            // Sorting
            $sortBy = $request->get('sort_by', 'id');
            $sortOrder = $request->get('sort_order', 'desc');
            $query->orderBy($sortBy, $sortOrder);

            // Pagination
            $perPage = min($request->get('per_page', 15), 50);
            $jobs = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $jobs,
                'message' => 'Jobs retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve jobs',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Get company dashboard statistics
     */
    public function dashboardStats()
    {
        if (auth()->user()->role !== 'company') {
            return response()->json([
                'success' => false,
                'message' => 'Only companies can access this endpoint'
            ], 403);
        }

        try {
            $company = auth()->user()->company;

            $stats = [
                'total_jobs' => Job::where('company_id', $company->id)->count(),
                'active_jobs' => Job::where('company_id', $company->id)->where('status', 'open')->count(),
                'pending_approval' => Job::where('company_id', $company->id)->where('approved_by_admin', false)->count(),
                'total_applications' => Application::whereHas('job', function($query) use ($company) {
                    $query->where('company_id', $company->id);
                })->count(),
                'pending_applications' => Application::whereHas('job', function($query) use ($company) {
                    $query->where('company_id', $company->id);
                })->where('status', 'pending')->count(),
                'shortlisted_applications' => Application::whereHas('job', function($query) use ($company) {
                    $query->where('company_id', $company->id);
                })->where('status', 'shortlisted')->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats,
                'message' => 'Dashboard statistics retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
