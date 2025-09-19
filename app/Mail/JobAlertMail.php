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
    public ?string $unsubscribeToken;

    public function __construct(array $filters, $jobs, ?string $unsubscribeToken = null)
    {
        $this->filters = $filters;
        $this->jobs = $jobs;
        $this->unsubscribeToken = $unsubscribeToken;
    }

    public function build()
    {
        return $this->subject('تنبيهات وظائف جديدة - Connect Jobs')
            ->view('emails.job-alert');
    }
}

