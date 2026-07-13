<x-pos-layout active="tenants" title="Data Tenant" subtitle="Kelola cafe/admin yang mendaftar ke Keijora POS.">
    <section class="overflow-hidden rounded-2xl border border-[#c6c5d2] bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-[#c6c5d2] p-5 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-xl font-extrabold text-[#171c20]">Daftar Tenant</h2>
                <p class="mt-1 text-sm text-[#454650]">Filter berdasarkan status aktivasi.</p>
            </div>
            <div class="flex gap-2 overflow-x-auto">
                @foreach (['' => 'Semua', 'pending' => 'Pending', 'active' => 'Active', 'suspended' => 'Suspended'] as $status => $label)
                    <a href="{{ route('super-admin.tenants.index', $status ? ['status' => $status] : []) }}" class="whitespace-nowrap rounded-xl px-4 py-2 text-sm font-bold {{ request('status') === $status || (! request('status') && $status === '') ? 'bg-[#001356] text-white' : 'bg-[#eaeef4] text-[#454650]' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[820px] text-left">
                <thead class="bg-[#f0f4fa]">
                    <tr>
                        <th class="px-5 py-3 text-sm font-extrabold text-[#454650]">Cafe</th>
                        <th class="px-5 py-3 text-sm font-extrabold text-[#454650]">Kontak</th>
                        <th class="px-5 py-3 text-sm font-extrabold text-[#454650]">User</th>
                        <th class="px-5 py-3 text-sm font-extrabold text-[#454650]">Status</th>
                        <th class="px-5 py-3 text-right text-sm font-extrabold text-[#454650]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#dfe3e9]">
                    @forelse ($tenants as $tenant)
                        <tr>
                            <td class="px-5 py-4">
                                <p class="font-bold text-[#171c20]">{{ $tenant->name }}</p>
                                <p class="text-xs text-[#767681]">{{ $tenant->created_at->format('d M Y H:i') }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm text-[#454650]">
                                <p>{{ $tenant->phone ?: '-' }}</p>
                                <p>{{ $tenant->business_email ?: '-' }}</p>
                            </td>
                            <td class="px-5 py-4 text-sm text-[#454650]">{{ $tenant->users->count() }} user</td>
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
                            <td colspan="5" class="px-5 py-10 text-center text-sm text-[#454650]">Belum ada tenant.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-[#dfe3e9] p-5">
            {{ $tenants->withQueryString()->links() }}
        </div>
    </section>
</x-pos-layout>
