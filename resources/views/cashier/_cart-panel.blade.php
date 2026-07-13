<div class="flex items-center justify-between border-b border-[#c6c5d2] bg-[#f0f4fa] px-4 py-3 sm:px-5 sm:py-4">
    <div>
        <h3 class="text-base font-extrabold text-[#171c20] sm:text-lg">Keranjang</h3>
        <p class="text-xs text-[#454650] sm:text-sm">{{ $cart->count() }} item transaksi</p>
    </div>
    <div class="flex items-center gap-2">
        @isset($showCloseButton)
            @if ($showCloseButton)
                <button type="button" onclick="closeCartDrawer()" class="flex h-11 w-11 items-center justify-center rounded-full text-[#454650] hover:bg-[#dfe3e9]" aria-label="Tutup keranjang">
                    <span class="material-symbols-outlined">close</span>
                </button>
            @endif
        @endisset
        <form method="POST" action="{{ route('cashier.cart.clear') }}" data-ajax-cart>
            @csrf
            @method('DELETE')
            <button class="flex h-11 w-11 items-center justify-center rounded-full text-[#ba1a1a] hover:bg-[#ffdad6]" @disabled($cart->isEmpty()) aria-label="Kosongkan keranjang">
                <span class="material-symbols-outlined">delete_sweep</span>
            </button>
        </form>
    </div>
</div>

