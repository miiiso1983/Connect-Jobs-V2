<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Mail;
use App\Mail\JobRejectedMail;

class JobAdminController extends Controller
{
    public function pending(\Illuminate\Http\Request $request): View
    {
        $q = trim((string)$request->get('q',''));
        $province = trim((string)$request->get('province',''));
        $approved = $request->get('approved','pending'); // pending, approved, all
        $status = $request->get('status',''); // open, closed, ''
        $sort = $request->get('sort','id');
        $dir = strtolower($request->get('dir','desc')) === 'asc' ? 'asc' : 'desc';

        $jobsQ = Job::query()->with('company');

        if ($approved === 'pending') {
            $jobsQ->where('approved_by_admin', false);
        } elseif ($approved === 'approved') {
            $jobsQ->where('approved_by_admin', true);
        }
        if ($status !== '') {
            $jobsQ->where('status', $status);
        }
        if ($province !== '') {
            $jobsQ->where('province', $province);
        }
        if ($q !== '') {
            $jobsQ->where(function($w) use ($q){
                $w->where('title','like',"%{$q}%")->orWhere('description','like',"%{$q}%");
            });
        }

        $allowedSort = ['id','title','province'];
        if (!in_array($sort, $allowedSort, true)) { $sort = 'id'; }
        $jobs = $jobsQ->orderBy($sort, $dir)->limit(500)->get();

        return view('admin.jobs.pending', compact('jobs','q','province','approved','status','sort','dir'));
    }

    public function approve(Job $job): RedirectResponse
    {
        $job->update(['approved_by_admin' => true, 'status' => 'open', 'admin_reject_reason' => null]);
        return back()->with('status','تمت الموافقة على الوظيفة.');
    }
    public function reject(Job $job, \Illuminate\Http\Request $request): RedirectResponse
    {
        $reason = trim((string)$request->input('reason',''));
        $job->update(['approved_by_admin' => false, 'status' => 'closed', 'admin_reject_reason' => ($reason ?: null)]);

        // Send email notification to company owner
        $user = optional($job->company)->user;
        $email = $user?->email ? trim((string)$user->email) : '';
        if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            try {
                Mail::to($email)->send(new JobRejectedMail($job, $reason ?: null));
            } catch (\Throwable $e) {
                // Silently ignore email failure in admin flow
            }
        }

        return back()->with('status','تم رفض الوظيفة وإغلاقها.' . ($reason ? ' (سبب: '.$reason.')' : ''));
    }

}

