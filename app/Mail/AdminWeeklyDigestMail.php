<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
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

    public function build()
    {
        return $this->subject('الملخص الأسبوعي - Connect Job')
            ->view('emails.admin-weekly-digest');
    }
}

