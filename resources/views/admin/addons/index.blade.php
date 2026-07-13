<x-pos-layout active="products" title="Add-on & Topping" subtitle="Tambahkan opsi ekstra seperti keju, bubble, atau topping lain." :back-url="route('admin.products.index')">
    <x-slot name="actions">
        <a href="{{ route('admin.addons.create') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#001356] px-4 py-3 text-sm font-bold text-white transition hover:bg-[#1b2b6b]">
            <span class="material-symbols-outlined text-[20px]">add</span>
            <span class="hidden sm:inline">Tambah Add-on</span>
        </a>
    </x-slot>

    @include('admin.shared.resource-list', [
        'items' => $addons,
        'titleField' => 'name',
        'subtitleCallback' => fn ($addon) => 'Topping tambahan',
        'priceCallback' => fn ($addon) => 'Rp '.number_format($addon->price, 0, ',', '.'),
        'editRoute' => 'admin.addons.edit',
        'destroyRoute' => 'admin.addons.destroy',
        'emptyIcon' => 'extension',
        'emptyTitle' => 'Belum ada add-on',
        'emptyText' => 'Tambahkan add-on pertama untuk pilihan ekstra di kasir.',
    ])
</x-pos-layout>
