<?php

namespace Tests\Feature\Auth;

use App\Mail\CompanyRegistrationRequestMail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'jobseeker',
            'whatsapp_number' => '07701234567',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'whatsapp_number' => '07701234567',
        ]);
    }

    public function test_jobseeker_registration_requires_mobile_number(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'email' => 'missing-mobile@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'jobseeker',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors('whatsapp_number');
        $this->assertGuest();
    }

    public function test_new_companies_queue_notification_emails_for_admin_recipients(): void
    {
        Mail::fake();

        config()->set('mail.company_registration_notification_emails', [
            'danea@connect-job.com',
            'mustafa.maxcon@outlook.com',
        ]);

        $response = $this->post('/register', [
            'name' => 'Test Company',
            'email' => 'company@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'role' => 'company',
            'scientific_office_name' => 'Office',
            'company_job_title' => 'Manager',
            'mobile_number' => '07700000000',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));

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
