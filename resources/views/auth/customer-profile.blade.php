<x-online-layout :tenant="$tenant" title="Profil Saya" active="profile">
    <div class="mx-auto max-w-5xl px-4 sm:px-6">
        <div class="mb-6">
            <h1 class="text-2xl font-extrabold leading-tight text-[#001356]">Profil Saya</h1>
            <p class="mt-2 text-sm text-[#454650]">Kelola informasi akun dan preferensi Anda</p>
        </div>

        <!-- Profile Card -->
        <div class="mb-6 overflow-hidden rounded-2xl border border-[#dfe3e9] bg-white shadow-[0_4px_12px_rgba(27,43,107,0.05)]">
            <div class="bg-gradient-to-r from-[#001356] to-[#001e6e] px-6 py-6 text-white">
                <div class="flex items-center gap-4">
                    <div class="flex h-16 w-16 shrink-0 items-center justify-center rounded-full bg-white/20 text-3xl font-extrabold">
                        {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-xl font-extrabold">{{ $user->name }}</p>
                        <p class="mt-1 text-sm font-semibold text-white/80">
                            {{ $user->phone }}
                        </p>
                        <div class="mt-2 flex items-center gap-2">
                            <span class="flex items-center gap-1 rounded-full bg-white/20 px-2 py-1 text-xs font-semibold">
                                <span class="material-symbols-outlined text-[14px]" style="font-variation-settings: 'FILL' 1;">verified</span>
                                Customer Terdaftar
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Account Information -->
        <div class="mb-6 overflow-hidden rounded-2xl border border-[#dfe3e9] bg-white shadow-[0_4px_12px_rgba(27,43,107,0.05)]">
            <div class="border-b border-[#eef3fb] px-6 py-4">
                <h2 class="text-sm font-extrabold text-[#001356]">Informasi Akun</h2>
            </div>
            <div class="divide-y divide-[#eef3fb]">
                <div class="flex items-center justify-between gap-4 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[#001356]" style="font-variation-settings: 'FILL' 1;">person</span>
                        <div>
                            <p class="text-xs font-semibold text-[#767681]">Nama Lengkap</p>
                            <p class="text-sm font-extrabold text-[#171c20]">{{ $user->name }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[#001356]" style="font-variation-settings: 'FILL' 1;">phone</span>
                        <div>
                            <p class="text-xs font-semibold text-[#767681]">Nomor HP</p>
                            <p class="text-sm font-extrabold text-[#171c20]">{{ $user->phone ?: '-' }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex items-center justify-between gap-4 px-6 py-4">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[#001356]" style="font-variation-settings: 'FILL' 1;">email</span>
                        <div>
                            <p class="text-xs font-semibold text-[#767681]">Email</p>
                            <p class="text-sm font-extrabold text-[#171c20]">{{ $user->email ?: '-' }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="mb-6 overflow-hidden rounded-2xl border border-[#dfe3e9] bg-white shadow-[0_4px_12px_rgba(27,43,107,0.05)]">
            <div class="border-b border-[#eef3fb] px-6 py-4">
                <h2 class="text-sm font-extrabold text-[#001356]">Aksi Cepat</h2>
            </div>
            <div class="divide-y divide-[#eef3fb]">
                <a href="{{ route('online-orders.track', $tenant) }}" class="flex items-center justify-between gap-4 px-6 py-4 transition hover:bg-[#f6faff]">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[#001356]" style="font-variation-settings: 'FILL' 1;">receipt_long</span>
                        <div>
                            <p class="text-sm font-extrabold text-[#171c20]">Riwayat Pesanan</p>
                            <p class="text-xs text-[#767681]">Lihat semua pesanan Anda</p>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-[#767681]">chevron_right</span>
                </a>

                <a href="{{ route('online-orders.address', $tenant) }}" class="flex items-center justify-between gap-4 px-6 py-4 transition hover:bg-[#f6faff]">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[#001356]" style="font-variation-settings: 'FILL' 1;">location_on</span>
                        <div>
                            <p class="text-sm font-extrabold text-[#171c20]">Alamat Pengiriman</p>
                            <p class="text-xs text-[#767681]">Kelola alamat pengiriman</p>
                        </div>
                    </div>
                    <span class="material-symbols-outlined text-[#767681]">chevron_right</span>
                </a>

                <a href="{{ route('online-orders.address', $tenant) }}" class="flex items-center justify-between gap-4 px-6 py-4 transition hover:bg-[#f6faff] {{ $cartCount < 1 ? 'pointer-events-none opacity-60' : '' }}">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-[#001356]" style="font-variation-settings: 'FILL' 1;">shopping_bag</span>
                        <div>
                            <p class="text-sm font-extrabold text-[#171c20]">Keranjang Belanja</p>
                            <p class="text-xs text-[#767681]">{{ $cartCount }} item di keranjang</p>
                        </div>
                    </div>
                    <span class="rounded-full bg-[#eef3ff] px-2 py-1 text-xs font-extrabold text-[#001356]">{{ $cartCount }}</span>
                </a>
            </div>
        </div>

        <!-- Logout Button -->
        <form method="POST" action="{{ route('online-orders.auth.logout', $tenant) }}">
            @csrf
            <button class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl border border-[#ffdad6] bg-white px-4 text-sm font-extrabold text-[#ba1a1a] transition active:scale-[0.98]">
                <span class="material-symbols-outlined text-[20px]">logout</span>
                Keluar dari Akun
            </button>
        </form>
    </div>
</x-online-layout>
