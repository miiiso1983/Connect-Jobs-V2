<?php

namespace Tests\Feature\Console;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WeeklyAlertsCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_alerts_send_weekly_runs_successfully_with_no_alerts(): void
    {
        $this->artisan('alerts:send-weekly')
            ->expectsOutputToContain('Sending weekly job alerts')
            ->assertExitCode(0);
    }
}

