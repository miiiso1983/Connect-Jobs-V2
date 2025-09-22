<?php

namespace App\Http\Controllers\Company;

use App\Http\Controllers\Controller;
use App\Models\JobSeeker;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class SeekerShowController extends Controller
{
    public function __invoke(JobSeeker $jobSeeker): View|RedirectResponse
    {
        $company = Auth::user()->company;
        abort_if(!$company, 403);

        // Subscription check (same logic used elsewhere)
        $expiresAt = optional($company)->subscription_expires_at
            ?: (optional($company)->subscription_expiry ? \Carbon\Carbon::parse($company->subscription_expiry)->endOfDay() : null);
        if ($expiresAt && now()->greaterThan($expiresAt)) {
            return redirect()->route('company.dashboard')->with('status','انتهى اشتراكك. الرجاء التجديد للوصول إلى الملفات.');
        }

        // Show limited profile even if not an applicant of this company
        $jobSeeker->load(['user']);
        return view('company.seekers.show', compact('jobSeeker'));
    }
}

