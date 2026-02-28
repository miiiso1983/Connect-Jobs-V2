<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminPermissionMiddleware
{
    public function handle(Request $request, Closure $next, ...$keys)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        if (($user->role ?? null) !== 'admin') {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'ليس لديك صلاحية الوصول إلى هذه الصفحة.'], 403);
            }
            return redirect()->route('dashboard')->with('status', 'ليس لديك صلاحية الوصول إلى هذه الصفحة.');
        }

        // Master admin always allowed
        if (method_exists($user, 'isMasterAdmin') && $user->isMasterAdmin()) {
            return $next($request);
        }

        $expanded = $this->expandKeys($keys);
        $perm = $user->adminPermission;

        $allowed = false;
        foreach ($expanded as $k) {
            if ($perm && $perm->allows($k)) {
                $allowed = true;
                break;
            }
        }

        if (!$allowed) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'ليس لديك صلاحية لتنفيذ هذا الإجراء.'], 403);
            }
            return redirect()->route('admin.dashboard')->with('status', 'ليس لديك صلاحية الوصول إلى هذه الصفحة.');
        }

        return $next($request);
    }

    private function expandKeys(array $keys): array
    {
        $out = [];
        foreach ($keys as $k) {
            foreach (explode(',', (string) $k) as $part) {
                $part = trim($part);
                if ($part !== '') {
                    $out[] = $part;
                }
            }
        }
        return array_values(array_unique($out));
    }
}
