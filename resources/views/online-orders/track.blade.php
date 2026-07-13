@php
    $formatRupiah = fn ($value) => 'Rp '.number_format($value, 0, ',', '.');
    $statuses = \App\Models\OnlineOrder::progressStatuses();
    $labels = \App\Models\OnlineOrder::statusLabels();
@endphp

<x-online-layout :tenant="$tenant" title="Cek Pesanan" active="orders">
    <section class="space-y-5">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.3em] text-[#767681]">Cek Pesanan</p>
            <h1 class="mt-1 text-2xl font-extrabold leading-tight text-[#001356]">Lacak status pesanan</h1>
        </div>

        <form method="GET" action="{{ route('online-orders.track', $tenant) }}" class="rounded-xl border border-[#c6c5d2] bg-white p-4 shadow-sm">
            <label class="mb-2 block text-sm font-bold text-[#171c20]">No. WhatsApp</label>
            <div class="flex gap-2">
                <input id="track-wa-number" name="wa_number" value="{{ $waNumber }}" type="tel" inputmode="numeric" pattern="[0-9]*" placeholder="0812xxxxxxx" class="h-12 min-w-0 flex-1 rounded-xl border-[#c6c5d2] text-base focus:border-[#001356] focus:ring-[#001356]">
                <button class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#001356] text-white">
                    <span class="material-symbols-outlined">search</span>
                </button>
            </div>
        </form>

        @if ($waNumber !== '')
            <div class="space-y-4">
                @forelse ($orders as $order)
                    @php($currentPosition = $order->statusPosition())
                    <article class="rounded-xl border border-[#c6c5d2] bg-white p-5 shadow-sm">
                        <div class="flex items-start justify-between gap-3">
                            <div class="min-w-0">
                                <p class="text-xs font-bold uppercase tracking-widest text-[#767681]">{{ $order->order_number }}</p>
                                <h2 class="mt-1 text-lg font-extrabold text-[#171c20]">{{ $order->customer_name }}</h2>
                            </div>
                            <span class="shrink-0 rounded-full border px-3 py-1 text-[11px] font-bold {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span>
                        </div>

                        <div class="mt-4 space-y-3">
                            @foreach ($statuses as $index => $status)
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full {{ $index <= $currentPosition ? 'bg-[#001356] text-white' : 'bg-[#dfe3e9] text-[#767681]' }}">
                                        <span class="material-symbols-outlined text-[18px]">{{ $index <= $currentPosition ? 'check' : 'radio_button_unchecked' }}</span>
                                    </div>
                                    <span class="text-sm font-bold {{ $index <= $currentPosition ? 'text-[#001356]' : 'text-[#767681]' }}">{{ $labels[$status] }}</span>
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-5 rounded-xl bg-[#f6faff] p-4">
                            <div class="flex justify-between text-sm text-[#454650]"><span>Total</span><span class="font-extrabold text-[#001356]">{{ $formatRupiah($order->total) }}</span></div>
                            <div class="mt-2 flex justify-between gap-4 text-sm text-[#454650]"><span>Masuk</span><span class="text-right">{{ $order->placed_at?->format('d M Y, H:i') }}</span></div>
                            <p class="mt-3 text-sm text-[#454650]">{{ $order->address }}</p>
                            @if ($order->address_note)
                                <p class="mt-2 text-xs font-semibold text-[#767681]">Patokan: {{ $order->address_note }}</p>
                            @endif
                        </div>

                        <div class="mt-4 space-y-1">
                            @foreach ($order->items as $item)
                                <p class="text-sm font-semibold text-[#171c20]">{{ $item->quantity }}x {{ $item->product_name }}</p>
                            @endforeach
                        </div>
                    </article>
                @empty
                    <div class="rounded-xl border border-dashed border-[#c6c5d2] bg-white p-10 text-center">
                        <span class="material-symbols-outlined text-5xl text-[#767681]">receipt_long</span>
                        <p class="mt-3 text-sm font-bold text-[#454650]">Tidak ada pesanan untuk nomor tersebut.</p>
                    </div>
                @endforelse
            </div>
        @endif
    </section>

    <script>
        const trackWaNumber = document.getElementById('track-wa-number');
        trackWaNumber?.addEventListener('input', () => {
            trackWaNumber.value = trackWaNumber.value.replace(/\D+/g, '');
        });
    </script>
</x-online-layout>
