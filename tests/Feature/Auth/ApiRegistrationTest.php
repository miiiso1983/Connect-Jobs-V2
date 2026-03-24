<?php

namespace Tests\Feature\Auth;

use App\Mail\CompanyRegistrationRequestMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ApiRegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_jobseeker_api_registration_requires_mobile_number(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'API Jobseeker',
            'full_name' => 'API Jobseeker',
            'email' => 'api-jobseeker-missing@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'jobseeker',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors('whatsapp_number');
    }

    public function test_jobseeker_api_registration_stores_mobile_number(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'API Jobseeker',
            'full_name' => 'API Jobseeker',
            'email' => 'api-jobseeker@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'jobseeker',
            'whatsapp_number' => '07801234567',
        ]);

        $response->assertCreated()->assertJsonPath('success', true);
        $this->assertDatabaseHas('users', [
            'email' => 'api-jobseeker@example.com',
            'whatsapp_number' => '07801234567',
        ]);
    }

    public function test_jobseeker_api_registration_accepts_phone_alias_for_mobile_number(): void
    {
        $response = $this->postJson('/api/v1/auth/register', [
            'name' => 'API Alias User',
            'full_name' => 'API Alias User',
            'email' => 'api-alias@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'jobseeker',
            'phone' => '07901234567',
        ]);

        $response->assertCreated()->assertJsonPath('success', true);
        $this->assertDatabaseHas('users', [
            'email' => 'api-alias@example.com',
            'whatsapp_number' => '07901234567',
        ]);
    }

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
