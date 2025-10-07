<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\JobController;
use App\Http\Controllers\Api\ApplicationController;
use App\Http\Controllers\Api\ProfileController;
use App\Http\Controllers\Api\FavoriteController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Public routes (no authentication required)
Route::prefix('v1')->group(function () {
    
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::post('register', [AuthController::class, 'register']);
        Route::post('login', [AuthController::class, 'login']);
        Route::post('refresh', [AuthController::class, 'refresh']);
    });

    // Public job listings (can be accessed without authentication)
    Route::get('jobs', [JobController::class, 'index']);
    Route::get('jobs/{id}', [JobController::class, 'show']);

    // Master settings for dropdowns
    Route::get('master-settings', [ProfileController::class, 'getMasterSettings']);
});

// Protected routes (authentication required)
Route::prefix('v1')->middleware(['auth:api'])->group(function () {
    
    // Authentication routes
    Route::prefix('auth')->group(function () {
        Route::get('me', [AuthController::class, 'me']);
        Route::post('logout', [AuthController::class, 'logout']);
    });

    // Profile management
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show']);
        Route::put('/', [ProfileController::class, 'update']);
        Route::post('change-password', [ProfileController::class, 'changePassword']);
        Route::delete('profile-image', [ProfileController::class, 'deleteProfileImage']);
    });

    // Job management
    Route::prefix('jobs')->group(function () {
        // Company routes
        Route::middleware('role:company')->group(function () {
            Route::post('/', [JobController::class, 'store']);
            Route::put('{id}', [JobController::class, 'update']);
            Route::delete('{id}', [JobController::class, 'destroy']);
            Route::get('my-jobs', [JobController::class, 'myJobs']);
            Route::get('dashboard-stats', [JobController::class, 'dashboardStats']);
            Route::get('{jobId}/applications', [ApplicationController::class, 'jobApplications']);
        });
    });

    // Application management
    Route::prefix('applications')->group(function () {
        // Job seeker routes
        Route::middleware('role:jobseeker')->group(function () {
            Route::post('apply/{jobId}', [ApplicationController::class, 'apply']);
            Route::get('my-applications', [ApplicationController::class, 'myApplications']);
            Route::delete('{applicationId}/withdraw', [ApplicationController::class, 'withdraw']);
        });

        // Company routes
        Route::middleware('role:company')->group(function () {
            Route::put('{applicationId}/status', [ApplicationController::class, 'updateStatus']);
        });
    });

    // Favorites routes
    Route::prefix('favorites')->group(function () {
        Route::get('/', [FavoriteController::class, 'index']);
        Route::post('{jobId}', [FavoriteController::class, 'store']);
        Route::delete('{jobId}', [FavoriteController::class, 'destroy']);
        Route::get('check/{jobId}', [FavoriteController::class, 'check']);
    });
});

// Health check endpoint
Route::get('health', function () {
    return response()->json([
        'success' => true,
        'message' => 'API is working',
        'timestamp' => now()->toISOString(),
        'version' => '1.0.0'
    ]);
});
