<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Queue\SerializesModels;

class CompanyRegistrationRequestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Company $company, public ?User $user = null) {}

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
        return $this->subject('إشعار إداري: طلب تسجيل شركة جديدة | Connect Jobs')
            ->view('emails.company-registration-request')
            ->with([
                'company' => $this->company,
                'user' => $this->user,
                'approveUrl' => route('admin.companies.index'),
                'submittedAt' => now(),
            ]);
    }
}

