@php
    $formatRupiah = fn ($value) => 'Rp '.number_format($value, 0, ',', '.');
@endphp

<x-online-layout :tenant="$tenant" title="Checkout" active="cart">
    <section class="space-y-5">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.3em] text-[#767681]">Checkout</p>
            <h1 class="mt-1 text-2xl font-extrabold leading-tight text-[#001356]">Lengkapi pesanan</h1>
        </div>

        @if ($errors->any())
            <div class="rounded-xl border border-[#ffdad6] bg-white px-4 py-3 text-sm font-semibold text-[#93000a]">
                {{ $errors->first() }}
            </div>
        @endif

        <div class="space-y-3">
            @forelse ($cart as $item)
                <article class="rounded-xl border border-[#c6c5d2] bg-white p-4 shadow-sm">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0">
                            <h2 class="line-clamp-1 text-sm font-extrabold text-[#171c20]">{{ $item['name'] }}</h2>
                            <p class="mt-1 text-xs text-[#454650]">{{ collect($item['variant_options'])->pluck('name')->merge(collect($item['addons'])->pluck('name'))->filter()->join(', ') ?: 'Tanpa varian' }}</p>
                            @if ($item['note'])
                                <p class="mt-1 text-xs font-semibold text-[#767681]">Catatan: {{ $item['note'] }}</p>
                            @endif
                        </div>
                        <p class="shrink-0 text-sm font-extrabold text-[#001356]">{{ $formatRupiah($item['line_total']) }}</p>
                    </div>
                    <div class="mt-3 flex items-center justify-between">
                        <span class="rounded-full bg-[#eef3ff] px-3 py-1 text-xs font-bold text-[#001356]">Qty {{ $item['quantity'] }}</span>
                        <form method="POST" action="{{ route('online-orders.cart.destroy', [$tenant, $item['key']]) }}">
                            @csrf
                            @method('DELETE')
                            <button class="text-xs font-bold text-[#ba1a1a]">Hapus</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="rounded-xl border border-dashed border-[#c6c5d2] bg-white p-10 text-center">
                    <span class="material-symbols-outlined text-5xl text-[#767681]">shopping_bag</span>
                    <p class="mt-3 text-sm font-bold text-[#454650]">Keranjang masih kosong.</p>
                    <a href="{{ route('online-orders.catalog', $tenant) }}" class="mt-4 inline-flex rounded-xl bg-[#001356] px-4 py-3 text-sm font-extrabold text-white">Pilih Menu</a>
                </div>
            @endforelse
        </div>

        <form method="POST" action="{{ route('online-orders.checkout', $tenant) }}" class="space-y-4 rounded-xl border border-[#c6c5d2] bg-white p-5 shadow-sm">
            @csrf
            <div>
                <label class="mb-2 block text-sm font-bold text-[#171c20]">Nama penerima</label>
                <input name="customer_name" value="{{ old('customer_name') }}" class="h-12 w-full rounded-xl border-[#c6c5d2] text-base focus:border-[#001356] focus:ring-[#001356]" placeholder="Nama lengkap">
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-[#171c20]">No. WhatsApp</label>
                <input name="wa_number" value="{{ old('wa_number') }}" class="h-12 w-full rounded-xl border-[#c6c5d2] text-base focus:border-[#001356] focus:ring-[#001356]" placeholder="0812xxxxxxx">
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-[#171c20]">Alamat pengantaran</label>
                <textarea name="address" rows="4" class="w-full rounded-xl border-[#c6c5d2] text-base focus:border-[#001356] focus:ring-[#001356]" placeholder="Tulis alamat lengkap...">{{ old('address') }}</textarea>
            </div>

            <div class="rounded-xl bg-[#f6faff] p-4">
                <div class="flex justify-between text-sm text-[#454650]"><span>Subtotal</span><span>{{ $formatRupiah($cartSubtotal) }}</span></div>
                <div class="mt-2 flex justify-between text-sm text-[#454650]"><span>Ongkir</span><span>{{ $formatRupiah($shippingCost) }}</span></div>
                <div class="mt-3 flex justify-between border-t border-[#dfe3e9] pt-3 text-base font-extrabold text-[#001356]"><span>Total</span><span>{{ $formatRupiah($total) }}</span></div>
            </div>

            <div class="rounded-xl border border-dashed border-[#c6c5d2] p-4">
                <p class="text-xs font-bold uppercase tracking-widest text-[#767681]">Pembayaran Manual</p>
                <p class="mt-2 text-sm font-bold text-[#001356]">{{ $paymentInfo['bank_name'] }}</p>
                <p class="text-lg font-extrabold text-[#171c20]">{{ $paymentInfo['account_number'] }}</p>
                <p class="text-sm text-[#454650]">{{ $paymentInfo['account_name'] }}</p>
            </div>

            <button class="w-full rounded-xl bg-[#001356] px-4 py-4 text-sm font-extrabold text-white shadow-sm {{ $cart->isEmpty() ? 'pointer-events-none opacity-60' : '' }}">Buat Pesanan</button>
        </form>
    </section>
</x-online-layout>
