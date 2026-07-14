@props([
    'tenant',
    'cart',
    'cartSubtotal',
    'deliveryCoverage',
])

@php
    $customer = auth()->user();
    $isGuest = ! auth()->check();
    $outOfCoverageMessage = \App\Services\DeliveryCoverageService::OUT_OF_COVERAGE_MESSAGE;
@endphp

<x-online-layout :tenant="$tenant" title="Konfirmasi Alamat" active="cart" :back-url="route('online-orders.catalog', $tenant)">
    <div class="mx-auto max-w-5xl px-4 sm:px-6">
        <div class="mb-6">
            <h1 class="text-2xl font-extrabold leading-tight text-[#001356]">Konfirmasi Alamat Pengiriman</h1>
            <p class="mt-2 text-sm text-[#454650]">Pilih alamat pengiriman untuk melanjutkan pesanan Anda.</p>
        </div>

        @if ($isGuest)
            <div class="mb-4 rounded-xl border border-[#b9c7df] bg-[#eef3ff] px-4 py-3">
                <div class="flex items-start justify-between gap-3">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-extrabold text-[#001356]">Masuk agar alamat tersimpan dan Anda dapat melihat riwayat pesanan.</p>
                    </div>
                    <a href="{{ route('online-orders.auth', [$tenant, 'redirect' => route('online-orders.address', $tenant)]) }}" class="shrink-0 rounded-lg bg-[#001356] px-3 py-2 text-xs font-extrabold text-white shadow-sm transition active:scale-[0.98]">
                        Masuk
                    </a>
                </div>
            </div>
        @endif

        <div id="delivery-location-root">
            <div id="location-initial-state" class="rounded-2xl border border-dashed border-[#c6c5d2] bg-white p-6 text-center">
                <span class="material-symbols-outlined mx-auto flex h-16 w-16 items-center justify-center rounded-full bg-[#eef3ff] text-[#001356]" style="font-variation-settings: 'FILL' 1;">location_searching</span>
                <p class="mt-4 text-lg font-extrabold text-[#171c20]">Pilih Alamat Pengiriman</p>
                <p class="mt-2 text-sm leading-5 text-[#454650]">Anda tetap bisa checkout meski GPS ditolak. Cari alamat atau pilih titik di peta.</p>
                <div class="mt-6 space-y-3">
                    <button id="choose-address-button" type="button" class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#001356] px-4 text-sm font-extrabold text-white shadow-sm transition active:scale-[0.98]">
                        <span class="material-symbols-outlined text-[20px]">map</span>
                        Pilih Alamat
                    </button>
                    <button id="use-my-location-button" type="button" class="flex min-h-11 w-full items-center justify-center gap-2 rounded-xl border border-[#c6c5d2] bg-white px-4 text-sm font-extrabold text-[#001356] transition active:scale-[0.98]">
                        <span class="material-symbols-outlined text-[20px]">my_location</span>
                        Gunakan Lokasi Perangkat
                    </button>
                </div>
            </div>

            <div id="location-selected-state" class="hidden">
                <input id="delivery-latitude" type="hidden" name="delivery_latitude">
                <input id="delivery-longitude" type="hidden" name="delivery_longitude">
                <input id="delivery-place-id" type="hidden" name="place_id">
                <input id="formatted-address-input" type="hidden" name="address">
                <input id="province-input" type="hidden" name="province">
                <input id="city-input" type="hidden" name="city">
                <input id="district-input" type="hidden" name="district">
                <input id="subdistrict-input" type="hidden" name="subdistrict">
                <input id="postal-code-input" type="hidden" name="postal_code">

                <div class="rounded-2xl border border-[#c6c5d2] bg-white p-4 shadow-sm">
                    <div class="flex items-center gap-2 mb-3">
                        <span class="material-symbols-outlined text-[#001356]" style="font-variation-settings: 'FILL' 1;">location_on</span>
                        <p class="text-sm font-bold text-[#171c20]">Alamat Pengiriman</p>
                    </div>
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
                                <div class="h-4 w-3/4 rounded-full bg-[#eef3ff]"></div>
                                <div class="h-4 w-1/2 rounded-full bg-[#eef3ff]"></div>
                            </div>
                            <div id="address-display-content">
                                <p id="address-text" class="text-sm font-semibold leading-6 text-[#171c20]"></p>
                            </div>
                            <div id="address-labels" class="mt-3 flex flex-wrap gap-2">
                                @php
                                    $labelOptions = [
                                        ['value' => 'rumah', 'label' => 'Rumah', 'icon' => 'home'],
                                        ['value' => 'kantor', 'label' => 'Kantor', 'icon' => 'business'],
                                        ['value' => 'apartemen', 'label' => 'Apartemen', 'icon' => 'apartment'],
                                        ['value' => 'lainnya', 'label' => 'Lainnya', 'icon' => 'more_horiz'],
                                    ];
                                    $defaultAddressLabel = 'rumah';
                                @endphp
                                @foreach ($labelOptions as $labelOption)
                                    <label class="flex min-h-11 cursor-pointer flex-col items-center justify-center gap-1 rounded-xl border px-2 py-2 text-center text-xs font-extrabold transition has-[:checked]:border-[#001356] has-[:checked]:bg-[#eef3ff] has-[:checked]:text-[#001356]">
                                        <input type="radio" name="address_label" value="{{ $labelOption['value'] }}" @checked($defaultAddressLabel === $labelOption['value']) class="sr-only">
                                        <span class="material-symbols-outlined text-[18px]">{{ $labelOption['icon'] }}</span>
                                        {{ $labelOption['label'] }}
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    </div>
                    <button id="change-on-map-button" type="button" class="mt-3 flex min-h-12 w-full items-center justify-center gap-2 rounded-xl border border-[#001356] bg-white px-4 text-sm font-extrabold text-[#001356] transition active:scale-[0.98]">
                        <span class="material-symbols-outlined text-[20px]">map</span>
                        Ubah Lokasi di Peta
                    </button>
                </div>

                <div id="coverage-success" class="mt-3 hidden rounded-xl border border-[#b9e3c8] bg-[#f0fdf4] px-4 py-3">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[#16a34a]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                        <p class="text-xs font-semibold leading-5 text-[#16a34a]">Alamat berada dalam area pengiriman</p>
                    </div>
                </div>

                <div id="coverage-banner" class="mt-3 hidden rounded-xl border border-[#ffdad6] bg-[#fff4f2] px-4 py-3 text-xs font-semibold leading-5 text-[#93000a]"></div>

                <div id="coverage-loading" class="mt-3 hidden rounded-xl border border-[#dfe3e9] bg-[#f6faff] px-4 py-3 text-xs font-semibold leading-5 text-[#454650]">
                    <span class="flex items-center gap-2">
                        <span class="h-4 w-4 animate-spin rounded-full border-2 border-[#c6c5d2] border-t-[#001356]"></span>
                        Memvalidasi area pengiriman...
                    </span>
                </div>

                <button id="confirm-address-button" type="button" class="mt-4 flex min-h-12 w-full items-center justify-center gap-2 rounded-xl bg-[#001356] px-4 text-sm font-extrabold text-white shadow-sm transition active:scale-[0.98]">
                    <span data-confirm-label>Lanjut ke Checkout</span>
                    <span data-confirm-loading class="hidden flex items-center gap-2">
                        <span class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                        Memproses...
                    </span>
                </button>
            </div>
        </div>

        <div id="location-permission-prompt" class="fixed inset-x-4 bottom-4 left-0 right-0 z-[100] hidden rounded-2xl border border-[#dfe3e9] bg-white p-4 shadow-[0_8px_32px_rgba(0,19,86,0.12)] sm:bottom-auto sm:top-20 sm:left-1/2 sm:right-auto sm:w-full sm:max-w-md sm:-translate-x-1/2">
            <div class="flex items-start gap-3">
                <span class="material-symbols-outlined flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-[#eef3ff] text-[#001356]" style="font-variation-settings: 'FILL' 1;">location_on</span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-extrabold text-[#171c20]">Aktifkan Lokasi</p>
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
                    <div id="map-coverage-success" class="mb-3 hidden rounded-xl border border-[#b9e3c8] bg-[#f0fdf4] px-4 py-3 text-xs font-semibold leading-5 text-[#16a34a]">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[16px]" style="font-variation-settings: 'FILL' 1;">check_circle</span>
                            Alamat berada dalam area pengiriman
                        </span>
                    </div>
                    <div class="flex gap-2">
                        <button id="map-confirm-button" type="button" class="flex min-h-12 flex-1 items-center justify-center gap-2 rounded-xl bg-[#001356] px-4 text-sm font-extrabold text-white shadow-sm transition active:scale-[0.98]">
                            <span data-map-confirm-label>Gunakan lokasi ini</span>
                            <span data-map-confirm-loading class="hidden flex items-center gap-2">
                                <span class="h-4 w-4 animate-spin rounded-full border-2 border-white/30 border-t-white"></span>
                                Memproses...
                            </span>
                        </button>
                        <button id="map-cancel-button" type="button" class="min-h-12 rounded-xl border border-[#c6c5d2] bg-white px-4 text-sm font-extrabold text-[#001356] transition active:scale-[0.98]">
                            Batal
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $onlineCheckoutDeliveryConfig = [
            'reverseGeocodeUrl' => route('online-orders.reverse-geocode', $tenant),
            'deliveryCoverageUrl' => route('online-orders.delivery-coverage', $tenant),
            'geocodeSearchUrl' => route('online-orders.geocode-search', $tenant),
            'geoapifyApiKey' => config('services.geoapify.key'),
            'deliveryCoverageConfig' => $deliveryCoverage,
            'outOfCoverageMessage' => $outOfCoverageMessage,
            'checkoutUrl' => route('online-orders.checkout.form', $tenant),
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
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof initAddressConfirmation === 'function') {
                initAddressConfirmation(@json($tenant), @json($onlineCheckoutDeliveryConfig));
            }
        });
    </script>
</x-online-layout>
