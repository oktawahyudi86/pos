<x-guest-layout>
    <main class="flex min-h-screen w-full flex-col overflow-hidden bg-[#eef2f8] md:flex-row" style="font-family: 'Plus Jakarta Sans', sans-serif;">
        <section class="relative hidden w-[58%] flex-col justify-between overflow-hidden bg-[#eef2f8] p-8 md:flex xl:p-12">
            <div class="absolute -left-28 -top-28 h-96 w-96 rounded-full bg-[#1b2b6b]/10 blur-3xl"></div>
            <div class="absolute bottom-16 right-10 h-72 w-72 rounded-full bg-[#6ffbbe]/20 blur-3xl"></div>

            <a href="{{ url('/') }}" class="relative z-10 inline-flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center">
                    <img src="{{ asset('images/keijora-bird-navy.png') }}" alt="Keijora" class="h-11 w-11 object-contain">
                </div>
                <div>
                    <img src="{{ asset('images/keijora-logo-cropped.png') }}" alt="Keijora" class="h-8 w-auto max-w-[128px] object-contain">
                    <p class="mt-1 text-[10px] font-extrabold uppercase tracking-[0.28em] text-[#767681]">POS System</p>
                </div>
            </a>

            <div class="relative z-10 max-w-xl">
                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-[#c6c5d2] bg-white/70 px-4 py-2 text-xs font-extrabold uppercase tracking-[0.18em] text-[#001356] shadow-sm backdrop-blur">
                    <span class="material-symbols-outlined text-[18px]">lock_reset</span>
                    Recovery akses
                </div>
                <h1 class="max-w-lg text-5xl font-extrabold leading-[1.02] tracking-tight text-[#1b2b6b]">
                    Tenang, akses POS bisa dipulihkan.
                </h1>
                <p class="mt-6 max-w-lg text-base leading-8 text-[#454650]">
                    Masukkan email akun Anda. Kami akan mengirim link aman untuk membuat password baru tanpa mengubah data transaksi atau tenant.
                </p>
            </div>

            <div class="relative z-10 grid max-w-xl gap-4 lg:grid-cols-2">
                <article class="rounded-2xl border border-white/70 bg-white/78 p-5 shadow-[0_12px_30px_rgba(27,43,107,0.08)] backdrop-blur">
                    <span class="material-symbols-outlined mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-[#eff4ff] text-[#001356]">mail</span>
                    <h3 class="text-sm font-extrabold text-[#1b2b6b]">Link via email</h3>
                    <p class="mt-2 text-xs leading-5 text-[#5a5f64]">Cek inbox atau spam setelah request dikirim.</p>
                </article>
                <article class="rounded-2xl border border-white/70 bg-white/78 p-5 shadow-[0_12px_30px_rgba(27,43,107,0.08)] backdrop-blur">
                    <span class="material-symbols-outlined mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-[#eff4ff] text-[#001356]">schedule</span>
                    <h3 class="text-sm font-extrabold text-[#1b2b6b]">Berlaku terbatas</h3>
                    <p class="mt-2 text-xs leading-5 text-[#5a5f64]">Gunakan link segera agar akun tetap aman.</p>
                </article>
            </div>
        </section>

        <section class="flex min-h-screen w-full items-center justify-center bg-white p-4 md:w-[42%] md:bg-transparent md:p-8">
            <div class="w-full max-w-md rounded-[1.5rem] bg-white p-6 shadow-none sm:p-8 md:border md:border-[#c6c5d2] md:shadow-[0_18px_50px_rgba(27,43,107,0.10)]">
                <a href="{{ url('/') }}" class="mb-8 inline-flex items-center gap-3 md:hidden">
                    <div class="flex h-11 w-11 items-center justify-center">
                        <img src="{{ asset('images/keijora-bird-navy.png') }}" alt="Keijora" class="h-10 w-10 object-contain">
                    </div>
                    <div>
                        <img src="{{ asset('images/keijora-logo-cropped.png') }}" alt="Keijora" class="h-7 w-auto max-w-[118px] object-contain">
                        <p class="mt-1 text-[9px] font-extrabold uppercase tracking-[0.24em] text-[#767681]">POS System</p>
                    </div>
                </a>

                <div class="mb-8">
                    <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#001356]">Lupa password</p>
                    <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-[#1b2b6b]">Kirim link reset</h2>
                    <p class="mt-2 text-sm leading-6 text-[#5a5f64]">Masukkan email login. Jika email terdaftar, link reset password akan dikirim.</p>
                </div>

                <x-auth-session-status class="mb-5 rounded-xl border border-[#6ffbbe] bg-[#effcf5] px-4 py-3 text-sm font-bold text-[#005236]" :status="session('status')" />

                <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                    @csrf

                    <div>
                        <label for="email" class="mb-2 block text-xs font-extrabold uppercase tracking-[0.14em] text-[#454650]">Email Login</label>
                        <div class="relative">
                            <input id="email" class="min-h-14 w-full rounded-xl border border-[#c6c5d2] bg-[#fbfcff] px-4 pr-12 text-sm font-semibold text-[#171c20] outline-none transition focus:border-[#001356] focus:ring-2 focus:ring-[#d5e3fc]" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="admin@cafe.com">
                            <span class="material-symbols-outlined absolute right-4 top-1/2 -translate-y-1/2 text-[#767681]">mail</span>
                        </div>
                        <x-input-error :messages="$errors->get('email')" class="mt-2" />
                    </div>

                    <button class="flex min-h-14 w-full items-center justify-center gap-2 rounded-xl bg-[#001356] px-6 text-sm font-extrabold text-white shadow-[0_8px_24px_rgba(27,43,107,0.18)] transition hover:bg-[#1b2b6b] active:scale-[0.99]">
                        Kirim Link Reset Password
                        <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                    </button>
                </form>

                <div class="mt-7 border-t border-[#dfe3e9] pt-6 text-center">
                    <a href="{{ route('login') }}" class="text-sm font-extrabold text-[#001356] underline underline-offset-4">
                        Kembali ke halaman masuk
                    </a>
                </div>
            </div>
        </section>
    </main>
</x-guest-layout>
