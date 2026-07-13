<x-pos-layout active="products" title="Edit Grup Varian" subtitle="Perbarui pilihan varian yang dapat dipilih saat transaksi." :back-url="route('admin.variant-groups.index')">

    <div class="mx-auto max-w-3xl">
        <div class="rounded-3xl border border-[#c6c5d2] bg-white p-5 shadow-sm md:p-7">
            <form method="POST" action="{{ route('admin.variant-groups.update', $variantGroup) }}">
                @method('PUT')
                @include('admin.variant-groups._form')
            </form>
        </div>
    </div>
</x-pos-layout>
