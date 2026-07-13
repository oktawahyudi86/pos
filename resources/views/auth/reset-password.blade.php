<x-guest-layout>
    <main class="flex min-h-screen w-full items-center justify-center overflow-hidden bg-[#eef2f8] p-4 sm:p-6" style="font-family: 'Plus Jakarta Sans', sans-serif;">
        <div class="absolute -left-24 -top-24 h-96 w-96 rounded-full bg-[#1b2b6b]/10 blur-3xl"></div>
        <div class="absolute bottom-0 right-0 h-80 w-80 rounded-full bg-[#6ffbbe]/20 blur-3xl"></div>

        <section class="relative w-full max-w-lg rounded-[1.5rem] border border-[#c6c5d2] bg-white p-6 shadow-[0_18px_50px_rgba(27,43,107,0.10)] sm:p-8">
            <a href="{{ url('/') }}" class="mb-8 inline-flex items-center gap-3">
                <div class="flex h-11 w-11 items-center justify-center">
                    <img src="{{ asset('images/keijora-bird-navy.png') }}" alt="Keijora" class="h-10 w-10 object-contain">
                </div>
                <div>
                    <img src="{{ asset('images/keijora-logo-cropped.png') }}" alt="Keijora" class="h-7 w-auto max-w-[118px] object-contain">
                    <p class="mt-1 text-[9px] font-extrabold uppercase tracking-[0.24em] text-[#767681]">POS System</p>
                </div>
            </a>

            <div class="mb-8">
                <p class="text-xs font-extrabold uppercase tracking-[0.2em] text-[#001356]">Password baru</p>
                <h2 class="mt-2 text-2xl font-extrabold tracking-tight text-[#1b2b6b]">Buat password pengganti</h2>
                <p class="mt-2 text-sm leading-6 text-[#5a5f64]">Gunakan password yang mudah diingat oleh Anda, tapi sulit ditebak orang lain.</p>
            </div>

            <form method="POST" action="{{ route('password.store') }}" class="space-y-5">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div>
                    <label for="email" class="mb-2 block text-xs font-extrabold uppercase tracking-[0.14em] text-[#454650]">Email Login</label>
                    <input id="email" class="min-h-14 w-full rounded-xl border border-[#c6c5d2] bg-[#fbfcff] px-4 text-sm font-semibold text-[#171c20] outline-none transition focus:border-[#001356] focus:ring-2 focus:ring-[#d5e3fc]" type="email" name="email" value="{{ old('email', $request->email) }}" required autofocus autocomplete="username">
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div>
                    <label for="password" class="mb-2 block text-xs font-extrabold uppercase tracking-[0.14em] text-[#454650]">Password Baru</label>
                    <input id="password" class="min-h-14 w-full rounded-xl border border-[#c6c5d2] bg-[#fbfcff] px-4 text-sm font-semibold text-[#171c20] outline-none transition focus:border-[#001356] focus:ring-2 focus:ring-[#d5e3fc]" type="password" name="password" required autocomplete="new-password" placeholder="Minimal 8 karakter">
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <label for="password_confirmation" class="mb-2 block text-xs font-extrabold uppercase tracking-[0.14em] text-[#454650]">Konfirmasi Password</label>
                    <input id="password_confirmation" class="min-h-14 w-full rounded-xl border border-[#c6c5d2] bg-[#fbfcff] px-4 text-sm font-semibold text-[#171c20] outline-none transition focus:border-[#001356] focus:ring-2 focus:ring-[#d5e3fc]" type="password" name="password_confirmation" required autocomplete="new-password" placeholder="Ulangi password baru">
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <button class="flex min-h-14 w-full items-center justify-center gap-2 rounded-xl bg-[#001356] px-6 text-sm font-extrabold text-white shadow-[0_8px_24px_rgba(27,43,107,0.18)] transition hover:bg-[#1b2b6b] active:scale-[0.99]">
                    Simpan Password Baru
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                </button>
            </form>

            <div class="mt-7 border-t border-[#dfe3e9] pt-6 text-center">
                <a href="{{ route('login') }}" class="text-sm font-extrabold text-[#001356] underline underline-offset-4">
                    Kembali ke halaman masuk
                </a>
            </div>
        </section>
    </main>
</x-guest-layout>
