<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class AdminWeeklyDigestMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $stats;
    public $topJobs;

    public function __construct(array $stats, $topJobs)
    {
        $this->stats = $stats;
        $this->topJobs = $topJobs;
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
        return $this->subject('الملخص الأسبوعي | Connect Jobs')
            ->view('emails.admin-weekly-digest');
    }
}

