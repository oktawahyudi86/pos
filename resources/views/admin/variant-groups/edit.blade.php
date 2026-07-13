<x-pos-layout active="products" title="Edit Grup Varian" subtitle="Perbarui pilihan varian yang dapat dipilih saat transaksi.">
    <x-slot name="actions">
        <a href="{{ route('admin.variant-groups.index') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-[#c6c5d2] bg-white px-4 py-3 text-sm font-bold text-[#001356] transition hover:bg-[#eef3fb]">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            <span class="hidden sm:inline">Kembali</span>
        </a>
    </x-slot>

    <div class="mx-auto max-w-3xl">
        <div class="rounded-3xl border border-[#c6c5d2] bg-white p-5 shadow-sm md:p-7">
            <form method="POST" action="{{ route('admin.variant-groups.update', $variantGroup) }}">
                @method('PUT')
                @include('admin.variant-groups._form')
            </form>
        </div>
    </div>
</x-pos-layout>
