@props([
    'tenant',
    'title' => 'Keijora Online',
    'active' => 'menu',
    'backUrl' => null,
    'backLabel' => 'Kembali',
])

@php
    $cartCount = collect(session('online_order_cart_'.$tenant->id, []))->sum('quantity');
    $receipt = \App\Models\Setting::getValue('receipt', [
        'logo_path' => null,
        'cafe_name' => $tenant?->name ?? config('app.name', 'Keijora POS'),
    ], $tenant->id);
    $receiptLogoPath = $receipt['logo_path'] ?? null;
    $receiptLogoUrl = $receiptLogoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($receiptLogoPath)
        ? \Illuminate\Support\Facades\Storage::url($receiptLogoPath)
        : null;
    $customerBrandText = trim((string) ($receipt['cafe_name'] ?? ''));
    $pageTitle = $customerBrandText !== '' ? $customerBrandText : ($tenant?->name ?? config('app.name', 'Keijora POS'));
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle }} - {{ $title }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        .material-symbols-outlined {
            font-family: 'Material Symbols Outlined';
            font-style: normal;
            font-weight: normal;
            font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
        .mobile-no-zoom input,
        .mobile-no-zoom textarea,
        .mobile-no-zoom select {
            font-size: 16px !important;
        }
        .online-header-scrolled {
            border-bottom-color: rgba(215, 221, 232, 0.18);
            background: rgba(0, 19, 86, 0.84);
            box-shadow: 0 8px 24px rgba(0, 19, 86, 0.18);
        }
        .online-header-scrolled .online-header-foreground {
            color: #ffffff;
        }
        .online-header-scrolled .online-header-brand {
            color: #ffffff;
        }
    </style>
</head>
<body class="page-loading mobile-no-zoom overflow-x-hidden bg-[#eef3fb] text-[#171c20]" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <div class="customer-page-skeleton" aria-hidden="true">
        <div class="mx-auto flex h-full w-full max-w-5xl flex-col px-4 pb-32 pt-0 sm:px-6">
            <div class="flex h-20 items-center justify-between">
                <div class="skeleton-shimmer h-11 w-11 rounded-full"></div>
                <div class="flex items-center gap-2">
                    <div class="skeleton-shimmer h-9 w-9 rounded-lg"></div>
                    <div class="skeleton-shimmer h-4 w-28 rounded-full"></div>
                </div>
                <div class="skeleton-shimmer h-11 w-11 rounded-full"></div>
            </div>
            <div class="mt-4 space-y-4">
                <div class="skeleton-shimmer h-32 rounded-[28px]"></div>
                <div class="grid grid-cols-2 gap-4">
                    <div class="skeleton-shimmer h-64 rounded-[28px]"></div>
                    <div class="skeleton-shimmer h-64 rounded-[28px]"></div>
                    <div class="skeleton-shimmer h-64 rounded-[28px]"></div>
                    <div class="skeleton-shimmer h-64 rounded-[28px]"></div>
                </div>
            </div>
        </div>
        <div class="fixed inset-x-0 bottom-0 border-t border-[#d7dde8] bg-white px-2 py-1 sm:px-4">
            <div class="mx-auto grid w-full max-w-5xl grid-cols-4 gap-2">
                <div class="skeleton-shimmer h-16 rounded-xl"></div>
                <div class="skeleton-shimmer h-16 rounded-xl"></div>
                <div class="skeleton-shimmer h-16 rounded-xl"></div>
                <div class="skeleton-shimmer h-16 rounded-xl"></div>
            </div>
        </div>
    </div>

    <header id="online-page-header" class="fixed inset-x-0 top-0 z-50 border-b border-[#d7dde8] bg-white/98 shadow-[0_4px_12px_rgba(27,43,107,0.04)] backdrop-blur transition-all duration-200">
        <div class="mx-auto flex h-20 w-full max-w-5xl items-center justify-between px-4 sm:px-6">
            <!-- Left: Logo + Brand -->
            <div class="flex items-center gap-2">
                @if ($backUrl)
                    <a href="{{ $backUrl }}" class="online-header-foreground flex h-9 w-9 items-center justify-center rounded-full text-[#001356] transition-colors duration-200 active:scale-[0.98] mr-2" aria-label="{{ $backLabel }}">
                        <span class="material-symbols-outlined text-[24px]">arrow_back</span>
                    </a>
                @endif
                @if ($receiptLogoUrl)
                    <img src="{{ $receiptLogoUrl }}" alt="{{ $receipt['cafe_name'] ?? $tenant?->name }}" class="h-7 w-auto object-contain sm:h-8">
                @else
                    <img src="{{ asset('images/keijora-logo-cropped.png') }}" alt="Keijora" class="h-7 w-auto object-contain sm:h-8">
                @endif
                @if ($customerBrandText !== '')
                    <span class="online-header-brand truncate text-xs font-extrabold leading-none text-[#001356] transition-colors duration-200 sm:text-sm">{{ $customerBrandText }}</span>
                @endif
            </div>

            <!-- Center: Location -->
            <a href="{{ route('online-orders.location', $tenant) }}" class="flex items-center gap-1 rounded-full border border-[#c6c5d2] bg-white px-2 py-1.5 transition active:scale-[0.98] hover:border-[#001356] sm:px-3">
                <span class="material-symbols-outlined text-[16px] text-[#001356]" style="font-variation-settings: 'FILL' 1;">location_on</span>
                <span id="header-location-text" class="max-w-[80px] truncate text-xs font-semibold text-[#454650] sm:max-w-[150px]">Mendeteksi lokasi...</span>
                <span class="material-symbols-outlined text-[18px] text-[#001356]">edit</span>
            </a>

            <!-- Right: Cart -->
            <a href="{{ route('online-orders.address', $tenant) }}" class="online-header-foreground relative flex h-10 w-10 items-center justify-center rounded-full text-[#001356] transition-colors duration-200 sm:h-11 sm:w-11">
                <svg viewBox="0 0 24 24" class="h-7 w-7 sm:h-8 sm:w-8" aria-hidden="true">
                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12l-1 13H7L6 7zm3 0a3 3 0 0 1 6 0"></path>
                </svg>
                @if ($cartCount > 0)
                    <span class="absolute right-0 top-0 flex h-5 min-w-5 items-center justify-center rounded-full bg-[#ce2418] px-0.5 text-[11px] font-bold leading-none text-white sm:h-6 sm:min-w-6 sm:px-1 sm:text-[12px]">{{ $cartCount > 9 ? '9+' : $cartCount }}</span>
                @endif
            </a>
        </div>
    </header>

    <div class="customer-page-content">

    <main class="mx-auto min-h-screen w-full max-w-5xl px-4 pb-32 pt-24 sm:px-6">
        {{ $slot }}
    </main>

    <nav class="fixed inset-x-0 bottom-0 z-50 border-t border-[#d7dde8] bg-white shadow-[0_-4px_12px_rgba(27,43,107,0.04)]">
        <div class="mx-auto grid w-full max-w-5xl grid-cols-4 px-2 py-1 sm:px-4">
            <a href="{{ route('online-orders.catalog', $tenant) }}" class="flex flex-col items-center justify-center rounded-xl py-2 {{ $active === 'menu' ? 'bg-[#001356] text-white' : 'text-[#454650]' }}">
                <span class="material-symbols-outlined text-[26px]">restaurant_menu</span>
                <span class="mt-1 text-[11px] font-bold">Menu</span>
            </a>
            <a href="{{ route('online-orders.track', $tenant) }}" class="flex flex-col items-center justify-center rounded-xl py-2 {{ $active === 'orders' ? 'bg-[#001356] text-white' : 'text-[#454650]' }}">
                <span class="material-symbols-outlined text-[26px]">receipt_long</span>
                <span class="mt-1 text-[11px] font-bold">Orders</span>
            </a>
            <a href="{{ route('online-orders.address', $tenant) }}" class="flex flex-col items-center justify-center rounded-xl py-2 {{ $active === 'cart' ? 'bg-[#001356] text-white' : 'text-[#454650]' }}">
                <span class="material-symbols-outlined text-[26px]">shopping_bag</span>
                <span class="mt-1 text-[11px] font-bold">Cart</span>
            </a>
            <a href="{{ route('online-orders.profile', $tenant) }}" class="relative flex flex-col items-center justify-center rounded-xl py-2 {{ $active === 'profile' ? 'bg-[#001356] text-white' : 'text-[#454650]' }}">
                <span class="material-symbols-outlined text-[26px]" style="font-variation-settings: 'FILL' {{ auth()->check() ? '1' : '0' }};">account_circle</span>
                <span class="mt-1 text-[11px] font-bold">Profil</span>
                @auth
                    <span class="absolute right-2 top-2 h-2.5 w-2.5 rounded-full border border-white bg-[#24d18f]"></span>
                @endauth
            </a>
        </div>
    </nav>
    <script>
        const onlinePageHeader = document.getElementById('online-page-header');

        function syncOnlineHeaderState() {
            if (!onlinePageHeader) return;

            onlinePageHeader.classList.toggle('online-header-scrolled', window.scrollY > 12);
        }

        window.addEventListener('scroll', syncOnlineHeaderState, { passive: true });
        syncOnlineHeaderState();
    </script>
    <script>
        window.onlineHeaderConfig = {
            geoapifyApiKey: '{{ config('services.geoapify.key') }}',
            tenant: @json($tenant)
        };
    </script>
    </div>
</body>
</html>
