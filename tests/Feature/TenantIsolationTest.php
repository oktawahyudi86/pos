<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class TenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_only_sees_own_tenant_products(): void
    {
        [$admin, $ownProduct, $otherProduct] = $this->makeTenantProducts();

        $response = $this->actingAs($admin)->get(route('admin.products.index'));

        $response->assertOk();
        $response->assertSee($ownProduct->name);
        $response->assertDontSee($otherProduct->name);
    }

    public function test_cashier_only_sees_own_tenant_products(): void
    {
        [, $cashier, $ownProduct, $otherProduct] = $this->makeTenantProducts(true);

        $response = $this->actingAs($cashier)->get(route('cashier.index'));

        $response->assertOk();
        $response->assertSee($ownProduct->name);
        $response->assertDontSee($otherProduct->name);
    }

    public function test_transactions_page_only_shows_own_tenant_transactions(): void
    {
        [$admin, $ownTransaction, $otherTransaction] = $this->makeTenantTransactions();

        $response = $this->actingAs($admin)->get(route('transactions.index'));

        $response->assertOk();
        $response->assertSee($ownTransaction->invoice_number);
        $response->assertDontSee($otherTransaction->invoice_number);
    }

    private function makeTenantProducts(bool $withCashier = false): array
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $cashierRole = Role::firstOrCreate(['name' => 'Kasir']);

        $tenantA = Tenant::create([
            'name' => 'Tenant A',
            'slug' => 'tenant-a',
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $tenantB = Tenant::create([
            'name' => 'Tenant B',
            'slug' => 'tenant-b',
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $admin = User::factory()->create([
            'tenant_id' => $tenantA->id,
            'status' => 'active',
        ]);
        $admin->assignRole($adminRole);

        if ($withCashier) {
            $cashier = User::factory()->create([
                'tenant_id' => $tenantA->id,
                'status' => 'active',
            ]);
            $cashier->assignRole($cashierRole);
        }

        $tenantACategory = Category::create([
            'tenant_id' => $tenantA->id,
            'name' => 'Minuman A',
            'slug' => 'minuman-a',
            'is_active' => true,
        ]);

        $tenantBCategory = Category::create([
            'tenant_id' => $tenantB->id,
            'name' => 'Minuman B',
            'slug' => 'minuman-b',
            'is_active' => true,
        ]);

        $ownProduct = Product::create([
            'tenant_id' => $tenantA->id,
            'category_id' => $tenantACategory->id,
            'sku' => 'A-001',
            'name' => 'Produk Tenant A',
            'price' => 10000,
            'stock' => 10,
            'is_active' => true,
        ]);

        $otherProduct = Product::create([
            'tenant_id' => $tenantB->id,
            'category_id' => $tenantBCategory->id,
            'sku' => 'B-001',
            'name' => 'Produk Tenant B',
            'price' => 12000,
            'stock' => 10,
            'is_active' => true,
        ]);

        return $withCashier
            ? [$admin, $cashier, $ownProduct, $otherProduct]
            : [$admin, $ownProduct, $otherProduct];
    }

    private function makeTenantTransactions(): array
    {
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);

        $tenantA = Tenant::create([
            'name' => 'Tenant A',
            'slug' => 'tenant-a',
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $tenantB = Tenant::create([
            'name' => 'Tenant B',
            'slug' => 'tenant-b',
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $admin = User::factory()->create([
            'tenant_id' => $tenantA->id,
            'status' => 'active',
        ]);
        $admin->assignRole($adminRole);

        $cashierA = User::factory()->create([
            'tenant_id' => $tenantA->id,
            'status' => 'active',
        ]);
        $cashierA->assignRole(Role::firstOrCreate(['name' => 'Kasir']));

        $cashierB = User::factory()->create([
            'tenant_id' => $tenantB->id,
            'status' => 'active',
        ]);
        $cashierB->assignRole(Role::firstOrCreate(['name' => 'Kasir']));

        $ownTransaction = Transaction::create([
            'tenant_id' => $tenantA->id,
            'invoice_number' => 'INV-A-001',
            'user_id' => $cashierA->id,
            'payment_method' => 'cash',
            'status' => 'paid',
            'subtotal' => 10000,
            'discount_type' => 'none',
            'discount_value' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 10000,
            'paid_amount' => 10000,
            'change_amount' => 0,
            'paid_at' => now(),
        ]);

        $otherTransaction = Transaction::create([
            'tenant_id' => $tenantB->id,
            'invoice_number' => 'INV-B-001',
            'user_id' => $cashierB->id,
            'payment_method' => 'cash',
            'status' => 'paid',
            'subtotal' => 12000,
            'discount_type' => 'none',
            'discount_value' => 0,
            'discount_amount' => 0,
            'tax_amount' => 0,
            'total' => 12000,
            'paid_amount' => 12000,
            'change_amount' => 0,
            'paid_at' => now(),
        ]);

        return [$admin, $ownTransaction, $otherTransaction];
    }
}
