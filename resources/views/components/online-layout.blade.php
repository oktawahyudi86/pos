@props([
    'tenant',
    'title' => 'Keijora Online',
    'active' => 'menu',
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
    </style>
</head>
<body class="mobile-no-zoom overflow-x-hidden bg-[#eef3fb] text-[#171c20]" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <header class="fixed inset-x-0 top-0 z-50 border-b border-[#d7dde8] bg-white/98 shadow-[0_4px_12px_rgba(27,43,107,0.04)] backdrop-blur">
        <div class="mx-auto flex h-20 w-full max-w-5xl items-center justify-between px-4 sm:px-6">
            <a href="{{ route('online-orders.catalog', $tenant) }}" class="flex h-11 w-11 items-center justify-center rounded-full text-[#001356]">
                <span class="material-symbols-outlined text-[30px]">restaurant_menu</span>
            </a>

            <div class="flex min-w-0 items-center gap-2">
                @if ($receiptLogoUrl)
                    <img src="{{ $receiptLogoUrl }}" alt="{{ $receipt['cafe_name'] ?? $tenant?->name }}" class="h-9 w-auto object-contain">
                @else
                    <img src="{{ asset('images/keijora-logo-cropped.png') }}" alt="Keijora" class="h-9 w-auto object-contain">
                @endif
                @if ($customerBrandText !== '')
                    <span class="truncate text-sm font-extrabold leading-none text-[#001356]">{{ $customerBrandText }}</span>
                @endif
            </div>

            <a href="{{ route('online-orders.checkout.form', $tenant) }}" class="relative flex h-11 w-11 items-center justify-center rounded-full text-[#001356]">
                <svg viewBox="0 0 24 24" class="h-8 w-8" aria-hidden="true">
                    <path fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12l-1 13H7L6 7zm3 0a3 3 0 0 1 6 0"></path>
                </svg>
                @if ($cartCount > 0)
                    <span class="absolute right-0 top-0 flex h-6 min-w-6 items-center justify-center rounded-full bg-[#ce2418] px-1 text-[12px] font-bold leading-none text-white">{{ $cartCount > 9 ? '9+' : $cartCount }}</span>
                @endif
            </a>
        </div>
    </header>

    <main class="mx-auto min-h-screen w-full max-w-5xl px-4 pb-32 pt-24 sm:px-6">
        {{ $slot }}
    </main>

    <nav class="fixed inset-x-0 bottom-0 z-50 border-t border-[#d7dde8] bg-white shadow-[0_-4px_12px_rgba(27,43,107,0.04)]">
        <div class="mx-auto grid w-full max-w-5xl grid-cols-3 px-2 py-1 sm:px-4">
            <a href="{{ route('online-orders.catalog', $tenant) }}" class="flex flex-col items-center justify-center rounded-xl py-2 {{ $active === 'menu' ? 'bg-[#001356] text-white' : 'text-[#454650]' }}">
                <span class="material-symbols-outlined text-[26px]">restaurant_menu</span>
                <span class="mt-1 text-[11px] font-bold">Menu</span>
            </a>
            <a href="{{ route('online-orders.track', $tenant) }}" class="flex flex-col items-center justify-center rounded-xl py-2 {{ $active === 'orders' ? 'bg-[#001356] text-white' : 'text-[#454650]' }}">
                <span class="material-symbols-outlined text-[26px]">receipt_long</span>
                <span class="mt-1 text-[11px] font-bold">Orders</span>
            </a>
            <a href="{{ route('online-orders.checkout.form', $tenant) }}" class="flex flex-col items-center justify-center rounded-xl py-2 {{ $active === 'cart' ? 'bg-[#001356] text-white' : 'text-[#454650]' }}">
                <span class="material-symbols-outlined text-[26px]">shopping_bag</span>
                <span class="mt-1 text-[11px] font-bold">Cart</span>
            </a>
        </div>
    </nav>
</body>
</html>
