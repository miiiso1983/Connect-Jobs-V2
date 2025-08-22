<?php

namespace App\Http\Middleware;

use App\Models\Job;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class EnsureJobBelongsToCompany
{
    public function handle(Request $request, Closure $next)
    {
        $job = $request->route('job');
        if ($job instanceof Job) {
            $companyId = $request->user()?->company?->id;
            if (!$companyId || $job->company_id !== $companyId) {
                throw new AccessDeniedHttpException('Unauthorized');
            }
        }
        return $next($request);
    }
}

