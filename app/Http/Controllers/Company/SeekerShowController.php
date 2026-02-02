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
        $user = Auth::user();
        // Allow admin or company users to view job seeker profiles
        abort_unless($user && \in_array($user->role ?? '', ['admin', 'company'], true), 403);

        // Load all relationships for complete profile view
        $jobSeeker->load(['user', 'applications.job']);

        $context = ($user->role === 'admin') ? 'admin' : 'company';

        return view('company.seekers.show', compact('jobSeeker', 'context'));
    }
}

