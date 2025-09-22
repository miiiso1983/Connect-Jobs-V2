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
        $jobSeeker->load(['user']);
        return view('company.seekers.show', compact('jobSeeker'));
    }
}

