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
    <title>Transaksi - {{ config('app.name', 'Keijora POS') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .custom-scrollbar::-webkit-scrollbar { width: 6px; height: 6px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #dfe3e9; border-radius: 999px; }
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
    </style>
</head>
<body class="mobile-no-zoom w-full max-w-full overflow-x-hidden bg-[#f6faff] text-[#171c20]" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <aside class="pos-sidebar fixed left-0 top-0 z-50 hidden h-screen w-64 flex-col gap-2 border-r border-[#c6c5d2] bg-[#f6faff] p-4 transition-all duration-300 md:flex">
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

        <div class="mt-auto rounded-xl bg-[#eaeef4] p-4">
            <div class="sidebar-user-card flex items-center gap-3">
                <div class="flex h-10 w-10 items-center justify-center rounded-full bg-[#001356] text-white">
                    <span class="material-symbols-outlined">account_circle</span>
                </div>
                <div class="sidebar-user-meta min-w-0">
                    <p class="truncate text-sm font-bold text-[#171c20]">{{ auth()->user()->name }}</p>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#767681]">{{ auth()->user()->hasRole('Admin') ? 'Admin' : 'Kasir' }}</p>
                </div>
            </div>
            <form method="POST" action="{{ route('logout') }}" class="mt-4">
                @csrf
                <button title="Keluar" class="sidebar-logout-button flex w-full items-center justify-center gap-2 rounded-lg border border-[#c6c5d2] bg-white px-3 py-2 text-xs font-bold text-[#454650] hover:text-[#001356]">
                    <span class="material-symbols-outlined text-[18px]">logout</span>
                    <span class="sidebar-logout-text">Keluar</span>
                </button>
            </form>
        </div>
    </aside>

    <header class="pos-topbar fixed left-0 right-0 top-0 z-40 flex h-16 items-center justify-between border-b border-[#c6c5d2] bg-[#f6faff] px-6 transition-all duration-300 md:left-64">
        <div class="flex items-center gap-4">
            <button class="rounded-lg p-2 text-[#001356] md:hidden"><span class="material-symbols-outlined">menu</span></button>
            <div class="hidden items-center gap-3 sm:flex">
                <button type="button" onclick="togglePosSidebar()" class="hidden h-11 w-11 shrink-0 items-center justify-center rounded-full border border-[#c6c5d2] bg-white text-[#001356] shadow-sm hover:bg-[#eef3ff] md:flex" aria-label="Buka tutup sidebar">
                    <span class="sidebar-collapse-icon material-symbols-outlined transition-transform">left_panel_close</span>
                </button>
                <div>
                    <h1 class="text-lg font-extrabold text-[#171c20]">Transaksi</h1>
                    <p class="text-xs font-medium text-[#454650]">Pantau pembayaran, invoice, dan performa kasir.</p>
                </div>
            </div>
            <div class="md:hidden">
                <img src="{{ asset('images/keijora-logo-cropped.png') }}" alt="Keijora" class="h-7 w-auto max-w-[120px] object-contain">
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div class="hidden flex-col items-end lg:flex">
                <span class="text-sm font-bold text-[#171c20]">Sistem Kasir</span>
                <span class="digital-clock-global text-xs text-[#454650]">00:00:00</span>
            </div>
            <button class="rounded-full p-2 text-[#454650] hover:text-[#001356]"><span class="material-symbols-outlined">notifications</span></button>
            <a href="{{ route('profile.edit') }}" class="rounded-full p-2 text-[#454650] hover:text-[#001356]"><span class="material-symbols-outlined">account_circle</span></a>
        </div>
    </header>

    <main class="pos-main min-h-screen min-w-0 max-w-full overflow-x-hidden pt-20 transition-all duration-300 md:ml-64">
        <div class="w-full max-w-full space-y-6 overflow-x-hidden px-4 pb-24 md:px-6 md:pb-6">
            @if (session('status'))
                <div class="rounded-xl border border-[#b9c7df] bg-[#eef3ff] px-4 py-3 text-sm font-semibold text-[#001356]">
                    {{ session('status') }}
                </div>
            @endif

            <section class="flex flex-col justify-between gap-4 rounded-xl border border-[#c6c5d2] bg-white p-4 shadow-sm md:flex-row md:items-center">
                <div class="flex items-center gap-2 rounded-lg bg-[#eaeef4] p-1">
                    <a href="{{ route('transactions.index', request()->except('date')) }}" class="rounded-md px-6 py-2 text-sm font-bold {{ request('date') ? 'text-[#454650] hover:bg-[#dfe3e9]' : 'bg-white text-[#001356] shadow-sm' }}">Semua</a>
                    <button type="button" class="rounded-md bg-white px-6 py-2 text-sm font-bold text-[#001356] shadow-sm">Harian</button>
                </div>
                <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-wrap items-center gap-3">
                    <label class="flex items-center gap-2 rounded-lg border border-[#c6c5d2] bg-white px-4 py-2 text-sm font-semibold text-[#171c20] hover:border-[#001356]">
                        <span class="material-symbols-outlined text-lg text-[#001356]">calendar_today</span>
                        <input type="date" name="date" value="{{ request('date') }}" class="border-0 bg-transparent p-0 text-sm font-semibold focus:ring-0">
                    </label>
                    <button class="flex items-center gap-2 rounded-lg bg-[#001356] px-5 py-2 text-sm font-bold text-white shadow-sm active:scale-95">
                        <span class="material-symbols-outlined text-[18px]">filter_list</span>
                        Terapkan
                    </button>
                </form>
            </section>

            <section class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-xl border border-[#c6c5d2] bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="mb-4 flex items-start justify-between">
                        <div class="rounded-lg bg-[#1b2b6b] p-2 text-[#8695db]"><span class="material-symbols-outlined">payments</span></div>
                        <span class="rounded-full bg-[#6ffbbe] px-2 py-1 text-[10px] font-bold text-[#005236]">LIVE</span>
                    </div>
                    <p class="text-sm font-bold text-[#454650]">Total Penjualan</p>
                    <h3 class="mt-1 text-2xl font-extrabold text-[#171c20]">{{ $formatRupiah($totalSales) }}</h3>
                </div>
                <div class="rounded-xl border border-[#c6c5d2] bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="mb-4 rounded-lg bg-[#d5e3fc] p-2 text-[#57657a] w-fit"><span class="material-symbols-outlined">receipt_long</span></div>
                    <p class="text-sm font-bold text-[#454650]">Jumlah Transaksi</p>
                    <h3 class="mt-1 text-2xl font-extrabold text-[#171c20]">{{ number_format($totalTransactions, 0, ',', '.') }}</h3>
                </div>
                <div class="rounded-xl border border-[#c6c5d2] bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="mb-4 rounded-lg bg-[#6ffbbe] p-2 text-[#002113] w-fit"><span class="material-symbols-outlined">trending_up</span></div>
                    <p class="text-sm font-bold text-[#454650]">Rata-rata Keranjang</p>
                    <h3 class="mt-1 text-2xl font-extrabold text-[#171c20]">{{ $formatRupiah($averageBasket) }}</h3>
                </div>
                <div class="relative overflow-hidden rounded-xl border border-[#c6c5d2] bg-white p-6 shadow-sm transition hover:-translate-y-0.5 hover:shadow-md">
                    <div class="relative z-10">
                        <div class="mb-4 rounded-lg bg-[#001356] p-2 text-white w-fit"><span class="material-symbols-outlined">stars</span></div>
                        <p class="text-sm font-bold text-[#454650]">Produk Terlaris</p>
                        <h3 class="mt-1 line-clamp-2 text-xl font-extrabold text-[#171c20]">{{ $topProduct }}</h3>
                    </div>
                    <span class="material-symbols-outlined absolute -bottom-5 -right-4 text-[96px] text-[#001356]/10">coffee</span>
                </div>
            </section>

            <section class="overflow-hidden rounded-xl border border-[#c6c5d2] bg-white shadow-sm">
                <div class="flex items-center justify-between border-b border-[#c6c5d2] p-6">
                    <div>
                        <h4 class="text-xl font-extrabold text-[#171c20]">Daftar Transaksi</h4>
                        <p class="mt-1 text-sm text-[#454650]">Menampilkan transaksi terbaru dari database POS.</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <button class="rounded-lg p-2 text-[#767681] hover:bg-[#eaeef4]"><span class="material-symbols-outlined">filter_list</span></button>
                        <button class="rounded-lg p-2 text-[#767681] hover:bg-[#eaeef4]"><span class="material-symbols-outlined">more_vert</span></button>
                    </div>
                </div>
                <div class="custom-scrollbar overflow-x-auto">
                    <table class="w-full min-w-[780px] border-collapse text-left">
                        <thead class="bg-[#f0f4fa]">
                            <tr>
                                <th class="px-6 py-4 text-sm font-extrabold text-[#454650]">No. Transaksi</th>
                                <th class="px-6 py-4 text-sm font-extrabold text-[#454650]">Kasir</th>
                                <th class="px-6 py-4 text-sm font-extrabold text-[#454650]">Waktu</th>
                                <th class="px-6 py-4 text-sm font-extrabold text-[#454650]">Total</th>
                                <th class="px-6 py-4 text-sm font-extrabold text-[#454650]">Status</th>
                                <th class="px-6 py-4 text-right text-sm font-extrabold text-[#454650]">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#c6c5d2]">
                            @forelse ($transactions as $transaction)
                                <tr class="group cursor-pointer transition hover:bg-[#f6faff]">
                                    <td class="px-6 py-4 text-sm font-extrabold text-[#001356]">{{ $transaction->invoice_number }}</td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2">
                                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#dde1ff] text-[10px] font-extrabold text-[#001356]">
                                                {{ str($transaction->cashier->name)->substr(0, 2)->upper() }}
                                            </div>
                                            <span class="text-sm font-medium text-[#171c20]">{{ $transaction->cashier->name }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-[#454650]">{{ $transaction->paid_at?->format('d M Y H:i') }}</td>
                                    <td class="px-6 py-4 text-sm font-extrabold text-[#171c20]">{{ $formatRupiah($transaction->total) }}</td>
                                    <td class="px-6 py-4">
                                        <span class="rounded-full bg-[#6ffbbe] px-3 py-1 text-xs font-extrabold text-[#002113]">Selesai</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('transactions.show', $transaction) }}" class="inline-flex rounded-lg p-2 text-[#767681] transition hover:text-[#001356] group-hover:scale-110">
                                            <span class="material-symbols-outlined">visibility</span>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-12 text-center text-sm text-[#454650]">Belum ada transaksi.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="flex flex-col gap-3 border-t border-[#c6c5d2] p-6 md:flex-row md:items-center md:justify-between">
                    <p class="text-sm text-[#454650]">Menampilkan {{ $transactions->count() }} dari {{ $transactions->total() }} transaksi</p>
                    {{ $transactions->links() }}
                </div>
            </section>
        </div>
    </main>

    <nav class="fixed bottom-0 left-0 z-50 flex w-full items-center justify-around rounded-t-xl bg-[#dfe3e9] px-2 py-2 pb-[calc(0.75rem+env(safe-area-inset-bottom))] shadow-[0_-4px_12px_rgba(27,43,107,0.08)] md:hidden">
        @foreach (array_slice($navItems, 0, 4) as $item)
            <a href="{{ $item['href'] }}" class="flex min-w-0 flex-1 flex-col items-center justify-center px-2 py-2 text-[11px] font-bold transition {{ $item['active'] ? 'rounded-full bg-[#001356] text-white' : 'text-[#454650]' }}">
                <span class="material-symbols-outlined text-[21px]">{{ $item['icon'] }}</span>
                <span class="truncate">{{ \Illuminate\Support\Str::limit($item['label'], 8, '') }}</span>
            </a>
        @endforeach
    </nav>

    <script>
        const sidebarPreferenceKey = 'pos-sidebar-collapsed';

        function applyPosSidebarPreference() {
            if (localStorage.getItem(sidebarPreferenceKey) === 'true') {
                document.body.classList.add('sidebar-collapsed');
            }
        }

        function togglePosSidebar() {
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem(sidebarPreferenceKey, document.body.classList.contains('sidebar-collapsed') ? 'true' : 'false');
        }

        function updateGlobalClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour12: false });
            document.querySelectorAll('.digital-clock-global').forEach((el) => el.textContent = timeString);
        }

        setInterval(updateGlobalClock, 1000);
        applyPosSidebarPreference();
        updateGlobalClock();
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
