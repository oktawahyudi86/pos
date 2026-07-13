<x-guest-layout>
    <main class="flex min-h-screen w-full flex-col overflow-hidden bg-[#EEF2F8] md:flex-row" style="font-family: 'Plus Jakarta Sans', sans-serif;">
        <section class="relative hidden w-[60%] flex-col justify-between overflow-hidden bg-[#EEF2F8] p-8 md:flex">
            <div class="absolute -left-24 -top-24 h-96 w-96 rounded-full bg-[#1b2b6b] opacity-5 blur-3xl"></div>

            <div class="relative z-10">
                <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center">
                        <img src="{{ asset('images/keijora-bird-navy.png') }}" alt="Keijora" class="h-11 w-11 object-contain">
                    </div>
                    <div>
                        <img src="{{ asset('images/keijora-logo-cropped.png') }}" alt="Keijora" class="h-8 w-auto max-w-[128px] object-contain">
                        <p class="mt-1 text-[10px] font-extrabold uppercase tracking-[0.28em] text-[#767681]">POS System</p>
                    </div>
                </a>
            </div>

            <div class="relative z-10 max-w-2xl">
                <div class="mb-6 inline-flex items-center rounded-full border border-[#c6c5d2] bg-[#dce9ff] px-4 py-1 text-xs font-semibold uppercase tracking-widest text-[#001356]">
                    Sistem Kasir
                </div>
                <h1 class="mb-4 max-w-xl text-4xl font-bold leading-tight text-[#1b2b6b]">
                    Sistem kasir yang cepat, ringkas, dan siap pakai.
                </h1>
                <p class="max-w-lg text-base leading-7 text-[#5a5f64]">
                    Kelola transaksi, produk, dan laporan bisnis F&amp;B Anda dari satu dashboard terintegrasi yang dirancang untuk kecepatan operasional.
                </p>
            </div>

            <div class="relative z-10 grid grid-cols-3 gap-4">
                <div class="rounded-xl bg-white p-6 shadow-[0_4px_12px_rgba(27,43,107,0.08)]">
                    <span class="material-symbols-outlined mb-4 flex h-10 w-10 items-center justify-center rounded-full bg-[#eff4ff] text-[#1b2b6b]">speed</span>
                    <h3 class="mb-1 text-base font-semibold text-[#1b2b6b]">Transaksi cepat</h3>
                    <p class="text-sm leading-5 text-[#5a5f64]">Proses pembayaran dalam hitungan detik.</p>
                </div>
                <div class="rounded-xl bg-white p-6 shadow-[0_4px_12px_rgba(27,43,107,0.08)]">
                    <span class="material-symbols-outlined mb-4 flex h-10 w-10 items-center justify-center rounded-full bg-[#eff4ff] text-[#1b2b6b]">bar_chart_4_bars</span>
                    <h3 class="mb-1 text-base font-semibold text-[#1b2b6b]">Laporan real-time</h3>
                    <p class="text-sm leading-5 text-[#5a5f64]">Pantau performa menu kapan saja.</p>
                </div>
                <div class="rounded-xl bg-white p-6 shadow-[0_4px_12px_rgba(27,43,107,0.08)]">
                    <span class="material-symbols-outlined mb-4 flex h-10 w-10 items-center justify-center rounded-full bg-[#eff4ff] text-[#1b2b6b]">group</span>
                    <h3 class="mb-1 text-base font-semibold text-[#1b2b6b]">Multi pengguna</h3>
                    <p class="text-sm leading-5 text-[#5a5f64]">Akses tim dengan kontrol otoritas penuh.</p>
                </div>
            </div>
        </section>

        <section class="flex min-h-screen w-full items-center justify-center bg-white p-4 md:w-[40%] md:bg-transparent md:p-8">
            <div class="w-full max-w-md rounded-xl bg-white p-8 md:shadow-[0_4px_12px_rgba(27,43,107,0.08)]">
                <div class="mb-8 md:hidden">
                    <a href="{{ url('/') }}" class="inline-flex items-center gap-3">
                        <div class="flex h-11 w-11 items-center justify-center">
                            <img src="{{ asset('images/keijora-bird-navy.png') }}" alt="Keijora" class="h-10 w-10 object-contain">
                        </div>
                        <div>
                            <img src="{{ asset('images/keijora-logo-cropped.png') }}" alt="Keijora" class="h-7 w-auto max-w-[118px] object-contain">
                            <p class="mt-1 text-[9px] font-extrabold uppercase tracking-[0.24em] text-[#767681]">POS System</p>
                        </div>
                    </a>
                </div>

                <div class="mb-8">
                    <h2 class="mb-1 text-2xl font-semibold text-[#1b2b6b]">Masuk</h2>
                    <p class="text-sm text-[#5a5f64]">Gunakan akun Anda untuk mengakses sistem.</p>
                </div>

                <x-auth-session-status class="mb-4" :status="session('status')" />

                <form method="POST" action="{{ route('login') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-[#5a5f64]">Email</label>
                        <div class="relative">
                            <input id="email" name="email" type="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="admin@pos.test" class="w-full rounded-lg border border-[#c6c5d2] bg-[#f8f9ff] px-4 py-4 pr-12 text-sm text-[#0b1c30] transition focus:border-[#1b2b6b] focus:outline-none focus:ring-1 focus:ring-[#1b2b6b]">
                            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-[#5a5f64] opacity-60">person</span>
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <div>
                        <label for="password" class="mb-2 block text-xs font-semibold uppercase tracking-wider text-[#5a5f64]">Password</label>
                        <div class="relative">
                            <input id="password" name="password" type="password" required autocomplete="current-password" placeholder="Masukkan password" class="w-full rounded-lg border border-[#c6c5d2] bg-[#f8f9ff] px-4 py-4 pr-12 text-sm text-[#0b1c30] transition focus:border-[#1b2b6b] focus:outline-none focus:ring-1 focus:ring-[#1b2b6b]">
                            <button type="button" onclick="togglePassword()" class="absolute right-4 top-1/2 -translate-y-1/2 text-[#5a5f64] transition hover:text-[#001356]">
                                <span class="material-symbols-outlined" id="pw-icon">visibility</span>
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-between">
                        <label for="remember_me" class="flex cursor-pointer items-center gap-2 text-sm text-[#454650]">
                            <input id="remember_me" type="checkbox" name="remember" class="h-5 w-5 rounded border-[#c6c5d2] text-[#1b2b6b] focus:ring-[#1b2b6b]">
                            Ingat saya
                        </label>

                        @if (Route::has('password.request'))
                            <a class="text-sm font-medium text-[#001356] hover:underline" href="{{ route('password.request') }}">
                                Lupa akses?
                            </a>
                        @endif
                    </div>

                    <button class="flex w-full items-center justify-center gap-2 rounded-lg bg-[#1b2b6b] py-4 text-base font-semibold text-white shadow-[0_4px_12px_rgba(27,43,107,0.08)] transition hover:opacity-90 active:scale-[0.98]">
                        Masuk
                        <span class="material-symbols-outlined">login</span>
                    </button>
                </form>

                <div class="mt-8 border-t border-[#c6c5d2] pt-6 text-center">
                    <p class="text-sm text-[#5a5f64]">
                        Belum punya akun cafe?
                        <a href="{{ route('register') }}" class="font-extrabold text-[#001356] underline underline-offset-4 hover:text-[#1b2b6b]">
                            Daftar admin baru
                        </a>
                    </p>
                </div>
            </div>
        </section>
    </main>

    <script>
        function togglePassword() {
            const input = document.getElementById('password');
            const icon = document.getElementById('pw-icon');

            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility_off';
                return;
            }

            input.type = 'password';
            icon.textContent = 'visibility';
        }
    </script>
</x-guest-layout>
