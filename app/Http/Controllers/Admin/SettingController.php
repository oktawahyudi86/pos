<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Models\Tenant;
use App\Services\DeliveryCoverageService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Str;

class SettingController extends Controller
{
    public function edit(): View
    {
        $tenantId = auth()->user()->tenant_id;

        $paymentMethods = Setting::getValue('payment_methods', [
            'cash' => true,
            'qris' => true,
        ], $tenantId);

        $receipt = Setting::getValue('receipt', [
            'logo_path' => null,
            'cafe_name' => auth()->user()->tenant?->name ?? config('app.name', 'Keijora POS'),
            'customer_username' => auth()->user()->tenant?->slug ?? '',
            'address' => '',
            'phone' => '',
            'footer_note' => 'Terima kasih atas kunjungan Anda.',
        ], $tenantId);

        $onlineBanner = Setting::getValue('online_banner', [
            'image_path' => null,
            'title' => '',
            'subtitle' => '',
        ], $tenantId);

        $onlinePayment = Setting::getValue('online_payment', [
            'methods' => [
                'transfer_bank' => true,
                'qris' => true,
            ],
            'bank_name' => 'Mandiri',
            'account_number' => '1234567890',
            'account_name' => auth()->user()->tenant?->name ?? config('app.name', 'Keijora POS'),
            'qris_image_path' => null,
            'qris_merchant_name' => '',
            'cashier_wa_number' => '',
        ], $tenantId);

        $onlineDelivery = app(DeliveryCoverageService::class)->settingsForTenant($tenantId);

        return view('admin.settings.edit', compact('paymentMethods', 'receipt', 'onlineBanner', 'onlinePayment', 'onlineDelivery'));
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'payment_methods' => ['nullable', 'array'],
            'receipt_logo' => ['nullable', 'image', 'max:2048'],
            'cafe_name' => ['required', 'string', 'max:120'],
            'customer_username' => ['required', 'string', 'max:120'],
            'address' => ['nullable', 'string', 'max:500'],
            'phone' => ['nullable', 'string', 'max:50'],
            'footer_note' => ['nullable', 'string', 'max:300'],
            'online_banner_image' => ['nullable', 'image', 'max:3072'],
            'online_banner_title' => ['nullable', 'string', 'max:120'],
            'online_banner_subtitle' => ['nullable', 'string', 'max:200'],
            'online_payment_methods' => ['nullable', 'array'],
            'online_bank_name' => ['nullable', 'string', 'max:120'],
            'online_account_number' => ['nullable', 'string', 'max:50'],
            'online_account_name' => ['nullable', 'string', 'max:120'],
            'online_qris_image' => ['nullable', 'image', 'max:3072'],
            'online_qris_merchant_name' => ['nullable', 'string', 'max:120'],
            'online_cashier_wa_number' => ['nullable', 'string', 'max:20'],
            'online_delivery_enabled' => ['nullable', 'boolean'],
            'online_delivery_max_radius_km' => ['nullable', 'numeric', 'min:0.1', 'max:100'],
            'online_delivery_store_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'online_delivery_store_longitude' => ['nullable', 'numeric', 'between:-180,180'],
        ]);

        if ($request->boolean('online_delivery_enabled')) {
            $request->validate([
                'online_delivery_max_radius_km' => ['required', 'numeric', 'min:0.1', 'max:100'],
                'online_delivery_store_latitude' => ['required', 'numeric', 'between:-90,90'],
                'online_delivery_store_longitude' => ['required', 'numeric', 'between:-180,180'],
            ]);
        }

        $activePaymentMethods = $validated['payment_methods'] ?? [];
        $tenantId = auth()->user()->tenant_id;

        Setting::setValue('payment_methods', [
            'cash' => in_array('cash', $activePaymentMethods, true),
            'qris' => in_array('qris', $activePaymentMethods, true),
        ], $tenantId);

        $receipt = Setting::getValue('receipt', [], $tenantId);

        if ($request->hasFile('receipt_logo')) {
            $receipt['logo_path'] = $request->file('receipt_logo')->store('receipt', 'public');
        }

        Setting::setValue('receipt', [
            'logo_path' => $receipt['logo_path'] ?? null,
            'cafe_name' => $validated['cafe_name'],
            'customer_username' => $validated['customer_username'],
            'address' => $validated['address'] ?? '',
            'phone' => $validated['phone'] ?? '',
            'footer_note' => $validated['footer_note'] ?? '',
        ], $tenantId);

        Tenant::query()
            ->whereKey($tenantId)
            ->update([
                'name' => $validated['cafe_name'],
                'slug' => $this->uniqueTenantSlug($validated['customer_username'], $tenantId),
            ]);

        $onlineBanner = Setting::getValue('online_banner', [], $tenantId);
        if ($request->hasFile('online_banner_image')) {
            $onlineBanner['image_path'] = $request->file('online_banner_image')->store('online-banner', 'public');
        }

        Setting::setValue('online_banner', [
            'image_path' => $onlineBanner['image_path'] ?? null,
            'title' => trim((string) ($validated['online_banner_title'] ?? '')),
            'subtitle' => trim((string) ($validated['online_banner_subtitle'] ?? '')),
        ], $tenantId);

        $onlinePaymentMethods = $validated['online_payment_methods'] ?? [];
        $onlinePayment = Setting::getValue('online_payment', [], $tenantId);

        if ($request->hasFile('online_qris_image')) {
            $onlinePayment['qris_image_path'] = $request->file('online_qris_image')->store('online-payment', 'public');
        }

        Setting::setValue('online_payment', [
            'methods' => [
                'transfer_bank' => in_array('transfer_bank', $onlinePaymentMethods, true),
                'qris' => in_array('qris', $onlinePaymentMethods, true),
            ],
            'bank_name' => trim((string) ($validated['online_bank_name'] ?? '')),
            'account_number' => trim((string) ($validated['online_account_number'] ?? '')),
            'account_name' => trim((string) ($validated['online_account_name'] ?? '')),
            'qris_image_path' => $onlinePayment['qris_image_path'] ?? null,
            'qris_merchant_name' => trim((string) ($validated['online_qris_merchant_name'] ?? '')),
            'cashier_wa_number' => preg_replace('/\D+/', '', (string) ($validated['online_cashier_wa_number'] ?? '')),
        ], $tenantId);

        Setting::setValue('online_delivery', [
            'enabled' => $request->boolean('online_delivery_enabled'),
            'max_radius_km' => $request->boolean('online_delivery_enabled')
                ? (float) $validated['online_delivery_max_radius_km']
                : null,
            'store_latitude' => $request->boolean('online_delivery_enabled')
                ? (float) $validated['online_delivery_store_latitude']
                : null,
            'store_longitude' => $request->boolean('online_delivery_enabled')
                ? (float) $validated['online_delivery_store_longitude']
                : null,
        ], $tenantId);

        return back()->with('status', 'Pengaturan berhasil disimpan.');
    }

    private function uniqueTenantSlug(string $name, int $tenantId): string
    {
        $base = Str::slug($name);
        $slug = $base !== '' ? $base : 'tenant';
        $counter = 2;

        while (Tenant::query()
            ->where('slug', $slug)
            ->whereKeyNot($tenantId)
            ->exists()) {
            $slug = $base !== '' ? $base.'-'.$counter : 'tenant-'.$counter;
            $counter++;
        }

        return $slug;
    }
}
