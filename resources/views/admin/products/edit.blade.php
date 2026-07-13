<x-pos-layout active="products" title="Edit Produk" subtitle="Perbarui data menu, foto, stok, varian, dan add-on." :back-url="route('admin.products.index')">

    <div class="mx-auto max-w-4xl">
        <div class="rounded-3xl border border-[#c6c5d2] bg-white p-5 shadow-sm md:p-7">
            <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
                @method('PUT')
                @include('admin.products._form')
            </form>
        </div>
    </div>
</x-pos-layout>
