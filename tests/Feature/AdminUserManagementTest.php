<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_cashiers_for_own_tenant(): void
    {
        [$admin, $cashier] = $this->adminWithCashier();

        $response = $this->actingAs($admin)->get(route('admin.users.index'));

        $response->assertOk();
        $response->assertSee($cashier->name);
    }

    public function test_admin_can_create_active_cashier_for_own_tenant(): void
    {
        [$admin] = $this->adminWithCashier();

        $response = $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Kasir Baru',
            'email' => 'kasirbaru@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect(route('admin.users.index'));

        $cashier = User::where('email', 'kasirbaru@example.com')->firstOrFail();

        $this->assertSame($admin->tenant_id, $cashier->tenant_id);
        $this->assertSame('active', $cashier->status);
        $this->assertTrue($cashier->hasRole('Kasir'));
    }

    public function test_admin_cannot_edit_cashier_from_other_tenant(): void
    {
        [$admin] = $this->adminWithCashier();
        [, $otherCashier] = $this->adminWithCashier('other');

        $response = $this->actingAs($admin)->get(route('admin.users.edit', $otherCashier));

        $response->assertNotFound();
    }

    public function test_admin_cannot_edit_admin_user_through_cashier_menu(): void
    {
        [$admin] = $this->adminWithCashier();

        $response = $this->actingAs($admin)->get(route('admin.users.edit', $admin));

        $response->assertNotFound();
    }

    public function test_cashier_cannot_access_admin_user_management(): void
    {
        [, $cashier] = $this->adminWithCashier();

        $response = $this->actingAs($cashier)->get(route('admin.users.index'));

        $response->assertForbidden();
    }

    private function adminWithCashier(string $suffix = 'main'): array
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $cashierRole = Role::firstOrCreate(['name' => 'Kasir']);

        $tenant = Tenant::create([
            'name' => "Cafe {$suffix}",
            'slug' => "cafe-{$suffix}",
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $admin = User::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => 'active',
            'email' => "admin-{$suffix}@example.com",
        ]);
        $admin->assignRole($adminRole);

        $cashier = User::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => 'active',
            'email' => "cashier-{$suffix}@example.com",
        ]);
        $cashier->assignRole($cashierRole);

        return [$admin, $cashier, $tenant];
    }
}
