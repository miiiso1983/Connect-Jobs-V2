<?php

namespace App\Mail;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class JobAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public array $filters;
    /** @var \Illuminate\Support\Collection<int,Job> */
    public $jobs;

    public function __construct(array $filters, $jobs)
    {
        $this->filters = $filters;
        $this->jobs = $jobs;
    }

    public function build()
    {
        return $this->subject('تنبيهات وظائف جديدة - Connect Jobs')
            ->view('emails.job-alert');
    }
}

