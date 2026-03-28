<?php

namespace App\Mail;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class JobRejectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public Job $job;
    public ?string $reason;

    public function __construct(Job $job, ?string $reason = null)
    {
        $this->job = $job;
        $this->reason = $reason;
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
        return $this->subject('إشعار رفض وظيفة | Connect Jobs')
            ->view('emails.job-rejected');
    }
}

