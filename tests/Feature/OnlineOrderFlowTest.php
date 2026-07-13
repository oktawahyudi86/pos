<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\OnlineOrder;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Tenant;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OnlineOrderFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_checkout_creates_incoming_order_without_reducing_stock(): void
    {
        [$tenant, $product] = $this->makeTenantProduct(stock: 8);

        $this->post(route('online-orders.cart.store', $tenant), [
            'product_id' => $product->id,
            'quantity' => 2,
        ])->assertRedirect();

        $response = $this->post(route('online-orders.checkout', $tenant), [
            'customer_name' => 'Nia',
            'wa_number' => '081234567890',
            'address' => 'Jl. Customer No. 1',
        ]);

        $order = OnlineOrder::first();

        $response->assertRedirect(route('online-orders.success', [$tenant, $order]));
        $this->assertSame(OnlineOrder::STATUS_PESANAN_MASUK, $order->status);
        $this->assertSame(1, $order->items()->count());
        $this->assertSame(8, $product->refresh()->stock);
    }

    public function test_cashier_payment_reminder_moves_order_and_reserves_stock_once(): void
    {
        [$cashier, $order, $product] = $this->makeCashierOrder();

        $this->actingAs($cashier)
            ->patch(route('cashier.orders.payment-reminder', $order))
            ->assertRedirect()
            ->assertSessionHas('open_order_detail', $order->id);

        $this->assertSame(7, $product->refresh()->stock);
        $this->assertSame(OnlineOrder::STATUS_KONFIRMASI_PEMBAYARAN, $order->refresh()->status);
        $this->assertNotNull($order->payment_reminded_at);
        $this->assertDatabaseHas('online_order_status_logs', [
            'online_order_id' => $order->id,
            'status' => OnlineOrder::STATUS_KONFIRMASI_PEMBAYARAN,
            'changed_by' => $cashier->id,
        ]);

        $this->actingAs($cashier)
            ->patch(route('cashier.orders.payment-reminder', $order))
            ->assertUnprocessable();

        $this->assertSame(7, $product->refresh()->stock);
    }

    public function test_online_order_status_flow_cannot_skip_steps(): void
    {
        [$cashier, $order] = $this->makeCashierOrder();

        $this->actingAs($cashier)
            ->patch(route('cashier.orders.process', $order))
            ->assertUnprocessable();

        $this->actingAs($cashier)->patch(route('cashier.orders.payment-reminder', $order))->assertRedirect();
        $this->assertSame(OnlineOrder::STATUS_KONFIRMASI_PEMBAYARAN, $order->refresh()->status);

        $this->actingAs($cashier)
            ->patch(route('cashier.orders.ship', $order))
            ->assertUnprocessable();

        $this->actingAs($cashier)->patch(route('cashier.orders.process', $order))->assertRedirect();
        $this->assertSame(OnlineOrder::STATUS_SEDANG_DIPROSES, $order->refresh()->status);

        $this->actingAs($cashier)->patch(route('cashier.orders.ship', $order))->assertRedirect();
        $this->assertSame(OnlineOrder::STATUS_DIKIRIM, $order->refresh()->status);
    }

    public function test_finish_creates_online_transaction_once_order_is_shipped(): void
    {
        [$cashier, $order] = $this->makeCashierOrder();

        $this->actingAs($cashier)->patch(route('cashier.orders.payment-reminder', $order))->assertRedirect();
        $this->actingAs($cashier)->patch(route('cashier.orders.process', $order))->assertRedirect();
        $this->actingAs($cashier)->patch(route('cashier.orders.ship', $order))->assertRedirect();

        $response = $this->actingAs($cashier)->patch(route('cashier.orders.finish', $order));

        $transaction = Transaction::first();

        $response->assertRedirect(route('transactions.show', $transaction));
        $this->assertSame(OnlineOrder::STATUS_SELESAI, $order->refresh()->status);
        $this->assertSame('online', $transaction->channel);
        $this->assertSame($order->total, $transaction->total);
        $this->assertSame(1, $transaction->items()->count());

        $this->actingAs($cashier)
            ->patch(route('cashier.orders.finish', $order))
            ->assertUnprocessable();

        $this->assertSame(1, Transaction::count());
    }

    public function test_cashier_can_cancel_order_and_restore_reserved_stock(): void
    {
        [$cashier, $order, $product] = $this->makeCashierOrder();

        $this->actingAs($cashier)->patch(route('cashier.orders.payment-reminder', $order))->assertRedirect();
        $this->assertSame(7, $product->refresh()->stock);

        $this->actingAs($cashier)
            ->patch(route('cashier.orders.cancel', $order))
            ->assertRedirect();

        $this->assertSame(OnlineOrder::STATUS_DIBATALKAN, $order->refresh()->status);
        $this->assertSame(10, $product->refresh()->stock);
        $this->assertDatabaseHas('online_order_status_logs', [
            'online_order_id' => $order->id,
            'status' => OnlineOrder::STATUS_DIBATALKAN,
            'changed_by' => $cashier->id,
        ]);
    }

    public function test_finished_order_cannot_be_cancelled(): void
    {
        [$cashier, $order] = $this->makeCashierOrder();

        $this->actingAs($cashier)->patch(route('cashier.orders.payment-reminder', $order))->assertRedirect();
        $this->actingAs($cashier)->patch(route('cashier.orders.process', $order))->assertRedirect();
        $this->actingAs($cashier)->patch(route('cashier.orders.ship', $order))->assertRedirect();
        $this->actingAs($cashier)->patch(route('cashier.orders.finish', $order))->assertRedirect();

        $this->actingAs($cashier)
            ->patch(route('cashier.orders.cancel', $order))
            ->assertUnprocessable();

        $this->assertSame(OnlineOrder::STATUS_SELESAI, $order->refresh()->status);
    }

    public function test_checkout_is_blocked_when_delivery_location_is_outside_radius(): void
    {
        [$tenant, $product] = $this->makeTenantProduct(stock: 8);

        Setting::setValue('online_delivery', [
            'enabled' => true,
            'max_radius_km' => 3,
            'store_latitude' => -7.7956,
            'store_longitude' => 110.3695,
        ], $tenant->id);

        $this->post(route('online-orders.cart.store', $tenant), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect();

        $response = $this->from(route('online-orders.checkout.form', $tenant))
            ->post(route('online-orders.checkout', $tenant), [
                'customer_name' => 'Nia',
                'wa_number' => '081234567890',
                'address' => 'Jl. Luar Jangkauan',
                'delivery_latitude' => -7.7506,
                'delivery_longitude' => 110.3695,
                'payment_method' => 'manual_transfer',
            ]);

        $response
            ->assertRedirect(route('online-orders.checkout.form', $tenant))
            ->assertSessionHasErrors('address');

        $this->assertStringContainsString(
            'belum tercover',
            session('errors')->first('address')
        );
        $this->assertSame(0, OnlineOrder::count());
    }

    public function test_checkout_is_allowed_when_delivery_location_is_within_radius(): void
    {
        [$tenant, $product] = $this->makeTenantProduct(stock: 8);

        Setting::setValue('online_delivery', [
            'enabled' => true,
            'max_radius_km' => 5,
            'store_latitude' => -7.7956,
            'store_longitude' => 110.3695,
        ], $tenant->id);

        $this->post(route('online-orders.cart.store', $tenant), [
            'product_id' => $product->id,
            'quantity' => 1,
        ])->assertRedirect();

        $response = $this->post(route('online-orders.checkout', $tenant), [
            'customer_name' => 'Nia',
            'wa_number' => '081234567890',
            'address' => 'Jl. Dekat Toko',
            'delivery_latitude' => -7.7960,
            'delivery_longitude' => 110.3700,
            'payment_method' => 'manual_transfer',
        ]);

        $order = OnlineOrder::first();

        $response->assertRedirect(route('online-orders.success', [$tenant, $order]));
        $this->assertNotNull($order);
    }

    public function test_online_order_views_render(): void
    {
        [$cashier, $order] = $this->makeCashierOrder();
        $tenant = $order->tenant;

        $this->get(route('online-orders.catalog', $tenant))->assertOk();
        $this->get(route('online-orders.checkout.form', $tenant))->assertOk();
        $this->get(route('online-orders.track', [$tenant, 'wa_number' => $order->wa_number]))->assertOk();
        $this->actingAs($cashier)->get(route('cashier.orders.index'))->assertOk();
    }

    private function makeCashierOrder(): array
    {
        [$tenant, $product] = $this->makeTenantProduct(stock: 10);
        $cashier = $this->makeCashier($tenant);

        $order = OnlineOrder::create([
            'tenant_id' => $tenant->id,
            'order_number' => 'ORD-TEST-001',
            'customer_name' => 'Nia',
            'wa_number' => '081234567890',
            'address' => 'Jl. Customer No. 1',
            'status' => OnlineOrder::STATUS_PESANAN_MASUK,
            'payment_method' => 'manual_transfer',
            'subtotal' => 45000,
            'shipping_cost' => 10000,
            'total' => 55000,
            'placed_at' => now(),
        ]);

        $order->items()->create([
            'product_id' => $product->id,
            'product_name' => $product->name,
            'base_price' => 15000,
            'unit_price' => 15000,
            'quantity' => 3,
            'line_total' => 45000,
        ]);

        $order->statusLogs()->create([
            'status' => OnlineOrder::STATUS_PESANAN_MASUK,
            'changed_at' => now(),
        ]);

        return [$cashier, $order, $product];
    }

    private function makeTenantProduct(int $stock): array
    {
        $tenant = Tenant::create([
            'name' => 'Keijora Demo',
            'slug' => 'keijora-demo',
            'status' => 'active',
            'approved_at' => now(),
        ]);

        $category = Category::create([
            'tenant_id' => $tenant->id,
            'name' => 'Minuman',
            'slug' => 'minuman',
            'is_active' => true,
        ]);

        $product = Product::create([
            'tenant_id' => $tenant->id,
            'category_id' => $category->id,
            'sku' => 'DRK-001',
            'name' => 'Es Kopi Susu',
            'price' => 15000,
            'stock' => $stock,
            'is_active' => true,
        ]);

        return [$tenant, $product];
    }

    private function makeCashier(Tenant $tenant): User
    {
        $role = Role::firstOrCreate(['name' => 'Kasir']);

        $cashier = User::factory()->create([
            'tenant_id' => $tenant->id,
            'status' => 'active',
        ]);
        $cashier->assignRole($role);

        return $cashier;
    }
}
