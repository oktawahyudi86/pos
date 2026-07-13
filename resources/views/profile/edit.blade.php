@php
    $user = auth()->user();
    $profileBackUrl = $user->hasRole('Super Admin')
        ? route('super-admin.dashboard')
        : ($user->hasRole('Admin')
            ? route('dashboard')
            : route('cashier.index'));
    $profileActive = $user->hasRole('Super Admin')
        ? 'dashboard'
        : ($user->hasRole('Admin') ? 'dashboard' : 'cashier');
@endphp

<x-pos-layout active="{{ $profileActive }}" title="Profil" subtitle="Kelola informasi akun dan keamanan login Anda." :back-url="$profileBackUrl">
    <div class="mx-auto max-w-3xl space-y-6">
        <div class="rounded-2xl border border-[#c6c5d2] bg-white p-5 shadow-sm sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>

        <div class="rounded-2xl border border-[#c6c5d2] bg-white p-5 shadow-sm sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.update-password-form')
            </div>
        </div>

        <div class="rounded-2xl border border-[#c6c5d2] bg-white p-5 shadow-sm sm:p-8">
            <div class="max-w-xl">
                @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
</x-pos-layout>
