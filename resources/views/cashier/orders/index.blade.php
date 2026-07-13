@php
    $formatRupiah = fn ($value) => 'Rp '.number_format($value, 0, ',', '.');
    $labels = \App\Models\OnlineOrder::statusLabels();
    $canCancel = fn ($order) => ! in_array($order->status, [
        \App\Models\OnlineOrder::STATUS_SELESAI,
        \App\Models\OnlineOrder::STATUS_DIBATALKAN,
    ], true);
@endphp

<x-pos-layout active="orders" title="Orderan" subtitle="Kelola pesanan online dari customer.">
    @if (session('status') || session('error'))
        <div class="rounded-xl border px-4 py-3 text-sm font-semibold {{ session('error') ? 'border-[#ffdad6] bg-white text-[#93000a]' : 'border-[#b9c7df] bg-white text-[#001356]' }}">
            {{ session('status') ?? session('error') }}
        </div>
    @endif

    <section class="-mx-1 flex gap-2 overflow-x-auto px-1 pb-1 snap-x snap-mandatory scrollbar-none md:mx-0 md:grid md:grid-cols-3 md:gap-3 md:overflow-visible md:pb-0 xl:grid-cols-6">
        @foreach ($statuses as $status)
            <a href="{{ route('cashier.orders.index', ['status' => $status]) }}" class="min-w-[9.5rem] shrink-0 snap-start rounded-xl border p-4 shadow-sm transition active:scale-[0.98] md:min-w-0 {{ $activeStatus === $status ? 'border-[#001356] bg-[#001356] text-white' : 'border-[#c6c5d2] bg-white text-[#171c20]' }}">
                <p class="text-[10px] font-bold uppercase tracking-widest {{ $activeStatus === $status ? 'text-white/75' : 'text-[#767681]' }}">{{ $labels[$status] }}</p>
                <p class="mt-2 text-2xl font-extrabold">{{ $counts[$status] ?? 0 }}</p>
            </a>
        @endforeach
    </section>

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-extrabold text-[#171c20]">{{ $activeStatus ? $labels[$activeStatus] : 'Semua Pesanan' }}</h2>
            <p class="text-sm text-[#454650]">{{ $orders->count() }} pesanan tampil</p>
        </div>
        @if ($activeStatus)
            <a href="{{ route('cashier.orders.index') }}" class="inline-flex min-h-12 items-center gap-2 rounded-xl border border-[#c6c5d2] bg-white px-4 py-2 text-sm font-bold text-[#454650] active:scale-[0.98]">
                <span class="material-symbols-outlined text-[18px]">filter_alt_off</span>
                Semua
            </a>
        @endif
    </div>

    <section class="space-y-3 xl:space-y-0 xl:overflow-hidden xl:rounded-xl xl:border xl:border-[#c6c5d2] xl:bg-white xl:shadow-[0_4px_12px_rgba(27,43,107,0.04)]">
        <div class="hidden grid-cols-[1.15fr_1.1fr_1.15fr_150px_180px_260px] gap-4 border-b border-[#dfe3e9] bg-[#f6faff] px-5 py-3 text-xs font-bold uppercase tracking-widest text-[#767681] xl:grid">
            <div>Order</div>
            <div>Customer</div>
            <div>Item</div>
            <div>Status</div>
            <div>Total</div>
            <div class="text-right">Action</div>
        </div>

        <div class="space-y-3 xl:divide-y xl:divide-[#dfe3e9] xl:space-y-0">
            @forelse ($orders as $order)
                <article class="rounded-xl border border-[#c6c5d2] bg-white p-4 shadow-sm xl:grid xl:grid-cols-[1.15fr_1.1fr_1.15fr_150px_180px_260px] xl:items-center xl:gap-4 xl:rounded-none xl:border-0 xl:p-5 xl:shadow-none">
                    <div class="min-w-0">
                        <div class="flex items-start justify-between gap-3 xl:block">
                            <div class="min-w-0">
                                <p class="truncate text-xs font-bold uppercase tracking-widest text-[#767681]">{{ $order->order_number }}</p>
                                <p class="mt-1 text-sm font-semibold text-[#454650]">{{ $order->placed_at?->format('d M Y, H:i') }}</p>
                            </div>
                            <span class="inline-flex shrink-0 rounded-full border px-3 py-1 text-[11px] font-bold xl:hidden {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span>
                        </div>
                    </div>

                    <div class="mt-4 min-w-0 xl:mt-0">
                        <h3 class="truncate text-base font-extrabold text-[#171c20]">{{ $order->customer_name }}</h3>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <a href="{{ $order->customerWhatsappUrl() }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center gap-2 rounded-xl border border-[#8fdcb7] bg-[#e7fff2] px-3 text-sm font-bold text-[#005236] active:scale-[0.98]">
                                <span class="material-symbols-outlined text-[18px]">chat</span>
                                {{ $order->wa_number }}
                            </a>
                        </div>
                    </div>

                    <div class="mt-4 min-w-0 xl:mt-0">
                        @foreach ($order->items->take(2) as $item)
                            <p class="truncate text-sm font-bold text-[#171c20]">{{ $item->quantity }}x {{ $item->product_name }}</p>
                        @endforeach
                        @if ($order->items->count() > 2)
                            <p class="text-xs font-semibold text-[#767681]">+{{ $order->items->count() - 2 }} item lain</p>
                        @endif

                        <div class="mt-3 rounded-xl bg-[#f6faff] p-3">
                            <p class="text-[11px] font-bold uppercase tracking-widest text-[#767681]">Alamat pengantaran</p>
                            <p class="mt-1 text-sm leading-6 text-[#171c20]">{{ $order->address }}</p>
                            @if ($order->deliveryAreaSummary())
                                <p class="mt-1 text-xs font-semibold text-[#454650]">{{ $order->deliveryAreaSummary() }}</p>
                            @endif
                            @if ($order->address_note)
                                <p class="mt-2 text-xs font-semibold text-[#767681]">Patokan: {{ $order->address_note }}</p>
                            @endif
                            @if ($order->deliveryDirectionsUrl())
                                <a href="{{ $order->deliveryDirectionsUrl() }}" target="_blank" rel="noopener noreferrer" class="mt-3 flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#001356] px-4 text-sm font-extrabold text-white active:scale-[0.98]">
                                    <span class="material-symbols-outlined text-[20px]">navigation</span>
                                    Navigasi ke Lokasi
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="hidden xl:block">
                        <span class="inline-flex rounded-full border px-3 py-1 text-[11px] font-bold {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span>
                    </div>

                    <div class="mt-4 flex items-center justify-between gap-3 xl:mt-0 xl:block">
                        <p class="text-xs font-bold uppercase tracking-widest text-[#767681] xl:hidden">Total</p>
                        <p class="text-lg font-extrabold text-[#001356]">{{ $formatRupiah($order->total) }}</p>
                    </div>

                    <div class="mt-4 grid grid-cols-2 gap-2 xl:mt-0 xl:flex xl:flex-wrap xl:justify-end">
                        <button type="button" onclick="openOrderDetail('order-detail-{{ $order->id }}')" class="col-span-2 inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-[#c6c5d2] bg-white px-3 text-sm font-bold text-[#454650] active:scale-[0.98] xl:col-span-1 xl:min-h-10">
                            <span class="material-symbols-outlined text-[18px]">visibility</span>
                            Detail
                        </button>

                        @if ($canCancel($order))
                            <form method="POST" action="{{ route('cashier.orders.cancel', $order) }}" onsubmit="return confirm('Batalkan pesanan ini?')" class="col-span-1">
                                @csrf
                                @method('PATCH')
                                <button class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl border border-[#ffdad6] bg-white px-3 text-sm font-bold text-[#93000a] active:scale-[0.98] xl:min-h-10">
                                    <span class="material-symbols-outlined text-[18px]">cancel</span>
                                    <span class="xl:hidden">Batal</span>
                                    <span class="hidden xl:inline">Cancel</span>
                                </button>
                            </form>
                        @endif

                        @if ($order->status === \App\Models\OnlineOrder::STATUS_PESANAN_MASUK)
                            <form method="POST" action="{{ route('cashier.orders.payment-reminder', $order) }}" class="{{ $canCancel($order) ? 'col-span-1' : 'col-span-2' }}">
                                @csrf
                                @method('PATCH')
                                <button class="inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#001356] px-3 text-sm font-extrabold text-white active:scale-[0.98] xl:min-h-10">
                                    <span class="material-symbols-outlined text-[18px]">send</span>
                                    Send
                                </button>
                            </form>
                        @endif
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-[#c6c5d2] bg-white p-12 text-center xl:rounded-none xl:border-0">
                    <span class="material-symbols-outlined text-5xl text-[#767681]">pending_actions</span>
                    <h3 class="mt-3 text-lg font-extrabold text-[#171c20]">Belum ada pesanan</h3>
                    <p class="mt-1 text-sm text-[#454650]">Pesanan customer dari halaman publik akan muncul di sini.</p>
                </div>
            @endforelse
        </div>
    </section>

    @foreach ($orders as $order)
        <div id="order-detail-{{ $order->id }}" class="fixed inset-0 z-[90] hidden items-end justify-center bg-[#171c20]/50 p-0 sm:items-center sm:p-4" onclick="closeOrderDetail('order-detail-{{ $order->id }}')">
            <section class="flex max-h-[94dvh] w-full max-w-3xl flex-col overflow-hidden rounded-t-2xl bg-white shadow-2xl sm:max-h-[92dvh] sm:rounded-2xl" onclick="event.stopPropagation()">
                <div class="flex items-start justify-between gap-4 border-b border-[#dfe3e9] p-5">
                    <div class="min-w-0">
                        <p class="truncate text-xs font-bold uppercase tracking-widest text-[#767681]">{{ $order->order_number }}</p>
                        <h3 class="mt-1 text-xl font-extrabold text-[#001356]">{{ $order->customer_name }}</h3>
                        <p class="mt-1 text-sm text-[#454650]">{{ $order->wa_number }} · {{ $order->placed_at?->format('d M Y, H:i') }}</p>
                    </div>
                    <button type="button" onclick="closeOrderDetail('order-detail-{{ $order->id }}')" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-[#454650] hover:bg-[#f6faff] active:scale-[0.98]">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto p-5">
                    <div class="space-y-4">
                        <div class="rounded-xl bg-[#f6faff] p-4">
                            <p class="text-xs font-bold uppercase tracking-widest text-[#767681]">Lokasi Pengantaran</p>
                            <p class="mt-2 text-sm leading-6 text-[#171c20]">{{ $order->address }}</p>

                            @if ($order->deliveryAreaParts())
                                <div class="mt-3 grid gap-2 sm:grid-cols-2">
                                    @foreach ($order->deliveryAreaParts() as $label => $value)
                                        <div class="rounded-lg bg-white px-3 py-2">
                                            <p class="text-[11px] font-bold uppercase tracking-widest text-[#767681]">{{ $label }}</p>
                                            <p class="mt-1 text-sm font-semibold text-[#171c20]">{{ $value }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            @endif

                            @if ($order->address_note)
                                <div class="mt-3 rounded-lg bg-white px-3 py-2">
                                    <p class="text-[11px] font-bold uppercase tracking-widest text-[#767681]">Patokan</p>
                                    <p class="mt-1 text-sm leading-5 text-[#171c20]">{{ $order->address_note }}</p>
                                </div>
                            @endif

                            @if ($order->delivery_latitude && $order->delivery_longitude)
                                <p class="mt-3 text-xs font-semibold text-[#454650]">
                                    Koordinat: {{ $order->delivery_latitude }}, {{ $order->delivery_longitude }}
                                </p>
                            @endif

                            <div class="mt-4 grid gap-2 sm:grid-cols-2">
                                @if ($order->deliveryDirectionsUrl())
                                    <a href="{{ $order->deliveryDirectionsUrl() }}" target="_blank" rel="noopener noreferrer" class="flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#001356] px-4 text-sm font-extrabold text-white active:scale-[0.98]">
                                        <span class="material-symbols-outlined text-[20px]">navigation</span>
                                        Navigasi Maps
                                    </a>
                                @endif
                                @if ($order->deliveryMapUrl())
                                    <a href="{{ $order->deliveryMapUrl() }}" target="_blank" rel="noopener noreferrer" class="flex min-h-12 items-center justify-center gap-2 rounded-xl border border-[#001356] bg-white px-4 text-sm font-extrabold text-[#001356] active:scale-[0.98]">
                                        <span class="material-symbols-outlined text-[20px]">map</span>
                                        Buka Titik
                                    </a>
                                @endif
                                <a href="{{ $order->customerWhatsappUrl() }}" target="_blank" rel="noopener noreferrer" class="flex min-h-12 items-center justify-center gap-2 rounded-xl border border-[#8fdcb7] bg-[#e7fff2] px-4 text-sm font-extrabold text-[#005236] active:scale-[0.98] sm:col-span-2">
                                    <span class="material-symbols-outlined text-[20px]">chat</span>
                                    Hubungi via WhatsApp
                                </a>
                            </div>
                        </div>

                        <div class="grid gap-4 lg:grid-cols-[1fr_260px]">
                            <div class="rounded-xl border border-[#dfe3e9] p-4">
                                <p class="text-xs font-bold uppercase tracking-widest text-[#767681]">Item</p>
                                <div class="mt-3 space-y-3">
                                    @foreach ($order->items as $item)
                                        <div class="flex justify-between gap-3 text-sm">
                                            <div class="min-w-0">
                                                <p class="font-bold text-[#171c20]">{{ $item->quantity }}x {{ $item->product_name }}</p>
                                                <p class="text-xs text-[#454650]">
                                                    {{ collect($item->variant_payload)->pluck('name')->merge(collect($item->addon_payload)->pluck('name'))->filter()->join(', ') ?: 'Tanpa varian' }}
                                                </p>
                                                @if ($item->note)
                                                    <p class="text-xs font-semibold text-[#767681]">Catatan: {{ $item->note }}</p>
                                                @endif
                                            </div>
                                            <p class="shrink-0 font-extrabold text-[#001356]">{{ $formatRupiah($item->line_total) }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <aside class="space-y-4">
                                <div class="rounded-xl border border-[#dfe3e9] p-4">
                                    <p class="text-xs font-bold uppercase tracking-widest text-[#767681]">Status</p>
                                    <span class="mt-3 inline-flex rounded-full border px-3 py-1 text-[11px] font-bold {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span>
                                    <div class="mt-4 space-y-2 text-sm">
                                        <div class="flex justify-between text-[#454650]"><span>Subtotal</span><span>{{ $formatRupiah($order->subtotal) }}</span></div>
                                        <div class="flex justify-between text-[#454650]"><span>Ongkir</span><span>{{ $formatRupiah($order->shipping_cost) }}</span></div>
                                        <div class="flex justify-between border-t border-[#dfe3e9] pt-2 font-extrabold text-[#001356]"><span>Total</span><span>{{ $formatRupiah($order->total) }}</span></div>
                                    </div>
                                </div>

                                <div class="rounded-xl border border-[#dfe3e9] p-4">
                                    <p class="text-xs font-bold uppercase tracking-widest text-[#767681]">Riwayat</p>
                                    <div class="mt-3 space-y-3">
                                        @foreach ($order->statusLogs->sortByDesc('changed_at')->take(6) as $log)
                                            <div>
                                                <p class="text-sm font-bold text-[#171c20]">{{ $labels[$log->status] ?? str($log->status)->replace('_', ' ')->title() }}</p>
                                                <p class="text-xs text-[#454650]">{{ $log->changed_at?->format('d M Y, H:i') }}{{ $log->changer ? ' oleh '.$log->changer->name : '' }}</p>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </aside>
                        </div>
                    </div>
                </div>

                @if (in_array($order->status, [
                    \App\Models\OnlineOrder::STATUS_KONFIRMASI_PEMBAYARAN,
                    \App\Models\OnlineOrder::STATUS_SEDANG_DIPROSES,
                    \App\Models\OnlineOrder::STATUS_DIKIRIM,
                ], true))
                    <div class="border-t border-[#dfe3e9] bg-white p-4 pb-[calc(1rem+env(safe-area-inset-bottom))]">
                        @if ($order->status === \App\Models\OnlineOrder::STATUS_KONFIRMASI_PEMBAYARAN)
                            <form method="POST" action="{{ route('cashier.orders.process', $order) }}">
                                @csrf
                                @method('PATCH')
                                <button class="w-full min-h-14 rounded-xl bg-[#001356] px-4 text-sm font-extrabold text-white active:scale-[0.98]">Mulai Proses</button>
                            </form>
                        @elseif ($order->status === \App\Models\OnlineOrder::STATUS_SEDANG_DIPROSES)
                            <form method="POST" action="{{ route('cashier.orders.ship', $order) }}">
                                @csrf
                                @method('PATCH')
                                <button class="w-full min-h-14 rounded-xl bg-[#001356] px-4 text-sm font-extrabold text-white active:scale-[0.98]">Kirim Pesanan</button>
                            </form>
                        @elseif ($order->status === \App\Models\OnlineOrder::STATUS_DIKIRIM)
                            <form method="POST" action="{{ route('cashier.orders.finish', $order) }}">
                                @csrf
                                @method('PATCH')
                                <button class="w-full min-h-14 rounded-xl bg-[#001356] px-4 text-sm font-extrabold text-white active:scale-[0.98]">Selesaikan</button>
                            </form>
                        @endif
                    </div>
                @endif
            </section>
        </div>
    @endforeach

    @if (session('whatsapp_url'))
        <script>
            window.addEventListener('load', () => {
                window.open(@json(session('whatsapp_url')), '_blank', 'noopener,noreferrer');
            });
        </script>
    @endif

    <script>
        function openOrderDetail(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('modal-open');
        }

        function closeOrderDetail(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('modal-open');
        }
    </script>
</x-pos-layout>
