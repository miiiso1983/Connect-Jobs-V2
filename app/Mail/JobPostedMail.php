<?php

namespace App\Mail;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class JobPostedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Job $job, public array $data = []) {}

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
        return $this->subject(__('وظيفة جديدة قد تهمك | New job that matches you'))
            ->view('emails.job-posted')
            ->with(['job' => $this->job] + $this->data);
    }
}

