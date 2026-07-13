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
                            @if ($order->address_detail)
                                <p class="mt-2 text-xs font-semibold text-[#767681]">Detail: {{ $order->address_detail }}</p>
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
                            <button type="button" onclick="openWhatsappConfirm({{ $order->id }})" class="{{ $canCancel($order) ? 'col-span-1' : 'col-span-2' }} inline-flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#001356] px-3 text-sm font-extrabold text-white active:scale-[0.98] xl:min-h-10">
                                <span class="material-symbols-outlined text-[18px]">send</span>
                                Send
                            </button>
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
        @php
            $workflowSteps = [
                ['key' => 'wa', 'label' => 'WA', 'done' => ! in_array($order->status, [\App\Models\OnlineOrder::STATUS_PESANAN_MASUK], true)],
                ['key' => 'proses', 'label' => 'Proses', 'done' => in_array($order->status, [\App\Models\OnlineOrder::STATUS_SEDANG_DIPROSES, \App\Models\OnlineOrder::STATUS_DIKIRIM, \App\Models\OnlineOrder::STATUS_SELESAI], true)],
                ['key' => 'pengantaran', 'label' => 'Antar', 'done' => in_array($order->status, [\App\Models\OnlineOrder::STATUS_DIKIRIM, \App\Models\OnlineOrder::STATUS_SELESAI], true)],
                ['key' => 'selesai', 'label' => 'Selesai', 'done' => $order->status === \App\Models\OnlineOrder::STATUS_SELESAI],
            ];
            $currentStepIndex = match ($order->status) {
                \App\Models\OnlineOrder::STATUS_PESANAN_MASUK => 0,
                \App\Models\OnlineOrder::STATUS_KONFIRMASI_PEMBAYARAN => 1,
                \App\Models\OnlineOrder::STATUS_SEDANG_DIPROSES => 2,
                \App\Models\OnlineOrder::STATUS_DIKIRIM => 3,
                \App\Models\OnlineOrder::STATUS_SELESAI => 4,
                default => -1,
            };
        @endphp
        <div id="order-detail-{{ $order->id }}" class="fixed inset-0 z-[90] hidden items-end justify-center bg-[#171c20]/50 p-0 sm:items-center sm:p-4" onclick="closeOrderDetail('order-detail-{{ $order->id }}')">
            <section class="flex h-[min(92dvh,680px)] w-full max-w-lg flex-col overflow-hidden rounded-t-2xl bg-white shadow-2xl sm:rounded-2xl" onclick="event.stopPropagation()">
                <div class="shrink-0 border-b border-[#dfe3e9] px-4 py-4">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <p class="truncate text-[11px] font-bold uppercase tracking-widest text-[#767681]">{{ $order->order_number }}</p>
                            <h3 class="mt-1 truncate text-lg font-extrabold text-[#001356]">{{ $order->customer_name }}</h3>
                            <p class="mt-1 text-xs text-[#454650]">{{ $order->wa_number }} · {{ $order->placed_at?->format('d M Y, H:i') }}</p>
                        </div>
                        <button type="button" onclick="closeOrderDetail('order-detail-{{ $order->id }}')" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-[#454650] hover:bg-[#f6faff] active:scale-[0.98]">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>
                    <div class="mt-3 flex items-center justify-between gap-2">
                        <span class="inline-flex rounded-full border px-2.5 py-1 text-[10px] font-bold {{ $order->statusBadgeClass() }}">{{ $order->statusLabel() }}</span>
                        <p class="text-base font-extrabold text-[#001356]">{{ $formatRupiah($order->total) }}</p>
                    </div>
                </div>

                <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4">
                    <div class="space-y-3">
                        <div class="rounded-xl bg-[#f6faff] p-3">
                            <p class="text-[11px] font-bold uppercase tracking-widest text-[#767681]">Lokasi Pengantaran</p>
                            <p class="mt-1.5 text-sm leading-5 text-[#171c20]">{{ $order->address }}</p>
                            @if ($order->deliveryAreaSummary())
                                <p class="mt-1 text-xs font-semibold text-[#454650]">{{ $order->deliveryAreaSummary() }}</p>
                            @endif
                            @if ($order->address_detail)
                                <p class="mt-2 text-xs font-semibold text-[#767681]">Detail: {{ $order->address_detail }}</p>
                            @endif
                            <div class="mt-3 grid grid-cols-2 gap-2">
                                @if ($order->deliveryDirectionsUrl())
                                    <a href="{{ $order->deliveryDirectionsUrl() }}" target="_blank" rel="noopener noreferrer" class="flex min-h-11 items-center justify-center gap-1.5 rounded-xl bg-[#001356] px-2 text-xs font-extrabold text-white active:scale-[0.98]">
                                        <span class="material-symbols-outlined text-[18px]">navigation</span>
                                        Navigasi
                                    </a>
                                @endif
                                @if ($order->deliveryMapUrl())
                                    <a href="{{ $order->deliveryMapUrl() }}" target="_blank" rel="noopener noreferrer" class="flex min-h-11 items-center justify-center gap-1.5 rounded-xl border border-[#001356] bg-white px-2 text-xs font-extrabold text-[#001356] active:scale-[0.98]">
                                        <span class="material-symbols-outlined text-[18px]">map</span>
                                        Titik
                                    </a>
                                @endif
                            </div>
                        </div>

                        <div class="rounded-xl border border-[#dfe3e9] p-3">
                            <p class="text-[11px] font-bold uppercase tracking-widest text-[#767681]">Item Pesanan</p>
                            <div class="mt-2 space-y-2">
                                @foreach ($order->items as $item)
                                    <div class="flex justify-between gap-2 text-sm">
                                        <p class="min-w-0 font-bold text-[#171c20]">{{ $item->quantity }}x {{ $item->product_name }}</p>
                                        <p class="shrink-0 font-extrabold text-[#001356]">{{ $formatRupiah($item->line_total) }}</p>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3 space-y-1 border-t border-[#dfe3e9] pt-2 text-xs text-[#454650]">
                                <div class="flex justify-between"><span>Subtotal</span><span>{{ $formatRupiah($order->subtotal) }}</span></div>
                                <div class="flex justify-between"><span>Ongkir</span><span>{{ $formatRupiah($order->shipping_cost) }}</span></div>
                            </div>
                        </div>

                        @if ($order->statusLogs->isNotEmpty())
                            <details class="rounded-xl border border-[#dfe3e9] p-3">
                                <summary class="cursor-pointer text-[11px] font-bold uppercase tracking-widest text-[#767681]">Riwayat Status</summary>
                                <div class="mt-2 space-y-2">
                                    @foreach ($order->statusLogs->sortByDesc('changed_at')->take(4) as $log)
                                        <div>
                                            <p class="text-sm font-bold text-[#171c20]">{{ $labels[$log->status] ?? str($log->status)->replace('_', ' ')->title() }}</p>
                                            <p class="text-xs text-[#454650]">{{ $log->changed_at?->format('d M Y, H:i') }}</p>
                                        </div>
                                    @endforeach
                                </div>
                            </details>
                        @endif
                    </div>
                </div>

                @if ($currentStepIndex >= 0 && $order->status !== \App\Models\OnlineOrder::STATUS_DIBATALKAN)
                    <div class="shrink-0 border-t border-[#dfe3e9] bg-white px-4 py-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
                        <div class="mb-3 grid grid-cols-4 gap-1">
                            @foreach ($workflowSteps as $index => $step)
                                <div class="text-center">
                                    <div class="mx-auto flex h-7 w-7 items-center justify-center rounded-full text-[10px] font-extrabold {{ $step['done'] ? 'bg-[#001356] text-white' : ($index === $currentStepIndex ? 'border-2 border-[#001356] bg-[#eef3ff] text-[#001356]' : 'border border-[#c6c5d2] bg-white text-[#767681]') }}">
                                        {{ $index + 1 }}
                                    </div>
                                    <p class="mt-1 text-[9px] font-bold uppercase tracking-wide {{ $step['done'] || $index === $currentStepIndex ? 'text-[#001356]' : 'text-[#767681]' }}">{{ $step['label'] }}</p>
                                </div>
                            @endforeach
                        </div>

                        @if ($order->status === \App\Models\OnlineOrder::STATUS_PESANAN_MASUK)
                            <button type="button" onclick="openWhatsappConfirm({{ $order->id }})" class="flex w-full min-h-14 items-center justify-center gap-2 rounded-xl bg-[#001356] px-4 text-sm font-extrabold text-white active:scale-[0.98]">
                                <span class="material-symbols-outlined text-[20px]">chat</span>
                                Selanjutnya: Kirim Konfirmasi WA
                            </button>
                        @elseif ($order->status === \App\Models\OnlineOrder::STATUS_KONFIRMASI_PEMBAYARAN)
                            <form method="POST" action="{{ route('cashier.orders.process', $order) }}">
                                @csrf
                                @method('PATCH')
                                <button class="flex w-full min-h-14 items-center justify-center gap-2 rounded-xl bg-[#001356] px-4 text-sm font-extrabold text-white active:scale-[0.98]">
                                    <span class="material-symbols-outlined text-[20px]">play_arrow</span>
                                    Selanjutnya: Mulai Proses
                                </button>
                            </form>
                        @elseif ($order->status === \App\Models\OnlineOrder::STATUS_SEDANG_DIPROSES)
                            <form method="POST" action="{{ route('cashier.orders.ship', $order) }}">
                                @csrf
                                @method('PATCH')
                                <button class="flex w-full min-h-14 items-center justify-center gap-2 rounded-xl bg-[#001356] px-4 text-sm font-extrabold text-white active:scale-[0.98]">
                                    <span class="material-symbols-outlined text-[20px]">local_shipping</span>
                                    Selanjutnya: Mulai Pengantaran
                                </button>
                            </form>
                        @elseif ($order->status === \App\Models\OnlineOrder::STATUS_DIKIRIM)
                            <form method="POST" action="{{ route('cashier.orders.finish', $order) }}">
                                @csrf
                                @method('PATCH')
                                <button class="flex w-full min-h-14 items-center justify-center gap-2 rounded-xl bg-[#001356] px-4 text-sm font-extrabold text-white active:scale-[0.98]">
                                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                                    Selanjutnya: Selesaikan Pesanan
                                </button>
                            </form>
                        @elseif ($order->status === \App\Models\OnlineOrder::STATUS_SELESAI)
                            <div class="flex min-h-14 items-center justify-center gap-2 rounded-xl border border-[#8fdcb7] bg-[#e7fff2] px-4 text-sm font-extrabold text-[#005236]">
                                <span class="material-symbols-outlined text-[20px]">task_alt</span>
                                Pesanan Selesai
                            </div>
                        @endif

                        @if ($order->status === \App\Models\OnlineOrder::STATUS_KONFIRMASI_PEMBAYARAN)
                            <a href="{{ $order->customerWhatsappUrl() }}" target="_blank" rel="noopener noreferrer" class="mt-2 flex min-h-11 w-full items-center justify-center gap-2 text-xs font-bold text-[#454650] underline-offset-2 hover:underline">
                                Buka chat WhatsApp lagi
                            </a>
                        @endif
                    </div>
                @endif
            </section>
        </div>
    @endforeach

    <div id="whatsapp-confirm-modal" class="fixed inset-0 z-[100] hidden items-end justify-center bg-[#171c20]/60 p-0 sm:items-center sm:p-4" onclick="closeWhatsappConfirm()">
        <section class="flex h-[min(88dvh,560px)] w-full max-w-lg flex-col overflow-hidden rounded-t-2xl bg-white shadow-2xl sm:rounded-2xl" onclick="event.stopPropagation()">
            <div class="shrink-0 border-b border-[#dfe3e9] px-4 py-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-[11px] font-bold uppercase tracking-widest text-[#767681]">Konfirmasi WhatsApp</p>
                        <h3 class="mt-1 text-lg font-extrabold text-[#001356]">Kirim pesan ke customer</h3>
                        <p class="mt-1 text-xs leading-5 text-[#454650]">Status pesanan baru berubah setelah Anda konfirmasi pengiriman.</p>
                    </div>
                    <button type="button" onclick="closeWhatsappConfirm()" class="flex h-10 w-10 items-center justify-center rounded-full text-[#454650] hover:bg-[#f6faff]">
                        <span class="material-symbols-outlined">close</span>
                    </button>
                </div>
            </div>

            <div class="min-h-0 flex-1 overflow-y-auto px-4 py-4">
                <label class="mb-2 block text-[11px] font-bold uppercase tracking-widest text-[#767681]">Preview pesan</label>
                <textarea id="wa-preview-message" readonly rows="12" class="w-full resize-none rounded-xl border border-[#c6c5d2] bg-[#f6faff] px-3 py-3 text-sm leading-6 text-[#171c20]"></textarea>
                <p id="wa-copy-status" class="mt-2 hidden text-xs font-semibold text-[#005236]">Pesan berhasil disalin.</p>
            </div>

            <div class="shrink-0 space-y-2 border-t border-[#dfe3e9] bg-white px-4 py-3 pb-[calc(0.75rem+env(safe-area-inset-bottom))]">
                <form id="wa-confirm-form" method="POST" action="#">
                    @csrf
                    @method('PATCH')
                </form>

                <button type="button" onclick="copyWhatsappMessage()" class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl border border-[#c6c5d2] bg-white px-4 text-sm font-extrabold text-[#454650] active:scale-[0.98]">
                    <span class="material-symbols-outlined text-[20px]">content_copy</span>
                    Salin Pesan
                </button>

                <button type="button" onclick="confirmWhatsappReminder(true)" class="flex min-h-14 w-full items-center justify-center gap-2 rounded-xl bg-[#001356] px-4 text-sm font-extrabold text-white active:scale-[0.98]">
                    <span class="material-symbols-outlined text-[20px]">chat</span>
                    Konfirmasi &amp; Buka WhatsApp
                </button>

                <button type="button" onclick="confirmWhatsappReminder(false)" class="flex min-h-11 w-full items-center justify-center gap-2 text-xs font-bold text-[#454650] underline-offset-2 hover:underline">
                    WhatsApp terblokir? Konfirmasi tanpa buka WA
                </button>
            </div>
        </section>
    </div>

    @if (session('open_order_detail'))
        <script>
            window.addEventListener('load', () => {
                openOrderDetail('order-detail-{{ session('open_order_detail') }}');
            });
        </script>
    @endif

    <script>
        const paymentReminders = @json($paymentReminders);
        let activeWhatsappReminder = null;

        function openWhatsappConfirm(orderId) {
            const reminder = paymentReminders[orderId];
            if (!reminder) return;

            activeWhatsappReminder = reminder;
            document.getElementById('wa-preview-message').value = reminder.message;
            document.getElementById('wa-confirm-form').action = reminder.action;
            document.getElementById('wa-copy-status').classList.add('hidden');

            const modal = document.getElementById('whatsapp-confirm-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeWhatsappConfirm() {
            const modal = document.getElementById('whatsapp-confirm-modal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            activeWhatsappReminder = null;

            if (!document.querySelector('[id^="order-detail-"].flex')) {
                document.body.classList.remove('overflow-hidden');
            }
        }

        async function copyWhatsappMessage() {
            const message = document.getElementById('wa-preview-message').value;
            if (!message) return;

            try {
                await navigator.clipboard.writeText(message);
            } catch (error) {
                const textarea = document.getElementById('wa-preview-message');
                textarea.focus();
                textarea.select();
                document.execCommand('copy');
            }

            const status = document.getElementById('wa-copy-status');
            status.textContent = 'Pesan berhasil disalin. Tempel manual di WhatsApp jika popup diblokir.';
            status.classList.remove('hidden');
        }

        function confirmWhatsappReminder(openWhatsapp) {
            if (!activeWhatsappReminder) return;

            if (openWhatsapp) {
                window.open(activeWhatsappReminder.url, '_blank', 'noopener,noreferrer');
            }

            document.getElementById('wa-confirm-form').submit();
        }

        function openOrderDetail(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
        }

        function closeOrderDetail(id) {
            const modal = document.getElementById(id);
            if (!modal) return;
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
        }
    </script>
</x-pos-layout>
