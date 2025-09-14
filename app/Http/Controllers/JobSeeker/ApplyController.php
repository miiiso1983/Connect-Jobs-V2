<?php

namespace App\Http\Controllers\JobSeeker;

use App\Http\Controllers\Controller;
use App\Models\Application;
use App\Models\Job;
use App\Models\JobSeeker;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class ApplyController extends Controller
{
    public function show(Job $job)
    {
        $js = JobSeeker::firstWhere('user_id', Auth::id());
        $alreadyApplied = false;
        if ($js) {
            $alreadyApplied = Application::where('job_id', $job->id)
                ->where('job_seeker_id', $js->id)
                ->exists();
        }
        return view('jobseeker.apply.confirm', compact('job','alreadyApplied'));
    }

    public function apply(Job $job): RedirectResponse
    {
        $js = JobSeeker::firstWhere('user_id', Auth::id());
        if (!$js) { return back()->withErrors('يرجى إكمال البروفايل أولاً.'); }

        // حساب نسبة المطابقة بناءً على متطلبات الشركة الأساسية
        $criteria = [
            'job_title' => $job->title,
            'province' => $job->province,
        ];
        $score = 0; $total = 0;
        foreach ($criteria as $field => $expected) {
            if ($expected) {
                $total++;
                if (strcasecmp((string)$js->$field, (string)$expected) === 0) { $score++; }
            }
        }
        // نقاط إضافية إن توافرت متطلبات نصية في حقل requirements (اختياري مبسط)
        if (!empty($job->requirements) && !empty($js->skills)) {
            $total++;
            $hasKeyword = false;
            foreach (explode(',', strtolower($js->skills)) as $kw) {
                $kw = trim($kw);
                if ($kw !== '' && str_contains(strtolower($job->requirements), $kw)) { $hasKeyword = true; break; }
            }
            if ($hasKeyword) $score++;
        }
        $percentage = $total ? round(($score/$total)*100,2) : 0;

        $application = Application::updateOrCreate(
            ['job_id' => $job->id, 'job_seeker_id' => $js->id],
            ['cv_file' => $js->cv_file, 'matching_percentage' => $percentage, 'applied_at' => now()]
        );

        // Notify jobseeker
        auth()->user()?->notify(new \App\Notifications\GenericNotification(
            title: __('notifications.application_submitted_title'),
            message: __('notifications.application_submitted_body', ['pct'=>$percentage])
        ));

        // Email the company asynchronously
        if ($job->company?->user?->email && optional($job->company->user)->application_notifications_opt_in !== false) {
            try {
                \Mail::to([$job->company->user->email => $job->company->company_name ?? 'Company'])
                    ->queue(new \App\Mail\NewApplicationMail($application));
                \DB::table('email_logs')->insert([
                    'mailable' => \App\Mail\NewApplicationMail::class,
                    'to_email' => $job->company->user->email,
                    'to_name' => $job->company->company_name ?? 'Company',
                    'payload' => json_encode(['application_id' => $application->id]),
                    'status' => 'queued',
                    'queued_at' => now(),
                ]);
            } catch (\Throwable $e) {
                \Log::error('Failed to queue NewApplicationMail: '.$e->getMessage(), [
                    'application_id' => $application->id,
                    'company_user_id' => optional($job->company->user)->id,
                ]);
                try {
                    \DB::table('email_logs')->insert([
                        'mailable' => \App\Mail\NewApplicationMail::class,
                        'to_email' => $job->company->user->email,
                        'to_name' => $job->company->company_name ?? 'Company',
                        'payload' => json_encode(['application_id' => $application->id]),
                        'status' => 'failed',
                        'queued_at' => now(),
                    ]);
                } catch (\Throwable $ignore) {}
            }
        }

        return back()->with('status','تم التقديم. نسبة المطابقة: '.$percentage.'%');
    }
}

