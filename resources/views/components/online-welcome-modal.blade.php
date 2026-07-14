@props(['tenant'])

@php
    $authUrl = route('online-orders.auth', [
        'tenant' => $tenant,
        'redirect' => url()->current(),
    ]);
@endphp

@guest
    <div
        id="online-welcome-modal"
        data-auth-url="{{ $authUrl }}"
        class="fixed inset-0 z-[1200] hidden items-center justify-center bg-[#001356]/55 px-4 py-6 opacity-0 backdrop-blur-sm transition-opacity duration-200"
        aria-hidden="true"
    >
        <section class="w-full max-w-lg translate-y-4 rounded-2xl bg-white p-5 shadow-[0_24px_70px_rgba(0,19,86,0.28)] transition-transform duration-200 sm:p-6" role="dialog" aria-modal="true" aria-labelledby="welcome-modal-title">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-extrabold uppercase tracking-[0.24em] text-[#767681]">Order Online</p>
                    <h2 id="welcome-modal-title" class="mt-2 text-2xl font-extrabold leading-tight text-[#001356]">Selamat Datang 👋</h2>
                </div>
                <button type="button" data-welcome-close class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-[#454650] transition hover:bg-[#f1f4fa]" aria-label="Tutup">
                    <span class="material-symbols-outlined">close</span>
                </button>
            </div>

            <p class="mt-4 text-sm font-semibold leading-6 text-[#454650]">Nikmati pengalaman pemesanan yang lebih mudah.</p>

            <div class="mt-5 rounded-2xl border border-[#dfe3e9] bg-[#f6faff] p-4">
                <p class="text-sm font-extrabold text-[#171c20]">Dengan akun Anda dapat:</p>
                <ul class="mt-3 space-y-2 text-sm font-semibold text-[#454650]">
                    @foreach ([
                        'Melihat riwayat pesanan',
                        'Menyimpan alamat favorit',
                        'Checkout lebih cepat',
                        'Mendapat promo khusus member',
                    ] as $benefit)
                        <li class="flex items-start gap-2">
                            <span class="material-symbols-outlined mt-0.5 text-[18px] text-[#001356]">check_circle</span>
                            <span>{{ $benefit }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>

            <label class="mt-5 flex cursor-pointer items-start gap-3 rounded-xl border border-[#dfe3e9] bg-white p-3 text-sm font-semibold text-[#454650]">
                <input id="welcome-do-not-show" type="checkbox" class="mt-0.5 h-5 w-5 rounded border-[#c6c5d2] text-[#001356] focus:ring-[#001356]">
                <span>Jangan tampilkan lagi di perangkat ini</span>
            </label>

            <div class="mt-5 grid gap-3 sm:grid-cols-[1.15fr_0.85fr]">
                <a href="{{ $authUrl }}" data-welcome-auth class="flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#001356] px-4 text-sm font-extrabold text-white shadow-[0_10px_26px_rgba(0,19,86,0.22)] transition active:scale-[0.98]">
                    Masuk / Daftar
                    <span class="material-symbols-outlined text-[20px]">login</span>
                </a>
                <button type="button" data-welcome-guest class="flex min-h-12 items-center justify-center rounded-xl border border-[#c6c5d2] bg-white px-4 text-sm font-extrabold text-[#001356] transition active:scale-[0.98]">
                    Lanjut Sebagai Tamu
                </button>
            </div>
        </section>
    </div>

    <script>
        (() => {
            const modal = document.getElementById('online-welcome-modal');
            if (!modal) return;

            const storageKey = 'keijora_online_guest_{{ $tenant->id }}';
            const doNotShowKey = 'keijora_online_welcome_hidden_{{ $tenant->id }}';
            const dismissedKey = 'keijora_online_welcome_dismissed_{{ $tenant->id }}';
            const doNotShowInput = document.getElementById('welcome-do-not-show');
            const dialog = modal.querySelector('section');

            const hasGuestChoice = localStorage.getItem(storageKey) === '1';
            const isHiddenPermanently = localStorage.getItem(doNotShowKey) === '1';
            const wasDismissed = localStorage.getItem(dismissedKey) === '1';

            function openModal() {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';

                requestAnimationFrame(() => {
                    modal.classList.remove('opacity-0');
                    dialog?.classList.remove('translate-y-4');
                });
            }

            function closeModal() {
                modal.classList.add('opacity-0');
                dialog?.classList.add('translate-y-4');
                modal.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';

                window.setTimeout(() => {
                    modal.classList.add('hidden');
                    modal.classList.remove('flex');
                }, 180);
            }

            function rememberGuestChoice() {
                localStorage.setItem(storageKey, '1');
                localStorage.setItem(dismissedKey, '1');

                if (doNotShowInput?.checked) {
                    localStorage.setItem(doNotShowKey, '1');
                }
            }

            function rememberDismissal() {
                localStorage.setItem(dismissedKey, '1');

                if (doNotShowInput?.checked) {
                    localStorage.setItem(doNotShowKey, '1');
                }
            }

            if (!hasGuestChoice && !isHiddenPermanently && !wasDismissed) {
                window.setTimeout(openModal, 250);
            }

            modal.querySelector('[data-welcome-guest]')?.addEventListener('click', () => {
                rememberGuestChoice();
                closeModal();
            });

            modal.querySelector('[data-welcome-auth]')?.addEventListener('click', () => {
                rememberDismissal();
                closeModal();
            });

            modal.querySelector('[data-welcome-close]')?.addEventListener('click', () => {
                rememberDismissal();
                closeModal();
            });

            modal.addEventListener('click', (event) => {
                if (event.target === modal) {
                    rememberDismissal();
                    closeModal();
                }
            });

            document.addEventListener('keydown', (event) => {
                if (event.key === 'Escape' && !modal.classList.contains('hidden')) {
                    rememberDismissal();
                    closeModal();
                }
            });
        })();
    </script>
@endguest
