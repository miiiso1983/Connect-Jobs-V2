<?php

namespace App\Mail;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class JobPendingReviewMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Job $job) {}

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
        return $this->subject('إعلان وظيفة بانتظار المراجعة | Connect Jobs')
            ->view('emails.job-pending-review')
            ->with([
                'job' => $this->job,
                'reviewUrl' => route('admin.jobs.pending'),
            ]);
    }
}

