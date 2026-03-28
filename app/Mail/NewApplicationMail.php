<?php

namespace App\Mail;

use App\Models\Application;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class NewApplicationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Application $application) {}

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
        return $this->subject(__('طلب توظيف جديد | New application received'))
            ->view('emails.new-application')
            ->with(['application' => $this->application]);
    }
}

