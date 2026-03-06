<?php

namespace Tests\Feature\Auth;

use App\Mail\CompanyRegistrationRequestMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ApiRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_company_api_registration_queues_notification_emails_for_admin_recipients(): void
    {
        Mail::fake();

        config()->set('mail.company_registration_notification_emails', [
            'danea@connect-job.com',
            'mustafa.maxcon@outlook.com',
        ]);

        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'API Company Owner',
            'email' => 'api-company@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'company',
            'company_name' => 'API Company',
            'scientific_office_name' => 'Office',
            'company_job_title' => 'Manager',
            'mobile_number' => '07700000000',
            'province' => 'Baghdad',
            'industry' => 'Healthcare',
        ]);

        $response->assertCreated()->assertJsonPath('success', true);

        Mail::assertQueued(CompanyRegistrationRequestMail::class, 2);
        Mail::assertQueued(CompanyRegistrationRequestMail::class, fn (CompanyRegistrationRequestMail $mail) => $mail->hasTo('danea@connect-job.com'));
        Mail::assertQueued(CompanyRegistrationRequestMail::class, fn (CompanyRegistrationRequestMail $mail) => $mail->hasTo('mustafa.maxcon@outlook.com'));

        $this->assertDatabaseCount('email_logs', 2);
        $this->assertDatabaseHas('email_logs', [
            'mailable' => CompanyRegistrationRequestMail::class,
            'to_email' => 'danea@connect-job.com',
            'status' => 'queued',
        ]);
        $this->assertDatabaseHas('email_logs', [
            'mailable' => CompanyRegistrationRequestMail::class,
            'to_email' => 'mustafa.maxcon@outlook.com',
            'status' => 'queued',
        ]);
    }
}