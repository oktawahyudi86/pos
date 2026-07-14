@foreach ($cart as $item)
    @php
        $cartProduct = $products->firstWhere('id', $item['product_id']);
        $selectedVariantIds = collect($item['variant_options'])->pluck('id');
        $selectedAddonIds = collect($item['addons'])->pluck('id');
    @endphp

    @if ($cartProduct)
        <div id="cart-edit-modal-{{ $item['key'] }}" class="hidden fixed inset-0 z-[110] items-end justify-center bg-[#171c20]/50 p-0 sm:items-center sm:p-4">
            <form method="POST" action="{{ route('cashier.cart.edit', $item['key']) }}" class="max-h-[92dvh] w-full max-w-2xl overflow-y-auto rounded-t-3xl bg-white shadow-[0_12px_24px_rgba(27,43,107,0.18)] sm:rounded-2xl" data-ajax-cart data-close-modal="cart-edit-modal-{{ $item['key'] }}">
                @csrf
                @method('PATCH')
                <input type="hidden" name="product_id" value="{{ $cartProduct->id }}">
                <div class="flex items-start justify-between border-b border-[#c6c5d2] bg-[#f0f4fa] p-4 sm:p-5 md:p-6">
                    <div class="min-w-0 flex-1">
                        <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-[#767681] sm:text-xs">Edit Keranjang</p>
                        <h3 class="mt-1 truncate text-base font-extrabold text-[#171c20] sm:text-lg">{{ $cartProduct->name }}</h3>
                        <p class="text-sm font-bold text-[#001356]">{{ $formatRupiah($cartProduct->price) }}</p>
                    </div>
                    <button type="button" onclick="closeProductModal('cart-edit-modal-{{ $item['key'] }}')" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-[#454650] hover:bg-[#dfe3e9] sm:h-12 sm:w-12">
                        <span class="material-symbols-outlined text-[24px] sm:text-[28px]">close</span>
                    </button>
                </div>

                <div class="space-y-4 p-4 sm:p-5 md:p-6">
                    @foreach ($cartProduct->variantGroups as $group)
                        <div>
                            <div class="mb-2 flex items-center justify-between">
                                <label class="text-sm font-extrabold text-[#171c20]">{{ $group->name }}</label>
                                <span class="text-[11px] font-bold text-[#767681] sm:text-xs">{{ $group->is_required ? 'Wajib pilih' : 'Opsional' }}</span>
                            </div>
                            <div class="grid gap-2 sm:gap-3 sm:grid-cols-2">
                                @foreach ($group->options->where('is_active', true) as $option)
                                    <label class="flex min-h-12 cursor-pointer items-center justify-between rounded-xl border border-[#c6c5d2] p-2.5 sm:p-3 sm:p-4 hover:border-[#001356] has-[:checked]:border-[#001356] has-[:checked]:bg-[#eef3ff]">
                                        <span class="flex items-center gap-2 sm:gap-3">
                                            <input type="radio" name="variant_options[{{ $group->id }}]" value="{{ $option->id }}" @checked($selectedVariantIds->contains($option->id)) class="h-4 w-4 border-[#c6c5d2] text-[#001356] focus:ring-[#001356] sm:h-5 sm:w-5">
                                            <span class="text-xs sm:text-sm font-semibold text-[#171c20]">{{ $option->name }}</span>
                                        </span>
                                        <span class="text-[11px] sm:text-xs font-bold text-[#454650]">+ {{ $formatRupiah($option->price_delta) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach

                    @if ($cartProduct->addons->isNotEmpty())
                        <div>
                            <label class="mb-2 block text-sm font-extrabold text-[#171c20]">Add-on & Topping</label>
                            <div class="grid gap-2 sm:gap-3 sm:grid-cols-2">
                                @foreach ($cartProduct->addons->where('is_active', true) as $addon)
                                    <label class="flex min-h-12 cursor-pointer items-center justify-between rounded-xl border border-[#c6c5d2] p-2.5 sm:p-3 sm:p-4 hover:border-[#001356] has-[:checked]:border-[#001356] has-[:checked]:bg-[#e7fff2]">
                                        <span class="flex items-center gap-2 sm:gap-3">
                                            <input type="checkbox" name="addons[]" value="{{ $addon->id }}" @checked($selectedAddonIds->contains($addon->id)) class="h-4 w-4 rounded border-[#c6c5d2] text-[#001356] focus:ring-[#001356] sm:h-5 sm:w-5">
                                            <span class="text-xs sm:text-sm font-semibold text-[#171c20]">{{ $addon->name }}</span>
                                        </span>
                                        <span class="text-[11px] sm:text-xs font-bold text-[#454650]">+ {{ $formatRupiah($addon->price) }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="grid gap-3 sm:gap-4 sm:grid-cols-[180px_1fr]">
                        <div>
                            <label class="mb-2 block text-sm font-bold text-[#454650]">Qty</label>
                            <div class="flex h-10 overflow-hidden rounded-xl border border-[#c6c5d2] bg-white sm:h-12">
                                <button type="button" onclick="changeModalQty('cart-qty-{{ $item['key'] }}', -1)" class="flex w-10 items-center justify-center text-[#001356] hover:bg-[#eef3ff] sm:w-12">
                                    <span class="material-symbols-outlined text-[20px]">remove</span>
                                </button>
                                <input id="cart-qty-{{ $item['key'] }}" type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" max="{{ $item['stock'] }}" class="w-full border-0 text-center text-sm font-bold text-[#171c20] focus:ring-0 sm:text-base">
                                <button type="button" onclick="changeModalQty('cart-qty-{{ $item['key'] }}', 1)" class="flex w-10 items-center justify-center bg-[#001356] text-white hover:brightness-110 sm:w-12">
                                    <span class="material-symbols-outlined text-[20px]">add</span>
                                </button>
                            </div>
                        </div>
                        <div>
                            <label class="mb-2 block text-sm font-bold text-[#454650]">Catatan</label>
                            <input type="text" name="note" value="{{ $item['note'] }}" maxlength="160" placeholder="Contoh: gula sedikit" class="h-10 w-full rounded-xl border-[#c6c5d2] text-sm focus:border-[#001356] focus:ring-[#001356] sm:h-12 sm:text-base">
                        </div>
                    </div>
                </div>

                <div class="sticky bottom-0 flex gap-2 sm:gap-3 border-t border-[#c6c5d2] bg-white p-4 sm:p-5 md:p-6">
                    <button type="button" onclick="closeProductModal('cart-edit-modal-{{ $item['key'] }}')" class="flex-1 rounded-xl border border-[#c6c5d2] py-3 text-xs font-bold text-[#454650] sm:py-4 sm:text-sm">Batal</button>
                    <button class="flex-[2] rounded-xl bg-[#001356] py-3 text-xs font-extrabold text-white shadow-md sm:py-4 sm:text-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    @endif
@endforeach
