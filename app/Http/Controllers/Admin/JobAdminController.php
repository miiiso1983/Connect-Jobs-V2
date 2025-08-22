<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class JobAdminController extends Controller
{
    public function pending(): View
    {
        $jobs = Job::where('approved_by_admin', false)->orderBy('id','desc')->get();
        return view('admin.jobs.pending', compact('jobs'));
    }

    public function approve(Job $job): RedirectResponse
    {
        $job->update(['approved_by_admin' => true, 'status' => 'open']);
        return back()->with('status','تمت الموافقة على الوظيفة.');
    }
}

