<?php

namespace Tests\Feature\Admin;

use App\Mail\JobApprovedMail;
use App\Mail\JobRejectedMail;
use App\Models\Company;
use App\Models\Job;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ModerationMailTest extends TestCase
{
    use RefreshDatabase;

    public function test_job_approved_and_rejected_mail_are_sent(): void
    {
        Mail::fake();

        $user = User::factory()->create(['role' => 'company']);
        $company = Company::create([
            'user_id' => $user->id,
            'company_name' => 'اختبار',
            'province' => 'بغداد',
            'industry' => 'أخرى',
        ]);
        $job = Job::create([
            'company_id' => $company->id,
            'title' => 'صيدلاني',
            'description' => 'وصف مختصر',
            'province' => 'بغداد',
            'status' => 'open',
            'approved_by_admin' => true,
        ]);

        Mail::to($user->email)->send(new JobApprovedMail($job));
        Mail::to($user->email)->send(new JobRejectedMail($job, 'سبب تجريبي'));

        Mail::assertSent(JobApprovedMail::class, 1);
        Mail::assertSent(JobRejectedMail::class, 1);
    }
}

