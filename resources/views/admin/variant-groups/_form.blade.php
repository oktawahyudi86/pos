@csrf

@php
    $existingOptions = old('options');
    if (! $existingOptions) {
        $existingOptions = isset($variantGroup) && $variantGroup
            ? $variantGroup->options->map(fn ($option) => [
                'name' => $option->name,
                'price_delta' => $option->price_delta,
                'is_active' => $option->is_active,
            ])->toArray()
            : [
                ['name' => '', 'price_delta' => 0, 'is_active' => true],
                ['name' => '', 'price_delta' => 0, 'is_active' => true],
            ];
    }
@endphp

<div class="space-y-6">
    <div>
        <x-input-label for="name" value="Nama Grup Varian" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $variantGroup->name ?? '')" placeholder="Contoh: Ukuran, Suhu, Level Gula" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div class="grid gap-6 md:grid-cols-2">
        <div>
            <x-input-label for="selection_type" value="Tipe Pilihan" />
            <select id="selection_type" name="selection_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-[#001356] focus:ring-[#001356]">
                <option value="single" @selected(old('selection_type', $variantGroup->selection_type ?? 'single') === 'single')>Pilihan tunggal</option>
                <option value="multiple" @selected(old('selection_type', $variantGroup->selection_type ?? '') === 'multiple')>Bisa banyak pilihan</option>
            </select>
            <x-input-error :messages="$errors->get('selection_type')" class="mt-2" />
        </div>

        <div class="flex items-end gap-6">
            <label class="flex items-center gap-2 pb-2">
                <input type="checkbox" name="is_required" value="1" class="rounded border-gray-300 text-[#001356] shadow-sm focus:ring-[#001356]" @checked(old('is_required', $variantGroup->is_required ?? false))>
                <span class="text-sm text-gray-700">Wajib dipilih</span>
            </label>
            <label class="flex items-center gap-2 pb-2">
                <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-[#001356] shadow-sm focus:ring-[#001356]" @checked(old('is_active', $variantGroup->is_active ?? true))>
                <span class="text-sm text-gray-700">Aktif</span>
            </label>
        </div>
    </div>

    <div>
        <div class="mb-3 flex items-center justify-between">
            <x-input-label value="Opsi Varian" />
            <button type="button" onclick="addVariantOption()" class="rounded-full bg-[#001356] px-4 py-2 text-xs font-semibold text-white">Tambah Opsi</button>
        </div>
        <div id="variant-options" class="space-y-3">
            @foreach ($existingOptions as $index => $option)
                <div class="grid gap-3 rounded-lg border border-[#c6c5d2] bg-[#f8f9ff] p-3 md:grid-cols-[1fr_160px_auto]">
                    <input name="options[{{ $index }}][name]" type="text" value="{{ $option['name'] ?? '' }}" placeholder="Nama opsi" class="rounded-md border-gray-300 shadow-sm focus:border-[#001356] focus:ring-[#001356]" required>
                    <input name="options[{{ $index }}][price_delta]" type="number" min="0" value="{{ $option['price_delta'] ?? 0 }}" placeholder="Tambahan harga" class="rounded-md border-gray-300 shadow-sm focus:border-[#001356] focus:ring-[#001356]">
                    <label class="flex items-center gap-2 whitespace-nowrap text-sm text-gray-700">
                        <input type="checkbox" name="options[{{ $index }}][is_active]" value="1" class="rounded border-gray-300 text-[#001356] shadow-sm focus:ring-[#001356]" @checked($option['is_active'] ?? true)>
                        Aktif
                    </label>
                </div>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('options')" class="mt-2" />
        <x-input-error :messages="$errors->get('options.*.name')" class="mt-2" />
    </div>
</div>

@include('admin.shared.form-actions', ['backRoute' => route('admin.variant-groups.index')])

<script>
    function addVariantOption() {
        const wrapper = document.getElementById('variant-options');
        const index = wrapper.children.length;
        const row = document.createElement('div');
        row.className = 'grid gap-3 rounded-lg border border-[#c6c5d2] bg-[#f8f9ff] p-3 md:grid-cols-[1fr_160px_auto]';
        row.innerHTML = `
            <input name="options[${index}][name]" type="text" placeholder="Nama opsi" class="rounded-md border-gray-300 shadow-sm focus:border-[#001356] focus:ring-[#001356]" required>
            <input name="options[${index}][price_delta]" type="number" min="0" value="0" placeholder="Tambahan harga" class="rounded-md border-gray-300 shadow-sm focus:border-[#001356] focus:ring-[#001356]">
            <label class="flex items-center gap-2 whitespace-nowrap text-sm text-gray-700">
                <input type="checkbox" name="options[${index}][is_active]" value="1" class="rounded border-gray-300 text-[#001356] shadow-sm focus:ring-[#001356]" checked>
                Aktif
            </label>
        `;
        wrapper.appendChild(row);
    }
</script>
