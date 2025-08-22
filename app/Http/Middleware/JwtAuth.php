<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JwtAuth
{
    public function handle(Request $request, Closure $next)
    {
        // Placeholder for future JWT validation; currently relies on session auth
        // If Authorization: Bearer <token> exists, you can decode/verify here (e.g., using tymon/jwt-auth)
        return $next($request);
    }
}

