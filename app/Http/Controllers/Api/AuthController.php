<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Company;
use App\Models\JobSeeker;
use App\Models\UserFcmToken;
use App\Services\NotificationHelperService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:company,jobseeker',
            // Company specific fields
            'company_name' => 'required_if:role,company|string|max:255',
            'scientific_office_name' => 'nullable|string|max:255',
            'company_job_title' => 'nullable|string|max:255',
            'mobile_number' => 'nullable|string|max:20',
            'province' => 'nullable|string|max:255',
            'industry' => 'nullable|string|max:255',
            // Job seeker specific fields
            'full_name' => 'required_if:role,jobseeker|string|max:255',
            'job_title' => 'nullable|string|max:255',
            'speciality' => 'nullable|string|max:255',
            'gender' => 'nullable|in:male,female',
            'own_car' => 'nullable|boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => $request->role,
                'status' => $request->role === 'company' ? 'pending' : 'active',
            ]);

            // Create role-specific profile
            if ($request->role === 'company') {
                $company = Company::create([
                    'user_id' => $user->id,
                    'company_name' => $request->company_name,
                    'scientific_office_name' => $request->scientific_office_name,
                    'company_job_title' => $request->company_job_title,
                    'mobile_number' => $request->mobile_number,
                    'province' => $request->province,
                    'industry' => $request->industry,
                    'status' => 'pending',
                ]);

                // Send notification to admins about new company registration
                try {
                    $notificationService = app(NotificationHelperService::class);
                    $notificationService->notifyAdminsNewCompanyRegistration($user, $company);
                } catch (\Exception $e) {
                    // Log error but don't fail registration
                    \Log::error('Failed to send admin notification for new company registration: ' . $e->getMessage());
                }
            } elseif ($request->role === 'jobseeker') {
                JobSeeker::create([
                    'user_id' => $user->id,
                    'full_name' => $request->full_name,
                    'job_title' => $request->job_title,
                    'speciality' => $request->speciality,
                    'province' => $request->province,
                    'gender' => $request->gender,
                    'own_car' => $request->own_car ?? false,
                    'profile_completed' => false,
                ]);
            }

            // Generate JWT token
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully',
                'data' => [
                    'user' => new UserResource($user->load($request->role === 'company' ? 'company' : 'jobSeeker')),
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60
                ]
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registration failed',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Login user
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation errors',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $credentials = $request->only('email', 'password');

            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid credentials'
                ], 401);
            }

            $user = auth()->user();

            // Check if user is active
            if ($user->status !== 'active') {
                return response()->json([
                    'success' => false,
                    'message' => 'Account is not active. Please contact admin.'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => [
                    'user' => $user->load($user->role === 'company' ? 'company' : 'jobSeeker'),
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60
                ]
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not create token'
            ], 500);
        }
    }

    /**
     * Get authenticated user
     */
    public function me()
    {
        try {
            $user = auth()->user();

            if (!$user) {
                return response()->json([
                    'success' => false,
                    'message' => 'User not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'user' => $user->load($user->role === 'company' ? 'company' : 'jobSeeker')
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not get user'
            ], 500);
        }
    }

    /**
     * Logout user
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out'
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not logout'
            ], 500);
        }
    }

    /**
     * Refresh token
     */
    public function refresh()
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => config('jwt.ttl') * 60
                ]
            ]);

        } catch (JWTException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not refresh token'
            ], 500);
        }
    }

    /**
     * Register FCM token for push notifications
     */
    public function registerFcmToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fcm_token' => 'required|string|max:500',
                'device_type' => 'nullable|string|in:ios,android,web,unknown',
                'device_id' => 'nullable|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = auth()->user();
            $fcmToken = $request->fcm_token;
            $deviceType = $request->device_type ?? 'unknown';
            $deviceId = $request->device_id;

            // Check if token already exists for this user
            $existingToken = UserFcmToken::where('fcm_token', $fcmToken)
                ->where('user_id', $user->id)
                ->first();

            if ($existingToken) {
                // Update existing token
                $existingToken->update([
                    'device_type' => $deviceType,
                    'device_id' => $deviceId,
                    'is_active' => true,
                    'last_used_at' => now(),
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'FCM token updated successfully',
                    'data' => $existingToken
                ]);
            }

            // Create new token record
            $userFcmToken = UserFcmToken::create([
                'user_id' => $user->id,
                'fcm_token' => $fcmToken,
                'device_type' => $deviceType,
                'device_id' => $deviceId,
                'is_active' => true,
                'last_used_at' => now(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'FCM token registered successfully',
                'data' => $userFcmToken
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to register FCM token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Unregister FCM token
     */
    public function unregisterFcmToken(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'fcm_token' => 'required|string|max:500',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = auth()->user();
            $fcmToken = $request->fcm_token;

            // Find and deactivate the token
            $userFcmToken = UserFcmToken::where('fcm_token', $fcmToken)
                ->where('user_id', $user->id)
                ->first();

            if (!$userFcmToken) {
                return response()->json([
                    'success' => false,
                    'message' => 'FCM token not found'
                ], 404);
            }

            $userFcmToken->deactivate();

            return response()->json([
                'success' => true,
                'message' => 'FCM token unregistered successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to unregister FCM token',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get user's FCM tokens
     */
    public function getFcmTokens()
    {
        try {
            $user = auth()->user();
            $tokens = $user->activeFcmTokens()->get();

            return response()->json([
                'success' => true,
                'data' => $tokens
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to get FCM tokens',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete user account permanently
     * Required by Apple App Store Guidelines 5.1.1(v)
     */
    public function deleteAccount(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'password' => 'required|string',
                'confirmation' => 'required|string|in:DELETE,delete',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }

            $user = auth()->user();

            // Verify password
            if (!Hash::check($request->password, $user->password)) {
                return response()->json([
                    'success' => false,
                    'message' => 'كلمة المرور غير صحيحة'
                ], 401);
            }

            // Delete related data based on role
            if ($user->role === 'company') {
                $company = $user->company;
                if ($company) {
                    // Delete company's jobs and their applications
                    foreach ($company->jobs as $job) {
                        $job->applications()->delete();
                        if ($job->jd_file) {
                            \Storage::disk('public')->delete($job->jd_file);
                        }
                        $job->delete();
                    }
                    // Delete company profile image
                    if ($company->profile_image) {
                        \Storage::disk('public')->delete($company->profile_image);
                    }
                    $company->delete();
                }
            } elseif ($user->role === 'jobseeker') {
                $jobSeeker = $user->jobSeeker;
                if ($jobSeeker) {
                    // Delete job seeker's applications
                    $jobSeeker->applications()->delete();
                    // Delete job seeker's favorites
                    $jobSeeker->favorites()->delete();
                    // Delete profile image and CV
                    if ($jobSeeker->profile_image) {
                        \Storage::disk('public')->delete($jobSeeker->profile_image);
                    }
                    if ($jobSeeker->cv_file) {
                        \Storage::disk('public')->delete($jobSeeker->cv_file);
                    }
                    $jobSeeker->delete();
                }
            }

            // Delete FCM tokens
            $user->fcmTokens()->delete();

            // Invalidate JWT token
            try {
                JWTAuth::invalidate(JWTAuth::getToken());
            } catch (\Exception $e) {
                // Continue even if token invalidation fails
            }

            // Delete user
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'تم حذف حسابك بنجاح. نأسف لرؤيتك تغادر.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'فشل في حذف الحساب',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
