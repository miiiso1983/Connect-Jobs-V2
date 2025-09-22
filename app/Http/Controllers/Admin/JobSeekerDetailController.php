<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JobSeeker;
use Illuminate\Http\Request;
use Illuminate\View\View;

class JobSeekerDetailController extends Controller
{
    public function show(JobSeeker $jobSeeker): View
    {
        $user = auth()->user();
        abort_unless($user && ($user->role ?? '')==='admin', 403);
        $jobSeeker->load(['user','applications.job']);
        return view('admin.jobseekers.show', compact('jobSeeker'));
    }
}

