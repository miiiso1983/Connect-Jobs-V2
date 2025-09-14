<?php

namespace App\Mail;

use App\Models\Application;
use App\Models\Job;
use App\Models\JobSeeker;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ProfileViewedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public JobSeeker $jobSeeker,
        public ?Job $job,
        public string $companyName,
    ){}

    public function build()
    {
        return $this->subject(__('تم الاطلاع على ملفك – Connect Job'))
            ->view('emails.profile-view-notification')
            ->with([
                'jobSeeker' => $this->jobSeeker,
                'job' => $this->job,
                'companyName' => $this->companyName,
                'viewedAt' => now(),
            ]);
    }
}

