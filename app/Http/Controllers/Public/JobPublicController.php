<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\View\View;

class JobPublicController extends Controller
{
    public function index(): View
    {
        $jobs = Job::where('approved_by_admin', true)->where('status','open')->orderByDesc('id')->paginate(12);
        return view('public.jobs.index', compact('jobs'));
    }

    public function show(Job $job): View
    {
        abort_if(!$job->approved_by_admin || $job->status !== 'open', 404);
        return view('public.jobs.show', compact('job'));
    }
}

