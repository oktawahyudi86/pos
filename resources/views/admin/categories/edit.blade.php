<x-pos-layout active="products" title="Edit Kategori" subtitle="Perbarui kategori produk yang tampil di master menu." :back-url="route('admin.categories.index')">

    <div class="mx-auto max-w-2xl">
        <div class="rounded-3xl border border-[#c6c5d2] bg-white p-5 shadow-sm md:p-7">
            <form method="POST" action="{{ route('admin.categories.update', $category) }}">
                @method('PUT')
                @include('admin.categories._form')
            </form>
        </div>
    </div>
</x-pos-layout>
