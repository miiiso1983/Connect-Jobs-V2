<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $userRole = Auth::user()->role ?? null;
        if ($userRole === null || !in_array($userRole, $roles)) {
            // For API/AJAX requests, return 403 JSON
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => __('ليس لديك صلاحية الوصول إلى هذه الصفحة.'),
                ], 403);
            }

            // For web requests, redirect to the appropriate dashboard with a friendly message
            $route = match ($userRole) {
                'admin' => 'admin.dashboard',
                'company' => 'company.dashboard',
                'jobseeker' => 'jobseeker.dashboard',
                default => '/',
            };

            return redirect()->route($route)->with('status', __('ليس لديك صلاحية الوصول إلى هذه الصفحة.'));
        }

        return $next($request);
    }
}

