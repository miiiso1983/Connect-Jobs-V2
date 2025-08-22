<?php

namespace App\Mail;

use App\Models\Job;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class JobPendingReviewMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Job $job) {}

    public function build()
    {
        return $this->subject('إعلان وظيفة بانتظار المراجعة')
            ->view('emails.job-pending-review')
            ->with([
                'job' => $this->job,
                'reviewUrl' => route('admin.jobs.pending'),
            ]);
    }
}

