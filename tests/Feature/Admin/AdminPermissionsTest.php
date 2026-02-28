<?php

namespace Tests\Feature\Admin;

use App\Models\AdminPermission;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPermissionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_without_permission_cannot_access_cv_verifications(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        AdminPermission::create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)->get('/admin/cv-verifications');

        $response->assertRedirect(route('admin.dashboard', absolute: false));
        $response->assertSessionHas('status');
    }

    public function test_admin_with_verifications_permission_can_access_cv_verifications(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        AdminPermission::create(['user_id' => $admin->id, 'manage_verifications' => true]);

        $response = $this->actingAs($admin)->get('/admin/cv-verifications');

        $response->assertOk();
        $response->assertSee('طلبات توثيق السيرة الذاتية');
    }

    public function test_master_admin_can_access_cv_verifications_without_permission_row(): void
    {
        $master = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'email' => 'mustafa@teamiapps.com',
        ]);

        $response = $this->actingAs($master)->get('/admin/cv-verifications');

        $response->assertOk();
    }

    public function test_admin_users_management_requires_permission(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        AdminPermission::create(['user_id' => $admin->id]);

        $response = $this->actingAs($admin)->get('/admin/admin-users');

        $response->assertRedirect(route('admin.dashboard', absolute: false));
        $response->assertSessionHas('status');
    }

    public function test_admin_with_admin_users_permission_can_access_admin_users_index(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        AdminPermission::create(['user_id' => $admin->id, 'manage_admin_users' => true]);

        $response = $this->actingAs($admin)->get('/admin/admin-users');

        $response->assertOk();
        $response->assertSee('إدارة المستخدمين الإداريين');
    }

    public function test_master_admin_cannot_be_deleted_by_other_admin(): void
    {
        $master = User::factory()->create([
            'role' => 'admin',
            'status' => 'active',
            'email' => 'mustafa@teamiapps.com',
        ]);

        $admin = User::factory()->create(['role' => 'admin', 'status' => 'active']);
        AdminPermission::create(['user_id' => $admin->id, 'manage_admin_users' => true]);

        // Provide a valid CSRF token (web middleware) so we can test the actual authorization rule.
        $token = 'test-token';
        $response = $this
            ->actingAs($admin)
            ->withSession(['_token' => $token])
            ->from(route('admin.admin_users.index', absolute: false))
            ->delete(route('admin.admin_users.destroy', $master, absolute: false), ['_token' => $token]);

        $response->assertRedirect(route('admin.admin_users.index', absolute: false));
        $response->assertSessionHas('status', 'لا يمكن حذف حساب الماستر أدمن.');

        $this->assertDatabaseHas('users', ['id' => $master->id]);
    }
}
