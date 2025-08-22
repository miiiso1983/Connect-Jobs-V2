<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Job;
use App\Models\JobSeeker;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CompanyDashboardController extends Controller
{
    public function __invoke(): View
    {
        $companyId = Auth::user()->company?->id;

        // KPIs
        $publishedJobs = Job::where('company_id', $companyId)->where('status', 'open')->count();
        $pendingJobs = Job::where('company_id', $companyId)->where('approved_by_admin', false)->count();

        $companyJobIds = Job::where('company_id', $companyId)->pluck('id');
        $weekAgo = Carbon::now()->subDays(7);
        $appsQuery = Application::whereIn('job_id', $companyJobIds);
        $appsThisWeek = (clone $appsQuery)->where('applied_at', '>=', $weekAgo)->count();
        $avgMatch = round(((clone $appsQuery)->avg('matching_percentage')) ?? 0, 1);

        // Distributions
        $appliedSeekerIds = (clone $appsQuery)->pluck('job_seeker_id')->unique();
        $byProvince = JobSeeker::select('province', DB::raw('COUNT(*) as c'))
            ->whereIn('id', $appliedSeekerIds)
            ->groupBy('province')->orderByDesc('c')->take(5)->get();
        $bySpeciality = JobSeeker::select('speciality', DB::raw('COUNT(*) as c'))
            ->whereIn('id', $appliedSeekerIds)
            ->groupBy('speciality')->orderByDesc('c')->take(5)->get();

        return view('dashboards.company', [
            'kpis' => [
                'published' => $publishedJobs,
                'pending' => $pendingJobs,
                'apps_week' => $appsThisWeek,
                'avg_match' => $avgMatch,
            ],
            'charts' => [
                'by_province' => $byProvince,
                'by_speciality' => $bySpeciality,
            ],
        ]);
    }
}

