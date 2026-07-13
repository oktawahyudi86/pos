@php
    $formatRupiah = fn ($value) => 'Rp '.number_format($value, 0, ',', '.');
$navItems = [
    ['label' => 'Kasir', 'icon' => 'shopping_basket', 'href' => route('cashier.index'), 'active' => true],
    ['label' => 'Orderan', 'icon' => 'pending_actions', 'href' => route('cashier.orders.index'), 'active' => false],
    ['label' => 'Transaksi', 'icon' => 'receipt_long', 'href' => route('transactions.index'), 'active' => false],
];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Kasir - {{ config('app.name', 'Keijora POS') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <style>
        html { -webkit-text-size-adjust: 100%; }
        .material-symbols-outlined { font-variation-settings: 'FILL' 0, 'wght' 400, 'GRAD' 0, 'opsz' 24; }
        .custom-scrollbar::-webkit-scrollbar { width: 4px; height: 4px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: #f1f1f1; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #c6c5d2; border-radius: 999px; }
        .cashier-safe-height { min-height: 100dvh; }
        .cart-drawer { transform: translateY(100%); transition: transform .24s ease; }
        .cart-drawer-backdrop { opacity: 0; pointer-events: none; transition: opacity .2s ease; }
        body.cart-drawer-open { overflow: hidden; }
        body.modal-open { overflow: hidden; }
        body.cart-drawer-open .cart-drawer { transform: translateY(0); }
        body.cart-drawer-open .cart-drawer-backdrop { opacity: 1; pointer-events: auto; }
        @media (min-width: 1024px) {
            body.sidebar-collapsed .cashier-sidebar { width: 5rem; padding-left: 0.75rem; padding-right: 0.75rem; }
            body.sidebar-collapsed .cashier-main { margin-left: 5rem; }
            body.sidebar-collapsed .cashier-topbar { left: 5rem; }
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
        @media (max-width: 1023px) {
            .mobile-no-zoom input,
            .mobile-no-zoom textarea,
            .mobile-no-zoom select {
                font-size: 16px !important;
            }
        }
    </style>
</head>
<body class="mobile-no-zoom overflow-x-hidden bg-[#f6faff] text-[#171c20]" style="font-family: 'Plus Jakarta Sans', sans-serif;">
    <div class="cashier-safe-height flex w-full">
        <aside class="cashier-sidebar fixed left-0 top-0 z-50 hidden h-screen w-64 flex-col gap-2 border-r border-[#c6c5d2] bg-[#f6faff] p-4 transition-all duration-300 lg:flex">
            <div class="sidebar-header mb-8 flex items-center">
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
                        <p class="text-[10px] font-bold uppercase tracking-wider text-[#767681]">Kasir</p>
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

        <main class="cashier-main relative flex min-h-dvh flex-1 flex-col pt-16 transition-all duration-300 lg:ml-64">
            <header class="cashier-topbar fixed left-0 right-0 top-0 z-40 flex h-16 items-center justify-between border-b border-[#c6c5d2] bg-[#f6faff] px-4 transition-all duration-300 sm:px-6 lg:left-64">
                <div class="flex items-center gap-4">
                    <div class="lg:hidden">
                        <img src="{{ asset('images/keijora-logo-cropped.png') }}" alt="Keijora" class="h-7 w-auto max-w-[120px] object-contain">
                    </div>
                    <div class="hidden items-center gap-3 sm:flex">
                        <button type="button" onclick="toggleCashierSidebar()" class="hidden h-11 w-11 shrink-0 items-center justify-center rounded-full border border-[#c6c5d2] bg-white text-[#001356] shadow-sm hover:bg-[#eef3ff] lg:flex" aria-label="Buka tutup sidebar">
                            <span class="sidebar-collapse-icon material-symbols-outlined transition-transform">left_panel_close</span>
                        </button>
                        <div>
                        <h1 class="text-lg font-extrabold text-[#171c20]">Kasir</h1>
                        <p class="text-xs font-medium text-[#454650]">Pilih produk dari master data yang sudah aktif.</p>
                        </div>
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

            @if (session('status') || session('error'))
                <div class="mx-4 mt-4 rounded-xl border px-4 py-3 text-sm font-semibold lg:mx-6 {{ session('error') ? 'border-[#ffdad6] bg-[#fff4f2] text-[#93000a]' : 'border-[#b9c7df] bg-[#eef3ff] text-[#001356]' }}">
                    {{ session('status') ?? session('error') }}
                </div>
            @endif

            <div class="flex flex-1 flex-col gap-4 p-4 pb-32 lg:min-h-0 lg:flex-row lg:overflow-hidden lg:p-6">
                <section class="flex min-h-0 flex-1 flex-col gap-4 lg:flex-[0.62] xl:gap-6">
                    <form method="GET" action="{{ route('cashier.index') }}" class="flex flex-col gap-4 sm:flex-row sm:items-center">
                        @if (request('category'))
                            <input type="hidden" name="category" value="{{ request('category') }}">
                        @endif
                        <div class="relative w-full">
                            <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#454650]">search</span>
                            <input name="search" value="{{ request('search') }}" class="h-12 w-full rounded-full border border-[#c6c5d2] bg-white pl-12 pr-4 text-base text-[#171c20] transition focus:border-[#001356] focus:outline-none focus:ring-1 focus:ring-[#001356] sm:text-sm" placeholder="Cari produk atau barcode..." type="text">
                        </div>
                        <button class="rounded-full bg-[#001356] px-6 py-3 text-sm font-bold text-white">Cari</button>
                    </form>

                    <div class="custom-scrollbar flex gap-2 overflow-x-auto pb-2">
                        <a href="{{ route('cashier.index', request()->except('category')) }}" class="whitespace-nowrap rounded-full px-6 py-2 text-sm font-bold {{ request('category') ? 'bg-[#dfe3e9] text-[#454650]' : 'bg-[#001356] text-white' }}">Semua</a>
                        @foreach ($categories as $category)
                            <a href="{{ route('cashier.index', array_merge(request()->except('page'), ['category' => $category->slug])) }}" class="whitespace-nowrap rounded-full px-6 py-2 text-sm font-bold {{ request('category') === $category->slug ? 'bg-[#001356] text-white' : 'bg-[#dfe3e9] text-[#454650] hover:bg-[#c6c5d2]' }}">{{ $category->name }}</a>
                        @endforeach
                    </div>

                    <div class="custom-scrollbar min-h-0 flex-1 lg:overflow-y-auto lg:pr-1">
                        <div class="grid grid-cols-2 gap-2 sm:gap-3 md:grid-cols-3 lg:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
                            @forelse ($products as $product)
                                <article class="group overflow-hidden rounded-xl border border-[#c6c5d2] bg-white shadow-[0_4px_12px_rgba(27,43,107,0.04)] transition hover:shadow-lg active:scale-[0.99]">
                                    <button type="button" onclick="openProductModal('product-modal-{{ $product->id }}')" class="block w-full text-left {{ $product->stock < 1 ? 'cursor-not-allowed opacity-60' : 'cursor-pointer' }}" @disabled($product->stock < 1)>
                                        <div class="relative h-28 bg-[#dfe3e9] sm:h-36 lg:h-28 xl:h-32 2xl:h-36">
                                            @if ($product->image_path)
                                                <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition group-hover:scale-105">
                                            @else
                                                <div class="flex h-full w-full items-center justify-center">
                                                    <span class="material-symbols-outlined text-4xl text-[#767681]">restaurant</span>
                                                </div>
                                            @endif
                                            @if ($product->stock < 1)
                                                <span class="absolute right-2 top-2 rounded bg-[#ffdad6] px-2 py-1 text-[10px] font-bold uppercase text-[#93000a]">Habis</span>
                                            @endif
                                        </div>
                                        <div class="p-2.5 sm:p-3">
                                            <h3 class="truncate text-xs font-bold text-[#171c20] sm:text-sm">{{ $product->name }}</h3>
                                            <p class="mt-1 text-xs font-extrabold text-[#001356] sm:text-sm">{{ $formatRupiah($product->price) }}</p>
                                            <div class="mt-2 flex items-center justify-between gap-2 text-[10px] text-[#454650] sm:text-xs">
                                                <span class="truncate">{{ $product->category->name }}</span>
                                                <span>Stok: {{ $product->stock }}</span>
                                            </div>
                                            @if ($product->variantGroups->isNotEmpty() || $product->addons->isNotEmpty())
                                                <div class="mt-3 space-y-2 border-t border-[#dfe3e9] pt-3">
                                                    <div class="flex flex-wrap gap-1">
                                                    @if ($product->variantGroups->isNotEmpty())
                                                        <span class="rounded-full bg-[#eef3ff] px-2 py-1 text-[10px] font-bold text-[#001356]">Varian</span>
                                                    @endif
                                                    @if ($product->addons->isNotEmpty())
                                                        <span class="rounded-full bg-[#e7fff2] px-2 py-1 text-[10px] font-bold text-[#005236]">Add-on</span>
                                                    @endif
                                                    </div>
                                                    <p class="line-clamp-2 text-[11px] leading-4 text-[#454650]">
                                                        {{ $product->variantGroups->pluck('name')->merge($product->addons->pluck('name'))->join(', ') }}
                                                    </p>
                                                </div>
                                            @endif
                                        </div>
                                    </button>
                                </article>
                            @empty
                                <div class="col-span-full rounded-2xl border border-dashed border-[#c6c5d2] bg-white p-12 text-center">
                                    <span class="material-symbols-outlined mb-3 text-5xl text-[#767681]">search_off</span>
                                    <h3 class="text-lg font-bold text-[#171c20]">Produk tidak ditemukan</h3>
                                    <p class="mt-1 text-sm text-[#454650]">Cek kata kunci, kategori, atau pastikan produk sudah aktif dan memiliki stok.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </section>

                <aside data-cart-panel="desktop" class="hidden min-h-0 flex-[0.38] flex-col overflow-hidden rounded-2xl border border-[#c6c5d2] bg-white shadow-[0_12px_24px_rgba(27,43,107,0.08)] lg:flex">
                    @include('cashier._cart-panel', ['showCloseButton' => false])
                </aside>
            </div>
        </main>
    </div>

    <div class="cart-drawer-backdrop fixed inset-0 z-[80] bg-[#171c20]/50 lg:hidden" onclick="closeCartDrawer()"></div>

    <section data-cart-panel="drawer" class="cart-drawer fixed inset-x-0 bottom-0 z-[90] flex max-h-[86dvh] flex-col overflow-hidden rounded-t-3xl border border-[#c6c5d2] bg-white shadow-[0_-14px_32px_rgba(27,43,107,0.22)] lg:hidden" aria-label="Keranjang transaksi">
        <div class="mx-auto mt-2 h-1.5 w-12 rounded-full bg-[#c6c5d2]"></div>
        @include('cashier._cart-panel', ['showCloseButton' => true])
    </section>

    <button type="button" onclick="openCartDrawer()" class="fixed bottom-[5.75rem] left-4 right-4 z-[70] flex h-14 items-center justify-between rounded-2xl bg-[#001356] px-5 text-white shadow-[0_12px_24px_rgba(0,19,86,0.22)] active:scale-[0.99] lg:hidden">
        <span class="flex items-center gap-3 font-extrabold">
            <span class="material-symbols-outlined">shopping_cart</span>
            Keranjang
            <span data-cart-count class="rounded-full bg-white px-2 py-0.5 text-xs font-extrabold text-[#001356]">{{ $cart->count() }}</span>
        </span>
        <span data-cart-total class="font-extrabold">{{ $formatRupiah($cartTotal) }}</span>
    </button>

    <nav class="fixed bottom-0 left-0 z-50 flex w-full items-center justify-around rounded-t-xl bg-[#dfe3e9] px-4 py-3 shadow-[0_-4px_12px_rgba(27,43,107,0.08)] lg:hidden">
        @foreach (array_slice($navItems, 0, 4) as $item)
            <a href="{{ $item['href'] }}" class="flex flex-col items-center justify-center px-4 py-2 text-xs font-bold transition {{ $item['active'] ? 'rounded-full bg-[#001356] text-white' : 'text-[#454650]' }}">
                <span class="material-symbols-outlined">{{ $item['icon'] }}</span>
                <span>{{ \Illuminate\Support\Str::limit($item['label'], 8, '') }}</span>
            </a>
        @endforeach
    </nav>

    <div id="cashier-toast" class="pointer-events-none fixed left-1/2 top-20 z-[160] hidden -translate-x-1/2 rounded-full bg-[#001356] px-4 py-2 text-sm font-bold text-white shadow-lg"></div>

    <div id="payment-modal" class="hidden fixed inset-0 z-[120] items-end justify-center bg-[#171c20]/50 p-0 sm:items-center sm:p-4 lg:p-8">
        <form id="checkout-form" method="POST" action="{{ route('cashier.checkout') }}" data-ajax-checkout="true" onsubmit="sanitizeCheckoutForm()" class="max-h-[92dvh] w-full max-w-md overflow-y-auto rounded-t-3xl bg-white shadow-[0_12px_24px_rgba(27,43,107,0.18)] sm:rounded-2xl">
            @csrf
            <div class="flex items-center justify-between border-b border-[#c6c5d2] bg-white p-5">
                <h3 class="text-lg font-extrabold text-[#171c20]">{{ $paymentMethod === 'qris' ? 'Pembayaran QRIS' : 'Pembayaran Tunai' }}</h3>
                <button type="button" onclick="closePaymentModal()" class="flex h-11 w-11 items-center justify-center rounded-full text-[#454650] hover:bg-[#dfe3e9]">
                    <span class="material-symbols-outlined text-[26px]">close</span>
                </button>
            </div>

            <div class="space-y-5 p-6">
                <div class="flex items-center justify-between rounded-xl bg-[#e4e8ee] p-4">
                    <span class="text-sm font-bold text-[#454650]">Total Tagihan</span>
                    <span data-payment-total-text class="text-2xl font-extrabold text-[#001356]">{{ $formatRupiah($cartTotal) }}</span>
                </div>

                @if ($paymentMethod === 'qris')
                    <div class="rounded-2xl border border-[#c6c5d2] bg-[#f6faff] p-6 text-center">
                        <div class="mx-auto flex h-28 w-28 items-center justify-center rounded-2xl bg-white text-[#001356] shadow-sm">
                            <span class="material-symbols-outlined text-[72px]">qr_code_2</span>
                        </div>
                        <h4 class="mt-4 text-base font-extrabold text-[#171c20]">Scan QRIS untuk membayar</h4>
                        <p class="mt-1 text-sm text-[#454650]">QR asli akan disambungkan saat integrasi payment gateway dibuat.</p>
                    </div>
                @else
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-[#454650]">Uang Diterima</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-sm font-bold text-[#454650]">Rp</span>
                            <input id="cash-received-input" name="paid_amount" type="text" inputmode="numeric" value="{{ $cartTotal > 0 ? $cartTotal : '' }}" data-total="{{ $cartTotal }}" class="h-14 w-full rounded-xl border-[#c6c5d2] bg-[#f6faff] pl-12 pr-4 text-[16px] font-bold text-[#171c20] focus:border-[#001356] focus:ring-[#001356] sm:text-2xl">
                        </div>
                        <div class="mt-2 flex flex-wrap gap-2">
                            <button type="button" onclick="setCashReceived({{ $cartTotal }})" class="rounded-full border border-[#c6c5d2] bg-[#eaeef4] px-3 py-1.5 text-sm font-semibold text-[#454650]">Pas</button>
                            <button type="button" onclick="setCashReceived(50000)" class="rounded-full border border-[#c6c5d2] bg-[#eaeef4] px-3 py-1.5 text-sm font-semibold text-[#454650]">50.000</button>
                            <button type="button" onclick="setCashReceived(100000)" class="rounded-full border border-[#c6c5d2] bg-[#eaeef4] px-3 py-1.5 text-sm font-semibold text-[#454650]">100.000</button>
                        </div>
                    </div>

                    <div class="flex items-center justify-between border-t border-[#c6c5d2] pt-5">
                        <span class="text-sm font-bold text-[#454650]">Kembalian</span>
                        <span id="cash-change-display" class="text-2xl font-extrabold text-[#171c20]">Rp 0</span>
                    </div>
                @endif

                <div class="space-y-4 border-t border-[#c6c5d2] pt-5">
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-[#454650]">Nama Pembeli (Opsional)</label>
                            <input type="text" name="customer_name" placeholder="Masukkan nama..." class="h-12 w-full rounded-xl border-[#c6c5d2] bg-[#f6faff] text-base focus:border-[#001356] focus:ring-[#001356] sm:text-sm">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-semibold text-[#454650]">Nomor HP (Opsional)</label>
                            <input type="tel" name="customer_phone" placeholder="08..." class="h-12 w-full rounded-xl border-[#c6c5d2] bg-[#f6faff] text-base focus:border-[#001356] focus:ring-[#001356] sm:text-sm">
                    </div>
                </div>
            </div>

            <div class="flex gap-3 border-t border-[#c6c5d2] bg-white p-5">
                <button type="button" onclick="closePaymentModal()" class="flex-1 rounded-xl border border-[#c6c5d2] py-3 text-sm font-bold text-[#454650]">Batal</button>
                <button class="flex-[2] rounded-xl bg-[#001356] py-3 text-sm font-extrabold text-white shadow-md">
                    Konfirmasi Pembayaran
                </button>
            </div>
        </form>
    </div>

    <div id="payment-success-modal" class="hidden fixed inset-0 z-[130] items-end justify-center bg-[#171c20]/50 p-0 sm:items-center sm:p-4">
        <div class="w-full max-w-sm rounded-t-3xl bg-[#f6faff] p-6 text-center shadow-[0_12px_24px_rgba(27,43,107,0.18)] sm:rounded-2xl">
            <div class="mx-auto flex h-20 w-20 items-center justify-center rounded-full bg-[#6ffbbe] text-[#002113]">
                <span class="material-symbols-outlined text-[48px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
            </div>
            <h3 class="mt-5 text-2xl font-extrabold text-[#171c20]">Pembayaran Berhasil!</h3>
            <p class="mt-2 text-sm text-[#454650]">Kembalian: <span id="success-change-display" class="font-extrabold text-[#171c20]">Rp 0</span></p>
            <p id="success-invoice-display" class="mt-1 text-xs font-bold uppercase tracking-[0.2em] text-[#767681]"></p>
            <div class="mt-6 space-y-3">
                <button type="button" onclick="openReceiptTab()" class="flex w-full items-center justify-center gap-2 rounded-xl bg-[#001356] py-3 text-sm font-extrabold text-white">
                    <span class="material-symbols-outlined">print</span>
                    Cetak Struk
                </button>
                <button type="button" onclick="shareReceiptWhatsApp()" class="flex w-full items-center justify-center gap-2 rounded-xl border border-[#c6c5d2] py-3 text-sm font-bold text-[#171c20]">
                    <span class="material-symbols-outlined text-green-600">send</span>
                    Kirim ke WhatsApp
                </button>
                <button type="button" onclick="closePaymentSuccessModal()" class="w-full rounded-xl py-3 text-sm font-bold text-[#454650]">Tutup</button>
            </div>
        </div>
    </div>

    @foreach ($products as $product)
        <div id="product-modal-{{ $product->id }}" class="hidden fixed inset-0 z-[100] items-end justify-center bg-[#171c20]/50 p-0 sm:items-center sm:p-4 lg:p-8">
            <form method="POST" action="{{ route('cashier.cart.store') }}" class="max-h-[92dvh] w-full max-w-2xl overflow-y-auto rounded-t-3xl bg-white shadow-[0_12px_24px_rgba(27,43,107,0.18)] sm:rounded-2xl" data-ajax-cart data-close-modal="product-modal-{{ $product->id }}">
                @csrf
                <input type="hidden" name="product_id" value="{{ $product->id }}">
                <div class="flex items-start justify-between border-b border-[#c6c5d2] bg-[#f0f4fa] p-5 md:p-6">
                    <div>
                        <h3 class="text-lg font-extrabold text-[#171c20]">{{ $product->name }}</h3>
                        <p class="text-sm font-bold text-[#001356]">{{ $formatRupiah($product->price) }}</p>
                        @if ($product->variantGroups->isNotEmpty() || $product->addons->isNotEmpty())
                            <div class="mt-3 flex flex-wrap gap-2">
                                @foreach ($product->variantGroups as $group)
                                    <span class="rounded-full bg-[#eef3ff] px-3 py-1 text-xs font-bold text-[#001356]">{{ $group->name }}</span>
                                @endforeach
                                @foreach ($product->addons as $addon)
                                    <span class="rounded-full bg-[#e7fff2] px-3 py-1 text-xs font-bold text-[#005236]">{{ $addon->name }}</span>
                                @endforeach
                            </div>
                        @else
                            <p class="mt-2 text-xs font-semibold text-[#767681]">Produk ini belum memiliki varian atau add-on.</p>
                        @endif
                    </div>
                    <button type="button" onclick="closeProductModal('product-modal-{{ $product->id }}')" class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full text-[#454650] hover:bg-[#dfe3e9]"><span class="material-symbols-outlined text-[28px]">close</span></button>
                </div>
                <div class="space-y-5 p-5 md:p-6">
                    @foreach ($product->variantGroups as $group)
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <label class="text-sm font-extrabold text-[#171c20]">{{ $group->name }}</label>
                                <span class="text-xs font-bold text-[#767681]">{{ $group->is_required ? 'Wajib pilih' : 'Opsional' }}</span>
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach ($group->options->where('is_active', true) as $option)
                                    <label class="flex min-h-14 cursor-pointer items-center justify-between rounded-xl border border-[#c6c5d2] p-4 hover:border-[#001356] has-[:checked]:border-[#001356] has-[:checked]:bg-[#eef3ff]">
                                        <span class="flex items-center gap-3">
                                            <input type="radio" name="variant_options[{{ $group->id }}]" value="{{ $option->id }}" @checked($loop->first && $group->is_required) class="h-5 w-5 border-[#c6c5d2] text-[#001356] focus:ring-[#001356]">
                                            <span class="text-sm font-semibold text-[#171c20]">{{ $option->name }}</span>
                                        </span>
                                        <span class="text-xs font-bold text-[#454650]">+ {{ $formatRupiah($option->price_delta) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    @if ($product->addons->isNotEmpty())
                        <div>
                            <label class="mb-2 block text-sm font-extrabold text-[#171c20]">Add-on & Topping</label>
                            <div class="grid gap-3 sm:grid-cols-2">
                                @foreach ($product->addons->where('is_active', true) as $addon)
                                    <label class="flex min-h-14 cursor-pointer items-center justify-between rounded-xl border border-[#c6c5d2] p-4 hover:border-[#001356] has-[:checked]:border-[#001356] has-[:checked]:bg-[#e7fff2]">
                                        <span class="flex items-center gap-3">
                                            <input type="checkbox" name="addons[]" value="{{ $addon->id }}" class="h-5 w-5 rounded border-[#c6c5d2] text-[#001356] focus:ring-[#001356]">
                                            <span class="text-sm font-semibold text-[#171c20]">{{ $addon->name }}</span>
                                        </span>
                                        <span class="text-xs font-bold text-[#454650]">+ {{ $formatRupiah($addon->price) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="grid gap-4 sm:grid-cols-[180px_1fr]">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-[#454650]">Qty</label>
                            <div class="flex h-12 overflow-hidden rounded-xl border border-[#c6c5d2] bg-white">
                                <button type="button" onclick="changeModalQty('product-qty-{{ $product->id }}', -1)" class="flex w-12 items-center justify-center text-[#001356] hover:bg-[#eef3ff]">
                                    <span class="material-symbols-outlined">remove</span>
                                </button>
                                <input id="product-qty-{{ $product->id }}" type="number" name="quantity" value="1" min="1" max="{{ $product->stock }}" class="w-full border-0 text-center text-base font-bold text-[#171c20] focus:ring-0">
                                <button type="button" onclick="changeModalQty('product-qty-{{ $product->id }}', 1)" class="flex w-12 items-center justify-center bg-[#001356] text-white hover:brightness-110">
                                    <span class="material-symbols-outlined">add</span>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-[#454650]">Catatan</label>
                            <input type="text" name="note" maxlength="160" placeholder="Contoh: gula sedikit" class="h-12 w-full rounded-xl border-[#c6c5d2] text-sm focus:border-[#001356] focus:ring-[#001356]">
                        </div>
                    </div>
                </div>
                <div class="sticky bottom-0 flex gap-3 border-t border-[#c6c5d2] bg-white p-5 md:p-6">
                    <button type="button" onclick="closeProductModal('product-modal-{{ $product->id }}')" class="flex-1 rounded-xl border border-[#c6c5d2] py-4 text-sm font-bold text-[#454650]">Batal</button>
                    <button class="flex-[2] rounded-xl bg-[#001356] py-4 text-sm font-extrabold text-white shadow-md">Tambah ke Keranjang</button>
                </div>
            </form>
        </div>
    @endforeach

    <div data-cart-edit-modals>
        @include('cashier._cart-edit-modals')
    </div>

    <script>
        const cashierSidebarPreferenceKey = 'cashier-sidebar-collapsed';

        function applyCashierSidebarPreference() {
            if (localStorage.getItem(cashierSidebarPreferenceKey) === 'true') {
                document.body.classList.add('sidebar-collapsed');
            }
        }

        function toggleCashierSidebar() {
            document.body.classList.toggle('sidebar-collapsed');
            localStorage.setItem(
                cashierSidebarPreferenceKey,
                document.body.classList.contains('sidebar-collapsed') ? 'true' : 'false'
            );
        }

        function updateGlobalClock() {
            const now = new Date();
            const timeString = now.toLocaleTimeString('id-ID', { hour12: false });
            document.querySelectorAll('.digital-clock-global').forEach((el) => el.textContent = timeString);
        }

        function openProductModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                closeCartDrawer();
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('modal-open');
            }
        }

        function closeProductModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('modal-open');
            }
        }

        function openCartDrawer() {
            document.body.classList.add('cart-drawer-open');
        }

        function closeCartDrawer() {
            document.body.classList.remove('cart-drawer-open');
        }

        function formatRupiah(value) {
            return new Intl.NumberFormat('id-ID', {
                style: 'currency',
                currency: 'IDR',
                maximumFractionDigits: 0,
            }).format(Math.max(0, Number(value || 0))).replace(/\s/g, ' ');
        }

        function numericValue(value) {
            return Number(String(value || '').replace(/[^\d]/g, '')) || 0;
        }

        function updateCashChange() {
            const input = document.getElementById('cash-received-input');
            const display = document.getElementById('cash-change-display');
            const successDisplay = document.getElementById('success-change-display');
            if (!input || !display) return;

            const total = Number(input.dataset.total || 0);
            const received = numericValue(input.value);
            const change = Math.max(0, received - total);
            display.textContent = formatRupiah(change);
            if (successDisplay) successDisplay.textContent = formatRupiah(change);
        }

        function setCashReceived(value) {
            const input = document.getElementById('cash-received-input');
            if (!input) return;
            input.value = value;
            updateCashChange();
        }

        function openPaymentModal() {
            const modal = document.getElementById('payment-modal');
            if (modal) {
                closeCartDrawer();
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('modal-open');
                updateCashChange();
            }
        }

        function closePaymentModal() {
            const modal = document.getElementById('payment-modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('modal-open');
            }
        }

        function confirmPayment() {
            closePaymentModal();
            const modal = document.getElementById('payment-success-modal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.classList.add('modal-open');
            }
        }

        function sanitizeCheckoutForm() {
            const input = document.getElementById('cash-received-input');
            if (input) {
                input.value = numericValue(input.value);
            }
        }

        function closePaymentSuccessModal() {
            const modal = document.getElementById('payment-success-modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.classList.remove('modal-open');
            }
        }

        function openReceiptTab() {
            if (window.cashierReceiptUrl) {
                window.open(window.cashierReceiptUrl, '_blank', 'noopener,noreferrer');
                return;
            }
            showCashierToast('Struk belum tersedia.', 'error');
        }

        async function shareReceiptWhatsApp() {
            if (!window.cashierReceiptUrl) {
                showCashierToast('Struk belum tersedia.', 'error');
                return;
            }

            const message = window.cashierWhatsappMessage || 'Terima kasih atas pesanan Anda.';
            const shareText = message;
            const waUrl = `https://wa.me/?text=${encodeURIComponent(shareText)}`;

            window.open(waUrl, '_blank', 'noopener,noreferrer');
        }

        function showCashierToast(message, type = 'success') {
            const toast = document.getElementById('cashier-toast');
            if (!toast || !message) return;

            toast.textContent = message;
            toast.classList.toggle('bg-[#ba1a1a]', type === 'error');
            toast.classList.toggle('bg-[#001356]', type !== 'error');
            toast.classList.remove('hidden');

            clearTimeout(window.cashierToastTimer);
            window.cashierToastTimer = setTimeout(() => {
                toast.classList.add('hidden');
            }, 2200);
        }

        function updateCartUi(payload) {
            if (!payload) return;

            const desktopPanel = document.querySelector('[data-cart-panel="desktop"]');
            const drawerPanel = document.querySelector('[data-cart-panel="drawer"]');
            if (desktopPanel && payload.html?.cart_panel) {
                desktopPanel.innerHTML = payload.html.cart_panel;
            }
            if (drawerPanel && payload.html?.cart_drawer_panel) {
                drawerPanel.innerHTML = '<div class="mx-auto mt-2 h-1.5 w-12 rounded-full bg-[#c6c5d2]"></div>' + payload.html.cart_drawer_panel;
            }
            const editModalContainer = document.querySelector('[data-cart-edit-modals]');
            if (editModalContainer && payload.html?.cart_edit_modals) {
                editModalContainer.innerHTML = payload.html.cart_edit_modals;
            }

            document.querySelectorAll('[data-cart-count]').forEach((el) => {
                el.textContent = payload.summary?.count ?? 0;
            });
            document.querySelectorAll('[data-cart-total]').forEach((el) => {
                el.textContent = payload.summary?.formatted?.total ?? formatRupiah(0);
            });
            document.querySelectorAll('[data-payment-total-text]').forEach((el) => {
                el.textContent = payload.summary?.formatted?.total ?? formatRupiah(0);
            });

            const cashInput = document.getElementById('cash-received-input');
            if (cashInput && payload.summary) {
                cashInput.dataset.total = payload.summary.total ?? 0;
                if (numericValue(cashInput.value) === 0 || numericValue(cashInput.value) < Number(payload.summary.total || 0)) {
                    cashInput.value = payload.summary.total > 0 ? payload.summary.total : '';
                }
                updateCashChange();
            }
        }

        async function submitCartAjax(form, submitter = null) {
            const formData = new FormData(form);
            if (submitter?.name) {
                formData.set(submitter.name, submitter.value ?? '');
            }

            const buttons = form.querySelectorAll('button, input, select');
            const originalSubmitterHtml = submitter?.innerHTML;

            buttons.forEach((button) => button.disabled = true);
            if (submitter) {
                submitter.classList.add('opacity-70');
                if (submitter.tagName === 'BUTTON') {
                    submitter.innerHTML = '<span class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>';
                }
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });
                const payload = await response.json();

                if (!response.ok || payload.ok === false) {
                    showCashierToast(payload.message || 'Aksi gagal diproses.', 'error');
                    return;
                }

                updateCartUi(payload);
                showCashierToast(payload.message || 'Keranjang diperbarui.');

                if (form.dataset.closeModal) {
                    closeProductModal(form.dataset.closeModal);
                }
            } catch (error) {
                showCashierToast('Koneksi lambat atau terputus. Coba ulangi.', 'error');
            } finally {
                buttons.forEach((button) => button.disabled = false);
                if (submitter) {
                    submitter.classList.remove('opacity-70');
                    if (originalSubmitterHtml && submitter.tagName === 'BUTTON') {
                        submitter.innerHTML = originalSubmitterHtml;
                    }
                }
            }
        }

        async function submitCheckoutAjax(form, submitter = null) {
            const formData = new FormData(form);
            if (submitter?.name) {
                formData.set(submitter.name, submitter.value ?? '');
            }

            const buttons = form.querySelectorAll('button, input, select');
            const originalSubmitterHtml = submitter?.innerHTML;
            buttons.forEach((button) => button.disabled = true);
            if (submitter && submitter.tagName === 'BUTTON') {
                submitter.classList.add('opacity-70');
                submitter.innerHTML = '<span class="material-symbols-outlined animate-spin text-[18px]">progress_activity</span>';
            }

            try {
                const response = await fetch(form.action, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: formData,
                });

                if (response.redirected) {
                    window.location.href = response.url;
                    return;
                }

                const payload = await response.json();
                if (!response.ok || payload.ok === false) {
                    showCashierToast(payload.message || 'Pembayaran gagal diproses.', 'error');
                    return;
                }

                closePaymentModal();
                updateCartUi(payload);

                window.cashierReceiptUrl = payload.transaction?.receipt_url || null;
                window.cashierWhatsappMessage = payload.transaction?.whatsapp_message || null;

                const invoiceDisplay = document.getElementById('success-invoice-display');
                const successDisplay = document.getElementById('success-change-display');
                if (invoiceDisplay) {
                    invoiceDisplay.textContent = payload.transaction?.invoice_number ? `INV ${payload.transaction.invoice_number}` : '';
                }
                if (successDisplay) {
                    successDisplay.textContent = formatRupiah(payload.transaction?.change_amount ?? 0);
                }

                const successModal = document.getElementById('payment-success-modal');
                if (successModal) {
                    successModal.classList.remove('hidden');
                    successModal.classList.add('flex');
                    document.body.classList.add('modal-open');
                }

                showCashierToast(payload.message || 'Transaksi berhasil disimpan.');
            } catch (error) {
                showCashierToast('Koneksi lambat atau terputus. Coba ulangi.', 'error');
            } finally {
                buttons.forEach((button) => button.disabled = false);
                if (submitter) {
                    submitter.classList.remove('opacity-70');
                    if (originalSubmitterHtml && submitter.tagName === 'BUTTON') {
                        submitter.innerHTML = originalSubmitterHtml;
                    }
                }
            }
        }

        function changeModalQty(inputId, step) {
            const input = document.getElementById(inputId);
            if (!input) return;

            const min = Number(input.min || 1);
            const max = Number(input.max || 999);
            const current = Number(input.value || min);
            input.value = Math.max(min, Math.min(max, current + step));
        }

        setInterval(updateGlobalClock, 1000);
        applyCashierSidebarPreference();
        updateGlobalClock();

        const cashReceivedInput = document.getElementById('cash-received-input');
        if (cashReceivedInput) {
            cashReceivedInput.addEventListener('input', updateCashChange);
            updateCashChange();
        }

        document.addEventListener('submit', (event) => {
            const form = event.target.closest('form[data-ajax-cart]');
            if (!form) return;

            event.preventDefault();
            submitCartAjax(form, event.submitter);
        });

        document.addEventListener('submit', (event) => {
            const form = event.target.closest('form[data-ajax-checkout]');
            if (!form) return;

            event.preventDefault();
            submitCheckoutAjax(form, event.submitter);
        });
    </script>
</body>
</html>
