<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Favorite;
use App\Models\Job;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    /**
     * Get user's favorite jobs
     */
    public function index(Request $request)
    {
        try {
            $query = Favorite::where('user_id', auth()->id())
                ->with(['job.company.user']);

            // Pagination
            $perPage = min($request->get('per_page', 15), 50);
            $favorites = $query->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $favorites,
                'message' => 'Favorites retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve favorites',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add job to favorites
     */
    public function store(Request $request, $jobId)
    {
        try {
            $job = Job::where('status', 'open')
                ->where('approved_by_admin', true)
                ->findOrFail($jobId);

            // Check if already favorited
            $existingFavorite = Favorite::where('user_id', auth()->id())
                ->where('job_id', $job->id)
                ->first();

            if ($existingFavorite) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job is already in favorites'
                ], 409);
            }

            $favorite = Favorite::create([
                'user_id' => auth()->id(),
                'job_id' => $job->id,
            ]);

            return response()->json([
                'success' => true,
                'data' => $favorite->load(['job.company.user']),
                'message' => 'Job added to favorites successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to add job to favorites',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove job from favorites
     */
    public function destroy($jobId)
    {
        try {
            $favorite = Favorite::where('user_id', auth()->id())
                ->where('job_id', $jobId)
                ->first();

            if (!$favorite) {
                return response()->json([
                    'success' => false,
                    'message' => 'Job is not in favorites'
                ], 404);
            }

            $favorite->delete();

            return response()->json([
                'success' => true,
                'message' => 'Job removed from favorites successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to remove job from favorites',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Check if job is favorited by user
     */
    public function check($jobId)
    {
        try {
            $isFavorited = Favorite::where('user_id', auth()->id())
                ->where('job_id', $jobId)
                ->exists();

            return response()->json([
                'success' => true,
                'data' => ['is_favorited' => $isFavorited],
                'message' => 'Favorite status retrieved successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to check favorite status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
