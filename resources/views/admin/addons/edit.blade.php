<x-pos-layout active="products" title="Edit Add-on" subtitle="Perbarui topping atau opsi ekstra yang tampil di kasir." :back-url="route('admin.addons.index')">

    <div class="mx-auto max-w-2xl">
        <div class="rounded-3xl border border-[#c6c5d2] bg-white p-5 shadow-sm md:p-7">
            <form method="POST" action="{{ route('admin.addons.update', $addon) }}">
                @method('PUT')
                @include('admin.addons._form')
            </form>
        </div>
    </div>
</x-pos-layout>
