<?php

namespace Tests\Feature\Alerts;

use App\Models\JobAlert;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UnsubscribeTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_unsubscribe_link_disables_alert_and_shows_confirmation(): void
    {
        $user = User::factory()->create();
        $alert = JobAlert::create([
            'user_id' => $user->id,
            'q' => 'php',
            'province' => 'بغداد',
            'industry' => 'أخرى',
            'job_title' => 'مطور',
            'frequency' => 'weekly',
            'channel' => 'email',
            'enabled' => true,
        ]);

        $this->assertNotEmpty($alert->unsubscribe_token);

        $resp = $this->get(route('alerts.unsubscribe', $alert->unsubscribe_token));
        $resp->assertStatus(200);
        $resp->assertSee('تم إلغاء تنبيه الوظائف');

        $alert->refresh();
        $this->assertFalse($alert->enabled);
    }
}

