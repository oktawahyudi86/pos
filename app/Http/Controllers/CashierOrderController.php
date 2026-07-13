<?php

namespace App\Http\Controllers;

use App\Models\OnlineOrder;
use App\Models\Product;
use App\Models\Setting;
use App\Models\Transaction;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Illuminate\Support\Str;

class CashierOrderController extends Controller
{
    public function index(Request $request): View
    {
        $tenantId = auth()->user()->tenant_id;
        $statuses = OnlineOrder::statuses();
        $activeStatus = $request->string('status')->toString();
        $activeStatus = in_array($activeStatus, $statuses, true) ? $activeStatus : '';

        $counts = collect($statuses)
            ->mapWithKeys(fn ($status) => [
                $status => OnlineOrder::query()
                    ->where('tenant_id', $tenantId)
                    ->where('status', $status)
                    ->count(),
            ]);

        $orders = OnlineOrder::query()
            ->where('tenant_id', $tenantId)
            ->with(['items', 'statusLogs.changer', 'tenant'])
            ->when($activeStatus !== '', fn ($query) => $query->where('status', $activeStatus))
            ->latest('placed_at')
            ->get();

        $paymentReminders = $orders
            ->filter(fn (OnlineOrder $order) => $order->status === OnlineOrder::STATUS_PESANAN_MASUK)
            ->mapWithKeys(fn (OnlineOrder $order) => [
                $order->id => [
                    'message' => $this->makePaymentReminderMessage($order),
                    'url' => $this->makePaymentReminderUrl($order),
                    'action' => route('cashier.orders.payment-reminder', $order),
                ],
            ]);

        return view('cashier.orders.index', compact('orders', 'counts', 'statuses', 'activeStatus', 'paymentReminders'));
    }

    public function paymentReminder(OnlineOrder $order): RedirectResponse
    {
        $this->assertTenantOrder($order);

        DB::transaction(function () use ($order) {
            $lockedOrder = OnlineOrder::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            abort_unless($lockedOrder->status === OnlineOrder::STATUS_PESANAN_MASUK, 422, 'Status pesanan tidak valid.');

            $this->reserveStock($lockedOrder);
            $this->transition($lockedOrder, OnlineOrder::STATUS_KONFIRMASI_PEMBAYARAN);
        });

        $order->refresh()->loadMissing('items');

        return back()
            ->with('status', 'Konfirmasi pembayaran tercatat dan stok sudah dikurangi.')
            ->with('open_order_detail', $order->id);
    }

    public function process(OnlineOrder $order): RedirectResponse
    {
        $this->assertTenantOrder($order);
        abort_unless($order->status === OnlineOrder::STATUS_KONFIRMASI_PEMBAYARAN, 422, 'Status pesanan tidak valid.');
        $this->transition($order, OnlineOrder::STATUS_SEDANG_DIPROSES);

        return back()
            ->with('status', 'Pesanan mulai diproses.')
            ->with('open_order_detail', $order->id);
    }

    public function ship(OnlineOrder $order): RedirectResponse
    {
        $this->assertTenantOrder($order);
        abort_unless($order->status === OnlineOrder::STATUS_SEDANG_DIPROSES, 422, 'Status pesanan tidak valid.');
        $this->transition($order, OnlineOrder::STATUS_DIKIRIM);

        return back()
            ->with('status', 'Pesanan masuk tahap dikirim.')
            ->with('open_order_detail', $order->id);
    }

