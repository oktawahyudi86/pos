@php
    $formatRupiah = fn ($value) => 'Rp '.number_format($value, 0, ',', '.');
    $defaultPaymentMethod = old('payment_method', $onlinePaymentMethods[0] ?? 'manual_transfer');
    $defaultAddressLabel = old('address_label', 'rumah');
    $qrisImageUrl = ! empty($paymentInfo['qris_image_path']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($paymentInfo['qris_image_path'])
        ? \Illuminate\Support\Facades\Storage::url($paymentInfo['qris_image_path'])
        : null;
    $customer = auth()->user();
    $isGuest = ! auth()->check();
@endphp

<x-online-layout :tenant="$tenant" title="Checkout" active="cart" :back-url="route('online-orders.catalog', $tenant)">
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

        @if ($isGuest)
            <div class="rounded-xl border border-[#b9c7df] bg-gradient-to-r from-[#eef3ff] to-white px-4 py-4">
                <div class="flex items-center justify-between gap-4">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-3">
                            <span class="material-symbols-outlined text-[#001356]" style="font-variation-settings: 'FILL' 1;">account_circle</span>
                            <div>
                                <p class="text-sm font-extrabold text-[#001356]">Guest</p>
                                <p class="text-xs text-[#454650]">Belum masuk</p>
                            </div>
                        </div>
                        <p class="mt-2 text-xs leading-5 text-[#454650]">Masuk agar alamat tersimpan dan Anda dapat melihat riwayat pesanan.</p>
                    </div>
                    <a href="{{ route('online-orders.auth', [$tenant, 'redirect' => route('online-orders.checkout.form', $tenant)]) }}" class="shrink-0 rounded-xl bg-[#001356] px-4 py-2.5 text-xs font-extrabold text-white shadow-sm transition active:scale-[0.98]">
                        Masuk
                    </a>
                </div>
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
                <input name="customer_name" value="{{ old('customer_name', $customer?->name) }}" autocomplete="name" class="h-12 w-full rounded-xl border-[#c6c5d2] text-base focus:border-[#001356] focus:ring-[#001356]" placeholder="Nama lengkap">
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-[#171c20]">No. WhatsApp</label>
                <input id="wa-number-input" name="wa_number" value="{{ old('wa_number', $customer?->phone) }}" type="tel" inputmode="numeric" pattern="[0-9]*" autocomplete="tel" class="h-12 w-full rounded-xl border-[#c6c5d2] text-base focus:border-[#001356] focus:ring-[#001356]" placeholder="0812xxxxxxx">
            </div>

            <div id="delivery-location-root" class="rounded-2xl border border-[#dfe3e9] bg-[#f6faff] p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-extrabold text-[#171c20]">Alamat Pengiriman</p>
                        <p id="location-hint" class="mt-1 text-xs leading-5 text-[#454650]">Lokasi perangkat belum diaktifkan. Silakan pilih alamat pengiriman.</p>
                    </div>
                    <span class="material-symbols-outlined text-[#001356]" style="font-variation-settings: 'FILL' 1;">location_on</span>
                </div>

                <div class="mt-4 space-y-3">
                    <div id="location-initial-state" class="rounded-2xl border border-dashed border-[#c6c5d2] bg-white p-4 text-center">
                        <span class="material-symbols-outlined mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-[#eef3ff] text-[#001356]" style="font-variation-settings: 'FILL' 1;">location_searching</span>
                        <p class="mt-3 text-sm font-extrabold text-[#171c20]">Pilih alamat dari peta</p>
                        <p class="mt-1 text-xs leading-5 text-[#454650]">Anda tetap bisa checkout meski GPS ditolak. Cari alamat atau pilih titik di peta.</p>
                        <button id="choose-address-button" type="button" class="mt-4 flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#001356] px-4 text-sm font-extrabold text-white shadow-sm transition active:scale-[0.98]">
                            <span class="material-symbols-outlined text-[20px]">map</span>
                            Pilih Alamat
                        </button>
                        <button id="use-my-location-button" type="button" class="mt-3 flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-[#c6c5d2] bg-white px-4 text-sm font-extrabold text-[#001356] transition active:scale-[0.98]">
                            <span class="material-symbols-outlined text-[20px]">my_location</span>
                            Gunakan Lokasi Perangkat
                        </button>
                    </div>

                    <div id="location-selected-state" class="hidden space-y-3">
                        <div>
                            <p class="mb-2 text-xs font-bold uppercase tracking-widest text-[#767681]">Label alamat</p>
                            <div class="grid grid-cols-3 gap-2">
                                @foreach ([
                                    ['value' => 'rumah', 'label' => 'Rumah', 'icon' => 'home'],
                                    ['value' => 'kantor', 'label' => 'Kantor', 'icon' => 'work'],
                                    ['value' => 'lainnya', 'label' => 'Lainnya', 'icon' => 'location_on'],
                                ] as $labelOption)
                                    <label class="flex min-h-11 cursor-pointer flex-col items-center justify-center gap-1 rounded-xl border px-2 py-2 text-center text-xs font-extrabold transition has-[:checked]:border-[#001356] has-[:checked]:bg-[#eef3ff] has-[:checked]:text-[#001356]">
                                        <input type="radio" name="address_label" value="{{ $labelOption['value'] }}" @checked($defaultAddressLabel === $labelOption['value']) class="sr-only">
                                        <span class="material-symbols-outlined text-[18px]">{{ $labelOption['icon'] }}</span>
                                        {{ $labelOption['label'] }}
                                    </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <p class="mb-2 text-sm font-bold text-[#171c20]">Alamat Pengiriman</p>
                            <div id="address-display-card" class="relative overflow-hidden rounded-2xl border border-[#c6c5d2] bg-white">
                                <div id="address-map-thumbnail" class="address-map-thumbnail hidden h-32 w-full cursor-pointer" title="Ketuk untuk ubah lokasi di peta">
                                    <div id="address-map-thumbnail-canvas" class="h-full w-full"></div>
                                    <div class="address-map-thumbnail-overlay absolute inset-0 flex items-center justify-center bg-black/30">
                                        <div class="flex items-center gap-2 rounded-full bg-white px-3 py-1.5 text-xs font-bold text-[#001356]">
                                            <span class="material-symbols-outlined text-[16px]">map</span>
                                            Ketuk untuk ubah di peta
                                        </div>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <div id="address-display-skeleton" class="hidden space-y-2">
                                        <div class="skeleton-shimmer h-3 w-4/5 rounded-full"></div>
                                        <div class="skeleton-shimmer h-3 w-full rounded-full"></div>
                                        <div class="skeleton-shimmer h-3 w-3/5 rounded-full"></div>
                                    </div>
                                    <div class="flex items-start gap-3">
                                        <span class="material-symbols-outlined mt-0.5 flex-shrink-0 text-[#001356]" style="font-variation-settings: 'FILL' 1;">location_on</span>
                                        <div class="min-w-0 flex-1">
                                            <p id="address-display-text" class="text-sm font-semibold leading-6 text-[#171c20] transition-opacity">{{ old('address') ?: 'Alamat belum tersedia' }}</p>
                                            <p id="address-coordinate-text" class="mt-1 text-[11px] font-semibold text-[#767681]">Latitude dan longitude tersimpan otomatis.</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <button id="change-on-map-button" type="button" class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl border border-[#001356] bg-white px-4 text-sm font-extrabold text-[#001356] transition active:scale-[0.98]">
                            <span class="material-symbols-outlined text-[20px]">map</span>
                            Ubah Lokasi di Peta
                        </button>

                        <div id="coverage-loading" class="hidden rounded-xl border border-[#dfe3e9] bg-white px-4 py-3 text-xs font-semibold leading-5 text-[#454650]">
                            <span class="flex items-center gap-2">
                                <span class="h-4 w-4 animate-spin rounded-full border-2 border-[#c6c5d2] border-t-[#001356]"></span>
                                Memvalidasi area pengiriman...
                            </span>
                        </div>

                        <div id="coverage-banner" class="hidden rounded-xl border border-[#ffdad6] bg-[#fff4f2] px-4 py-3 text-xs font-semibold leading-5 text-[#93000a]"></div>

                        <div id="coverage-success" class="hidden rounded-xl border border-[#8fdcb7] bg-[#e7fff2] px-4 py-3 text-xs font-semibold leading-5 text-[#005236]">
                            <span class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-[18px]">check_circle</span>
                                Area Pengiriman Tersedia
                            </span>
                        </div>
                    </div>

                    <input id="formatted-address-input" type="hidden" name="address" value="{{ old('address') }}">
                    <input type="hidden" name="address_detail" value="{{ old('address_detail') }}">
                    <input id="delivery-latitude" type="hidden" name="delivery_latitude" value="{{ old('delivery_latitude') }}">
                    <input id="delivery-longitude" type="hidden" name="delivery_longitude" value="{{ old('delivery_longitude') }}">
                    <input id="delivery-place-id" type="hidden" name="place_id" value="{{ old('place_id') }}">
                    <input id="delivery-province" type="hidden" name="province" value="{{ old('province') }}">
                    <input id="delivery-city" type="hidden" name="city" value="{{ old('city') }}">
                    <input id="delivery-district" type="hidden" name="district" value="{{ old('district') }}">
                    <input id="delivery-subdistrict" type="hidden" name="subdistrict" value="{{ old('subdistrict', old('village')) }}">
                    <input id="delivery-postal-code" type="hidden" name="postal_code" value="{{ old('postal_code') }}">
                </div>
            </div>

            <div class="rounded-xl bg-[#f6faff] p-4">
                <div class="flex justify-between text-sm text-[#454650]"><span>Subtotal</span><span data-subtotal-value="{{ $cartSubtotal }}">{{ $formatRupiah($cartSubtotal) }}</span></div>
                <div class="mt-2 flex justify-between text-sm text-[#454650]"><span>Ongkir</span><span id="shipping-cost-text" data-shipping-cost="{{ $shippingCost }}">{{ $formatRupiah($shippingCost) }}</span></div>
                <div class="mt-3 flex justify-between border-t border-[#dfe3e9] pt-3 text-base font-extrabold text-[#001356]"><span>Total</span><span id="order-total-text" data-total="{{ $total }}">{{ $formatRupiah($total) }}</span></div>
            </div>

            <div class="rounded-xl border border-dashed border-[#c6c5d2] p-4">
                <p class="text-xs font-bold uppercase tracking-widest text-[#767681]">Metode Pembayaran</p>

                @if (count($onlinePaymentMethods) > 1)
                    <div class="mt-3 grid grid-cols-2 gap-2">
                        @if (in_array('manual_transfer', $onlinePaymentMethods, true))
                            <label class="flex min-h-12 cursor-pointer items-center justify-center gap-2 rounded-xl border px-3 text-sm font-extrabold transition has-[:checked]:border-[#001356] has-[:checked]:bg-[#eef3ff] has-[:checked]:text-[#001356]">
                                <input type="radio" name="payment_method" value="manual_transfer" @checked($defaultPaymentMethod === 'manual_transfer') class="sr-only" onchange="toggleOnlinePaymentMethod()">
                                <span class="material-symbols-outlined text-[18px]">account_balance</span>
                                Transfer Bank
                            </label>
                        @endif
                        @if (in_array('qris', $onlinePaymentMethods, true))
                            <label class="flex min-h-12 cursor-pointer items-center justify-center gap-2 rounded-xl border px-3 text-sm font-extrabold transition has-[:checked]:border-[#001356] has-[:checked]:bg-[#eef3ff] has-[:checked]:text-[#001356]">
                                <input type="radio" name="payment_method" value="qris" @checked($defaultPaymentMethod === 'qris') class="sr-only" onchange="toggleOnlinePaymentMethod()">
                                <span class="material-symbols-outlined text-[18px]">qr_code_2</span>
                                QRIS
                            </label>
                        @endif
                    </div>
                @else
                    <input type="hidden" name="payment_method" value="{{ $onlinePaymentMethods[0] ?? 'manual_transfer' }}">
                @endif

                <div id="payment-transfer-panel" class="mt-4 rounded-xl border border-dashed border-[#c6c5d2] bg-[#f6faff] p-4 {{ $defaultPaymentMethod === 'manual_transfer' ? '' : 'hidden' }}">
                    <p class="text-sm font-bold text-[#001356]">{{ $paymentInfo['bank_name'] }}</p>
                    <p class="text-lg font-extrabold text-[#171c20]">{{ $paymentInfo['account_number'] }}</p>
                    <p class="text-sm text-[#454650]">{{ $paymentInfo['account_name'] }}</p>
                </div>

                <div id="payment-qris-panel" class="mt-4 rounded-xl border border-dashed border-[#c6c5d2] bg-[#f6faff] p-3 text-center {{ $defaultPaymentMethod === 'qris' ? '' : 'hidden' }}">
                    @if ($qrisImageUrl)
                        <div class="overflow-hidden rounded-xl bg-white shadow-sm">
                            <img src="{{ $qrisImageUrl }}" alt="QRIS {{ $paymentInfo['qris_merchant_name'] ?? '' }}" class="block w-full h-auto">
                        </div>
                        <a href="{{ $qrisImageUrl }}" download="qris-{{ $tenant->slug }}.jpg" class="mt-2 inline-flex items-center gap-1 rounded-full border border-[#c6c5d2] bg-white px-3 py-1.5 text-[11px] font-bold text-[#454650] active:scale-[0.98]">
                            <span class="material-symbols-outlined text-[16px]">download</span>
                            Unduh QRIS
                        </a>
                    @else
                        <div class="mx-auto flex h-28 w-28 items-center justify-center rounded-2xl bg-white text-[#001356] shadow-sm">
                            <span class="material-symbols-outlined text-[72px]">qr_code_2</span>
                        </div>
                    @endif
                    <p class="mt-3 text-sm font-bold text-[#171c20]">{{ $paymentInfo['qris_merchant_name'] ?: 'Scan QRIS untuk membayar' }}</p>
                    <p class="mt-1 text-xs text-[#454650]">Tunjukkan bukti pembayaran ke kasir setelah transfer.</p>
                </div>
            </div>

            <button id="checkout-submit-button" data-cart-empty="{{ $cart->isEmpty() ? '1' : '0' }}" class="w-full rounded-xl bg-[#001356] px-4 py-4 text-sm font-extrabold text-white shadow-sm transition active:scale-[0.98] {{ $cart->isEmpty() ? 'pointer-events-none opacity-60' : 'opacity-60 pointer-events-none' }}" disabled>Buat Pesanan</button>
        </form>
    </section>

    <div id="location-permission-prompt" class="fixed inset-x-4 bottom-24 z-[900] hidden rounded-2xl border border-[#dfe3e9] bg-white p-4 shadow-[0_18px_50px_rgba(0,19,86,0.18)] sm:left-auto sm:right-6 sm:max-w-sm">
        <div class="flex items-start gap-3">
            <span class="material-symbols-outlined mt-0.5 text-[#001356]" style="font-variation-settings: 'FILL' 1;">my_location</span>
            <div class="min-w-0 flex-1">
                <p class="text-sm font-extrabold text-[#171c20]">Aktifkan lokasi</p>
                <p class="mt-1 text-xs leading-5 text-[#454650]">Aktifkan lokasi untuk membantu menemukan alamat pengiriman Anda.</p>
            </div>
        </div>
        <div class="mt-4 grid grid-cols-2 gap-2">
            <button id="location-permission-allow" type="button" class="min-h-11 rounded-xl bg-[#001356] px-3 text-sm font-extrabold text-white">Aktifkan Lokasi</button>
            <button id="location-permission-later" type="button" class="min-h-11 rounded-xl border border-[#c6c5d2] bg-white px-3 text-sm font-extrabold text-[#454650]">Nanti Saja</button>
        </div>
    </div>

    <div id="map-picker-modal" class="fixed inset-0 z-[1000] hidden bg-white">
        <div class="flex h-full flex-col">
            <div class="flex items-center gap-3 border-b border-[#dfe3e9] bg-white px-4 py-3">
                <button id="map-close-button" type="button" class="flex h-10 w-10 items-center justify-center rounded-full text-[#001356] active:scale-[0.98]" aria-label="Tutup peta">
                    <span class="material-symbols-outlined text-[28px]">arrow_back</span>
                </button>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-extrabold text-[#171c20]">Pilih lokasi pengantaran</p>
                    <p class="text-[11px] text-[#767681]">Cari alamat atau pilih titik pada peta</p>
                </div>
            </div>

            <div class="relative min-h-0 flex-1">
                <div class="absolute inset-x-4 top-4 z-[500]">
                    <div class="relative">
                        <input id="map-search-input" type="text" autocomplete="off" class="h-12 w-full rounded-2xl border border-[#c6c5d2] bg-white px-4 text-base shadow-lg focus:border-[#001356] focus:ring-[#001356]" placeholder="Cari alamat, gedung, atau tempat...">
                        <div id="map-search-results" class="map-search-results hidden"></div>
                    </div>
                </div>
                <div id="map-picker-canvas" class="h-full w-full"></div>
            </div>

            <div class="border-t border-[#dfe3e9] bg-white p-4">
                <div id="map-coverage-banner" class="mb-3 hidden rounded-xl border border-[#ffdad6] bg-[#fff4f2] px-4 py-3 text-xs font-semibold leading-5 text-[#93000a]"></div>
                <div id="map-coverage-loading" class="mb-3 hidden rounded-xl border border-[#dfe3e9] bg-[#f6faff] px-4 py-3 text-xs font-semibold leading-5 text-[#454650]">
                    <span class="flex items-center gap-2">
                        <span class="h-4 w-4 animate-spin rounded-full border-2 border-[#c6c5d2] border-t-[#001356]"></span>
                        Memvalidasi area pengiriman...
                    </span>
                </div>
                <div id="map-coverage-success" class="mb-3 hidden rounded-xl border border-[#8fdcb7] bg-[#e7fff2] px-4 py-3 text-xs font-semibold leading-5 text-[#005236]">
                    <span class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">check_circle</span>
                        Lokasi ini dapat dilayani.
                    </span>
                </div>
                <button id="map-confirm-button" type="button" class="mb-2 flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#001356] px-4 text-sm font-extrabold text-white active:scale-[0.98]">
                    <span data-map-confirm-label class="flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-[20px]">check</span>
                        Gunakan lokasi ini
                    </span>
                    <span data-map-confirm-loading class="hidden items-center justify-center gap-2">
                        <span class="btn-spinner" aria-hidden="true"></span>
                        Menyimpan lokasi...
                    </span>
                </button>
                <button id="map-cancel-button" type="button" class="flex min-h-11 w-full items-center justify-center rounded-xl border border-[#c6c5d2] bg-white px-4 text-sm font-bold text-[#454650] active:scale-[0.98]">
                    Batal
                </button>
            </div>
        </div>
    </div>

    @php
        $onlineCheckoutDeliveryConfig = [
            'tenant' => $tenant,
            'reverseGeocodeUrl' => route('online-orders.reverse-geocode', $tenant),
            'deliveryCoverageUrl' => route('online-orders.delivery-coverage', $tenant),
            'geocodeSearchUrl' => route('online-orders.geocode-search', $tenant),
            'geoapifyApiKey' => config('services.geoapify.key'),
            'deliveryCoverageConfig' => $deliveryCoverage,
            'outOfCoverageMessage' => \App\Services\DeliveryCoverageService::OUT_OF_COVERAGE_MESSAGE,
            'totals' => [
                'subtotal' => $cartSubtotal,
                'shippingCost' => $shippingCost,
                'total' => $total,
            ],
            'oldValues' => [
                'latitude' => old('delivery_latitude'),
                'longitude' => old('delivery_longitude'),
                'address' => old('address'),
                'placeId' => old('place_id'),
                'province' => old('province'),
                'city' => old('city'),
                'district' => old('district'),
                'subdistrict' => old('subdistrict', old('village')),
                'postalCode' => old('postal_code'),
            ],
        ];
    @endphp
    <script>
        window.onlineCheckoutDeliveryConfig = @json($onlineCheckoutDeliveryConfig);
    </script>
    @vite('resources/js/online-checkout.js')
    <script>
        const waNumberInput = document.getElementById('wa-number-input');

        function sanitizePhoneNumber() {
            if (!waNumberInput) return;
            waNumberInput.value = waNumberInput.value.replace(/\D+/g, '');
        }

        function toggleOnlinePaymentMethod() {
            const selected = document.querySelector('input[name="payment_method"]:checked')?.value
                || document.querySelector('input[name="payment_method"]')?.value
                || 'manual_transfer';
            const transferPanel = document.getElementById('payment-transfer-panel');
            const qrisPanel = document.getElementById('payment-qris-panel');

            transferPanel?.classList.toggle('hidden', selected !== 'manual_transfer');
            qrisPanel?.classList.toggle('hidden', selected !== 'qris');
        }

        waNumberInput?.addEventListener('input', sanitizePhoneNumber);
        toggleOnlinePaymentMethod();
    </script>
</x-online-layout>
