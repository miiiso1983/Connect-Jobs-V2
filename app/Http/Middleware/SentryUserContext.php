<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SentryUserContext
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            if (function_exists('Sentry\configureScope')) {
                \Sentry\configureScope(function (\Sentry\State\Scope $scope) {
                    if ($user = Auth::user()) {
                        $scope->setUser([
                            'id' => $user->id,
                            'email' => $user->email,
                        ]);
                        if (!empty($user->role)) {
                            $scope->setTag('role', (string) $user->role);
                        }
                    }
                });
            }
        } catch (\Throwable $e) {
            // Do not break the request if Sentry is misconfigured
        }

        return $next($request);
    }
}

