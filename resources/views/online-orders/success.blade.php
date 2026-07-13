@php
    $formatRupiah = fn ($value) => 'Rp '.number_format($value, 0, ',', '.');
    $qrisImageUrl = ! empty($paymentInfo['qris_image_path']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($paymentInfo['qris_image_path'])
        ? \Illuminate\Support\Facades\Storage::url($paymentInfo['qris_image_path'])
        : null;
@endphp

<x-online-layout :tenant="$tenant" title="Pesanan Berhasil" active="orders" :back-url="route('online-orders.catalog', $tenant)">
    <section class="space-y-5">
        <div class="rounded-xl border border-[#c6c5d2] bg-white p-6 text-center shadow-sm">
            <div class="mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#e7fff2] text-[#005236]">
                <span class="material-symbols-outlined text-4xl" style="font-variation-settings: 'FILL' 1;">check_circle</span>
            </div>
            <h1 class="mt-4 text-2xl font-extrabold leading-tight text-[#001356]">Pesanan berhasil dibuat</h1>
            <p class="mt-2 text-sm text-[#454650]">Nomor pesanan</p>
            <p class="mt-1 text-lg font-extrabold text-[#171c20]">{{ $order->order_number }}</p>
        </div>

        <div class="rounded-xl border border-[#c6c5d2] bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-widest text-[#767681]">Ringkasan</p>
            <div class="mt-4 space-y-2">
                @foreach ($order->items as $item)
                    <div class="flex justify-between gap-3 text-sm">
                        <span class="text-[#454650]">{{ $item->quantity }}x {{ $item->product_name }}</span>
                        <span class="font-bold text-[#171c20]">{{ $formatRupiah($item->line_total) }}</span>
                    </div>
                @endforeach
                <div class="flex justify-between border-t border-[#dfe3e9] pt-3 text-base font-extrabold text-[#001356]">
                    <span>Total</span>
                    <span>{{ $formatRupiah($order->total) }}</span>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-[#c6c5d2] bg-white p-5 shadow-sm">
            <p class="text-xs font-bold uppercase tracking-widest text-[#767681]">Instruksi Pembayaran</p>
            <p class="mt-3 text-sm text-[#454650]">
                Lakukan pembayaran via <span class="font-bold text-[#001356]">{{ $order->paymentMethodLabel() }}</span>, lalu konfirmasi ke kasir melalui WhatsApp.
            </p>

            @if ($order->payment_method === 'qris')
                <div class="mt-4 rounded-xl border border-dashed border-[#c6c5d2] bg-[#f6faff] p-3 text-center">
                    @if ($qrisImageUrl)
                        <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                            <img src="{{ $qrisImageUrl }}" alt="QRIS" class="block w-full h-auto">
                        </div>
                        <a href="{{ $qrisImageUrl }}" download="qris-{{ $order->order_number }}.jpg" class="mt-2 inline-flex items-center gap-1 rounded-full border border-[#c6c5d2] bg-white px-3 py-1.5 text-[11px] font-bold text-[#454650] active:scale-[0.98]">
                            <span class="material-symbols-outlined text-[16px]">download</span>
                            Unduh QRIS
                        </a>
                    @else
                        <div class="mx-auto flex h-28 w-28 items-center justify-center rounded-2xl bg-white text-[#001356] shadow-sm">
                            <span class="material-symbols-outlined text-[72px]">qr_code_2</span>
                        </div>
                    @endif
                    <p class="mt-3 text-sm font-bold text-[#171c20]">{{ $paymentInfo['qris_merchant_name'] ?: 'Scan QRIS untuk membayar' }}</p>
                    <p class="mt-1 text-xs text-[#454650]">Total pembayaran: {{ $formatRupiah($order->total) }}</p>
                </div>
            @else
                <div class="mt-4 rounded-xl border border-dashed border-[#c6c5d2] bg-[#f6faff] p-4">
                    <p class="text-sm font-bold text-[#001356]">{{ $paymentInfo['bank_name'] }}</p>
                    <p class="text-lg font-extrabold text-[#171c20]">{{ $paymentInfo['account_number'] }}</p>
                    <p class="text-sm text-[#454650]">{{ $paymentInfo['account_name'] }}</p>
                    <p class="mt-2 text-xs font-semibold text-[#767681]">Total transfer: {{ $formatRupiah($order->total) }}</p>
                </div>
            @endif
        </div>

        @if ($cashierConfirmationUrl)
            <a href="{{ $cashierConfirmationUrl }}" target="_blank" rel="noopener noreferrer" class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#001356] px-4 py-4 text-sm font-extrabold text-white shadow-sm active:scale-[0.98]">
                <span class="material-symbols-outlined text-[20px]">chat</span>
                Konfirmasi ke WhatsApp
            </a>
        @else
            <div class="rounded-xl border border-[#ffdad6] bg-[#fff4f2] px-4 py-3 text-sm font-semibold text-[#93000a]">
                Nomor WhatsApp kasir belum diatur di pengaturan admin.
            </div>
        @endif

        <a href="{{ route('online-orders.track', $tenant) }}?wa_number={{ urlencode($order->wa_number) }}" class="flex w-full items-center justify-center rounded-xl border border-[#c6c5d2] bg-white px-4 py-4 text-sm font-extrabold text-[#454650]">
            Cek Status Pesanan
        </a>
    </section>
</x-online-layout>
