<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CvVerificationRequest;
use App\Models\JobSeeker;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CvVerificationController extends Controller
{
    public function status(Request $request)
    {
        $user = auth()->user();
        if (! $user || ($user->role ?? null) !== 'jobseeker') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $jobSeeker = JobSeeker::firstWhere('user_id', $user->id);
        if (! $jobSeeker) {
            return response()->json(['success' => false, 'message' => 'Job seeker profile not found'], 404);
        }

        $latest = CvVerificationRequest::where('job_seeker_id', $jobSeeker->id)
            ->orderByDesc('id')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'cv_verified' => (bool) ($jobSeeker->cv_verified ?? false),
                'has_cv' => !empty($jobSeeker->cv_file),
                'latest_request' => $latest,
            ],
        ]);
    }

    public function request(Request $request)
    {
        $user = auth()->user();
        if (! $user || ($user->role ?? null) !== 'jobseeker') {
            return response()->json(['success' => false, 'message' => 'Unauthorized'], 401);
        }

        $jobSeeker = JobSeeker::firstWhere('user_id', $user->id);
        if (! $jobSeeker) {
            return response()->json(['success' => false, 'message' => 'Job seeker profile not found'], 404);
        }

        if ((bool) ($jobSeeker->cv_verified ?? false)) {
            return response()->json(['success' => false, 'message' => 'CV already verified'], 400);
        }

        $title = Str::lower((string) ($jobSeeker->job_title ?? ''));
        $isPharmacist = str_contains($title, 'صيدل') || str_contains($title, 'pharmac');
        if (! $isPharmacist) {
            return response()->json(['success' => false, 'message' => 'CV verification is available for pharmacists only'], 403);
        }

        if (empty($jobSeeker->cv_file)) {
            return response()->json(['success' => false, 'message' => 'Please upload your CV first'], 422);
        }

        $hasPending = CvVerificationRequest::where('job_seeker_id', $jobSeeker->id)
            ->where('status', CvVerificationRequest::STATUS_PENDING)
            ->exists();

        if ($hasPending) {
            return response()->json(['success' => false, 'message' => 'A pending request already exists'], 409);
        }

        $req = CvVerificationRequest::create([
            'job_seeker_id' => $jobSeeker->id,
            'cv_file' => $jobSeeker->cv_file,
            'status' => CvVerificationRequest::STATUS_PENDING,
        ]);

        return response()->json([
            'success' => true,
            'data' => $req,
            'message' => 'Verification request created',
        ]);
    }
}

