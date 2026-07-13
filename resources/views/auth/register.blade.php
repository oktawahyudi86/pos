<x-guest-layout>
    <main class="min-h-screen overflow-hidden bg-[#eef2f8]" style="font-family: 'Plus Jakarta Sans', sans-serif; -webkit-text-size-adjust: 100%;">
        <div class="grid min-h-screen lg:grid-cols-[0.92fr_1.08fr]">
            <section class="relative hidden overflow-hidden bg-[#eef2f8] px-8 py-10 lg:flex lg:flex-col lg:justify-between xl:px-12">
                <div class="absolute -left-28 -top-28 h-96 w-96 rounded-full bg-[#1b2b6b]/10 blur-3xl"></div>
                <div class="absolute bottom-20 right-8 h-72 w-72 rounded-full bg-[#6ffbbe]/20 blur-3xl"></div>

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
                        <span class="h-2 w-2 rounded-full bg-[#24d18f]"></span>
                        Registrasi Tenant
                    </div>
                    <h1 class="max-w-lg text-5xl font-extrabold leading-[1.02] tracking-tight text-[#1b2b6b] xl:text-6xl">
                        Mulai dari satu kasir, tumbuh jadi satu sistem.
                    </h1>
                    <p class="mt-6 max-w-lg text-base leading-8 text-[#454650]">
                        Buat akun bisnis terpisah untuk cafe Anda. Setelah disetujui Super Admin, Anda bisa mengelola produk, transaksi, dan tim kasir dari dashboard sendiri.
                    </p>
                </div>

                <div class="relative z-10 grid max-w-2xl gap-4 xl:grid-cols-3">
                    @foreach ([
                        ['icon' => 'domain', 'title' => 'Tenant terpisah', 'text' => 'Data cafe tidak tercampur dengan bisnis lain.'],
                        ['icon' => 'group_add', 'title' => 'Kelola kasir', 'text' => 'Admin bisa menambahkan kasir setelah aktif.'],
                        ['icon' => 'verified_user', 'title' => 'Approval aman', 'text' => 'Akun baru dicek Super Admin terlebih dahulu.'],
                    ] as $item)
                        <article class="rounded-2xl border border-white/70 bg-white/78 p-5 shadow-[0_12px_30px_rgba(27,43,107,0.08)] backdrop-blur">
                            <span class="material-symbols-outlined mb-4 flex h-10 w-10 items-center justify-center rounded-xl bg-[#eff4ff] text-[#001356]">{{ $item['icon'] }}</span>
                            <h3 class="text-sm font-extrabold text-[#1b2b6b]">{{ $item['title'] }}</h3>
                            <p class="mt-2 text-xs leading-5 text-[#5a5f64]">{{ $item['text'] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section class="flex min-h-screen items-start justify-center overflow-y-auto bg-white px-4 py-6 sm:px-6 lg:items-center lg:bg-transparent lg:px-8 lg:py-10">
                <div class="w-full max-w-3xl rounded-[1.75rem] bg-white p-5 shadow-none sm:p-7 lg:border lg:border-[#c6c5d2] lg:p-8 lg:shadow-[0_18px_50px_rgba(27,43,107,0.10)] xl:p-10">
                    <div class="mb-7 flex items-start justify-between gap-4">
                        <div>
                            <a href="{{ url('/') }}" class="mb-6 inline-flex items-center gap-3 lg:hidden">
                                <div class="flex h-10 w-10 items-center justify-center">
                                    <img src="{{ asset('images/keijora-bird-navy.png') }}" alt="Keijora" class="h-9 w-9 object-contain">
                                </div>
                                <div>
                                    <img src="{{ asset('images/keijora-logo-cropped.png') }}" alt="Keijora" class="h-6 w-auto max-w-[104px] object-contain">
                                    <p class="mt-1 text-[9px] font-extrabold uppercase tracking-[0.24em] text-[#767681]">POS System</p>
                                </div>
                            </a>
                            <p class="text-[11px] font-extrabold uppercase tracking-[0.18em] text-[#001356] sm:text-xs">Buat akun bisnis</p>
                            <h2 class="mt-2 text-[28px] font-extrabold leading-tight tracking-tight text-[#1b2b6b] sm:text-3xl">Registrasi Admin Cafe</h2>
                            <p class="mt-2 max-w-xl text-sm leading-6 text-[#5a5f64]">Isi data utama. Nanti Super Admin akan mengaktifkan akun sebelum POS bisa digunakan penuh.</p>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('register') }}" enctype="multipart/form-data" class="space-y-7">
                        @csrf

                        <div class="rounded-2xl border border-[#dfe3e9] bg-[#fbfcff] p-4 sm:p-5">
                            <div class="mb-5 flex items-center gap-3">
                                <span class="flex h-10 w-10 items-center justify-center"><img src="{{ asset('images/keijora-bird-navy.png') }}" alt="Keijora" class="h-9 w-9 object-contain"></span>
                                <div>
                                    <h3 class="text-base font-extrabold text-[#171c20]">Data Cafe</h3>
                                    <p class="text-xs text-[#5a5f64]">Identitas bisnis yang akan tampil di sistem.</p>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="md:col-span-2">
                                    <label for="tenant_name" class="mb-2 block text-xs font-extrabold uppercase tracking-[0.14em] text-[#454650]">Nama Cafe / Bisnis</label>
                                    <input id="tenant_name" class="min-h-12 w-full rounded-xl border border-[#c6c5d2] bg-white px-4 text-base font-semibold text-[#171c20] outline-none transition focus:border-[#001356] focus:ring-2 focus:ring-[#d5e3fc] sm:text-sm" type="text" name="tenant_name" value="{{ old('tenant_name') }}" required autofocus placeholder="Contoh: Keijora Coffee">
                                    <x-input-error :messages="$errors->get('tenant_name')" class="mt-2" />
                                </div>

                                <div>
                                    <label for="business_email" class="mb-2 block text-xs font-extrabold uppercase tracking-[0.14em] text-[#454650]">Email Bisnis</label>
                                    <input id="business_email" class="min-h-12 w-full rounded-xl border border-[#c6c5d2] bg-white px-4 text-base font-semibold text-[#171c20] outline-none transition focus:border-[#001356] focus:ring-2 focus:ring-[#d5e3fc] sm:text-sm" type="email" name="business_email" value="{{ old('business_email') }}" placeholder="owner@cafe.com">
                                    <x-input-error :messages="$errors->get('business_email')" class="mt-2" />
                                </div>

                                <div>
                                    <label for="phone" class="mb-2 block text-xs font-extrabold uppercase tracking-[0.14em] text-[#454650]">Nomor HP / WhatsApp</label>
                                    <input id="phone" class="min-h-12 w-full rounded-xl border border-[#c6c5d2] bg-white px-4 text-base font-semibold text-[#171c20] outline-none transition focus:border-[#001356] focus:ring-2 focus:ring-[#d5e3fc] sm:text-sm" type="text" name="phone" value="{{ old('phone') }}" required placeholder="08xxxxxxxxxx">
                                    <x-input-error :messages="$errors->get('phone')" class="mt-2" />
                                </div>

                                <div class="md:col-span-2">
                                    <label for="address" class="mb-2 block text-xs font-extrabold uppercase tracking-[0.14em] text-[#454650]">Alamat Cafe</label>
                                    <textarea id="address" name="address" rows="3" required class="w-full rounded-xl border border-[#c6c5d2] bg-white px-4 py-3 text-sm font-semibold text-[#171c20] outline-none transition focus:border-[#001356] focus:ring-2 focus:ring-[#d5e3fc]" placeholder="Alamat lengkap outlet utama">{{ old('address') }}</textarea>
                                    <x-input-error :messages="$errors->get('address')" class="mt-2" />
                                </div>

                                <div class="md:col-span-2">
                                    <label for="logo" class="mb-2 block text-xs font-extrabold uppercase tracking-[0.14em] text-[#454650]">Logo Cafe <span class="font-bold normal-case tracking-normal text-[#767681]">(Opsional)</span></label>
                                    <input id="logo" name="logo" type="file" accept="image/*" class="block w-full rounded-xl border border-dashed border-[#c6c5d2] bg-white px-3 py-3 text-sm text-[#454650] file:mr-4 file:rounded-lg file:border-0 file:bg-[#001356] file:px-4 file:py-2 file:text-sm file:font-extrabold file:text-white focus:border-[#001356] focus:ring-2 focus:ring-[#d5e3fc]">
                                    <x-input-error :messages="$errors->get('logo')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="rounded-2xl border border-[#dfe3e9] bg-white p-4 sm:p-5">
                            <div class="mb-5 flex items-center gap-3">
                                <span class="material-symbols-outlined flex h-10 w-10 items-center justify-center rounded-xl bg-[#eff4ff] text-[#001356]">admin_panel_settings</span>
                                <div>
                                    <h3 class="text-base font-extrabold text-[#171c20]">Akun Admin</h3>
                                    <p class="text-xs text-[#5a5f64]">Akun utama untuk mengelola cafe dan kasir.</p>
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div>
                                    <label for="name" class="mb-2 block text-xs font-extrabold uppercase tracking-[0.14em] text-[#454650]">Nama Admin</label>
                                    <input id="name" class="min-h-12 w-full rounded-xl border border-[#c6c5d2] bg-[#fbfcff] px-4 text-base font-semibold text-[#171c20] outline-none transition focus:border-[#001356] focus:ring-2 focus:ring-[#d5e3fc] sm:text-sm" type="text" name="name" value="{{ old('name') }}" required autocomplete="name" placeholder="Nama penanggung jawab">
                                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                                </div>

                                <div>
                                    <label for="email" class="mb-2 block text-xs font-extrabold uppercase tracking-[0.14em] text-[#454650]">Email Login Admin</label>
                                    <input id="email" class="min-h-12 w-full rounded-xl border border-[#c6c5d2] bg-[#fbfcff] px-4 text-base font-semibold text-[#171c20] outline-none transition focus:border-[#001356] focus:ring-2 focus:ring-[#d5e3fc] sm:text-sm" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" placeholder="admin@cafe.com">
                                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                                </div>

                                <div>
                                    <label for="password" class="mb-2 block text-xs font-extrabold uppercase tracking-[0.14em] text-[#454650]">Password</label>
                                    <input id="password" class="min-h-12 w-full rounded-xl border border-[#c6c5d2] bg-[#fbfcff] px-4 text-base font-semibold text-[#171c20] outline-none transition focus:border-[#001356] focus:ring-2 focus:ring-[#d5e3fc] sm:text-sm" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter">
                                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                                </div>

                                <div>
                                    <label for="password_confirmation" class="mb-2 block text-xs font-extrabold uppercase tracking-[0.14em] text-[#454650]">Konfirmasi Password</label>
                                    <input id="password_confirmation" class="min-h-12 w-full rounded-xl border border-[#c6c5d2] bg-[#fbfcff] px-4 text-base font-semibold text-[#171c20] outline-none transition focus:border-[#001356] focus:ring-2 focus:ring-[#d5e3fc] sm:text-sm" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi password">
                                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                                </div>
                            </div>
                        </div>

                        <div class="sticky bottom-0 -mx-5 -mb-5 border-t border-[#dfe3e9] bg-white/95 px-5 py-4 backdrop-blur sm:-mx-7 sm:-mb-7 sm:px-7 lg:static lg:m-0 lg:flex lg:items-center lg:justify-between lg:border-t lg:bg-transparent lg:px-0 lg:pb-0 lg:pt-6">
                            <a class="mb-3 inline-flex min-h-11 items-center text-sm font-extrabold text-[#454650] underline underline-offset-4 hover:text-[#001356] lg:mb-0" href="{{ route('login') }}">
                                Sudah punya akun?
                            </a>
                            <button class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#001356] px-6 text-sm font-extrabold text-white shadow-[0_8px_24px_rgba(27,43,107,0.18)] transition hover:bg-[#1b2b6b] active:scale-[0.99] lg:w-auto">
                                Daftar & Menunggu Aktivasi
                                <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                            </button>
                        </div>
                    </form>
                </div>
            </section>
        </div>
    </main>
</x-guest-layout>
