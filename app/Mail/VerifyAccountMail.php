<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class VerifyAccountMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public array $data) {}

    public function headers(): Headers
    {
        return new Headers(
            text: [
                'X-Mailer' => 'Connect Jobs Mailer',
                'Reply-To' => 'info@connect-job.com',
            ],
        );
    }

    public function build()
    {
        return $this->subject(__('تأكيد حسابك | Verify your account'))
            ->view('emails.verify-account')
            ->with($this->data);
    }
}

