<x-pos-layout active="users" title="Edit Kasir" subtitle="Ubah data login, status, atau reset password kasir.">
    <section class="mx-auto max-w-3xl rounded-2xl border border-[#c6c5d2] bg-white p-6 shadow-sm">
        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-6">
            @method('PUT')
            @include('admin.users._form')
        </form>
    </section>
</x-pos-layout>
