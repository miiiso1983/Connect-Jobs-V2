<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Company;
use App\Models\JobSeeker;
use App\Models\MasterSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class ProfileController extends Controller
{
    /**
     * Get user profile
     */
    public function show()
    {
        try {
            $user = auth()->user();
            $profile = $user->load($user->role === 'company' ? 'company' : 'jobSeeker');

            return response()->json([
                'success' => true,
                'data' => $profile,
                'message' => 'Profile retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user profile
     */
    public function update(Request $request)
    {
        try {
            $user = auth()->user();

            if ($user->role === 'company') {
                return $this->updateCompanyProfile($request, $user);
            } elseif ($user->role === 'jobseeker') {
                return $this->updateJobSeekerProfile($request, $user);
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid user role'
            ], 400);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update profile',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update company profile
     */
    private function updateCompanyProfile(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'company_name' => 'sometimes|required|string|max:255',
            'scientific_office_name' => 'nullable|string|max:255',
            'company_job_title' => 'nullable|string|max:255',
            'mobile_number' => 'nullable|string|max:20',
            'province' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // 2MB max
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update user data
        $userData = $request->only(['name', 'email']);
        if (!empty($userData)) {
            $user->update($userData);
        }

        // Update company data
        $company = $user->company;
        if (!$company) {
            $company = Company::create(['user_id' => $user->id]);
        }

        $companyData = $request->only([
            'company_name', 'scientific_office_name', 'company_job_title',
            'mobile_number', 'province', 'industry'
        ]);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($company->profile_image) {
                Storage::disk('public')->delete($company->profile_image);
            }
            
            $file = $request->file('profile_image');
            $filename = time() . '_company_' . $user->id . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profile_images', $filename, 'public');
            $companyData['profile_image'] = $path;
        }

        $company->update($companyData);

        return response()->json([
            'success' => true,
            'data' => $user->load('company'),
            'message' => 'Company profile updated successfully'
        ]);
    }

    /**
     * Update job seeker profile
     */
    private function updateJobSeekerProfile(Request $request, User $user)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $user->id,
            'full_name' => 'sometimes|required|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'speciality' => 'nullable|string|max:255',
            'specialities' => 'nullable|array',
            'province' => 'nullable|string|max:255',
            'districts' => 'nullable|array',
            'education_level' => 'nullable|string|max:255',
            'experience_level' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female',
            'own_car' => 'nullable|boolean',
            'skills' => 'nullable|string|max:1000',
            'summary' => 'nullable|string|max:2000',
            'qualifications' => 'nullable|string|max:2000',
            'experiences' => 'nullable|string|max:2000',
            'languages' => 'nullable|string|max:1000',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'cv_file' => 'nullable|file|mimes:pdf,doc,docx|max:5120',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        // Update user data
        $userData = $request->only(['name', 'email']);
        if (!empty($userData)) {
            $user->update($userData);
        }

        // Update job seeker data
        $jobSeeker = $user->jobSeeker;
        if (!$jobSeeker) {
            $jobSeeker = JobSeeker::create(['user_id' => $user->id]);
        }

        $jobSeekerData = $request->only([
            'full_name', 'job_title', 'speciality', 'specialities',
            'province', 'districts', 'education_level', 'experience_level',
            'gender', 'own_car', 'skills', 'summary', 'qualifications',
            'experiences', 'languages'
        ]);

        // Handle profile image upload
        if ($request->hasFile('profile_image')) {
            // Delete old image if exists
            if ($jobSeeker->profile_image) {
                Storage::disk('public')->delete($jobSeeker->profile_image);
            }
            
            $file = $request->file('profile_image');
            $filename = time() . '_seeker_' . $user->id . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('profile_images', $filename, 'public');
            $jobSeekerData['profile_image'] = $path;
        }

        // Handle CV file upload
        if ($request->hasFile('cv_file')) {
            // Delete old CV if exists
            if ($jobSeeker->cv_file) {
                Storage::disk('public')->delete($jobSeeker->cv_file);
            }
            
            $file = $request->file('cv_file');
            $filename = time() . '_cv_' . $user->id . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('cvs', $filename, 'public');
            $jobSeekerData['cv_file'] = $path;
        }

        // Check if profile is completed
        $requiredFields = ['full_name', 'job_title', 'speciality', 'province', 'gender'];
        $isCompleted = true;
        foreach ($requiredFields as $field) {
            if (empty($jobSeekerData[$field] ?? $jobSeeker->$field)) {
                $isCompleted = false;
                break;
            }
        }
        $jobSeekerData['profile_completed'] = $isCompleted;

        $jobSeeker->update($jobSeekerData);

        return response()->json([
            'success' => true,
            'data' => $user->load('jobSeeker'),
            'message' => 'Job seeker profile updated successfully'
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = auth()->user();

            // Check current password
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Current password is incorrect'
                ], 400);
            }

            // Update password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to change password',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete profile image
     */
    public function deleteProfileImage()
    {
        try {
            $user = auth()->user();
            
            if ($user->role === 'company') {
                $profile = $user->company;
            } elseif ($user->role === 'jobseeker') {
                $profile = $user->jobSeeker;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid user role'
                ], 400);
            }

            if ($profile && $profile->profile_image) {
                Storage::disk('public')->delete($profile->profile_image);
                $profile->update(['profile_image' => null]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Profile image deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete profile image',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get master settings for dropdowns
     */
    public function getMasterSettings()
    {
        try {
            $settings = MasterSetting::select('setting_type', 'value')
                ->whereIn('setting_type', [
                    'province', 'speciality', 'gender',
                    'education_level', 'experience_level', 'job_title'
                ])
                ->get()
                ->groupBy('setting_type')
                ->map(function ($items) {
                    return $items->pluck('value')->toArray();
                });

            return response()->json([
                'success' => true,
                'data' => $settings,
                'message' => 'Master settings retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve master settings',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
