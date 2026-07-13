@csrf

<div class="space-y-6">
    <div>
        <x-input-label for="name" value="Nama Kategori" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $category->name ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <label class="flex items-center gap-2">
        <input type="checkbox" name="is_active" value="1" class="rounded border-gray-300 text-[#001356] shadow-sm focus:ring-[#001356]" @checked(old('is_active', $category->is_active ?? true))>
        <span class="text-sm text-gray-700">Kategori aktif</span>
    </label>
</div>

@include('admin.shared.form-actions', ['backRoute' => route('admin.categories.index')])
