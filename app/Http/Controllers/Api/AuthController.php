<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\Company;
use App\Models\JobSeeker;
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
                Company::create([
                    'user_id' => $user->id,
                    'company_name' => $request->company_name,
                    'scientific_office_name' => $request->scientific_office_name,
                    'company_job_title' => $request->company_job_title,
                    'mobile_number' => $request->mobile_number,
                    'province' => $request->province,
                    'industry' => $request->industry,
                    'status' => 'pending',
                ]);
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
}