<div class="custom-scrollbar min-h-0 flex-1 overflow-y-auto px-4 py-3 sm:px-5">
    @forelse ($cart as $item)
        <div class="grid grid-cols-[minmax(0,1fr)_auto] gap-3 border-b border-[#dfe3e9] py-3 last:border-0 xl:grid-cols-[minmax(0,1fr)_auto_auto] xl:items-center">
            <div class="min-w-0">
                <div class="flex items-center gap-1.5">
                    <p class="truncate text-sm font-bold text-[#171c20] sm:text-base">{{ $item['name'] }}</p>
                    <button type="button" onclick="openProductModal('cart-edit-modal-{{ $item['key'] }}')" class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full text-[#001356] hover:bg-[#eef3ff] sm:h-11 sm:w-11" aria-label="Edit {{ $item['name'] }}">
                        <span class="material-symbols-outlined text-[20px]">edit_square</span>
                    </button>
                </div>
                <p class="text-[11px] font-semibold text-[#454650] sm:text-xs">{{ $formatRupiah($item['unit_price']) }}</p>
                @if (count($item['variant_options']) || count($item['addons']))
                    <p class="mt-0.5 line-clamp-2 text-[10px] leading-4 text-[#454650] sm:text-[11px]">
                        {{ collect($item['variant_options'])->map(fn ($option) => $option['group'].': '.$option['name'])->merge(collect($item['addons'])->pluck('name'))->join(', ') }}
                    </p>
                @endif
                @if ($item['note'])
                    <p class="mt-0.5 line-clamp-1 text-[10px] italic text-[#767681] sm:text-[11px]">Catatan: {{ $item['note'] }}</p>
                @endif
            </div>

            <div class="flex items-center gap-1 rounded-full border border-[#c6c5d2] bg-[#eaeef4] p-1">
                <form method="POST" action="{{ route('cashier.cart.update', $item['key']) }}" data-ajax-cart>
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="action" value="decrement">
                    <button class="flex h-9 w-9 items-center justify-center rounded-full text-[#001356] hover:bg-white active:scale-95 sm:h-10 sm:w-10"><span class="material-symbols-outlined text-[20px]">remove</span></button>
                </form>
                <span class="w-6 text-center text-sm font-bold sm:w-7">{{ $item['quantity'] }}</span>
                <form method="POST" action="{{ route('cashier.cart.update', $item['key']) }}" data-ajax-cart>
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="action" value="increment">
                    <button class="flex h-9 w-9 items-center justify-center rounded-full bg-[#001356] text-white active:scale-95 sm:h-10 sm:w-10"><span class="material-symbols-outlined text-[20px]">add</span></button>
                </form>
            </div>

            <div class="col-span-2 flex items-center justify-between xl:col-span-1 xl:block xl:w-[86px] xl:text-right">
                <p class="text-sm font-bold text-[#171c20] sm:text-base">{{ $formatRupiah($item['line_total']) }}</p>
                <form method="POST" action="{{ route('cashier.cart.destroy', $item['key']) }}" class="xl:mt-1" data-ajax-cart>
                    @csrf
                    @method('DELETE')
                    <button class="flex h-9 items-center rounded-lg px-2 text-xs font-bold text-[#ba1a1a] hover:bg-[#ffdad6]">Hapus</button>
                </form>
            </div>
        </div>
    @empty
        <div class="flex h-full min-h-56 items-center justify-center text-center">
            <div>
                <span class="material-symbols-outlined mb-4 text-5xl text-[#767681] sm:text-6xl">shopping_basket</span>
                <h4 class="text-base font-bold text-[#171c20] sm:text-lg">Keranjang kosong</h4>
                <p class="mt-2 max-w-sm text-sm leading-6 text-[#454650]">Klik produk untuk memilih varian, add-on, dan memasukkannya ke transaksi.</p>
            </div>
        </div>
    @endforelse
</div>

<div class="shrink-0 border-t border-[#c6c5d2] bg-[#f0f4fa] p-4">
    <div class="mb-4 space-y-2">
        <div class="flex justify-between text-[#454650]"><span class="text-sm">Subtotal</span><span class="text-sm font-bold">{{ $formatRupiah($cartSubtotal) }}</span></div>
        <div class="flex justify-between text-[#454650]"><span class="text-sm">Pajak (0%)</span><span class="text-sm font-bold">Rp 0</span></div>
        <form method="POST" action="{{ route('cashier.discount.update') }}" class="flex flex-wrap items-center gap-2 rounded-xl border border-[#c6c5d2] bg-white px-3 py-2" data-ajax-cart>
            @csrf
            @method('PATCH')
            <div class="min-w-[92px]">
                <label class="block text-sm font-bold text-[#171c20]">Diskon</label>
                <span class="block text-[11px] font-bold {{ $cartDiscount['amount'] > 0 ? 'text-[#ba1a1a]' : 'text-[#767681]' }}">
                    {{ $cartDiscount['amount'] > 0 ? '- '.$formatRupiah($cartDiscount['amount']) : 'Opsional' }}
                </span>
            </div>
            <select name="discount_type" class="h-10 w-16 rounded-lg border-[#c6c5d2] py-1 pl-2 pr-7 text-xs font-bold text-[#171c20] focus:border-[#001356] focus:ring-[#001356]" @disabled($cart->isEmpty())>
                <option value="nominal" @selected($cartDiscount['type'] === 'nominal')>Rp</option>
                <option value="percent" @selected($cartDiscount['type'] === 'percent')>%</option>
            </select>
            <input name="discount_value" value="{{ $cartDiscount['value'] > 0 ? rtrim(rtrim(number_format($cartDiscount['value'], 2, '.', ''), '0'), '.') : '' }}" inputmode="decimal" placeholder="0" class="h-10 min-w-[90px] flex-1 rounded-lg border-[#c6c5d2] py-1 text-sm font-bold text-[#171c20] focus:border-[#001356] focus:ring-[#001356]" @disabled($cart->isEmpty())>
            <button class="h-10 rounded-lg bg-[#001356] px-4 text-xs font-extrabold text-white disabled:bg-[#c6c5d2]" @disabled($cart->isEmpty())>OK</button>
            @if ($cartDiscount['amount'] > 0)
                <button name="discount_value" value="0" class="flex h-10 w-10 items-center justify-center rounded-lg text-[#ba1a1a] hover:bg-[#ffdad6]" title="Hapus diskon">
                    <span class="material-symbols-outlined text-[18px]">close</span>
                </button>
            @endif
        </form>
        <form method="POST" action="{{ route('cashier.payment-method.update') }}" class="space-y-1.5" data-ajax-cart>
            @csrf
            @method('PATCH')
            <p class="text-xs font-bold text-[#454650]">Metode Pembayaran</p>
            <div class="grid gap-2 {{ count($paymentMethods) > 1 ? 'grid-cols-2' : 'grid-cols-1' }}">
                @if (in_array('cash', $paymentMethods, true))
                    <button name="payment_method" value="cash" class="flex h-12 items-center justify-center gap-2 rounded-xl border-2 text-sm font-bold transition active:scale-[0.98] {{ $paymentMethod === 'cash' ? 'border-[#001356] bg-[#d5e3fc] text-[#001356]' : 'border-[#c6c5d2] bg-white text-[#454650] hover:border-[#001356]' }}" @disabled($cart->isEmpty())>
                        <span class="material-symbols-outlined text-[19px]">payments</span>
                        Tunai
                    </button>
                @endif
                @if (in_array('qris', $paymentMethods, true))
                    <button name="payment_method" value="qris" class="flex h-12 items-center justify-center gap-2 rounded-xl border-2 text-sm font-bold transition active:scale-[0.98] {{ $paymentMethod === 'qris' ? 'border-[#001356] bg-[#d5e3fc] text-[#001356]' : 'border-[#c6c5d2] bg-white text-[#454650] hover:border-[#001356]' }}" @disabled($cart->isEmpty())>
                        <span class="material-symbols-outlined text-[19px]">qr_code_2</span>
                        QRIS
                    </button>
                @endif
            </div>
        </form>
        <div class="flex items-center justify-between border-t border-[#c6c5d2] pt-3"><span class="text-lg font-bold text-[#171c20]">Total</span><span class="text-2xl font-extrabold text-[#001356]">{{ $formatRupiah($cartTotal) }}</span></div>
    </div>
    <button type="button" onclick="openPaymentModal()" @disabled($cart->isEmpty()) class="flex h-12 w-full items-center justify-center gap-3 rounded-xl {{ $cart->isEmpty() ? 'bg-[#c6c5d2]' : 'bg-[#001356]' }} text-lg font-extrabold text-white active:scale-[0.98]">
        <span>Bayar</span>
        <span class="material-symbols-outlined">arrow_forward</span>
    </button>
</div>
