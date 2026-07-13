<x-pos-layout active="products" title="Tambah Grup Varian" subtitle="Buat pilihan seperti suhu, ukuran, level gula, atau topping wajib." :back-url="route('admin.variant-groups.index')">

    <div class="mx-auto max-w-3xl">
        <div class="rounded-3xl border border-[#c6c5d2] bg-white p-5 shadow-sm md:p-7">
            <form method="POST" action="{{ route('admin.variant-groups.store') }}">
                @include('admin.variant-groups._form', ['variantGroup' => null])
            </form>
        </div>
    </div>
</x-pos-layout>
