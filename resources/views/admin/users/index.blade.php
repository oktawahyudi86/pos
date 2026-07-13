<x-pos-layout active="users" title="Manajemen Pengguna" subtitle="Kelola kasir yang bisa mengakses tenant cafe ini.">
    <x-slot name="actions">
        <a href="{{ route('admin.users.create') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#001356] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#1b2b6b]">
            <span class="material-symbols-outlined text-[20px]">add</span>
            <span class="hidden sm:inline">Tambah Kasir</span>
        </a>
    </x-slot>

    @if (session('status'))
        <div class="rounded-2xl border border-[#6ffbbe] bg-[#effcf5] px-5 py-4 text-sm font-bold text-[#005236]">
            {{ session('status') }}
        </div>
    @endif

    <section class="overflow-hidden rounded-2xl border border-[#c6c5d2] bg-white shadow-sm">
        <div class="flex flex-col gap-4 border-b border-[#c6c5d2] p-5 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="text-xl font-extrabold text-[#171c20]">Daftar Kasir</h2>
                <p class="mt-1 text-sm text-[#454650]">Kasir hanya dapat masuk ke layar kasir dan transaksi.</p>
            </div>

            <form method="GET" action="{{ route('admin.users.index') }}" class="relative w-full lg:max-w-sm">
                <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-[#767681]">search</span>
                <input name="search" value="{{ request('search') }}" class="min-h-12 w-full rounded-full border border-[#c6c5d2] bg-[#f6faff] pl-12 pr-4 text-sm font-semibold text-[#171c20] outline-none focus:border-[#001356] focus:ring-2 focus:ring-[#d5e3fc]" placeholder="Cari nama atau email..." type="search">
            </form>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-left">
                <thead class="bg-[#f0f4fa]">
                    <tr>
                        <th class="px-5 py-4 text-sm font-extrabold text-[#454650]">Nama</th>
                        <th class="px-5 py-4 text-sm font-extrabold text-[#454650]">Email</th>
                        <th class="px-5 py-4 text-sm font-extrabold text-[#454650]">Role</th>
                        <th class="px-5 py-4 text-sm font-extrabold text-[#454650]">Status</th>
                        <th class="px-5 py-4 text-right text-sm font-extrabold text-[#454650]">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#dfe3e9]">
                    @forelse ($users as $cashier)
                        <tr class="transition hover:bg-[#f6faff]">
                            <td class="px-5 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#d5e3fc] text-xs font-extrabold text-[#001356]">
                                        {{ str($cashier->name)->explode(' ')->map(fn ($word) => str($word)->substr(0, 1))->take(2)->join('') }}
                                    </div>
                                    <div>
                                        <p class="font-extrabold text-[#171c20]">{{ $cashier->name }}</p>
                                        <p class="text-xs text-[#767681]">Dibuat {{ $cashier->created_at->format('d M Y') }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-5 py-4 text-sm font-semibold text-[#454650]">{{ $cashier->email }}</td>
                            <td class="px-5 py-4 text-sm font-semibold text-[#454650]">Kasir</td>
                            <td class="px-5 py-4">
                                <span class="rounded-full px-3 py-1 text-xs font-extrabold {{ $cashier->status === 'active' ? 'bg-[#dff8ea] text-[#005236]' : 'bg-[#ffdad6] text-[#93000a]' }}">
                                    {{ $cashier->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </span>
                            </td>
                            <td class="px-5 py-4">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('admin.users.edit', $cashier) }}" class="inline-flex min-h-10 items-center justify-center rounded-lg px-3 text-sm font-bold text-[#001356] hover:bg-[#d5e3fc]">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('admin.users.destroy', $cashier) }}" onsubmit="return confirm('Hapus kasir ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="inline-flex min-h-10 items-center justify-center rounded-lg px-3 text-sm font-bold text-[#ba1a1a] hover:bg-[#ffdad6]">
                                            Hapus
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-5 py-14 text-center">
                                <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-2xl bg-[#eaeef4] text-[#001356]">
                                    <span class="material-symbols-outlined text-3xl">group</span>
                                </div>
                                <h3 class="mt-4 text-lg font-extrabold text-[#171c20]">Belum ada kasir</h3>
                                <p class="mt-1 text-sm text-[#454650]">Tambahkan kasir pertama agar transaksi bisa mulai diproses.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="border-t border-[#dfe3e9] p-5">
            {{ $users->withQueryString()->links() }}
        </div>
    </section>

</x-pos-layout>
