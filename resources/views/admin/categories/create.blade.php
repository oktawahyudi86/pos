<x-pos-layout active="products" title="Tambah Kategori" subtitle="Buat kategori baru agar produk lebih mudah dicari kasir." :back-url="route('admin.categories.index')">

    <div class="mx-auto max-w-2xl">
        <div class="rounded-3xl border border-[#c6c5d2] bg-white p-5 shadow-sm md:p-7">
            <form method="POST" action="{{ route('admin.categories.store') }}">
                @include('admin.categories._form', ['category' => null])
            </form>
        </div>
    </div>
</x-pos-layout>
