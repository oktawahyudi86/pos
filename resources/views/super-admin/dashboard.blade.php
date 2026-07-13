<x-pos-layout active="dashboard" title="Super Admin" subtitle="Pantau tenant yang mendaftar dan status aktivasi akun.">
    <div class="space-y-6">
        <section class="grid grid-cols-2 gap-3 md:grid-cols-3">
            @foreach ([
                ['label' => 'Pending', 'value' => $pendingCount, 'icon' => 'hourglass_top', 'color' => 'text-[#b26a00] bg-[#fff3cd]'],
                ['label' => 'Active', 'value' => $activeCount, 'icon' => 'verified', 'color' => 'text-[#005236] bg-[#dff8ea]'],
                ['label' => 'Suspended', 'value' => $suspendedCount, 'icon' => 'block', 'color' => 'text-[#93000a] bg-[#ffdad6]'],
            ] as $card)
                <article class="rounded-2xl border border-[#c6c5d2] bg-white p-4 shadow-sm sm:p-6">
                    <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-2xl {{ $card['color'] }}">
                        <span class="material-symbols-outlined">{{ $card['icon'] }}</span>
                    </div>
                    <p class="text-sm font-bold text-[#454650]">{{ $card['label'] }}</p>
                    <p class="mt-1 text-3xl font-extrabold text-[#001356]">{{ $card['value'] }}</p>
                </article>
            @endforeach
        </section>

        <section class="overflow-hidden rounded-2xl border border-[#c6c5d2] bg-white shadow-sm">
            <div class="flex items-center justify-between border-b border-[#c6c5d2] p-5">
                <div>
                    <h2 class="text-xl font-extrabold text-[#171c20]">Tenant Terbaru</h2>
                    <p class="mt-1 text-sm text-[#454650]">Pendaftaran cafe terbaru.</p>
                </div>
                <a href="{{ route('super-admin.tenants.index') }}" class="rounded-xl bg-[#001356] px-4 py-3 text-sm font-bold text-white">Lihat Semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full min-w-[760px] text-left">
                    <thead class="bg-[#f0f4fa]">
                        <tr>
                            <th class="px-5 py-3 text-sm font-extrabold text-[#454650]">Cafe</th>
                            <th class="px-5 py-3 text-sm font-extrabold text-[#454650]">Admin</th>
                            <th class="px-5 py-3 text-sm font-extrabold text-[#454650]">Status</th>
                            <th class="px-5 py-3 text-right text-sm font-extrabold text-[#454650]">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#dfe3e9]">
                        @forelse ($latestTenants as $tenant)
                            <tr>
                                <td class="px-5 py-4 font-bold text-[#171c20]">{{ $tenant->name }}</td>
                                <td class="px-5 py-4 text-sm text-[#454650]">{{ $tenant->users->first(fn ($user) => $user->hasRole('Admin'))?->email ?? '-' }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-3 py-1 text-xs font-extrabold {{ $tenant->status === 'active' ? 'bg-[#dff8ea] text-[#005236]' : ($tenant->status === 'pending' ? 'bg-[#fff3cd] text-[#7a4b00]' : 'bg-[#ffdad6] text-[#93000a]') }}">
                                        {{ ucfirst($tenant->status) }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('super-admin.tenants.show', $tenant) }}" class="text-sm font-bold text-[#001356] hover:underline">Detail</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-5 py-10 text-center text-sm text-[#454650]">Belum ada tenant.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
</x-pos-layout>
