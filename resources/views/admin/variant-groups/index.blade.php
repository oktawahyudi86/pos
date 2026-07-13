<x-pos-layout active="products" title="Grup Varian" subtitle="Buat grup varian seperti Es/Panas atau ukuran yang dipilih kasir." :back-url="route('admin.products.index')">
    <x-slot name="actions">
        <a href="{{ route('admin.variant-groups.create') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#001356] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#1b2b6b]">
            <span class="material-symbols-outlined text-[20px]">add</span>
            <span class="hidden sm:inline">Tambah Grup Varian</span>
        </a>
    </x-slot>

    @include('admin.shared.resource-list', [
        'items' => $variantGroups,
        'titleField' => 'name',
        'subtitleCallback' => fn ($group) => $group->options_count.' opsi'.($group->is_required ? ' | Wajib dipilih' : ' | Opsional'),
        'priceCallback' => fn ($group) => $group->selection_type === 'single' ? 'Pilihan tunggal' : 'Bisa banyak pilihan',
        'editRoute' => 'admin.variant-groups.edit',
        'destroyRoute' => 'admin.variant-groups.destroy',
        'emptyIcon' => 'style',
        'emptyTitle' => 'Belum ada grup varian',
        'emptyText' => 'Tambahkan grup seperti ukuran, level gula, atau pilihan panas/dingin.',
    ])
</x-pos-layout>
