@php
    $navItems = [
        ['label' => 'Dashboard', 'icon' => 'dashboard', 'href' => route('dashboard'), 'active' => false],
        ['label' => 'Produk', 'icon' => 'inventory_2', 'href' => route('admin.products.index'), 'active' => false],
        ['label' => 'Transaksi', 'icon' => 'receipt_long', 'href' => route('transactions.index'), 'active' => false],
        ['label' => 'Pengaturan', 'icon' => 'settings', 'href' => route('admin.settings.edit'), 'active' => true],
    ];
@endphp

<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Pengaturan - {{ config('app.name', 'Keijora POS') }}</title>
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
                    <p class="text-[10px] font-bold uppercase tracking-wider text-[#767681]">Admin</p>
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
                <button type="button" onclick="togglePosSidebar()" class="hidden h-11 w-11 shrink-0 items-center justify-center rounded-full border border-[#c6c5d2] bg-white text-[#001356] shadow-sm hover:bg-[#eef3ff] md:flex">
                    <span class="sidebar-collapse-icon material-symbols-outlined transition-transform">left_panel_close</span>
                </button>
                <div>
                    <h1 class="text-lg font-extrabold text-[#171c20]">Pengaturan</h1>
                    <p class="text-xs font-medium text-[#454650]">Kelola metode pembayaran, identitas cafe, dan template struk.</p>
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

    <main class="pos-main min-h-screen pt-20 transition-all duration-300 md:ml-64">
        <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" class="mx-auto max-w-6xl space-y-5 px-4 pb-24 md:px-6 md:pb-6">
            @csrf
            @method('PUT')

            @if (session('status'))
                <div class="rounded-xl border border-[#b9c7df] bg-[#eef3ff] px-4 py-3 text-sm font-semibold text-[#001356]">
                    {{ session('status') }}
                </div>
            @endif

            <section class="grid gap-4 lg:grid-cols-[1fr_360px]">
                <div class="rounded-2xl border border-[#c6c5d2] bg-white p-4 shadow-[0_8px_20px_rgba(27,43,107,0.05)] sm:p-5">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-extrabold text-[#171c20]">Metode Pembayaran</h3>
                            <p class="mt-1 text-sm text-[#454650]">Metode aktif akan tampil di halaman kasir dan saat checkout.</p>
                        </div>
                        <span class="rounded-full bg-[#dde1ff] px-3 py-1 text-xs font-extrabold text-[#001356]">Kasir</span>
                    </div>
                    <div class="mt-4 grid gap-3 md:grid-cols-2">
                        <label class="flex min-h-16 cursor-pointer items-center justify-between rounded-xl border border-[#c6c5d2] p-4 transition has-[:checked]:border-[#001356] has-[:checked]:bg-[#eef3ff]">
                            <span class="flex items-center gap-3 text-sm font-bold text-[#171c20]">
                                <span class="material-symbols-outlined text-[#001356]">payments</span>
                                Tunai
                            </span>
                            <input type="checkbox" name="payment_methods[]" value="cash" @checked($paymentMethods['cash'] ?? true) class="h-5 w-5 rounded border-[#c6c5d2] text-[#001356] focus:ring-[#001356]">
                        </label>
                        <label class="flex min-h-16 cursor-pointer items-center justify-between rounded-xl border border-[#c6c5d2] p-4 transition has-[:checked]:border-[#001356] has-[:checked]:bg-[#eef3ff]">
                            <span class="flex items-center gap-3 text-sm font-bold text-[#171c20]">
                                <span class="material-symbols-outlined text-[#001356]">qr_code_2</span>
                                QRIS
                            </span>
                            <input type="checkbox" name="payment_methods[]" value="qris" @checked($paymentMethods['qris'] ?? true) class="h-5 w-5 rounded border-[#c6c5d2] text-[#001356] focus:ring-[#001356]">
                        </label>
                    </div>
                </div>

                <div class="rounded-2xl border border-[#c6c5d2] bg-[#f0f4fa] p-4 sm:p-5">
                    <div class="flex h-full flex-col justify-between gap-4">
                        <div>
                            <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#001356] text-white">
                                <span class="material-symbols-outlined">receipt_long</span>
                            </div>
                            <h3 class="mt-4 text-lg font-extrabold text-[#171c20]">Preview Struk</h3>
                            <p class="mt-1 text-sm leading-6 text-[#454650]">Data cafe di bawah akan dipakai di header dan footer struk transaksi.</p>
                        </div>
                        @php
                            $receiptLogoPath = $receipt['logo_path'] ?? null;
                            $receiptLogoUrl = $receiptLogoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($receiptLogoPath)
                                ? \Illuminate\Support\Facades\Storage::url($receiptLogoPath)
                                : null;
                        @endphp
                        <div class="rounded-xl bg-white p-4 text-center shadow-sm">
                            <div class="mx-auto mb-3 flex h-14 w-14 items-center justify-center overflow-hidden rounded-full bg-[#f0f4fa]">
                                <img src="{{ $receiptLogoUrl ?? asset('images/keijora-bird-navy.png') }}" alt="Logo Struk" class="h-12 w-12 object-contain">
                            </div>
                            <p class="text-sm font-extrabold text-[#171c20]">{{ $receipt['cafe_name'] ?: config('app.name', 'Keijora POS') }}</p>
                            <p class="mt-1 text-xs text-[#454650]">{{ $receipt['address'] ?: 'Alamat cafe belum diisi' }}</p>
                            <p class="mt-1 text-xs text-[#454650]">{{ $receipt['phone'] ?: 'Nomor telepon belum diisi' }}</p>
                            <p class="mt-3 border-t border-dashed border-[#c6c5d2] pt-3 text-xs text-[#767681]">{{ $receipt['footer_note'] ?: 'Terima kasih atas kunjungan Anda.' }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-[#c6c5d2] bg-white p-4 shadow-[0_8px_20px_rgba(27,43,107,0.05)] sm:p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-extrabold text-[#171c20]">Banner Customer</h3>
                        <p class="mt-1 text-sm text-[#454650]">Banner ini tampil di halaman menu customer. Jika kosong, customer akan melihat empty state.</p>
                    </div>
                    <span class="rounded-full bg-[#dde1ff] px-3 py-1 text-xs font-extrabold text-[#001356]">Online</span>
                </div>
                <div class="mt-4 grid gap-4 lg:grid-cols-[1fr_280px]">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-bold text-[#454650]">Gambar Banner</label>
                            <input type="file" name="online_banner_image" accept="image/*" class="w-full rounded-xl border border-[#c6c5d2] bg-white p-3 text-sm">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-bold text-[#454650]">Judul Banner</label>
                            <input type="text" name="online_banner_title" value="{{ old('online_banner_title', $onlineBanner['title'] ?? '') }}" placeholder="Opsional" class="w-full rounded-xl border-[#c6c5d2] text-sm focus:border-[#001356] focus:ring-[#001356]">
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-bold text-[#454650]">Subjudul Banner</label>
                            <textarea name="online_banner_subtitle" rows="3" placeholder="Opsional" class="w-full rounded-xl border-[#c6c5d2] text-sm focus:border-[#001356] focus:ring-[#001356]">{{ old('online_banner_subtitle', $onlineBanner['subtitle'] ?? '') }}</textarea>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-dashed border-[#c6c5d2] bg-[#f6faff] p-4">
                        <p class="text-xs font-bold uppercase tracking-widest text-[#767681]">Preview</p>
                        @if (!empty($onlineBanner['image_path']))
                            @php
                                $bannerPath = $onlineBanner['image_path'] ?? null;
                                $bannerUrl = $bannerPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($bannerPath)
                                    ? \Illuminate\Support\Facades\Storage::url($bannerPath)
                                    : null;
                            @endphp
                            @if ($bannerUrl)
                            <div class="mt-3 overflow-hidden rounded-[20px] bg-[#001356]">
                                <div class="relative aspect-[16/7]">
                                    <img src="{{ $bannerUrl }}" alt="Preview banner online" class="h-full w-full object-cover object-center">
                                    <div class="absolute inset-0 bg-gradient-to-r from-[#001356]/85 via-[#001356]/45 to-transparent"></div>
                                    <div class="absolute inset-0 flex items-end p-4">
                                        <div class="max-w-[80%] text-white">
                                            <p class="text-[10px] font-bold uppercase tracking-[0.32em] text-white/80">Keijora Cafe</p>
                                            <h4 class="mt-1 text-lg font-extrabold leading-tight">{{ $onlineBanner['title'] ?? 'Selamat Datang di Keijora' }}</h4>
                                            <p class="mt-1 text-xs leading-5 text-white/90">{{ $onlineBanner['subtitle'] ?? '' }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            @else
                                <div class="mt-3 rounded-[20px] border border-dashed border-[#c6c5d2] bg-white p-5 text-center">
                                    <span class="material-symbols-outlined text-4xl text-[#767681]">image</span>
                                    <p class="mt-2 text-sm font-bold text-[#171c20]">Banner kosong</p>
                                    <p class="mt-1 text-xs text-[#454650]">File banner belum bisa dibaca dari storage.</p>
                                </div>
                            @endif
                        @else
                            <div class="mt-3 rounded-[20px] border border-dashed border-[#c6c5d2] bg-white p-5 text-center">
                                <span class="material-symbols-outlined text-4xl text-[#767681]">image</span>
                                <p class="mt-2 text-sm font-bold text-[#171c20]">Banner kosong</p>
                                <p class="mt-1 text-xs text-[#454650]">Customer akan melihat empty state sampai gambar diisi.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-[#c6c5d2] bg-white p-4 shadow-[0_8px_20px_rgba(27,43,107,0.05)] sm:p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-extrabold text-[#171c20]">Pembayaran Online</h3>
                        <p class="mt-1 text-sm text-[#454650]">Metode dan detail pembayaran untuk pesanan customer di halaman checkout online.</p>
                    </div>
                    <span class="rounded-full bg-[#dde1ff] px-3 py-1 text-xs font-extrabold text-[#001356]">Online</span>
                </div>

                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    <label class="flex min-h-16 cursor-pointer items-center justify-between rounded-xl border border-[#c6c5d2] p-4 transition has-[:checked]:border-[#001356] has-[:checked]:bg-[#eef3ff]">
                        <span class="flex items-center gap-3 text-sm font-bold text-[#171c20]">
                            <span class="material-symbols-outlined text-[#001356]">account_balance</span>
                            Transfer Bank
                        </span>
                        <input type="checkbox" name="online_payment_methods[]" value="transfer_bank" @checked($onlinePayment['methods']['transfer_bank'] ?? true) class="h-5 w-5 rounded border-[#c6c5d2] text-[#001356] focus:ring-[#001356]">
                    </label>
                    <label class="flex min-h-16 cursor-pointer items-center justify-between rounded-xl border border-[#c6c5d2] p-4 transition has-[:checked]:border-[#001356] has-[:checked]:bg-[#eef3ff]">
                        <span class="flex items-center gap-3 text-sm font-bold text-[#171c20]">
                            <span class="material-symbols-outlined text-[#001356]">qr_code_2</span>
                            QRIS
                        </span>
                        <input type="checkbox" name="online_payment_methods[]" value="qris" @checked($onlinePayment['methods']['qris'] ?? true) class="h-5 w-5 rounded border-[#c6c5d2] text-[#001356] focus:ring-[#001356]">
                    </label>
                </div>

                <div class="mt-5 grid gap-4 md:grid-cols-2">
                    <div>
                        <p class="mb-3 text-xs font-bold uppercase tracking-widest text-[#767681]">Transfer Bank</p>
                        <div class="space-y-3">
                            <div>
                                <label class="mb-2 block text-sm font-bold text-[#454650]">Nama Bank</label>
                                <input type="text" name="online_bank_name" value="{{ old('online_bank_name', $onlinePayment['bank_name'] ?? '') }}" class="w-full rounded-xl border-[#c6c5d2] text-sm focus:border-[#001356] focus:ring-[#001356]">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-bold text-[#454650]">Nomor Rekening</label>
                                <input type="text" name="online_account_number" value="{{ old('online_account_number', $onlinePayment['account_number'] ?? '') }}" class="w-full rounded-xl border-[#c6c5d2] text-sm focus:border-[#001356] focus:ring-[#001356]">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-bold text-[#454650]">Atas Nama</label>
                                <input type="text" name="online_account_name" value="{{ old('online_account_name', $onlinePayment['account_name'] ?? '') }}" class="w-full rounded-xl border-[#c6c5d2] text-sm focus:border-[#001356] focus:ring-[#001356]">
                            </div>
                        </div>
                    </div>

                    <div>
                        <p class="mb-3 text-xs font-bold uppercase tracking-widest text-[#767681]">QRIS</p>
                        <div class="space-y-3">
                            <div>
                                <label class="mb-2 block text-sm font-bold text-[#454650]">Gambar QRIS</label>
                                <input type="file" name="online_qris_image" accept="image/*" class="w-full rounded-xl border border-[#c6c5d2] bg-white p-3 text-sm">
                            </div>
                            <div>
                                <label class="mb-2 block text-sm font-bold text-[#454650]">Nama Merchant QRIS</label>
                                <input type="text" name="online_qris_merchant_name" value="{{ old('online_qris_merchant_name', $onlinePayment['qris_merchant_name'] ?? '') }}" class="w-full rounded-xl border-[#c6c5d2] text-sm focus:border-[#001356] focus:ring-[#001356]">
                            </div>
                            @php
                                $qrisImagePath = $onlinePayment['qris_image_path'] ?? null;
                                $qrisImageUrl = $qrisImagePath && \Illuminate\Support\Facades\Storage::disk('public')->exists($qrisImagePath)
                                    ? \Illuminate\Support\Facades\Storage::url($qrisImagePath)
                                    : null;
                            @endphp
                            @if ($qrisImageUrl)
                                <div class="rounded-xl border border-dashed border-[#c6c5d2] bg-[#f6faff] p-4 text-center">
                                    <img src="{{ $qrisImageUrl }}" alt="QRIS" class="mx-auto h-40 w-40 rounded-xl bg-white object-contain p-2 shadow-sm">
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-bold text-[#454650]">Nomor WhatsApp Kasir</label>
                        <input type="text" name="online_cashier_wa_number" value="{{ old('online_cashier_wa_number', $onlinePayment['cashier_wa_number'] ?? '') }}" placeholder="0812xxxxxxx" class="w-full rounded-xl border-[#c6c5d2] text-sm focus:border-[#001356] focus:ring-[#001356]">
                        <p class="mt-1 text-xs text-[#767681]">Customer akan konfirmasi pembayaran ke nomor ini setelah checkout.</p>
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-[#c6c5d2] bg-white p-4 shadow-[0_8px_20px_rgba(27,43,107,0.05)] sm:p-5">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-lg font-extrabold text-[#171c20]">Format & Template Struk</h3>
                        <p class="mt-1 text-sm text-[#454650]">Atur identitas yang tampil di struk cetak dan riwayat transaksi.</p>
                    </div>
                    <span class="rounded-full bg-[#e7fff2] px-3 py-1 text-xs font-extrabold text-[#005236]">Branding</span>
                </div>
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-2 block text-sm font-bold text-[#454650]">Logo Struk</label>
                        <input type="file" name="receipt_logo" accept="image/*" class="w-full rounded-xl border border-[#c6c5d2] bg-white p-3 text-sm">
                    </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-[#454650]">Nama Cafe</label>
                            <input type="text" name="cafe_name" value="{{ old('cafe_name', $receipt['cafe_name'] ?? '') }}" class="w-full rounded-xl border-[#c6c5d2] text-sm focus:border-[#001356] focus:ring-[#001356]">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-[#454650]">Username Cafe</label>
                            <input type="text" name="customer_username" value="{{ old('customer_username', $receipt['customer_username'] ?? '') }}" class="w-full rounded-xl border-[#c6c5d2] text-sm focus:border-[#001356] focus:ring-[#001356]">
                            <p class="mt-1 text-xs text-[#767681]">Dipakai untuk URL customer. Contoh: <span class="font-semibold">haikikedai</span></p>
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-2 block text-sm font-bold text-[#454650]">Alamat</label>
                            <textarea name="address" rows="3" class="w-full rounded-xl border-[#c6c5d2] text-sm focus:border-[#001356] focus:ring-[#001356]">{{ old('address', $receipt['address'] ?? '') }}</textarea>
                        </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold text-[#454650]">Nomor Telepon</label>
                        <input type="text" name="phone" value="{{ old('phone', $receipt['phone'] ?? '') }}" class="w-full rounded-xl border-[#c6c5d2] text-sm focus:border-[#001356] focus:ring-[#001356]">
                    </div>
                    <div>
                        <label class="mb-2 block text-sm font-bold text-[#454650]">Catatan Footer</label>
                        <input type="text" name="footer_note" value="{{ old('footer_note', $receipt['footer_note'] ?? '') }}" class="w-full rounded-xl border-[#c6c5d2] text-sm focus:border-[#001356] focus:ring-[#001356]">
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-dashed border-[#c6c5d2] bg-[#f0f4fa] p-4 sm:p-5">
                <h3 class="text-lg font-extrabold text-[#171c20]">Saran Menu Pengaturan Berikutnya</h3>
                <div class="mt-3 grid grid-cols-2 gap-3 md:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-xl bg-white p-4"><p class="font-bold text-[#171c20]">Pajak & Service Charge</p><p class="text-sm text-[#454650]">PPN, service fee, pembulatan total.</p></div>
                    <div class="rounded-xl bg-white p-4"><p class="font-bold text-[#171c20]">Printer & Ukuran Struk</p><p class="text-sm text-[#454650]">58mm/80mm, auto print, jumlah copy.</p></div>
                    <div class="rounded-xl bg-white p-4"><p class="font-bold text-[#171c20]">Nomor Invoice</p><p class="text-sm text-[#454650]">Prefix invoice, reset harian/bulanan.</p></div>
                    <div class="rounded-xl bg-white p-4"><p class="font-bold text-[#171c20]">Operasional Toko</p><p class="text-sm text-[#454650]">Jam buka, shift kasir, open/close cash drawer.</p></div>
                </div>
            </section>

            <div class="sticky bottom-0 z-20 -mx-4 border-t border-[#c6c5d2] bg-[#f6faff]/95 px-4 py-4 backdrop-blur md:static md:mx-0 md:border-0 md:bg-transparent md:p-0">
                <div class="flex justify-end">
                    <button class="rounded-xl bg-[#001356] px-6 py-3 text-sm font-extrabold text-white shadow-lg active:scale-[0.98]">Simpan Pengaturan</button>
                </div>
            </div>
        </form>
    </main>

    <nav class="fixed bottom-0 left-0 z-50 flex w-full items-center justify-around rounded-t-xl bg-[#dfe3e9] px-4 py-3 shadow-[0_-4px_12px_rgba(27,43,107,0.08)] md:hidden">
        @foreach (array_slice($navItems, 0, 4) as $item)
            <a href="{{ $item['href'] }}" class="flex flex-col items-center justify-center px-4 py-2 text-xs font-bold transition {{ $item['active'] ? 'rounded-full bg-[#001356] text-white' : 'text-[#454650]' }}">
                <span class="material-symbols-outlined">{{ $item['icon'] }}</span>
                <span>{{ \Illuminate\Support\Str::limit($item['label'], 8, '') }}</span>
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
