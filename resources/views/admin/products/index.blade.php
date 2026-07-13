<x-pos-layout active="products" title="Master Produk" subtitle="Kelola menu, stok, kategori, varian, dan add-on untuk layar kasir.">
    <x-slot name="actions">
        <a href="{{ route('admin.products.create') }}" class="inline-flex min-h-12 items-center justify-center gap-2 rounded-xl bg-[#001356] px-4 py-3 text-sm font-bold text-white shadow-sm transition hover:bg-[#1b2b6b]">
            <span class="material-symbols-outlined text-[20px]">add</span>
            <span class="hidden sm:inline">Tambah Produk</span>
        </a>
    </x-slot>

    <div>
            @if (session('status'))
                <div class="mb-5 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                    {{ session('status') }}
                </div>
            @endif

            <div class="mb-8 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex w-full overflow-x-auto rounded-xl border border-[#c6c5d2] bg-[#f0f4fa] p-1 md:w-auto">
                    <a href="{{ route('admin.products.index', request()->except('category')) }}" class="whitespace-nowrap rounded-lg px-5 py-2 text-sm font-semibold transition {{ request('category') ? 'text-[#454650] hover:bg-[#dfe3e9]' : 'bg-[#001356] text-white shadow-sm' }}">
                        Semua
                    </a>
                    @foreach ($categories as $category)
                        <a href="{{ route('admin.products.index', array_merge(request()->except('page'), ['category' => $category->slug])) }}" class="whitespace-nowrap rounded-lg px-5 py-2 text-sm font-semibold transition {{ request('category') === $category->slug ? 'bg-[#001356] text-white shadow-sm' : 'text-[#454650] hover:bg-[#dfe3e9]' }}">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>

                <form method="GET" action="{{ route('admin.products.index') }}" class="relative w-full md:w-56">
                    @if (request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                    <select name="status" onchange="this.form.submit()" class="w-full appearance-none rounded-xl border border-[#c6c5d2] bg-white px-4 py-3 pr-10 text-sm font-semibold text-[#171c20] shadow-sm focus:border-[#001356] focus:ring-[#001356]">
                        <option value="">Semua Status</option>
                        <option value="active" @selected(request('status') === 'active')>Tersedia</option>
                        <option value="empty" @selected(request('status') === 'empty')>Stok Habis</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Nonaktif</option>
                    </select>
                    <span class="material-symbols-outlined pointer-events-none absolute right-3 top-1/2 -translate-y-1/2 text-[#454650]">expand_more</span>
                </form>
            </div>

            <div class="grid grid-cols-2 gap-3 pb-8 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                @forelse ($products as $product)
                    <article class="group overflow-hidden rounded-2xl border border-[#c6c5d2] bg-white transition hover:border-[#001356]/20 hover:shadow-xl">
                        <div class="relative flex aspect-square items-center justify-center bg-[#e4e8ee]">
                            @if ($product->image_path)
                                <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-105">
                            @else
                                <span class="material-symbols-outlined text-5xl text-[#767681]">restaurant</span>
                            @endif
                            <span class="absolute right-3 top-3 rounded px-2 py-1 text-[10px] font-bold uppercase tracking-wider {{ $product->is_active && $product->stock > 0 ? 'bg-[#6ffbbe] text-[#002113]' : 'bg-[#ffdad6] text-[#93000a]' }}">
                                {{ $product->is_active && $product->stock > 0 ? 'Tersedia' : ($product->stock < 1 ? 'Habis' : 'Nonaktif') }}
                            </span>
                        </div>
                        <div class="p-3 sm:p-4">
                            <p class="mb-1 text-[10px] font-bold uppercase tracking-widest text-[#454650]">{{ $product->category->name }}</p>
                            <h3 class="mb-2 truncate text-sm font-bold text-[#171c20] sm:text-lg">{{ $product->name }}</h3>
                            <div class="flex flex-col gap-1 sm:flex-row sm:items-center sm:justify-between sm:gap-3">
                                <p class="text-base font-bold text-[#001356] sm:text-xl">Rp {{ number_format($product->price, 0, ',', '.') }}</p>
                                <span class="whitespace-nowrap text-xs text-[#454650] sm:text-sm">Stok: {{ $product->stock }}</span>
                            </div>
                            <div class="mt-3 space-y-1 text-[10px] text-[#454650] sm:text-xs">
                                @if ($product->variantGroups->isNotEmpty())
                                    <p><span class="font-semibold text-[#171c20]">Varian:</span> {{ $product->variantGroups->pluck('name')->join(', ') }}</p>
                                @endif
                                @if ($product->addons->isNotEmpty())
                                    <p><span class="font-semibold text-[#171c20]">Add-on:</span> {{ $product->addons->pluck('name')->join(', ') }}</p>
                                @endif
                            </div>
                            <div class="mt-4 flex flex-col gap-2 border-t border-[#dfe3e9] pt-4 sm:flex-row sm:items-center sm:justify-between">
                                <span class="text-[10px] font-semibold text-[#767681] sm:text-xs">{{ $product->sku }}</span>
                                <div class="flex items-center gap-3 text-xs font-semibold sm:text-sm">
                                    <a href="{{ route('admin.products.edit', $product) }}" class="text-[#001356] hover:underline">Edit</a>
                                    <form action="{{ route('admin.products.destroy', $product) }}" method="POST" onsubmit="return confirm('Hapus produk ini?')">
                                        @csrf
                                        @method('DELETE')
                                        <button class="text-[#ba1a1a] hover:underline">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </article>
                @empty
                    <div class="col-span-full rounded-2xl border border-dashed border-[#c6c5d2] bg-white p-12 text-center">
                        <span class="material-symbols-outlined mb-3 text-5xl text-[#767681]">inventory_2</span>
                        <h3 class="text-lg font-bold text-[#171c20]">Belum ada produk</h3>
                        <p class="mt-1 text-sm text-[#454650]">Tambahkan produk pertama agar kasir bisa mulai transaksi.</p>
                    </div>
                @endforelse
            </div>

            <div class="mb-8">
                {{ $products->withQueryString()->links() }}
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <section class="rounded-2xl border border-[#c6c5d2] bg-white p-5 text-center sm:p-8">
                    <span class="material-symbols-outlined mb-4 text-4xl text-[#767681]">category</span>
                    <h3 class="mb-2 text-lg font-bold text-[#171c20]">Manajemen Kategori</h3>
                    <p class="mb-6 text-sm text-[#454650]">Atur kategori produk untuk memudahkan pencarian di kasir.</p>
                    <a href="{{ route('admin.categories.index') }}" class="inline-flex rounded-full bg-[#001356] px-6 py-2 text-sm font-semibold text-white">Tambah Kategori</a>
                </section>
                <section class="rounded-2xl border border-[#c6c5d2] bg-white p-5 text-center sm:p-8">
                    <span class="material-symbols-outlined mb-4 text-4xl text-[#767681]">style</span>
                    <h3 class="mb-2 text-lg font-bold text-[#171c20]">Grup Varian</h3>
                    <p class="mb-6 text-sm text-[#454650]">Buat grup varian seperti Es/Panas atau ukuran yang dipilih kasir.</p>
                    <a href="{{ route('admin.variant-groups.index') }}" class="inline-flex rounded-full bg-[#001356] px-6 py-2 text-sm font-semibold text-white">Tambah Grup Varian</a>
                </section>
                <section class="rounded-2xl border border-[#c6c5d2] bg-white p-5 text-center sm:p-8">
                    <span class="material-symbols-outlined mb-4 text-4xl text-[#767681]">extension</span>
                    <h3 class="mb-2 text-lg font-bold text-[#171c20]">Add-on & Topping</h3>
                    <p class="mb-6 text-sm text-[#454650]">Tambahkan opsi ekstra seperti keju, bubble, atau topping lain.</p>
                    <a href="{{ route('admin.addons.index') }}" class="inline-flex rounded-full bg-[#001356] px-6 py-2 text-sm font-semibold text-white">Tambah Add-on</a>
                </section>
            </div>
    </div>
</x-pos-layout>
