<?php

namespace App\Mail;

use App\Models\JobSeeker;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewJobSeekerRegisteredMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public JobSeeker $seeker, public ?User $user = null) {}

    public function build()
    {
        return $this->subject('تسجيل باحث عمل جديد')
            ->view('emails.new-jobseeker-registered')
            ->with([
                'seeker' => $this->seeker,
                'user' => $this->user,
                'profileUrl' => route('admin.dashboard'),
            ]);
    }
}

