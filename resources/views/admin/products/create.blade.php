<x-pos-layout active="products" title="Tambah Produk" subtitle="Lengkapi data menu, foto, varian, dan add-on yang akan tampil di kasir.">
    <x-slot name="actions">
        <a href="{{ route('admin.products.index') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl border border-[#c6c5d2] bg-white px-4 py-3 text-sm font-bold text-[#001356] transition hover:bg-[#eef3fb]">
            <span class="material-symbols-outlined text-[20px]">arrow_back</span>
            <span class="hidden sm:inline">Kembali</span>
        </a>
    </x-slot>

    <div class="mx-auto max-w-4xl">
        <div class="rounded-3xl border border-[#c6c5d2] bg-white p-5 shadow-sm md:p-7">
            <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                @include('admin.products._form', ['product' => null])
            </form>
        </div>
    </div>
</x-pos-layout>
