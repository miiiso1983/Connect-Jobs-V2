<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


use Illuminate\Support\Facades\Mail;

Artisan::command('mail:test {to}', function (string $to) {
    try {
        Mail::raw('هذا بريد تجريبي من Connect Job للتأكد من إعدادات SMTP', function ($m) use ($to) {
            $m->to($to)->subject('اختبار البريد - Connect Job');
        });
        $this->info('Test email dispatched to: ' . $to);
    } catch (\Throwable $e) {
        $this->error('Failed to send: ' . $e->getMessage());
    }
})->purpose('Send a test email to verify MAIL_ settings');


use App\Models\JobAlert;
use App\Models\Job;
use Illuminate\Support\Facades\Mail as MailFacade;
use App\Mail\JobAlertMail;

Artisan::command('alerts:send-weekly', function(){
    $this->info('Sending weekly job alerts...');
    $count = 0;
    $alerts = JobAlert::with('user')
        ->where('enabled', true)
        ->where('frequency', 'weekly')
        ->get();

    foreach ($alerts as $alert) {
        $user = $alert->user;
        if (!$user) { continue; }
        $email = trim((string) $user->email);
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) { continue; }

        $q = trim((string)($alert->q ?? ''));
        $province = trim((string)($alert->province ?? ''));
        $industry = trim((string)($alert->industry ?? ''));
        $jobTitle = trim((string)($alert->job_title ?? ''));

        $jobsQ = Job::query()
            ->with('company')
            ->withCount('applications')
            ->where('approved_by_admin', true)
            ->where('status','open');

        if ($q !== '') {
            $jobsQ->where(function($qq) use ($q){
                $qq->where('title','like',"%{$q}%")
                   ->orWhere('description','like',"%{$q}%");
            });
        }
        if ($province !== '') { $jobsQ->where('province', $province); }
        if ($industry !== '') {
            $jobsQ->whereHas('company', function($cq) use ($industry){ $cq->where('industry', $industry); });
        }
        if ($jobTitle !== '') { $jobsQ->where('title','like',"%{$jobTitle}%"); }

        $jobs = $jobsQ->orderByDesc('id')->limit(20)->get();
        try {
            MailFacade::to($email)->send(new JobAlertMail([
                'q'=>$q,'province'=>$province,'industry'=>$industry,'job_title'=>$jobTitle
            ], $jobs, $alert->unsubscribe_token));
            $alert->last_sent_at = now();
            $alert->save();
            $count++;
        } catch (\Throwable $e) {
            $this->error('Failed to send to '.$email.': '.$e->getMessage());
        }
    }

    $this->info('Weekly alerts sent: '.$count);
})->purpose('Send weekly job alerts to users');

Artisan::command('alerts:backfill-unsubscribe-tokens', function(){
    $count = 0;
    $missing = \App\Models\JobAlert::whereNull('unsubscribe_token')->orWhere('unsubscribe_token','')->get();
    foreach ($missing as $alert) {
        try {
            $alert->unsubscribe_token = bin2hex(random_bytes(16));
            $alert->save();
            $count++;
        } catch (\Throwable $e) {}
    }
    $this->info('Backfilled unsubscribe_token for '.$count.' alerts.');
})->purpose('Generate unsubscribe_token for old job alerts');

