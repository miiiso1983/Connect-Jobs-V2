<?php

namespace App\Mail;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class JobApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Job $job;

    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    public function build()
    {
        return $this->subject('تم اعتماد إعلانك - Connect Jobs')
            ->view('emails.job-approved');
    }
}

