<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $userRole = auth()->user()->role ?? null;
        if ($userRole === null || !in_array($userRole, $roles)) {
            throw new AccessDeniedHttpException('ليس لديك صلاحية الوصول إلى هذه الصفحة.');
        }

        return $next($request);
    }
}

