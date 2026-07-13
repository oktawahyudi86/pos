<x-pos-layout active="products" title="Tambah Add-on" subtitle="Tambahkan topping atau opsi ekstra yang dapat dipilih kasir." :back-url="route('admin.addons.index')">

    <div class="mx-auto max-w-2xl">
        <div class="rounded-3xl border border-[#c6c5d2] bg-white p-5 shadow-sm md:p-7">
            <form method="POST" action="{{ route('admin.addons.store') }}">
                @include('admin.addons._form', ['addon' => null])
            </form>
        </div>
    </div>
</x-pos-layout>
