@php
    $formatRupiah = fn ($value) => 'Rp '.number_format($value, 0, ',', '.');
@endphp

<x-online-layout :tenant="$tenant" title="Pesanan Berhasil" active="orders">
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
            <p class="mt-3 text-sm text-[#454650]">Kasir akan mengirim reminder pembayaran melalui WhatsApp. Siapkan pembayaran manual ke rekening berikut.</p>
            <div class="mt-4 rounded-xl border border-dashed border-[#c6c5d2] bg-[#f6faff] p-4">
                <p class="text-sm font-bold text-[#001356]">{{ $paymentInfo['bank_name'] }}</p>
                <p class="text-lg font-extrabold text-[#171c20]">{{ $paymentInfo['account_number'] }}</p>
                <p class="text-sm text-[#454650]">{{ $paymentInfo['account_name'] }}</p>
            </div>
        </div>

        <a href="{{ route('online-orders.track', $tenant) }}?wa_number={{ urlencode($order->wa_number) }}" class="flex w-full items-center justify-center rounded-xl bg-[#001356] px-4 py-4 text-sm font-extrabold text-white shadow-sm">
            Cek Status Pesanan
        </a>
    </section>
</x-online-layout>
