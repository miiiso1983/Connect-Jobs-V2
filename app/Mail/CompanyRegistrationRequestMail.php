<?php

namespace App\Mail;

use App\Models\Company;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CompanyRegistrationRequestMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Company $company, public ?User $user = null) {}

    public function build()
    {
        return $this->subject('طلب تسجيل شركة جديد')
            ->view('emails.company-registration-request')
            ->with([
                'company' => $this->company,
                'user' => $this->user,
                'approveUrl' => route('admin.companies.index'),
            ]);
    }
}

