<x-guest-layout>
    <div class="flex min-h-screen items-center justify-center bg-[#f6faff] px-4 py-10" style="font-family: 'Plus Jakarta Sans', sans-serif;">
        <div class="w-full max-w-2xl rounded-[2rem] border border-[#c6c5d2] bg-white p-8 text-center shadow-[0_24px_60px_rgba(27,43,107,0.12)]">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-3xl bg-[#d5e3fc] text-[#001356]">
                <span class="material-symbols-outlined text-4xl">hourglass_top</span>
            </div>
            <h1 class="text-3xl font-extrabold text-[#001356]">Akun menunggu aktivasi</h1>
            <p class="mx-auto mt-4 max-w-xl text-sm leading-7 text-[#454650]">
                Pendaftaran {{ auth()->user()?->tenant?->name ?? 'cafe Anda' }} sudah diterima. Super Admin akan memeriksa dan mengaktifkan akun sebelum Anda bisa menggunakan dashboard POS.
            </p>

            <div class="mt-8 rounded-2xl border border-[#c6c5d2] bg-[#f0f4fa] p-5 text-left">
                <p class="text-xs font-extrabold uppercase tracking-[0.18em] text-[#767681]">Informasi Pendaftaran</p>
                <div class="mt-4 grid gap-3 text-sm sm:grid-cols-2">
                    <div>
                        <p class="font-bold text-[#171c20]">Cafe</p>
                        <p class="text-[#454650]">{{ auth()->user()?->tenant?->name ?? '-' }}</p>
                    </div>
                    <div>
                        <p class="font-bold text-[#171c20]">Status</p>
                        <p class="font-bold text-[#b26a00]">Pending Approval</p>
                    </div>
                    <div>
                        <p class="font-bold text-[#171c20]">Admin</p>
                        <p class="text-[#454650]">{{ auth()->user()?->name }}</p>
                    </div>
                    <div>
                        <p class="font-bold text-[#171c20]">Email</p>
                        <p class="text-[#454650]">{{ auth()->user()?->email }}</p>
                    </div>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="mt-8">
                @csrf
                <button class="inline-flex min-h-12 items-center justify-center rounded-xl bg-[#001356] px-6 text-sm font-extrabold text-white">
                    Keluar
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>
