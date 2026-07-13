@csrf

@php
    $selectedVariantGroups = collect(old('variant_group_ids', isset($product) && $product ? $product->variantGroups->pluck('id')->all() : []))->map(fn ($id) => (int) $id)->all();
    $selectedAddons = collect(old('addon_ids', isset($product) && $product ? $product->addons->pluck('id')->all() : []))->map(fn ($id) => (int) $id)->all();
@endphp

<div class="grid gap-6 md:grid-cols-2">
    <div>
        <x-input-label for="category_id" value="Kategori" />
        <select id="category_id" name="category_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#001356] focus:ring-[#001356]">
            <option value="">Pilih kategori</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id ?? '') == $category->id)>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
        <x-input-error :messages="$errors->get('category_id')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="sku" value="SKU" />
        <x-text-input id="sku" name="sku" type="text" class="mt-1 block w-full" :value="old('sku', $product->sku ?? '')" required />
        <x-input-error :messages="$errors->get('sku')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="name" value="Nama Produk" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $product->name ?? '')" required />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="price" value="Harga Jual" />
        <x-text-input id="price" name="price" type="number" min="0" class="mt-1 block w-full" :value="old('price', $product->price ?? 0)" required />
        <x-input-error :messages="$errors->get('price')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="stock" value="Stok" />
        <x-text-input id="stock" name="stock" type="number" min="0" class="mt-1 block w-full" :value="old('stock', $product->stock ?? 0)" required />
        <x-input-error :messages="$errors->get('stock')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <x-input-label for="image" value="Foto Menu Produk" />
        <input id="image" name="image" type="file" accept="image/*" class="mt-1 block w-full rounded-md border border-gray-300 bg-white px-3 py-2 text-sm shadow-sm file:mr-4 file:rounded-md file:border-0 file:bg-[#001356] file:px-4 file:py-2 file:text-sm file:font-semibold file:text-white focus:border-[#001356] focus:ring-[#001356]">
        <p class="mt-1 text-xs text-gray-500">Format gambar umum, maksimal 2 MB.</p>
        <x-input-error :messages="$errors->get('image')" class="mt-2" />

        @if (! empty($product?->image_path))
            <div class="mt-4 flex items-center gap-4 rounded-lg border border-[#c6c5d2] bg-[#f8f9ff] p-3">
                <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}" class="h-20 w-20 rounded-lg object-cover">
                <div>
                    <p class="text-sm font-semibold text-gray-800">Foto saat ini</p>
                    <p class="text-xs text-gray-500">Upload gambar baru untuk mengganti foto ini.</p>
                </div>
            </div>
        @endif
    </div>

    <div class="md:col-span-2">
        <x-input-label for="description" value="Deskripsi" />
        <textarea id="description" name="description" rows="4" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#001356] focus:ring-[#001356]">{{ old('description', $product->description ?? '') }}</textarea>
        <x-input-error :messages="$errors->get('description')" class="mt-2" />
    </div>

    <div class="md:col-span-2">
        <div class="rounded-2xl border border-[#c6c5d2] bg-[#f8f9ff] p-5">
            <div class="mb-4 flex items-start justify-between gap-4">
                <div>
                    <h3 class="font-bold text-[#171c20]">Varian Produk</h3>
                    <p class="mt-1 text-sm text-[#454650]">Pilih grup varian yang berlaku untuk produk ini.</p>
                </div>
                <a href="{{ route('admin.variant-groups.create') }}" class="text-sm font-semibold text-[#001356] hover:underline">Tambah grup</a>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                @forelse ($variantGroups as $variantGroup)
                    <label class="rounded-xl border border-[#dfe3e9] bg-white p-4">
                        <div class="flex items-start gap-3">
                            <input type="checkbox" name="variant_group_ids[]" value="{{ $variantGroup->id }}" class="mt-1 rounded border-gray-300 text-[#001356] focus:ring-[#001356]" @checked(in_array($variantGroup->id, $selectedVariantGroups, true))>
                            <div>
                                <p class="font-semibold text-[#171c20]">{{ $variantGroup->name }}</p>
                                <p class="text-xs text-[#454650]">
                                    {{ $variantGroup->is_required ? 'Wajib dipilih' : 'Opsional' }} |
                                    {{ $variantGroup->selection_type === 'single' ? 'Pilihan tunggal' : 'Bisa banyak pilihan' }}
                                </p>
                                <p class="mt-2 text-xs text-[#767681]">
                                    Opsi:
                                    {{ $variantGroup->options->map(fn ($option) => $option->name.($option->price_delta > 0 ? ' +Rp '.number_format($option->price_delta, 0, ',', '.') : ''))->join(', ') }}
                                </p>
                            </div>
                        </div>
                    </label>
                @empty
                    <div class="md:col-span-2 rounded-xl border border-dashed border-[#c6c5d2] bg-white p-5 text-sm text-[#454650]">
                        Belum ada grup varian. Buat varian seperti Es/Panas, ukuran, atau level gula.
                    </div>
                @endforelse
            </div>
            <x-input-error :messages="$errors->get('variant_group_ids')" class="mt-2" />
        </div>
    </div>

    <div class="md:col-span-2">
        <div class="rounded-2xl border border-[#c6c5d2] bg-[#f8f9ff] p-5">
            <div class="mb-4 flex items-start justify-between gap-4">
                <div>
                    <h3 class="font-bold text-[#171c20]">Add-on & Topping</h3>
                    <p class="mt-1 text-sm text-[#454650]">Pilih topping atau ekstra yang dapat ditambahkan saat transaksi.</p>
                </div>
                <a href="{{ route('admin.addons.create') }}" class="text-sm font-semibold text-[#001356] hover:underline">Tambah add-on</a>
            </div>

            <div class="grid gap-3 md:grid-cols-2">
                @forelse ($addons as $addon)
                    <label class="flex items-center justify-between gap-3 rounded-xl border border-[#dfe3e9] bg-white p-4">
                        <span class="flex items-center gap-3">
                            <input type="checkbox" name="addon_ids[]" value="{{ $addon->id }}" class="rounded border-gray-300 text-[#001356] focus:ring-[#001356]" @checked(in_array($addon->id, $selectedAddons, true))>
                            <span class="font-semibold text-[#171c20]">{{ $addon->name }}</span>
                        </span>
                        <span class="text-sm font-bold text-[#001356]">Rp {{ number_format($addon->price, 0, ',', '.') }}</span>
                    </label>
                @empty
                    <div class="md:col-span-2 rounded-xl border border-dashed border-[#c6c5d2] bg-white p-5 text-sm text-[#454650]">
                        Belum ada add-on. Buat topping seperti keju, bubble, atau extra shot.
                    </div>
                @endforelse
            </div>
            <x-input-error :messages="$errors->get('addon_ids')" class="mt-2" />
        </div>
    </div>

    <label class="flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-[#001356] shadow-sm focus:ring-[#001356]" @checked(old('is_active', $product->is_active ?? true))>
        <span class="text-sm text-gray-700">Produk aktif dan tampil di kasir</span>
    </label>
</div>

<div class="mt-8 flex items-center justify-end gap-3">
    <a href="{{ route('admin.products.index') }}" class="rounded-md border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-gray-50">
        Batal
    </a>
    <button class="rounded-md bg-[#001356] px-4 py-2 text-sm font-semibold text-white hover:bg-[#1b2b6b]">
        Simpan
    </button>
</div>
