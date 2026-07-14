<x-pos-layout active="customers" title="Pelanggan" subtitle="Kelola data pelanggan dan status akun.">
    @if (session('success'))
        <div class="rounded-xl border border-[#b9c7df] bg-white px-4 py-3 text-sm font-semibold text-[#001356]">
            {{ session('success') }}
        </div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h2 class="text-lg font-extrabold text-[#171c20]">Data Pelanggan</h2>
            <p class="text-sm text-[#454650]">{{ $customers->count() }} pelanggan terdaftar</p>
        </div>
    </div>

    <section class="space-y-3 xl:space-y-0 xl:overflow-hidden xl:rounded-xl xl:border xl:border-[#c6c5d2] xl:bg-white xl:shadow-[0_4px_12px_rgba(27,43,107,0.04)]">
        <div class="hidden grid-cols-[1fr_1fr_1fr_100px_150px_200px] gap-4 border-b border-[#dfe3e9] bg-[#f6faff] px-5 py-3 text-xs font-bold uppercase tracking-widest text-[#767681] xl:grid">
            <div>Nama</div>
            <div>No. HP</div>
            <div>ID Pelanggan</div>
            <div>Order</div>
            <div>Status</div>
            <div class="text-right">Aksi</div>
        </div>

        <div class="space-y-3 xl:divide-t xl:divide-[#dfe3e9] xl:space-y-0">
            @forelse ($customers as $customer)
                <div class="grid grid-cols-1 gap-4 border-b border-[#dfe3e9] bg-white px-5 py-4 xl:grid-cols-[1fr_1fr_1fr_100px_150px_200px] xl:border-b-0 xl:bg-transparent xl:py-3">
                    <div class="min-w-0">
                        <p class="text-sm font-bold text-[#171c20]">{{ $customer->name }}</p>
                        <p class="text-xs text-[#767681] xl:hidden">No. HP: {{ $customer->phone }}</p>
                        <p class="text-xs text-[#767681] xl:hidden">ID: #{{ $customer->id }}</p>
                        <p class="text-xs text-[#767681] xl:hidden">Order: {{ $customer->online_orders_count }}</p>
                    </div>
                    <div class="hidden xl:block">
                        <p class="text-sm font-semibold text-[#171c20]">{{ $customer->phone }}</p>
                    </div>
                    <div class="hidden xl:block">
                        <p class="text-sm font-semibold text-[#171c20]">#{{ $customer->id }}</p>
                    </div>
                    <div class="hidden xl:block">
                        <p class="text-sm font-bold text-[#001356]">{{ $customer->online_orders_count }}</p>
                    </div>
                    <div>
                        @if ($customer->status === 'active')
                            <span class="inline-flex items-center gap-1 rounded-full bg-[#e7fff2] px-3 py-1 text-xs font-bold text-[#005236]">
                                <span class="h-2 w-2 rounded-full bg-[#005236]"></span>
                                Aktif
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 rounded-full bg-[#fff4f2] px-3 py-1 text-xs font-bold text-[#93000a]">
                                <span class="h-2 w-2 rounded-full bg-[#93000a]"></span>
                                Nonaktif
                            </span>
                        @endif
                    </div>
                    <div class="flex items-center justify-end gap-2">
                        @if ($customer->status === 'active')
                            <form method="POST" action="{{ route('customers.deactivate', $customer) }}" onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan pelanggan ini?')">
                                @csrf
                                <button type="submit" class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-[#ffdad6] bg-white px-3 py-2 text-xs font-bold text-[#93000a] transition active:scale-[0.98]">
                                    <span class="material-symbols-outlined text-[18px]">block</span>
                                    Nonaktifkan
                                </button>
                            </form>
                        @else
                            <form method="POST" action="{{ route('customers.activate', $customer) }}">
                                @csrf
                                <button type="submit" class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-[#b9c7df] bg-white px-3 py-2 text-xs font-bold text-[#001356] transition active:scale-[0.98]">
                                    <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                    Aktifkan
                                </button>
                            </form>
                        @endif
                        <button type="button" onclick="openPasswordResetModal('{{ $customer->id }}', '{{ $customer->name }}', '{{ $customer->phone }}')" class="inline-flex min-h-10 items-center gap-2 rounded-xl border border-[#c6c5d2] bg-white px-3 py-2 text-xs font-bold text-[#454650] transition active:scale-[0.98]">
                            <span class="material-symbols-outlined text-[18px]">link</span>
                            Reset Password
                        </button>
                    </div>
                </div>
            @empty
                <div class="p-10 text-center text-sm text-[#454650]">
                    <span class="material-symbols-outlined mx-auto mb-3 flex h-16 w-16 items-center justify-center rounded-full bg-[#eef3ff] text-[#001356] text-[32px]">people</span>
                    <p class="font-semibold text-[#171c20]">Belum ada pelanggan</p>
                    <p class="mt-1">Pelanggan akan muncul di sini setelah mendaftar melalui aplikasi online.</p>
                </div>
            @endforelse
        </div>
    </section>

    <!-- Password Reset Modal -->
    <div id="password-reset-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-4 sm:p-6 shadow-xl">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-base font-bold text-[#171c20] sm:text-lg">Reset Password</h3>
                <button onclick="closePasswordResetModal()" class="text-[#767681] transition hover:text-[#171c20]">
                    <span class="material-symbols-outlined text-[20px] sm:text-[24px]">close</span>
                </button>
            </div>
            <p class="mb-4 text-sm text-[#454650]">
                Reset password untuk <span id="customer-name" class="font-semibold text-[#171c20]"></span>
            </p>
            <div class="mb-4 rounded-xl bg-[#f6faff] p-3 sm:p-4">
                <p class="mb-2 text-xs font-bold text-[#767681]">Link Reset Password:</p>
                <div class="flex gap-2">
                    <input id="reset-link-input" type="text" readonly class="flex-1 rounded-lg border border-[#c6c5d2] bg-white px-3 py-2 text-xs sm:text-sm text-[#171c20]">
                    <button onclick="copyResetLink()" class="rounded-lg bg-[#001356] px-2 sm:px-3 py-2 text-xs font-bold text-white transition active:scale-[0.98]">
                        Salin
                    </button>
                </div>
            </div>
            <div class="flex flex-col gap-2 sm:flex-row sm:gap-3">
                <button onclick="sendViaWhatsApp()" class="flex items-center justify-center gap-2 rounded-xl bg-[#25D366] px-4 py-2.5 text-xs font-bold text-white transition active:scale-[0.98] sm:py-3 sm:text-sm">
                    <svg class="h-4 w-4 sm:h-5 sm:w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Kirim via WhatsApp
                </button>
                <button onclick="closePasswordResetModal()" class="flex items-center justify-center rounded-xl border border-[#c6c5d2] bg-white px-4 py-2.5 text-xs font-bold text-[#454650] transition active:scale-[0.98] sm:py-3 sm:text-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        let currentResetLink = '';
        let currentCustomerPhone = '';

        function openPasswordResetModal(customerId, customerName, customerPhone) {
            document.getElementById('customer-name').textContent = customerName;
            currentCustomerPhone = customerPhone;
            
            // Generate reset link via API
            fetch(`/pelanggan/${customerId}/reset-password`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({})
            })
            .then(response => response.text())
            .then(html => {
                // Extract the link from the response
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const successDiv = doc.querySelector('.border-\\[\\#b9c7df\\]');
                if (successDiv) {
                    const linkText = successDiv.textContent.replace('Link reset password berhasil dibuat: ', '');
                    currentResetLink = linkText;
                    document.getElementById('reset-link-input').value = linkText;
                }
            });

            document.getElementById('password-reset-modal').classList.remove('hidden');
            document.getElementById('password-reset-modal').classList.add('flex');
        }

        function closePasswordResetModal() {
            document.getElementById('password-reset-modal').classList.add('hidden');
            document.getElementById('password-reset-modal').classList.remove('flex');
        }

        function copyResetLink() {
            const input = document.getElementById('reset-link-input');
            input.select();
            document.execCommand('copy');
            alert('Link berhasil disalin!');
        }

        function sendViaWhatsApp() {
            const message = `Halo, berikut link untuk reset password Anda: ${currentResetLink}`;
            const whatsappUrl = `https://wa.me/${currentCustomerPhone}?text=${encodeURIComponent(message)}`;
            window.open(whatsappUrl, '_blank');
        }
    </script>
</x-pos-layout>
