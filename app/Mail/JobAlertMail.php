<?php

namespace App\Mail;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Headers;
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

    public function headers(): Headers
    {
        $text = [
            'X-Mailer' => 'Connect Jobs Mailer',
            'Reply-To' => 'info@connect-job.com',
        ];

        if ($this->unsubscribeToken) {
            $unsubUrl = route('alerts.unsubscribe', $this->unsubscribeToken);
            $text['List-Unsubscribe'] = "<{$unsubUrl}>, <mailto:info@connect-job.com?subject=unsubscribe>";
            $text['List-Unsubscribe-Post'] = 'List-Unsubscribe=One-Click';
        } else {
            $text['List-Unsubscribe'] = '<mailto:info@connect-job.com?subject=unsubscribe>';
        }

        return new Headers(text: $text);
    }

    public function build()
    {
        return $this->subject('تنبيهات وظائف جديدة | Connect Jobs')
            ->view('emails.job-alert');
    }
}

