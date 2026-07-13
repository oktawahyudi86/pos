<x-pos-layout active="products" title="Tambah Produk" subtitle="Lengkapi data menu, foto, varian, dan add-on yang akan tampil di kasir." :back-url="route('admin.products.index')">

    <div class="mx-auto max-w-4xl">
        <div class="rounded-3xl border border-[#c6c5d2] bg-white p-5 shadow-sm md:p-7">
            <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
                @include('admin.products._form', ['product' => null])
            </form>
        </div>
    </div>
</x-pos-layout>
