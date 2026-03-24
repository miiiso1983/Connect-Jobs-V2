<?php

namespace Tests\Feature\Admin;

use App\Models\AdminPermission;
use App\Models\JobSeeker;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class JobSeekerExportTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_with_jobseekers_permission_can_export_jobseekers_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        AdminPermission::create(['user_id' => $admin->id, 'manage_jobseekers' => true]);

        $user = User::factory()->create([
            'role' => 'jobseeker',
            'status' => 'active',
            'name' => 'Ali Export',
            'email' => 'ali@example.com',
        ]);

        JobSeeker::create([
            'user_id' => $user->id,
            'full_name' => 'Ali Ahmad',
            'province' => 'Baghdad',
            'job_title' => 'Pharmacist',
            'speciality' => 'صيدلة',
            'profile_completed' => true,
            'cv_file' => 'cvs/ali.pdf',
            'cv_verified' => true,
            'university_name' => 'Baghdad University',
            'graduation_year' => 2024,
            'is_fresh_graduate' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('admin.jobseekers.export', [], false));

        $response->assertOk();
        $response->assertStreamed();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type') ?? '');
        $this->assertStringContainsString('jobseekers_', $response->headers->get('content-disposition') ?? '');

        $content = $response->streamedContent();

        $this->assertTrue(str_starts_with($content, "\xEF\xBB\xBF"));
        $this->assertStringContainsString('Ali Ahmad', $content);
        $this->assertStringContainsString('ali@example.com', $content);
        $this->assertStringContainsString('Baghdad', $content);
    }

    public function test_export_respects_selected_filters(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        AdminPermission::create(['user_id' => $admin->id, 'manage_jobseekers' => true]);

        $baghdadUser = User::factory()->create(['role' => 'jobseeker', 'status' => 'active', 'email' => 'baghdad@example.com']);
        JobSeeker::create(['user_id' => $baghdadUser->id, 'full_name' => 'Baghdad User', 'province' => 'Baghdad']);

        $basraUser = User::factory()->create(['role' => 'jobseeker', 'status' => 'active', 'email' => 'basra@example.com']);
        JobSeeker::create(['user_id' => $basraUser->id, 'full_name' => 'Basra User', 'province' => 'Basra']);

        $response = $this->actingAs($admin)->get(route('admin.jobseekers.export', ['province' => 'Baghdad'], false));
        $content = $response->streamedContent();

        $this->assertStringContainsString('Baghdad User', $content);
        $this->assertStringNotContainsString('Basra User', $content);
        $this->assertStringNotContainsString('basra@example.com', $content);
    }

    public function test_admin_without_jobseekers_permission_cannot_export_jobseekers_csv(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        AdminPermission::create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)->get(route('admin.jobseekers.export', [], false));

        $response->assertRedirect(route('admin.dashboard', absolute: false));
        $response->assertSessionHas('status');
    }
}