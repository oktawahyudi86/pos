@php
    $formatRupiah = fn ($value) => 'Rp '.number_format($value, 0, ',', '.');
    $navItems = auth()->user()->hasRole('Admin')
        ? [
            ['label' => 'Dashboard', 'icon' => 'dashboard', 'href' => route('dashboard'), 'active' => false],
            ['label' => 'Produk', 'icon' => 'inventory_2', 'href' => route('admin.products.index'), 'active' => false],
            ['label' => 'Transaksi', 'icon' => 'receipt_long', 'href' => route('transactions.index'), 'active' => true],
            ['label' => 'Pengaturan', 'icon' => 'settings', 'href' => route('admin.settings.edit'), 'active' => false],
        ]
        : [
            ['label' => 'Kasir', 'icon' => 'shopping_basket', 'href' => route('cashier.index'), 'active' => false],
            ['label' => 'Orderan', 'icon' => 'pending_actions', 'href' => route('cashier.orders.index'), 'active' => false],
            ['label' => 'Transaksi', 'icon' => 'receipt_long', 'href' => route('transactions.index'), 'active' => true],
        ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $transaction->invoice_number }} - {{ config('app.name', 'Keijora POS') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        @media (min-width: 768px) {
            body.sidebar-collapsed .pos-sidebar { width: 5rem; padding-left: 0.75rem; padding-right: 0.75rem; }
            body.sidebar-collapsed .pos-main { margin-left: 5rem; }
            body.sidebar-collapsed .pos-topbar { left: 5rem; }
            body.sidebar-collapsed .sidebar-label,
            body.sidebar-collapsed .sidebar-brand-text,
            body.sidebar-collapsed .sidebar-user-meta,
            body.sidebar-collapsed .sidebar-logout-text { display: none; }
            body.sidebar-collapsed .sidebar-brand,
            body.sidebar-collapsed .sidebar-link,
            body.sidebar-collapsed .sidebar-user-card,
            body.sidebar-collapsed .sidebar-logout-button { justify-content: center; padding-left: 0.75rem; padding-right: 0.75rem; }
            body.sidebar-collapsed .sidebar-collapse-icon { transform: rotate(180deg); }
        }
        @media print {
            .no-print { display: none !important; }
            .print-card { box-shadow: none !important; border: 0 !important; }
            body { background: white !important; }
        }
    </style>
</head>
<body class="enable-page-skeleton page-loading mobile-no-zoom w-full max-w-full overflow-x-hidden bg-[#f6faff] text-[#171c20]" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <aside class="pos-sidebar no-print fixed left-0 top-0 z-50 hidden h-screen w-64 flex-col gap-2 border-r border-[#c6c5d2] bg-[#f6faff] p-4 transition-all duration-300 md:flex">
        <div class="mb-8 flex items-center">
            <a href="{{ route('dashboard') }}" class="sidebar-brand flex min-w-0 items-center gap-3 rounded-xl px-4 py-2">
                <div class="flex h-10 w-10 shrink-0 items-center justify-center">
                    <img src="{{ asset('images/keijora-bird-navy.png') }}" alt="Keijora" class="h-9 w-9 object-contain">
                </div>
                <div class="sidebar-brand-text min-w-0">
                    <img src="{{ asset('images/keijora-logo-cropped.png') }}" alt="Keijora" class="h-8 w-auto max-w-[132px] object-contain">
                    <div class="text-xs font-bold uppercase tracking-[0.24em] text-[#767681]">POS System</div>
                </div>
            </a>
        </div>

        <nav class="flex-1 space-y-1">
            @foreach ($navItems as $item)
                <a href="{{ $item['href'] }}" title="{{ $item['label'] }}" class="sidebar-link group flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold transition {{ $item['active'] ? 'bg-[#001356] text-white shadow-sm' : 'text-[#454650] hover:bg-[#d5e3fc]' }}">
                    <span class="material-symbols-outlined {{ $item['active'] ? 'text-white' : 'group-hover:text-[#001356]' }}">{{ $item['icon'] }}</span>
                    <span class="sidebar-label">{{ $item['label'] }}</span>
                </a>
            @endforeach
        </nav>
    </aside>

    <header class="pos-topbar no-print fixed left-0 right-0 top-0 z-40 flex h-16 items-center justify-between gap-3 border-b border-[#c6c5d2] bg-[#f6faff] px-4 transition-all duration-300 md:left-64 md:px-6">
        <div class="flex min-w-0 flex-1 items-center gap-2 md:gap-3">
            <x-page-back-button :href="route('transactions.index')" label="Kembali ke daftar transaksi" class="h-10 w-10 px-0 sm:h-11 sm:w-auto sm:px-4" />
            <button type="button" onclick="togglePosSidebar()" class="hidden h-11 w-11 shrink-0 items-center justify-center rounded-full border border-[#c6c5d2] bg-white text-[#001356] shadow-sm hover:bg-[#eef3ff] md:flex" aria-label="Buka tutup sidebar">
                <span class="sidebar-collapse-icon material-symbols-outlined transition-transform">left_panel_close</span>
            </button>
            <div class="min-w-0">
                <h1 class="truncate text-base font-extrabold text-[#171c20] md:text-lg">{{ $transaction->invoice_number }}</h1>
                <p class="truncate text-xs font-medium text-[#454650]">Detail transaksi dan ringkasan pembayaran.</p>
            </div>
        </div>
        <div class="flex shrink-0 items-center gap-2">
            <a href="{{ route('transactions.receipt', $transaction) }}" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 rounded-xl bg-[#001356] px-3 py-2 text-xs font-bold text-white sm:gap-2 sm:px-4 sm:text-sm">
                <span class="material-symbols-outlined text-[18px] sm:hidden">print</span>
                <span class="hidden sm:inline">Cetak Struk</span>
                <span class="sm:hidden">Cetak</span>
            </a>
        </div>
    </header>

    <main class="pos-main min-h-screen min-w-0 max-w-full overflow-x-hidden pt-20 transition-all duration-300 md:ml-64">
        <div class="mx-auto grid w-full max-w-7xl min-w-0 gap-5 overflow-x-hidden px-4 pb-24 md:px-6 md:pb-6 lg:grid-cols-[1fr_380px]">
            <section class="print-card rounded-2xl border border-[#c6c5d2] bg-white p-5 shadow-[0_8px_20px_rgba(27,43,107,0.05)]">
                <div class="grid gap-4 border-b border-[#dfe3e9] pb-5 md:grid-cols-3">
                    <div>
                        <p class="text-xs font-bold uppercase text-[#767681]">Kasir</p>
                        <p class="mt-1 font-bold text-[#171c20]">{{ $transaction->cashier->name }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-[#767681]">Waktu</p>
                        <p class="mt-1 font-bold text-[#171c20]">{{ $transaction->paid_at?->format('d M Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-bold uppercase text-[#767681]">Pembayaran</p>
                        <p class="mt-1 font-bold uppercase text-[#001356]">{{ $transaction->payment_method === 'cash' ? 'Tunai' : 'QRIS' }}</p>
                    </div>
                </div>

                <div class="mt-5 divide-y divide-[#dfe3e9]">
                    @foreach ($transaction->items as $item)
                        <div class="py-4">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <p class="font-bold text-[#171c20]">{{ $item->product_name }}</p>
                                    <p class="text-sm text-[#454650]">{{ $item->quantity }} x {{ $formatRupiah($item->unit_price) }}</p>
                                    @if ($item->variantOptions->isNotEmpty() || $item->addons->isNotEmpty())
                                        <p class="mt-1 text-xs text-[#454650]">
                                            {{ $item->variantOptions->map(fn ($option) => $option->variant_group_name.': '.$option->option_name)->merge($item->addons->pluck('addon_name'))->join(', ') }}
                                        </p>
                                    @endif
                                    @if ($item->note)
                                        <p class="mt-1 text-xs italic text-[#767681]">Catatan: {{ $item->note }}</p>
                                    @endif
                                </div>
                                <p class="font-extrabold text-[#001356]">{{ $formatRupiah($item->line_total) }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </section>

            <aside class="print-card rounded-2xl border border-[#c6c5d2] bg-white p-5 shadow-[0_8px_20px_rgba(27,43,107,0.05)]">
                <h3 class="text-lg font-extrabold text-[#171c20]">Ringkasan</h3>
                <div class="mt-4 space-y-3 text-sm">
                    <div class="flex justify-between text-[#454650]"><span>Subtotal</span><span class="font-bold">{{ $formatRupiah($transaction->subtotal) }}</span></div>
                    <div class="flex justify-between text-[#454650]"><span>Diskon</span><span class="font-bold text-[#ba1a1a]">- {{ $formatRupiah($transaction->discount_amount) }}</span></div>
                    <div class="flex justify-between text-[#454650]"><span>Pajak</span><span class="font-bold">{{ $formatRupiah($transaction->tax_amount) }}</span></div>
                    <div class="flex justify-between border-t border-[#dfe3e9] pt-4 text-xl font-extrabold text-[#001356]"><span>Total</span><span>{{ $formatRupiah($transaction->total) }}</span></div>
                    <div class="flex justify-between text-[#454650]"><span>Dibayar</span><span class="font-bold">{{ $formatRupiah($transaction->paid_amount) }}</span></div>
                    <div class="flex justify-between text-[#454650]"><span>Kembalian</span><span class="font-bold">{{ $formatRupiah($transaction->change_amount) }}</span></div>
                </div>

                @if ($transaction->customer_name || $transaction->customer_phone)
                    <div class="mt-5 rounded-xl bg-[#f0f4fa] p-4">
                        <p class="text-xs font-bold uppercase text-[#767681]">Pembeli</p>
                        <p class="mt-1 font-bold text-[#171c20]">{{ $transaction->customer_name ?: '-' }}</p>
                        <p class="text-sm text-[#454650]">{{ $transaction->customer_phone ?: '-' }}</p>
                    </div>
                @endif
            </aside>
        </div>
    </main>

    <nav class="no-print fixed inset-x-0 bottom-0 z-50 grid grid-cols-3 gap-1.5 border-t border-[#c6c5d2] bg-white/95 px-2 py-2 pb-[calc(0.75rem+env(safe-area-inset-bottom))] shadow-2xl backdrop-blur md:hidden {{ auth()->user()->hasRole('Admin') ? 'sm:grid-cols-4' : '' }}">
        @foreach ($navItems as $item)
            <a href="{{ $item['href'] }}" class="flex min-h-[4.25rem] min-w-0 flex-1 flex-col items-center justify-center gap-1 rounded-xl px-1.5 py-2 text-[10px] font-bold active:scale-[0.98] {{ $item['active'] ? 'bg-[#001356] text-white' : 'text-[#454650]' }}">
                <span class="material-symbols-outlined text-[22px]">{{ $item['icon'] }}</span>
                <span class="truncate">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <script>
        const sidebarPreferenceKey = 'pos-sidebar-collapsed';
        function applyPosSidebarPreference() {
            if (localStorage.getItem(sidebarPreferenceKey) === 'true') document.body.classList.add('sidebar-collapsed');
        }
        function togglePosSidebar() {
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem(sidebarPreferenceKey, document.body.classList.contains('sidebar-collapsed') ? 'true' : 'false');
        }
        applyPosSidebarPreference();
    </script>
    <style>
        .mobile-no-zoom input,
        .mobile-no-zoom textarea,
        .mobile-no-zoom select {
            font-size: 16px !important;
        }
    </style>
</body>
</html>
