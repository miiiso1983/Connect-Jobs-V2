<?php

namespace App\Jobs;

use App\Mail\JobPostedMail;
use App\Models\Job;
use App\Models\JobSeeker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendJobAlerts implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;
    public $backoff = [60, 300, 600];

    // Avoid conflict with InteractsWithQueue::$job
    private Job $domainJob;

    public function __construct(Job $domainJob)
    {
        $this->domainJob = $domainJob;
    }

    public function handle(): void
    {
        $job = $this->domainJob->fresh('company');
        // Target seekers by province or speciality overlap
        $query = JobSeeker::query()
            ->with('user')
            ->where('profile_completed', true)
            ->whereHas('user', function($q){
                $q->where('email_opt_in', true)->where('job_alerts_opt_in', true);
            });

        if ($job->province) {
            $query->where('province', $job->province);
        }
        if (!empty($job->districts)) {
            $query->whereJsonContains('districts', $job->districts[0]);
        }

        $seekers = $query->limit(500)->get();
        foreach ($seekers as $seeker) {
            try {
                Mail::to([$seeker->user->email => $seeker->full_name])
                    ->queue(new JobPostedMail($job));
                \DB::table('email_logs')->insert([
                    'mailable' => JobPostedMail::class,
                    'to_email' => $seeker->user->email,
                    'to_name' => $seeker->full_name,
                    'payload' => json_encode(['job_id' => $job->id]),
                    'status' => 'queued',
                    'queued_at' => now(),
                ]);
            } catch (\Throwable $e) {
                Log::error('Job alert mail failed: '.$e->getMessage());
                \DB::table('email_logs')->insert([
                    'mailable' => JobPostedMail::class,
                    'to_email' => $seeker->user->email,
                    'to_name' => $seeker->full_name,
                    'payload' => json_encode(['job_id' => $job->id]),
                    'status' => 'failed',
                    'error' => $e->getMessage(),
                    'failed_at' => now(),
                ]);
            }
        }
    }
}

