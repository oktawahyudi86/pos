<x-pos-layout active="users" title="Tambah Kasir" subtitle="Buat akun kasir baru untuk tenant cafe ini." :back-url="route('admin.users.index')">
    <section class="mx-auto max-w-3xl rounded-2xl border border-[#c6c5d2] bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-6">
            @include('admin.users._form')
        </form>
    </section>
</x-pos-layout>
