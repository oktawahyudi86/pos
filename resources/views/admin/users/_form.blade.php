@csrf

<div class="grid gap-5">
    <div>
        <x-input-label for="name" value="Nama Kasir" />
        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name ?? '')" required autofocus />
        <x-input-error :messages="$errors->get('name')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Email Login" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email ?? '')" required />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>

    @if ($user->exists)
        <div>
            <x-input-label for="status" value="Status Kasir" />
            <select id="status" name="status" class="mt-1 block min-h-12 w-full rounded-xl border-gray-300 text-sm font-semibold text-[#171c20] shadow-sm focus:border-[#001356] focus:ring-[#001356]">
                <option value="active" @selected(old('status', $user->status) === 'active')>Aktif</option>
                <option value="suspended" @selected(old('status', $user->status) === 'suspended')>Nonaktif</option>
            </select>
            <x-input-error :messages="$errors->get('status')" class="mt-2" />
        </div>
    @endif

    <div class="grid gap-5 md:grid-cols-2">
        <div>
            <x-input-label for="password" :value="$user->exists ? 'Password Baru (Opsional)' : 'Password'" />
            <x-text-input id="password" name="password" type="password" class="mt-1 block w-full" :required="! $user->exists" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="password_confirmation" value="Konfirmasi Password" />
            <x-text-input id="password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" :required="! $user->exists" />
        </div>
    </div>
</div>

@include('admin.shared.form-actions', ['backRoute' => route('admin.users.index')])
