@php
    $currentTab = old('_auth_tab', $activeTab ?? 'login');
@endphp

<x-online-layout :tenant="$tenant" title="Login Customer" :back-url="route('online-orders.catalog', $tenant)">
    <div class="mx-auto max-w-5xl px-4 sm:px-6">
        <div class="mb-6">
            <h1 class="text-2xl font-extrabold leading-tight text-[#001356]">Login Customer</h1>
            <p class="mt-2 text-sm text-[#454650]">Masuk untuk checkout lebih cepat dan simpan riwayat pesanan.</p>
        </div>

        <div class="rounded-2xl border border-[#dfe3e9] bg-white p-5 shadow-[0_4px_12px_rgba(27,43,107,0.05)] sm:p-7">
            @if ($errors->any())
                <div class="mb-5 rounded-xl border border-[#ffdad6] bg-[#fff4f2] px-4 py-3 text-sm font-semibold text-[#93000a]">
                    {{ $errors->first() }}
                </div>
            @endif

            <div class="mb-5 grid grid-cols-2 rounded-xl bg-[#eef3fb] p-1">
                <button type="button" data-auth-tab-button="login" class="min-h-11 rounded-lg text-sm font-extrabold transition {{ $currentTab === 'login' ? 'bg-white text-[#001356] shadow-sm' : 'text-[#454650]' }}">Login</button>
                <button type="button" data-auth-tab-button="register" class="min-h-11 rounded-lg text-sm font-extrabold transition {{ $currentTab === 'register' ? 'bg-white text-[#001356] shadow-sm' : 'text-[#454650]' }}">Register</button>
            </div>

            <div class="mb-5 flex items-center gap-4">
                <div class="h-px flex-1 bg-[#c6c5d2]"></div>
                <span class="text-xs font-bold text-[#767681]">ATAU</span>
                <div class="h-px flex-1 bg-[#c6c5d2]"></div>
            </div>

            <a href="{{ $redirectTo ?? route('online-orders.catalog', $tenant) }}" class="mb-5 flex w-full items-center justify-center gap-2 rounded-xl border border-[#c6c5d2] bg-white px-4 py-3 text-sm font-extrabold text-[#001356] shadow-sm transition active:scale-[0.98]">
                <span class="material-symbols-outlined text-[20px]">person_outline</span>
                Lanjut Sebagai Tamu
            </a>

            <form method="POST" action="{{ route('online-orders.auth.login', $tenant) }}" data-auth-tab-panel="login" class="space-y-4 {{ $currentTab === 'login' ? '' : 'hidden' }}">
                @csrf
                <input type="hidden" name="_auth_tab" value="login">
                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">

                <div>
                    <label for="login-phone" class="mb-2 block text-sm font-bold text-[#171c20]">Nomor HP</label>
                    <input id="login-phone" name="phone" value="{{ old('_auth_tab') === 'login' ? old('phone') : '' }}" type="tel" inputmode="numeric" autocomplete="tel" required class="h-12 w-full rounded-xl border-[#c6c5d2] text-base focus:border-[#001356] focus:ring-[#001356]" placeholder="0812xxxxxxx">
                </div>

                <div>
                    <label for="login-password" class="mb-2 block text-sm font-bold text-[#171c20]">Password</label>
                    <div class="relative">
                        <input id="login-password" name="password" type="password" autocomplete="current-password" required class="h-12 w-full rounded-xl border-[#c6c5d2] text-base focus:border-[#001356] focus:ring-[#001356] pr-12" placeholder="Masukkan password">
                        <button type="button" onclick="togglePassword('login-password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#767681] transition hover:text-[#001356]">
                            <span class="material-symbols-outlined text-[20px]">visibility</span>
                        </button>
                    </div>
                </div>

                <button class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#001356] px-4 text-sm font-extrabold text-white shadow-[0_10px_26px_rgba(0,19,86,0.20)] transition active:scale-[0.98]">
                    Masuk
                    <span class="material-symbols-outlined text-[20px]">login</span>
                </button>
            </form>

            <form method="POST" action="{{ route('online-orders.auth.register.store', $tenant) }}" data-auth-tab-panel="register" class="space-y-4 {{ $currentTab === 'register' ? '' : 'hidden' }}">
                @csrf
                <input type="hidden" name="_auth_tab" value="register">
                <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">

                <div>
                    <label for="register-name" class="mb-2 block text-sm font-bold text-[#171c20]">Nama Lengkap</label>
                    <input id="register-name" name="name" value="{{ old('name') }}" autocomplete="name" required class="h-12 w-full rounded-xl border-[#c6c5d2] text-base focus:border-[#001356] focus:ring-[#001356]" placeholder="Nama lengkap">
                </div>

                <div>
                    <label for="register-phone" class="mb-2 block text-sm font-bold text-[#171c20]">Nomor HP</label>
                    <input id="register-phone" name="phone" value="{{ old('_auth_tab') === 'register' ? old('phone') : '' }}" type="tel" inputmode="numeric" autocomplete="tel" required class="h-12 w-full rounded-xl border-[#c6c5d2] text-base focus:border-[#001356] focus:ring-[#001356]" placeholder="0812xxxxxxx">
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="register-password" class="mb-2 block text-sm font-bold text-[#171c20]">Password</label>
                        <div class="relative">
                            <input id="register-password" name="password" type="password" autocomplete="new-password" required class="h-12 w-full rounded-xl border-[#c6c5d2] text-base focus:border-[#001356] focus:ring-[#001356] pr-12" placeholder="Minimal 8 karakter">
                            <button type="button" onclick="togglePassword('register-password', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#767681] transition hover:text-[#001356]">
                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                            </button>
                        </div>
                    </div>
                    <div>
                        <label for="register-password-confirmation" class="mb-2 block text-sm font-bold text-[#171c20]">Konfirmasi Password</label>
                        <div class="relative">
                            <input id="register-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" required class="h-12 w-full rounded-xl border-[#c6c5d2] text-base focus:border-[#001356] focus:ring-[#001356] pr-12" placeholder="Ulangi password">
                            <button type="button" onclick="togglePassword('register-password-confirmation', this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#767681] transition hover:text-[#001356]">
                                <span class="material-symbols-outlined text-[20px]">visibility</span>
                            </button>
                        </div>
                    </div>
                </div>

                <button class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#001356] px-4 text-sm font-extrabold text-white shadow-[0_10px_26px_rgba(0,19,86,0.20)] transition active:scale-[0.98]">
                    Daftar
                    <span class="material-symbols-outlined text-[20px]">arrow_forward</span>
                </button>
            </form>
        </div>
    </div>

    <script>
        function togglePassword(inputId, button) {
            const input = document.getElementById(inputId);
            const icon = button.querySelector('.material-symbols-outlined');

            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility';
            }
        }

        (() => {
            const buttons = Array.from(document.querySelectorAll('[data-auth-tab-button]'));
            const panels = Array.from(document.querySelectorAll('[data-auth-tab-panel]'));

            function activate(tab) {
                buttons.forEach((button) => {
                    const active = button.dataset.authTabButton === tab;
                    button.classList.toggle('bg-white', active);
                    button.classList.toggle('text-[#001356]', active);
                    button.classList.toggle('shadow-sm', active);
                    button.classList.toggle('text-[#454650]', !active);
                });

                panels.forEach((panel) => {
                    panel.classList.toggle('hidden', panel.dataset.authTabPanel !== tab);
                });
            }

            buttons.forEach((button) => {
                button.addEventListener('click', () => activate(button.dataset.authTabButton));
            });
        })();
    </script>
</x-online-layout>