    public function finish(OnlineOrder $order): RedirectResponse
    {
        $this->assertTenantOrder($order);

        $transaction = DB::transaction(function () use ($order) {
            $lockedOrder = OnlineOrder::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->with('items')
                ->firstOrFail();

            abort_unless($lockedOrder->status === OnlineOrder::STATUS_DIKIRIM, 422, 'Status pesanan tidak valid.');

            $transaction = Transaction::create(array_filter([
                'tenant_id' => $lockedOrder->tenant_id,
                'invoice_number' => $this->makeInvoiceNumber(),
                'receipt_code' => Schema::hasColumn('transactions', 'receipt_code') ? $this->makeReceiptCode() : null,
                'channel' => 'online',
                'user_id' => auth()->id(),
                'customer_name' => $lockedOrder->customer_name,
                'customer_phone' => $lockedOrder->wa_number,
                'payment_method' => 'qris',
                'status' => 'paid',
                'subtotal' => $lockedOrder->subtotal,
                'discount_type' => 'none',
                'discount_value' => 0,
                'discount_amount' => 0,
                'tax_amount' => 0,
                'total' => $lockedOrder->total,
                'paid_amount' => $lockedOrder->total,
                'change_amount' => 0,
                'paid_at' => now(),
            ], fn ($value) => $value !== null));

            foreach ($lockedOrder->items as $item) {
                $transactionItem = $transaction->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product_name,
                    'base_price' => $item->base_price,
                    'unit_price' => $item->unit_price,
                    'quantity' => $item->quantity,
                    'line_total' => $item->line_total,
                    'note' => $item->note,
                ]);

                foreach (collect($item->variant_payload ?? []) as $variant) {
                    $transactionItem->variantOptions()->create([
                        'variant_option_id' => $variant['id'] ?? null,
                        'variant_group_name' => $variant['group'] ?? '-',
                        'option_name' => $variant['name'] ?? '-',
                        'price_delta' => (int) ($variant['price_delta'] ?? 0),
                    ]);
                }

                foreach (collect($item->addon_payload ?? []) as $addon) {
                    $transactionItem->addons()->create([
                        'addon_id' => $addon['id'] ?? null,
                        'addon_name' => $addon['name'] ?? '-',
                        'price' => (int) ($addon['price'] ?? 0),
                    ]);
                }
            }

            $this->transition($lockedOrder, OnlineOrder::STATUS_SELESAI);

            return $transaction;
        });

        return redirect()->route('transactions.show', $transaction)->with('status', 'Pesanan selesai dan sudah masuk ke transaksi.');
    }

    public function cancel(OnlineOrder $order): RedirectResponse
    {
        $this->assertTenantOrder($order);

        DB::transaction(function () use ($order) {
            $lockedOrder = OnlineOrder::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->with('items')
                ->firstOrFail();

            abort_unless($this->canCancel($lockedOrder), 422, 'Pesanan tidak bisa dibatalkan.');

            if ($this->hasReservedStock($lockedOrder)) {
                $this->restoreStock($lockedOrder);
            }

            $this->transition($lockedOrder, OnlineOrder::STATUS_DIBATALKAN);
        });

        return back()->with('status', 'Pesanan dibatalkan.');
    }

    private function transition(OnlineOrder $order, string $status): void
    {
        $timestampField = match ($status) {
            OnlineOrder::STATUS_KONFIRMASI_PEMBAYARAN => 'payment_reminded_at',
            OnlineOrder::STATUS_SEDANG_DIPROSES => 'processing_at',
            OnlineOrder::STATUS_DIKIRIM => 'out_for_delivery_at',
            OnlineOrder::STATUS_SELESAI => 'finished_at',
            default => null,
        };

        $payload = ['status' => $status];

        if ($timestampField) {
            $payload[$timestampField] = now();
        }

        $order->update($payload);
        $order->statusLogs()->create([
            'status' => $status,
            'changed_by' => auth()->id(),
            'changed_at' => now(),
        ]);
    }

    private function reserveStock(OnlineOrder $order): void
    {
        $order->loadMissing('items');

        $quantityByProduct = $order->items
            ->groupBy('product_id')
            ->map(fn (Collection $items) => $items->sum('quantity'));

        foreach ($quantityByProduct as $productId => $quantity) {
            $product = Product::query()
                ->where('tenant_id', $order->tenant_id)
                ->lockForUpdate()
                ->find($productId);

            if (! $product || $product->stock < $quantity) {
                abort(422, 'Stok produk tidak cukup.');
            }

            $product->decrement('stock', $quantity);
        }
    }

    private function restoreStock(OnlineOrder $order): void
    {
        $quantityByProduct = $order->items
            ->groupBy('product_id')
            ->map(fn (Collection $items) => $items->sum('quantity'));

        foreach ($quantityByProduct as $productId => $quantity) {
            Product::query()
                ->where('tenant_id', $order->tenant_id)
                ->whereKey($productId)
                ->lockForUpdate()
                ->first()
                ?->increment('stock', $quantity);
        }
    }

    private function canCancel(OnlineOrder $order): bool
    {
        return ! in_array($order->status, [
            OnlineOrder::STATUS_SELESAI,
            OnlineOrder::STATUS_DIBATALKAN,
        ], true);
    }

    private function hasReservedStock(OnlineOrder $order): bool
    {
        return in_array($order->status, [
            OnlineOrder::STATUS_KONFIRMASI_PEMBAYARAN,
            OnlineOrder::STATUS_SEDANG_DIPROSES,
            OnlineOrder::STATUS_DIKIRIM,
        ], true);
    }

    private function makePaymentReminderMessage(OnlineOrder $order): string
    {
        $paymentInfo = Setting::getValue('online_payment', [
            'methods' => [
                'transfer_bank' => true,
                'qris' => true,
            ],
            'bank_name' => 'Mandiri',
            'account_number' => '1234567890',
            'account_name' => $order->tenant?->name ?? config('app.name', 'Keijora POS'),
            'qris_merchant_name' => '',
            'cashier_wa_number' => '',
        ], $order->tenant_id);

        $items = $order->items
            ->map(fn ($item) => "- {$item->quantity}x {$item->product_name} ({$this->formatRupiah($item->line_total)})")
            ->join("\n");

        return implode("\n", array_filter([
            "Halo {$order->customer_name}, pesanan {$order->order_number} sudah kami terima.",
            '',
            'Ringkasan pesanan:',
            $items,
            '',
            'Subtotal: '.$this->formatRupiah($order->subtotal),
            'Ongkir: '.$this->formatRupiah($order->shipping_cost),
            'Total: '.$this->formatRupiah($order->total),
            '',
            'Metode pembayaran: '.$order->paymentMethodLabel(),
            'Alamat: '.$order->address,
            $order->deliveryAreaSummary() ? 'Wilayah: '.$order->deliveryAreaSummary() : null,
            $order->address_detail ? 'Detail: '.$order->address_detail : null,
            $order->deliveryDirectionsUrl() ? 'Navigasi Maps: '.$order->deliveryDirectionsUrl() : ($order->deliveryMapUrl() ? 'Maps: '.$order->deliveryMapUrl() : null),
            '',
            'Silakan lakukan pembayaran ke:',
            $order->payment_method === 'qris'
                ? 'QRIS'.($paymentInfo['qris_merchant_name'] ? ' - '.$paymentInfo['qris_merchant_name'] : '')
                : ($paymentInfo['bank_name'] ?? 'Bank').' - '.($paymentInfo['account_number'] ?? '-'),
            $order->payment_method === 'qris'
                ? null
                : 'a.n. '.($paymentInfo['account_name'] ?? '-'),
            '',
            'Setelah transfer, balas pesan ini dengan bukti pembayaran ya. Terima kasih.',
        ], fn ($line) => $line !== null));
    }

    private function makePaymentReminderUrl(OnlineOrder $order): string
    {
        return 'https://wa.me/'.$this->normalizeWhatsappNumber($order->wa_number).'?text='.rawurlencode($this->makePaymentReminderMessage($order));
    }

    private function normalizeWhatsappNumber(string $number): string
    {
        $digits = preg_replace('/\D+/', '', $number) ?: '';

        if (str_starts_with($digits, '0')) {
            return '62'.substr($digits, 1);
        }

        return $digits;
    }

    private function formatRupiah(int $value): string
    {
        return 'Rp '.number_format($value, 0, ',', '.');
    }

    private function assertTenantOrder(OnlineOrder $order): void
    {
        abort_unless((int) $order->tenant_id === (int) auth()->user()->tenant_id, 404);
    }

    private function makeInvoiceNumber(): string
    {
        do {
            $invoice = 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
        } while (Transaction::query()->where('invoice_number', $invoice)->exists());

        return $invoice;
    }

    private function makeReceiptCode(): string
    {
        do {
            $code = Str::upper(Str::random(8));
        } while (Transaction::query()->where('receipt_code', $code)->exists());

        return $code;
    }
}
