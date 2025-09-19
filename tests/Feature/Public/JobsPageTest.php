<?php

namespace Tests\Feature\Public;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobsPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_jobs_index_returns_ok_without_params(): void
    {
        $response = $this->get('/jobs');
        $response->assertStatus(200);
        $response->assertSee('الوظائف المتاحة');
    }

    public function test_jobs_index_returns_ok_with_filters(): void
    {
        $url = '/jobs?q=test&province=&industry=&job_title=&company_id=0&company=&sort=latest';
        $response = $this->get($url);
        $response->assertStatus(200);
        $response->assertSee('الوظائف المتاحة');
    }
}

