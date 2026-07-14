@php
    $formatRupiah = fn ($value) => 'Rp '.number_format($value, 0, ',', '.');
    $receipt = \App\Models\Setting::getValue('receipt', [
        'logo_path' => null,
        'cafe_name' => $tenant->name,
    ], $tenant->id);
    $receiptLogoPath = $receipt['logo_path'] ?? null;
    $receiptLogoUrl = $receiptLogoPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($receiptLogoPath)
        ? \Illuminate\Support\Facades\Storage::url($receiptLogoPath)
        : null;
    $banner = \App\Models\Setting::getValue('online_banner', [
        'image_path' => null,
        'title' => '',
        'subtitle' => '',
    ], $tenant->id);
    $bannerPath = $banner['image_path'] ?? null;
    $bannerImageUrl = $bannerPath && \Illuminate\Support\Facades\Storage::disk('public')->exists($bannerPath)
        ? \Illuminate\Support\Facades\Storage::url($bannerPath)
        : null;
@endphp

<x-online-layout :tenant="$tenant" title="Menu Online" active="menu">
    @if (session('status'))
        <div class="mb-4 rounded-xl border border-[#b9c7df] bg-white px-4 py-3 text-sm font-semibold text-[#001356] shadow-sm">{{ session('status') }}</div>
    @endif

    <form method="GET" action="{{ route('online-orders.catalog', $tenant) }}" class="flex items-center gap-2">
        @if (request('category'))
            <input type="hidden" name="category" value="{{ request('category') }}">
        @endif
        <label class="flex h-12 flex-1 items-center gap-3 rounded-xl border border-[#c6c5d2] bg-white px-4 shadow-sm">
            <span class="material-symbols-outlined text-[#767681]">search</span>
            <input name="search" value="{{ request('search') }}" placeholder="Cari produk..." class="w-full border-0 bg-transparent p-0 text-base text-[#171c20] placeholder:text-[#767681] focus:ring-0">
        </label>
        <button class="flex h-12 w-12 items-center justify-center rounded-xl bg-[#001356] text-white shadow-sm" aria-label="Cari">
            <span class="material-symbols-outlined">search</span>
        </button>
    </form>

    <div class="no-scrollbar mt-5 flex gap-3 overflow-x-auto pb-1">
        <a href="{{ route('online-orders.catalog', $tenant) }}" class="whitespace-nowrap rounded-full border px-5 py-2 text-sm font-bold shadow-sm {{ request('category') ? 'border-[#c6c5d2] bg-white text-[#454650]' : 'border-[#001356] bg-[#001356] text-white' }}">Semua</a>
        @foreach ($categories as $category)
            <a href="{{ route('online-orders.catalog', array_merge(['tenant' => $tenant, 'category' => $category->slug], request()->except(['page', 'category']))) }}" class="whitespace-nowrap rounded-full border px-5 py-2 text-sm font-bold shadow-sm {{ request('category') === $category->slug ? 'border-[#001356] bg-[#001356] text-white' : 'border-[#c6c5d2] bg-white text-[#454650]' }}">{{ $category->name }}</a>
        @endforeach
    </div>

    @if ($bannerImageUrl)
        <section class="mt-5 -mx-4 overflow-hidden bg-[#001356] shadow-[0_4px_12px_rgba(27,43,107,0.06)] sm:-mx-6 sm:rounded-[28px]">
            <div class="relative aspect-[16/7] w-full min-h-[150px] sm:aspect-[16/6] sm:min-h-[176px]">
                <img src="{{ $bannerImageUrl }}" alt="Selamat datang di {{ $tenant->name }}" class="h-full w-full object-cover object-center">
                <div class="absolute inset-0 bg-gradient-to-r from-[#001356]/85 via-[#001356]/50 to-transparent"></div>
                <div class="absolute inset-0 flex items-end p-4 sm:p-6">
                    <div class="max-w-[80%] text-white sm:max-w-[60%]">
                        <p class="text-[10px] font-bold uppercase tracking-[0.32em] text-white/80">{{ $receipt['cafe_name'] ?? $tenant->name }}</p>
                        @if (!empty($banner['title']))
                            <h2 class="mt-1 text-[22px] font-extrabold leading-[1.05] sm:text-[30px]">{{ $banner['title'] }}</h2>
                        @endif
                        @if (!empty($banner['subtitle']))
                            <p class="mt-2 text-sm leading-5 text-white/90 sm:text-base">{{ $banner['subtitle'] }}</p>
                        @endif
                    </div>
                </div>
            </div>
        </section>
    @else
        <section class="mt-5 rounded-[28px] border border-dashed border-[#c6c5d2] bg-white px-5 py-10 text-center shadow-[0_4px_12px_rgba(27,43,107,0.04)]">
            <span class="material-symbols-outlined text-5xl text-[#767681]">image</span>
            <h2 class="mt-3 text-lg font-extrabold text-[#171c20]">Banner belum diatur</h2>
            <p class="mt-1 text-sm text-[#454650]">Atur gambar banner di menu Pengaturan supaya tampil di halaman customer.</p>
        </section>
    @endif

    <section class="mt-5 grid grid-cols-2 gap-2 sm:gap-3">
        @forelse ($products as $product)
            <article class="overflow-hidden rounded-[20px] border border-[#dfe3e9] bg-white shadow-[0_4px_12px_rgba(27,43,107,0.05)]">
                <button
                    type="button"
                    onclick="openProductSheet({{ $product->id }})"
                    data-product-id="{{ $product->id }}"
                    class="block w-full text-left active:scale-[0.99] {{ $product->stock < 1 ? 'pointer-events-none opacity-60' : '' }}"
                >
                    <div class="relative aspect-[4/3] bg-[#e8ecf3]">
                        @if ($product->image_path)
                            <img src="{{ asset('storage/'.$product->image_path) }}" alt="{{ $product->name }}" class="h-full w-full object-cover object-center">
                        @else
                            <div class="flex h-full w-full items-center justify-center bg-[#dfe3e9] text-[#767681]">
                                <span class="material-symbols-outlined text-4xl">restaurant</span>
                            </div>
                        @endif
                        @if ($product->stock < 1)
                            <span class="absolute left-2 top-2 rounded-full bg-[#ba1a1a] px-2 py-1 text-[9px] font-bold uppercase tracking-wide text-white shadow-sm">LOW STOCK</span>
                        @elseif ($product->stock <= 5)
                            <span class="absolute left-2 top-2 rounded-full bg-[#fff3cd] px-2 py-1 text-[9px] font-bold uppercase tracking-wide text-[#7a4b00] shadow-sm">LOW STOCK</span>
                        @endif
                    </div>
                    <div class="space-y-1.5 p-3 sm:p-4">
                        <h2 class="line-clamp-2 min-h-[36px] text-[14px] font-extrabold leading-tight text-[#171c20] sm:text-lg">{{ $product->name }}</h2>
                        <p class="text-[17px] font-extrabold leading-none text-[#001356] sm:text-[20px]">{{ $formatRupiah($product->price) }}</p>
                        <p class="line-clamp-1 text-[12px] font-medium text-[#454650]">{{ $product->category->name }}</p>
                        @if (($product->variant_groups_count ?? 0) > 0 || ($product->addons_count ?? 0) > 0)
                            <div class="flex flex-wrap gap-1 pt-1">
                                @if (($product->variant_groups_count ?? 0) > 0)
                                    <span class="rounded-md bg-[#eef3ff] px-2 py-1 text-[10px] font-bold text-[#001356]">Varian</span>
                                @endif
                                @if (($product->addons_count ?? 0) > 0)
                                    <span class="rounded-md bg-[#e7fff2] px-2 py-1 text-[10px] font-bold text-[#005236]">Add-on</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </button>
            </article>
        @empty
            <div class="col-span-2 rounded-xl border border-dashed border-[#c6c5d2] bg-white p-10 text-center">
                <span class="material-symbols-outlined text-5xl text-[#767681]">search_off</span>
                <p class="mt-3 text-sm font-bold text-[#454650]">Produk tidak ditemukan.</p>
            </div>
        @endforelse
    </section>

    <a href="{{ route('online-orders.address', $tenant) }}" class="fixed bottom-20 right-4 z-40 flex items-center gap-3 rounded-full border-4 border-white bg-[#001356] px-4 py-3 text-white shadow-[0_12px_28px_rgba(1,19,86,0.32)] {{ $cart->isEmpty() ? 'pointer-events-none opacity-60' : '' }}">
        <span class="material-symbols-outlined" style="font-variation-settings: 'FILL' 1;">shopping_cart</span>
        <span class="text-left leading-tight">
            <span class="block text-[10px] font-bold uppercase tracking-widest text-white/80">Cart Total</span>
            <span class="block text-base font-extrabold sm:text-lg">{{ $formatRupiah($total) }}</span>
        </span>
    </a>

    <x-online-welcome-modal :tenant="$tenant" />

    <div id="product-sheet" class="fixed inset-0 z-[80] hidden items-end bg-[#171c20]/45" onclick="closeProductSheet()">
        <form id="product-sheet-form" method="POST" action="{{ route('online-orders.cart.store', $tenant) }}" class="max-h-[88dvh] w-full overflow-y-auto rounded-t-2xl bg-white shadow-2xl" onclick="event.stopPropagation()">
            @csrf
            <input type="hidden" name="product_id" value="">
            <div class="mx-auto mt-2 h-1.5 w-12 rounded-full bg-[#c6c5d2]"></div>
            <div class="p-4 sm:p-5">
                <div id="product-sheet-loading" class="space-y-5">
                    <div class="flex gap-4">
                        <div class="h-20 w-20 shrink-0 animate-pulse rounded-xl bg-[#dfe3e9] sm:h-24 sm:w-24"></div>
                        <div class="min-w-0 flex-1 space-y-3">
                            <div class="h-3 w-24 animate-pulse rounded-full bg-[#dfe3e9]"></div>
                            <div class="h-5 w-3/4 animate-pulse rounded-full bg-[#dfe3e9]"></div>
                            <div class="h-4 w-24 animate-pulse rounded-full bg-[#dfe3e9]"></div>
                        </div>
                    </div>
                    <div class="space-y-3">
                        <div class="h-10 animate-pulse rounded-xl bg-[#eef3ff]"></div>
                        <div class="h-10 animate-pulse rounded-xl bg-[#eef3ff]"></div>
                        <div class="h-10 animate-pulse rounded-xl bg-[#eef3ff]"></div>
                    </div>
                </div>

                <div id="product-sheet-content" class="hidden space-y-5">
                    <div class="flex gap-4">
                        <img id="product-sheet-image" src="" alt="" class="hidden h-20 w-20 shrink-0 rounded-xl object-cover object-center sm:h-24 sm:w-24">
                        <div id="product-sheet-image-placeholder" class="flex h-20 w-20 shrink-0 items-center justify-center rounded-xl bg-[#dfe3e9] text-[#767681] sm:h-24 sm:w-24">
                            <span class="material-symbols-outlined text-3xl">restaurant</span>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p id="product-sheet-category" class="text-[10px] font-bold uppercase tracking-[0.24em] text-[#767681]"></p>
                            <h3 id="product-sheet-name" class="mt-1 text-lg font-extrabold leading-tight text-[#001356] sm:text-xl"></h3>
                            <p id="product-sheet-price" class="mt-1 text-sm font-extrabold text-[#171c20] sm:text-base"></p>
                            <p id="product-sheet-stock" class="mt-1 text-xs text-[#454650]"></p>
                        </div>
                        <button type="button" onclick="closeProductSheet()" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-[#454650]">
                            <span class="material-symbols-outlined">close</span>
                        </button>
                    </div>

                    <div id="product-sheet-options" class="space-y-5"></div>

                    <div class="grid grid-cols-[112px_1fr] gap-3">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-[#454650]">Qty</label>
                            <input id="product-sheet-quantity" type="number" name="quantity" value="1" min="1" class="h-12 w-full rounded-xl border-[#c6c5d2] text-center text-base font-bold focus:border-[#001356] focus:ring-[#001356]">
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-[#454650]">Catatan</label>
                            <input type="text" name="note" maxlength="160" placeholder="Contoh: tanpa es" class="h-12 w-full rounded-xl border-[#c6c5d2] text-sm focus:border-[#001356] focus:ring-[#001356]">
                        </div>
                    </div>
                </div>
            </div>
            <div class="sticky bottom-0 flex gap-3 border-t border-[#dfe3e9] bg-white p-4">
                <button type="button" onclick="closeProductSheet()" class="flex-1 rounded-xl border border-[#c6c5d2] py-3 text-sm font-bold text-[#454650]">Batal</button>
                <button id="product-sheet-submit" class="flex-[2] rounded-xl bg-[#001356] py-3 text-sm font-extrabold text-white">Tambah</button>
            </div>
        </form>
    </div>

    <script>
        const productSheet = document.getElementById('product-sheet');
        const productSheetLoading = document.getElementById('product-sheet-loading');
        const productSheetContent = document.getElementById('product-sheet-content');
        const productSheetForm = document.getElementById('product-sheet-form');
        const productSheetOptions = document.getElementById('product-sheet-options');
        const productSheetImage = document.getElementById('product-sheet-image');
        const productSheetImagePlaceholder = document.getElementById('product-sheet-image-placeholder');
        const productSheetCategory = document.getElementById('product-sheet-category');
        const productSheetName = document.getElementById('product-sheet-name');
        const productSheetPrice = document.getElementById('product-sheet-price');
        const productSheetStock = document.getElementById('product-sheet-stock');
        const productSheetSubmit = document.getElementById('product-sheet-submit');
        const detailEndpointTemplate = '{{ route('online-orders.product.detail', [$tenant, '__PRODUCT__']) }}';
        const cartFormatRupiah = 'Rp ';

        function formatRupiah(value) {
            return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
        }

        function setSheetVisible(visible) {
            if (!productSheet) return;
            productSheet.classList.toggle('hidden', !visible);
            productSheet.classList.toggle('flex', visible);
            document.body.style.overflow = visible ? 'hidden' : '';
        }

        function renderLoadingState() {
            productSheetLoading.classList.remove('hidden');
            productSheetContent.classList.add('hidden');
            productSheetOptions.innerHTML = '';
            productSheetSubmit.disabled = true;
            productSheetSubmit.classList.add('opacity-60');
        }

        function renderEmptyOptions(message) {
            productSheetOptions.innerHTML = `<div class="rounded-xl border border-dashed border-[#c6c5d2] bg-[#f6faff] px-4 py-3 text-sm text-[#454650]">${message}</div>`;
        }

        function renderGroup(group, index) {
            const options = group.options.map((option) => `
                <label class="flex min-h-12 cursor-pointer items-center justify-between rounded-xl border border-[#c6c5d2] p-3 has-[:checked]:border-[#001356] has-[:checked]:bg-[#eef3ff]">
                    <span class="flex items-center gap-3">
                        <input type="${group.selection_type === 'multiple' ? 'checkbox' : 'radio'}" name="${group.selection_type === 'multiple' ? `variant_options[${group.id}][]` : `variant_options[${group.id}]`}" value="${option.id}" ${group.is_required && index === 0 && option === group.options[0] ? 'checked' : ''} class="h-5 w-5 border-[#c6c5d2] text-[#001356] focus:ring-[#001356]">
                        <span class="text-sm font-semibold text-[#171c20]">${option.name}</span>
                    </span>
                    <span class="text-xs font-bold text-[#454650]">+ ${formatRupiah(option.price_delta)}</span>
                </label>
            `).join('');

            return `
                <div>
                    <div class="mb-2 flex items-center justify-between gap-3">
                        <label class="text-sm font-extrabold text-[#171c20]">${group.name}</label>
                        <span class="text-xs font-bold text-[#767681]">${group.is_required ? 'Wajib pilih' : 'Opsional'}</span>
                    </div>
                    <div class="space-y-2">${options}</div>
                </div>
            `;
        }

        function renderAddon(addon) {
            return `
                <label class="flex min-h-12 cursor-pointer items-center justify-between rounded-xl border border-[#c6c5d2] p-3 has-[:checked]:border-[#001356] has-[:checked]:bg-[#e7fff2]">
                    <span class="flex items-center gap-3">
                        <input type="checkbox" name="addons[]" value="${addon.id}" class="h-5 w-5 rounded border-[#c6c5d2] text-[#001356] focus:ring-[#001356]">
                        <span class="text-sm font-semibold text-[#171c20]">${addon.name}</span>
                    </span>
                    <span class="text-xs font-bold text-[#454650]">+ ${formatRupiah(addon.price)}</span>
                </label>
            `;
        }

        async function openProductSheet(productId) {
            setSheetVisible(true);
            renderLoadingState();

            try {
                const endpoint = detailEndpointTemplate.replace('__PRODUCT__', productId);
                const response = await fetch(endpoint, {
                    headers: { 'Accept': 'application/json' },
                    credentials: 'same-origin',
                });

                if (!response.ok) throw new Error('Failed to load product detail');

                const product = await response.json();

                productSheetForm.querySelector('input[name="product_id"]').value = product.id;
                productSheetCategory.textContent = product.category?.name || '';
                productSheetName.textContent = product.name || '';
                productSheetPrice.textContent = formatRupiah(product.price);
                productSheetStock.textContent = `Stok ${product.stock}`;
                productSheetSubmit.disabled = product.stock < 1;
                productSheetSubmit.classList.toggle('opacity-60', product.stock < 1);

                if (product.image_url) {
                    productSheetImage.src = product.image_url;
                    productSheetImage.alt = product.name || '';
                    productSheetImage.classList.remove('hidden');
                    productSheetImagePlaceholder.classList.add('hidden');
                } else {
                    productSheetImage.removeAttribute('src');
                    productSheetImage.classList.add('hidden');
                    productSheetImagePlaceholder.classList.remove('hidden');
                }

                const groups = Array.isArray(product.variant_groups) ? product.variant_groups : [];
                const addons = Array.isArray(product.addons) ? product.addons : [];

                if (!groups.length && !addons.length) {
                    renderEmptyOptions('Produk ini tidak punya varian atau add-on.');
                } else {
                    productSheetOptions.innerHTML = [
                        ...groups.map(renderGroup),
                        addons.length ? `
                            <div>
                                <label class="mb-2 block text-sm font-extrabold text-[#171c20]">Add-on</label>
                                <div class="space-y-2">
                                    ${addons.map(renderAddon).join('')}
                                </div>
                            </div>
                        ` : ''
                    ].join('');
                }

                productSheetLoading.classList.add('hidden');
                productSheetContent.classList.remove('hidden');
            } catch (error) {
                renderEmptyOptions('Detail produk gagal dimuat. Coba lagi ya.');
                productSheetLoading.classList.add('hidden');
                productSheetContent.classList.remove('hidden');
            }
        }

        function closeProductSheet() {
            setSheetVisible(false);
        }

        productSheet?.addEventListener('click', (event) => {
            if (event.target === productSheet) closeProductSheet();
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') closeProductSheet();
        });
    </script>
</x-online-layout>
