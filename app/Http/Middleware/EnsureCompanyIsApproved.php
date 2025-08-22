<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureCompanyIsApproved
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && auth()->user()->role === 'company') {
            $status = auth()->user()->status ?? 'inactive';
            if ($status !== 'active') {
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->withErrors([
                    'email' => 'حساب الشركة بانتظار موافقة المشرف.'
                ]);
            }
        }

        return $next($request);
    }
}

