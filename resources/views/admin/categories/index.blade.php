<x-pos-layout active="products" title="Manajemen Kategori" subtitle="Atur kategori produk untuk memudahkan pencarian di kasir." :back-url="route('admin.products.index')">
    <x-slot name="actions">
        <a href="{{ route('admin.categories.create') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#001356] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#1b2b6b]">
            <span class="material-symbols-outlined text-[20px]">add</span>
            <span class="hidden sm:inline">Tambah Kategori</span>
        </a>
    </x-slot>

    @include('admin.shared.resource-list', [
        'items' => $categories,
        'titleField' => 'name',
        'subtitleCallback' => fn ($category) => $category->products_count.' produk',
        'priceCallback' => null,
        'editRoute' => 'admin.categories.edit',
        'destroyRoute' => 'admin.categories.destroy',
        'emptyIcon' => 'category',
        'emptyTitle' => 'Belum ada kategori',
        'emptyText' => 'Tambahkan kategori pertama untuk mengelompokkan produk.',
    ])
</x-pos-layout>
