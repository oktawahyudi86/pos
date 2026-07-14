<x-pos-layout active="tenants" title="Detail Tenant" subtitle="Aktivasi cafe, lihat admin, dan kelola akun kasir." :back-url="route('super-admin.tenants.index')">
    <div class="space-y-6">
        <section class="grid gap-4 xl:grid-cols-[1.1fr_0.9fr]">
            <article class="overflow-hidden rounded-2xl border border-[#c6c5d2] bg-white shadow-sm">
                <div class="border-b border-[#c6c5d2] p-5">
                    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
                        <div class="flex gap-4">
                            <div class="flex h-14 w-14 shrink-0 items-center justify-center overflow-hidden rounded-2xl bg-[#e8f1ff] text-[#001356]">
                                @if ($tenant->logo_path)
                                    <img src="{{ asset('storage/'.$tenant->logo_path) }}" alt="{{ $tenant->name }}" class="h-full w-full object-cover">
                                @else
                                    <img src="{{ asset('images/keijora-bird-navy.png') }}" alt="Keijora" class="h-11 w-11 object-contain">
                                @endif
                            </div>
                            <div>
                                <h2 class="text-2xl font-extrabold text-[#171c20]">{{ $tenant->name }}</h2>
                                <p class="mt-1 text-sm text-[#454650]">{{ $tenant->address ?: 'Alamat belum diisi' }}</p>
                            </div>
                        </div>
                        <span class="w-fit rounded-full px-3 py-1 text-xs font-extrabold {{ $tenant->status === 'active' ? 'bg-[#dff8ea] text-[#005236]' : ($tenant->status === 'pending' ? 'bg-[#fff3cd] text-[#7a4b00]' : 'bg-[#ffdad6] text-[#93000a]') }}">
                            {{ ucfirst($tenant->status) }}
                        </span>
                    </div>
                </div>

                <div class="grid gap-4 p-5 sm:grid-cols-2">
                    <div class="rounded-2xl bg-[#f6faff] p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#767681]">Email Bisnis</p>
                        <p class="mt-2 font-bold text-[#171c20]">{{ $tenant->business_email ?: '-' }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#f6faff] p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#767681]">Nomor HP</p>
                        <p class="mt-2 font-bold text-[#171c20]">{{ $tenant->phone ?: '-' }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#f6faff] p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#767681]">Tanggal Daftar</p>
                        <p class="mt-2 font-bold text-[#171c20]">{{ $tenant->created_at->format('d M Y H:i') }}</p>
                    </div>
                    <div class="rounded-2xl bg-[#f6faff] p-4">
                        <p class="text-xs font-bold uppercase tracking-[0.18em] text-[#767681]">Disetujui Oleh</p>
                        <p class="mt-2 font-bold text-[#171c20]">{{ $tenant->approver?->name ?? '-' }}</p>
                    </div>
                </div>
            </article>

            <article class="rounded-2xl border border-[#c6c5d2] bg-white p-5 shadow-sm">
                <h3 class="text-xl font-extrabold text-[#171c20]">Kontrol Aktivasi</h3>
                <p class="mt-1 text-sm text-[#454650]">Akun admin baru hanya bisa masuk penuh setelah tenant diaktifkan Super Admin.</p>

                <div class="mt-5 grid gap-3">
                    @if ($tenant->status !== 'active')
                        <form method="POST" action="{{ route('super-admin.tenants.activate', $tenant) }}">
                            @csrf
                            @method('PATCH')
                            <button class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#001356] px-4 py-3 text-sm font-extrabold text-white shadow-sm hover:bg-[#001f7a]">
                                <span class="material-symbols-outlined">verified</span>
                                Aktifkan Tenant
                            </button>
                        </form>
                    @endif

                    @if ($tenant->status !== 'suspended')
                        <form method="POST" action="{{ route('super-admin.tenants.suspend', $tenant) }}">
                            @csrf
                            @method('PATCH')
                            <button class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl border border-[#ffb4ab] bg-[#fff5f3] px-4 py-3 text-sm font-extrabold text-[#93000a] hover:bg-[#ffdad6]">
                                <span class="material-symbols-outlined">block</span>
                                Suspend Tenant
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('super-admin.tenants.index') }}" class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl border border-[#c6c5d2] bg-white px-4 py-3 text-sm font-extrabold text-[#454650] hover:text-[#001356]">
                        <span class="material-symbols-outlined">arrow_back</span>
                        Kembali ke Data Tenant
                    </a>
                </div>
            </article>
        </section>

        <section class="overflow-hidden rounded-2xl border border-[#c6c5d2] bg-white shadow-sm">
            <div class="border-b border-[#c6c5d2] p-5">
                <h3 class="text-xl font-extrabold text-[#171c20]">User Admin & Kasir</h3>
                <p class="mt-1 text-sm text-[#454650]">Super Admin bisa mengubah email, status, dan reset password user pada tenant ini.</p>
            </div>

            <div class="divide-y divide-[#dfe3e9]">
                @forelse ($tenant->users as $tenantUser)
                    <form method="POST" action="{{ route('super-admin.tenants.users.update', [$tenant, $tenantUser]) }}" class="grid gap-4 p-5 lg:grid-cols-[1fr_1fr_150px_1fr_auto] lg:items-end">
                        @csrf
                        @method('PATCH')

                        <div>
                            <label class="text-xs font-bold uppercase tracking-[0.16em] text-[#767681]">Nama</label>
                            <input name="name" value="{{ old('name', $tenantUser->name) }}" class="mt-2 min-h-12 w-full rounded-xl border border-[#c6c5d2] bg-white px-4 text-sm font-semibold text-[#171c20] outline-none focus:border-[#001356]">
                            <p class="mt-1 text-xs font-bold uppercase tracking-[0.16em] text-[#001356]">{{ $tenantUser->roles->pluck('name')->join(', ') ?: 'User' }}</p>
                        </div>

                        <div>
                            <label class="text-xs font-bold uppercase tracking-[0.16em] text-[#767681]">Email</label>
                            <input name="email" type="email" value="{{ old('email', $tenantUser->email) }}" class="mt-2 min-h-12 w-full rounded-xl border border-[#c6c5d2] bg-white px-4 text-sm font-semibold text-[#171c20] outline-none focus:border-[#001356]">
                        </div>

                        <div>
                            <label class="text-xs font-bold uppercase tracking-[0.16em] text-[#767681]">Status</label>
                            <select name="status" class="mt-2 min-h-12 w-full rounded-xl border border-[#c6c5d2] bg-white px-4 text-sm font-semibold text-[#171c20] outline-none focus:border-[#001356]">
                                @foreach (['pending' => 'Pending', 'active' => 'Active', 'suspended' => 'Suspended'] as $status => $label)
                                    <option value="{{ $status }}" @selected(old('status', $tenantUser->status) === $status)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label class="text-xs font-bold uppercase tracking-[0.16em] text-[#767681]">Password Baru</label>
                            <div class="mt-2 grid gap-2 sm:grid-cols-2 lg:grid-cols-1 xl:grid-cols-2">
                                <div class="relative">
                                    <input name="password" type="password" placeholder="Opsional" class="min-h-12 w-full rounded-xl border border-[#c6c5d2] bg-white px-4 text-sm font-semibold text-[#171c20] outline-none focus:border-[#001356] pr-12">
                                    <button type="button" onclick="togglePassword(this.previousElementSibling, this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#767681] transition hover:text-[#001356]">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </button>
                                </div>
                                <div class="relative">
                                    <input name="password_confirmation" type="password" placeholder="Konfirmasi" class="min-h-12 w-full rounded-xl border border-[#c6c5d2] bg-white px-4 text-sm font-semibold text-[#171c20] outline-none focus:border-[#001356] pr-12">
                                    <button type="button" onclick="togglePassword(this.previousElementSibling, this)" class="absolute right-3 top-1/2 -translate-y-1/2 text-[#767681] transition hover:text-[#001356]">
                                        <span class="material-symbols-outlined text-[20px]">visibility</span>
                                    </button>
                                </div>
                            </div>
                            <button type="button" onclick="openUserPasswordResetModal('{{ $tenantUser->id }}', '{{ $tenantUser->name }}', '{{ $tenantUser->email }}')" class="mt-2 inline-flex items-center gap-2 text-xs font-bold text-[#001356] hover:underline">
                                <span class="material-symbols-outlined text-[16px]">link</span>
                                Kirim Link Reset Password
                            </button>
                        </div>

                        <button class="min-h-12 rounded-xl bg-[#001356] px-5 text-sm font-extrabold text-white shadow-sm hover:bg-[#001f7a]">
                            Simpan
                        </button>
                    </form>
                @empty
                    <div class="p-10 text-center text-sm text-[#454650]">Belum ada user pada tenant ini.</div>
                @endforelse
            </div>
        </section>
    </div>

    <!-- Password Reset Modal for Tenant Users -->
    <div id="user-password-reset-modal" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/50 p-4">
        <div class="w-full max-w-md rounded-2xl bg-white p-4 sm:p-6 shadow-xl">
            <div class="mb-4 flex items-center justify-between">
                <h3 class="text-base font-bold text-[#171c20] sm:text-lg">Reset Password User</h3>
                <button onclick="closeUserPasswordResetModal()" class="text-[#767681] transition hover:text-[#171c20]">
                    <span class="material-symbols-outlined text-[20px] sm:text-[24px]">close</span>
                </button>
            </div>
            <p class="mb-4 text-sm text-[#454650]">
                Reset password untuk <span id="user-name" class="font-semibold text-[#171c20]"></span>
            </p>
            <div class="mb-4 rounded-xl bg-[#f6faff] p-3 sm:p-4">
                <p class="mb-2 text-xs font-bold text-[#767681]">Link Reset Password:</p>
                <div class="flex gap-2">
                    <input id="user-reset-link-input" type="text" readonly class="flex-1 rounded-lg border border-[#c6c5d2] bg-white px-3 py-2 text-xs sm:text-sm text-[#171c20]">
                    <button onclick="copyUserResetLink()" class="rounded-lg bg-[#001356] px-2 sm:px-3 py-2 text-xs font-bold text-white transition active:scale-[0.98]">
                        Salin
                    </button>
                </div>
            </div>
            <div class="flex gap-3">
                <button onclick="closeUserPasswordResetModal()" class="flex flex-1 items-center justify-center rounded-xl border border-[#c6c5d2] bg-white px-4 py-2.5 text-xs font-bold text-[#454650] transition active:scale-[0.98] sm:py-3 sm:text-sm">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <script>
        function togglePassword(input, button) {
            const icon = button.querySelector('.material-symbols-outlined');

            if (input.type === 'password') {
                input.type = 'text';
                icon.textContent = 'visibility_off';
            } else {
                input.type = 'password';
                icon.textContent = 'visibility';
            }
        }

        let currentUserResetLink = '';

        function openUserPasswordResetModal(userId, userName, userEmail) {
            document.getElementById('user-name').textContent = userName;

            // Generate reset link via API
            fetch(`/super-admin/tenants/{{ $tenant->id }}/users/${userId}/reset-password`, {
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
                    currentUserResetLink = linkText;
                    document.getElementById('user-reset-link-input').value = linkText;
                }
            });

            document.getElementById('user-password-reset-modal').classList.remove('hidden');
            document.getElementById('user-password-reset-modal').classList.add('flex');
        }

        function closeUserPasswordResetModal() {
            document.getElementById('user-password-reset-modal').classList.add('hidden');
            document.getElementById('user-password-reset-modal').classList.remove('flex');
        }

        function copyUserResetLink() {
            const input = document.getElementById('user-reset-link-input');
            input.select();
            document.execCommand('copy');
            alert('Link berhasil disalin!');
        }
    </script>
</x-pos-layout>
