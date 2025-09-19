<?php

namespace App\Http\Middleware;

use App\Models\Job;
use Closure;
use Illuminate\Http\Request;

class EnsureJobBelongsToCompany
{
    public function handle(Request $request, Closure $next)
    {
        $job = $request->route('job');
        if ($job instanceof Job) {
            $companyId = $request->user()?->company?->id;
            if (!$companyId || $job->company_id !== $companyId) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => __('غير مسموح بالوصول إلى هذه الوظيفة.')], 403);
                }
                return redirect()->route('company.jobs.index')->with('status', __('لا تملك صلاحية لعرض هذه الوظيفة.'));
            }
        }
        return $next($request);
    }
}

