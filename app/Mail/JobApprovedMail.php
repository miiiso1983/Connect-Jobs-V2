<?php

namespace App\Mail;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class JobApprovedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Job $job;

    public function __construct(Job $job)
    {
        $this->job = $job;
    }

    public function headers(): Headers
    {
        return new Headers(
            text: [
                'List-Unsubscribe' => '<mailto:info@connect-job.com?subject=unsubscribe>',
                'X-Mailer' => 'Connect Jobs Mailer',
                'Reply-To' => 'info@connect-job.com',
            ],
        );
    }

    public function build()
    {
        return $this->subject('تم اعتماد إعلانك | Connect Jobs')
            ->view('emails.job-approved');
    }
}

