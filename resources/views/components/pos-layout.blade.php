@props([
    'active' => '',
    'title' => 'POS',
    'subtitle' => '',
])

@php
    $user = auth()->user();

    $navItems = $user?->hasRole('Super Admin')
        ? [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard', 'href' => route('super-admin.dashboard')],
            ['key' => 'tenants', 'label' => 'Tenant', 'icon' => 'domain', 'href' => route('super-admin.tenants.index')],
        ]
        : ($user?->hasRole('Admin')
        ? [
            ['key' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard', 'href' => route('dashboard')],
            ['key' => 'products', 'label' => 'Produk', 'icon' => 'inventory_2', 'href' => route('admin.products.index')],
            ['key' => 'transactions', 'label' => 'Transaksi', 'icon' => 'receipt_long', 'href' => route('transactions.index')],
            ['key' => 'users', 'label' => 'Pengguna', 'icon' => 'group', 'href' => route('admin.users.index')],
            ['key' => 'settings', 'label' => 'Pengaturan', 'icon' => 'settings', 'href' => route('admin.settings.edit')],
        ]
        : [
            ['key' => 'cashier', 'label' => 'Kasir', 'icon' => 'shopping_basket', 'href' => route('cashier.index')],
            ['key' => 'orders', 'label' => 'Orderan', 'icon' => 'pending_actions', 'href' => route('cashier.orders.index')],
            ['key' => 'transactions', 'label' => 'Transaksi', 'icon' => 'receipt_long', 'href' => route('transactions.index')],
        ]);

    $roleLabel = $user?->hasRole('Super Admin') ? 'Super Admin' : ($user?->hasRole('Admin') ? 'Admin' : 'Kasir');
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }} - {{ $title }}</title>

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
    </style>
</head>
<body class="mobile-no-zoom bg-[#f6faff] text-[#171c20]" style="font-family: 'Plus Jakarta Sans', sans-serif;">
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
                <a href="{{ $item['href'] }}" title="{{ $item['label'] }}" class="sidebar-link group flex items-center gap-3 rounded-lg px-4 py-3 text-sm font-semibold transition {{ $active === $item['key'] ? 'bg-[#001356] text-white shadow-sm' : 'text-[#454650] hover:bg-[#d5e3fc]' }}">
                    <span class="material-symbols-outlined {{ $active === $item['key'] ? 'text-white' : 'group-hover:text-[#001356]' }}">{{ $item['icon'] }}</span>
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
                    <p class="truncate text-sm font-bold text-[#171c20]">{{ $user?->name ?? 'User' }}</p>
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#767681]">{{ $roleLabel }}</p>
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
        <div class="flex min-w-0 items-center gap-4">
            <button class="rounded-lg p-2 text-[#001356] md:hidden"><span class="material-symbols-outlined">menu</span></button>
            <div class="hidden min-w-0 items-center gap-3 sm:flex">
                <button type="button" onclick="togglePosSidebar()" class="hidden h-11 w-11 shrink-0 items-center justify-center rounded-full border border-[#c6c5d2] bg-white text-[#001356] shadow-sm hover:bg-[#eef3ff] md:flex" aria-label="Buka tutup sidebar">
                    <span class="sidebar-collapse-icon material-symbols-outlined transition-transform">left_panel_close</span>
                </button>
                <div class="min-w-0">
                    <h1 class="truncate text-lg font-extrabold text-[#171c20]">{{ $title }}</h1>
                    @if ($subtitle)
                        <p class="truncate text-xs font-medium text-[#454650]">{{ $subtitle }}</p>
                    @endif
                </div>
            </div>
            <div class="md:hidden">
                <img src="{{ asset('images/keijora-logo-cropped.png') }}" alt="Keijora" class="h-7 w-auto max-w-[120px] object-contain">
            </div>
        </div>
        <div class="flex shrink-0 items-center gap-4">
            @isset($actions)
                {{ $actions }}
            @endisset
            <div class="hidden flex-col items-end lg:flex">
                <span class="text-sm font-bold text-[#171c20]">Sistem Kasir</span>
                <span class="text-xs text-[#454650]">{{ now()->format('H:i:s') }}</span>
            </div>
            <button class="rounded-full p-2 text-[#454650] hover:text-[#001356]"><span class="material-symbols-outlined">notifications</span></button>
            <a href="{{ route('profile.edit') }}" class="rounded-full p-2 text-[#454650] hover:text-[#001356]"><span class="material-symbols-outlined">account_circle</span></a>
        </div>
    </header>

    <main class="pos-main min-h-screen pt-20 transition-all duration-300 md:ml-64">
        <div class="space-y-6 px-4 pb-24 md:px-6 md:pb-6">
            {{ $slot }}
        </div>
    </main>

    <nav class="fixed inset-x-0 bottom-0 z-50 grid grid-cols-3 gap-1.5 border-t border-[#c6c5d2] bg-white/95 px-2 py-2 pb-[calc(0.75rem+env(safe-area-inset-bottom))] shadow-2xl backdrop-blur sm:grid-cols-5 md:hidden">
        @foreach (array_slice($navItems, 0, 5) as $item)
            <a href="{{ $item['href'] }}" class="flex min-h-[4.25rem] min-w-0 flex-1 flex-col items-center justify-center gap-1 rounded-xl px-1.5 py-2 text-[10px] font-bold active:scale-[0.98] {{ $active === $item['key'] ? 'bg-[#001356] text-white' : 'text-[#454650]' }}">
                <span class="material-symbols-outlined text-[22px]">{{ $item['icon'] }}</span>
                <span class="truncate">{{ $item['label'] }}</span>
            </a>
        @endforeach
    </nav>

    <script>
        function togglePosSidebar() {
            document.body.classList.toggle('sidebar-collapsed');
        }
    </script>
</body>
</html>
