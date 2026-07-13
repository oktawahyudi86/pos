@php
    $formatRupiah = fn ($value) => 'Rp '.number_format($value, 0, ',', '.');
    $defaultPaymentMethod = old('payment_method', $onlinePaymentMethods[0] ?? 'manual_transfer');
    $qrisImageUrl = ! empty($paymentInfo['qris_image_path']) && \Illuminate\Support\Facades\Storage::disk('public')->exists($paymentInfo['qris_image_path'])
        ? \Illuminate\Support\Facades\Storage::url($paymentInfo['qris_image_path'])
        : null;
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
                <input name="customer_name" value="{{ old('customer_name') }}" autocomplete="name" class="h-12 w-full rounded-xl border-[#c6c5d2] text-base focus:border-[#001356] focus:ring-[#001356]" placeholder="Nama lengkap">
            </div>
            <div>
                <label class="mb-2 block text-sm font-bold text-[#171c20]">No. WhatsApp</label>
                <input id="wa-number-input" name="wa_number" value="{{ old('wa_number') }}" type="tel" inputmode="numeric" pattern="[0-9]*" autocomplete="tel" class="h-12 w-full rounded-xl border-[#c6c5d2] text-base focus:border-[#001356] focus:ring-[#001356]" placeholder="0812xxxxxxx">
            </div>

            <div class="rounded-2xl border border-[#dfe3e9] bg-[#f6faff] p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-sm font-extrabold text-[#171c20]">Lokasi pengantaran</p>
                        <p class="mt-1 text-xs leading-5 text-[#454650]">Aktifkan lokasi HP supaya kasir dapat titik Maps yang akurat.</p>
                    </div>
                    <span class="material-symbols-outlined text-[#001356]">location_on</span>
                </div>

                <div class="mt-4 space-y-3">
                    <div>
                        <label class="mb-2 block text-sm font-bold text-[#171c20]">Alamat lengkap</label>
                        <textarea name="address" rows="4" class="w-full rounded-xl border-[#c6c5d2] bg-white text-base focus:border-[#001356] focus:ring-[#001356]" placeholder="Tulis nama jalan, nomor rumah, RT/RW...">{{ old('address') }}</textarea>
                    </div>

                    <input id="delivery-latitude" type="hidden" name="delivery_latitude" value="{{ old('delivery_latitude') }}">
                    <input id="delivery-longitude" type="hidden" name="delivery_longitude" value="{{ old('delivery_longitude') }}">
                    <input id="delivery-province" type="hidden" name="province" value="{{ old('province') }}">
                    <input id="delivery-city" type="hidden" name="city" value="{{ old('city') }}">
                    <input id="delivery-district" type="hidden" name="district" value="{{ old('district') }}">
                    <input id="delivery-village" type="hidden" name="village" value="{{ old('village') }}">
                    <input id="delivery-postal-code" type="hidden" name="postal_code" value="{{ old('postal_code') }}">

                    <button id="detect-location-button" type="button" class="flex min-h-12 w-full items-center justify-center gap-2 rounded-xl border border-[#001356] bg-white px-4 text-sm font-extrabold text-[#001356] active:scale-[0.98]">
                        <span class="material-symbols-outlined text-[20px]">my_location</span>
                        Gunakan lokasi saya
                    </button>

                    <div id="location-status" class="rounded-xl border border-dashed border-[#c6c5d2] bg-white px-4 py-3 text-xs font-semibold leading-5 text-[#454650]">
                        Lokasi belum terdeteksi. Izinkan akses lokasi agar titik Maps ikut terkirim.
                    </div>

                    <a id="maps-preview-link" href="#" target="_blank" rel="noopener noreferrer" class="hidden items-center justify-between gap-3 rounded-xl bg-[#001356] px-4 py-3 text-sm font-extrabold text-white">
                        <span class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-[20px]">map</span>
                            Buka titik di Maps
                        </span>
                        <span class="material-symbols-outlined text-[20px]">open_in_new</span>
                    </a>

                    <div>
                        <label class="mb-2 block text-sm font-bold text-[#171c20]">Catatan patokan</label>
                        <textarea name="address_note" rows="3" class="w-full rounded-xl border-[#c6c5d2] bg-white text-base focus:border-[#001356] focus:ring-[#001356]" placeholder="Contoh: pagar hitam, sebelah minimarket, masuk gang kecil...">{{ old('address_note') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="rounded-xl bg-[#f6faff] p-4">
                <div class="flex justify-between text-sm text-[#454650]"><span>Subtotal</span><span>{{ $formatRupiah($cartSubtotal) }}</span></div>
                <div class="mt-2 flex justify-between text-sm text-[#454650]"><span>Ongkir</span><span>{{ $formatRupiah($shippingCost) }}</span></div>
                <div class="mt-3 flex justify-between border-t border-[#dfe3e9] pt-3 text-base font-extrabold text-[#001356]"><span>Total</span><span>{{ $formatRupiah($total) }}</span></div>
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

            <button class="w-full rounded-xl bg-[#001356] px-4 py-4 text-sm font-extrabold text-white shadow-sm {{ $cart->isEmpty() ? 'pointer-events-none opacity-60' : '' }}">Buat Pesanan</button>
        </form>
    </section>

    <script>
        const waNumberInput = document.getElementById('wa-number-input');
        const detectLocationButton = document.getElementById('detect-location-button');
        const locationStatus = document.getElementById('location-status');
        const mapsPreviewLink = document.getElementById('maps-preview-link');
        const latitudeInput = document.getElementById('delivery-latitude');
        const longitudeInput = document.getElementById('delivery-longitude');
        const addressInput = document.querySelector('[name="address"]');
        const provinceInput = document.getElementById('delivery-province');
        const cityInput = document.getElementById('delivery-city');
        const districtInput = document.getElementById('delivery-district');
        const villageInput = document.getElementById('delivery-village');
        const postalCodeInput = document.getElementById('delivery-postal-code');
        const reverseGeocodeUrl = @json(route('online-orders.reverse-geocode', $tenant));

        const statusClasses = {
            idle: 'rounded-xl border border-dashed border-[#c6c5d2] bg-white px-4 py-3 text-xs font-semibold leading-5 text-[#454650]',
            loading: 'rounded-xl border border-[#b9c7df] bg-white px-4 py-3 text-xs font-semibold leading-5 text-[#001356]',
            success: 'rounded-xl border border-[#8fdcb7] bg-[#e7fff2] px-4 py-3 text-xs font-semibold leading-5 text-[#005236]',
            error: 'rounded-xl border border-[#ffdad6] bg-[#fff4f2] px-4 py-3 text-xs font-semibold leading-5 text-[#93000a]',
        };

        let addressManuallyEdited = Boolean(addressInput?.value.trim());
        let isDetectingLocation = false;

        function sanitizePhoneNumber() {
            if (!waNumberInput) return;
            waNumberInput.value = waNumberInput.value.replace(/\D+/g, '');
        }

        function setStatus(type, message) {
            locationStatus.className = statusClasses[type] ?? statusClasses.idle;
            locationStatus.textContent = message;
        }

        function setDetectingState(isLoading) {
            isDetectingLocation = isLoading;
            detectLocationButton.disabled = isLoading;
            detectLocationButton.classList.toggle('opacity-70', isLoading);
        }

        function updateCoordinates(latitude, longitude) {
            latitudeInput.value = Number(latitude).toFixed(7);
            longitudeInput.value = Number(longitude).toFixed(7);

            const mapsUrl = `https://www.google.com/maps?q=${latitudeInput.value},${longitudeInput.value}`;
            mapsPreviewLink.href = mapsUrl;
            mapsPreviewLink.classList.remove('hidden');
            mapsPreviewLink.classList.add('flex');
        }

        function updateAddressComponents(components = {}) {
            provinceInput.value = components.province ?? '';
            cityInput.value = components.city ?? '';
            districtInput.value = components.district ?? '';
            villageInput.value = components.village ?? '';
            postalCodeInput.value = components.postal_code ?? '';
        }

        function applyDetectedAddress(address) {
            if (!addressInput || !address) {
                return;
            }

            addressInput.value = address;
            addressManuallyEdited = false;
        }

        function getCurrentPosition() {
            return new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(resolve, reject, {
                    enableHighAccuracy: true,
                    timeout: 12000,
                    maximumAge: 0,
                });
            });
        }

        async function reverseGeocode(latitude, longitude) {
            const params = new URLSearchParams({
                latitude: Number(latitude).toFixed(7),
                longitude: Number(longitude).toFixed(7),
            });

            const response = await fetch(`${reverseGeocodeUrl}?${params.toString()}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(payload.message || 'Alamat tidak dapat ditentukan dari lokasi ini.');
            }

            return payload;
        }

        async function detectLocation({ forceAddressUpdate = false } = {}) {
            if (isDetectingLocation) {
                return;
            }

            if (!navigator.geolocation) {
                setStatus('error', 'Browser ini belum mendukung deteksi lokasi. Isi alamat dan patokan secara manual.');
                return;
            }

            setDetectingState(true);
            setStatus('loading', 'Mengambil lokasi...');

            try {
                const position = await getCurrentPosition();
                const { latitude, longitude, accuracy } = position.coords;

                updateCoordinates(latitude, longitude);
                setStatus('loading', 'Menentukan alamat...');

                try {
                    const geocoded = await reverseGeocode(latitude, longitude);

                    updateAddressComponents(geocoded);

                    if (forceAddressUpdate || !addressManuallyEdited) {
                        applyDetectedAddress(geocoded.address);
                    }

                    setStatus('success', 'Alamat berhasil diperbarui');
                } catch (geocodeError) {
                    setStatus('error', geocodeError.message || 'Alamat tidak dapat ditentukan dari lokasi ini. Silakan isi alamat secara manual.');
                }
            } catch (error) {
                const denied = error?.code === 1;
                const message = denied
                    ? 'Izin lokasi ditolak. Aktifkan izin lokasi atau isi alamat secara manual.'
                    : 'Lokasi belum bisa dideteksi. Pastikan izin lokasi aktif, lalu coba lagi.';

                setStatus('error', message);
            } finally {
                setDetectingState(false);
            }
        }

        waNumberInput?.addEventListener('input', sanitizePhoneNumber);

        function toggleOnlinePaymentMethod() {
            const selected = document.querySelector('input[name="payment_method"]:checked')?.value
                || document.querySelector('input[name="payment_method"]')?.value
                || 'manual_transfer';
            const transferPanel = document.getElementById('payment-transfer-panel');
            const qrisPanel = document.getElementById('payment-qris-panel');

            transferPanel?.classList.toggle('hidden', selected !== 'manual_transfer');
            qrisPanel?.classList.toggle('hidden', selected !== 'qris');
        }

        addressInput?.addEventListener('input', () => {
            addressManuallyEdited = true;
        });

        detectLocationButton?.addEventListener('click', () => {
            detectLocation({ forceAddressUpdate: true });
        });

        if (latitudeInput?.value && longitudeInput?.value) {
            updateCoordinates(latitudeInput.value, longitudeInput.value);

            if (addressInput?.value.trim()) {
                setStatus('success', 'Alamat berhasil diperbarui');
            }
        } else {
            window.addEventListener('load', () => setTimeout(() => detectLocation(), 600));
        }

        toggleOnlinePaymentMethod();
    </script>
</x-online-layout>
