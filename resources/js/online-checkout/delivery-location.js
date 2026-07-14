import { createCoverageEvaluator } from './coverage';
import { getCurrentPosition, getGeolocationErrorMessage, isGeolocationSupported } from './geolocation';
import { createLeafletMapPicker } from './leaflet-map-picker';
import { createMapThumbnail } from './map-thumbnail';
import { reverseGeocode } from './reverse-geocoding';

const LABEL_OPTIONS = [
    { value: 'rumah', label: 'Rumah', icon: 'home' },
    { value: 'kantor', label: 'Kantor', icon: 'work' },
    { value: 'lainnya', label: 'Lainnya', icon: 'location_on' },
];

export function initDeliveryLocation(config) {
    const {
        tenant,
        reverseGeocodeUrl,
        deliveryCoverageUrl,
        geocodeSearchUrl,
        geoapifyApiKey,
        deliveryCoverageConfig,
        outOfCoverageMessage,
        totals = {},
        oldValues = {},
    } = config;

    const elements = {
        initialState: document.getElementById('location-initial-state'),
        selectedState: document.getElementById('location-selected-state'),
        chooseAddressButton: document.getElementById('choose-address-button'),
        useLocationButton: document.getElementById('use-my-location-button'),
        changeOnMapButton: document.getElementById('change-on-map-button'),
        addressCard: document.getElementById('address-display-card'),
        addressText: document.getElementById('address-display-text'),
        addressSkeleton: document.getElementById('address-display-skeleton'),
        addressMapThumbnail: document.getElementById('address-map-thumbnail'),
        addressMapThumbnailCanvas: document.getElementById('address-map-thumbnail-canvas'),
        coverageBanner: document.getElementById('coverage-banner'),
        coverageLoading: document.getElementById('coverage-loading'),
        coverageSuccess: document.getElementById('coverage-success'),
        mapCoverageBanner: document.getElementById('map-coverage-banner'),
        mapCoverageLoading: document.getElementById('map-coverage-loading'),
        mapCoverageSuccess: document.getElementById('map-coverage-success'),
        permissionPrompt: document.getElementById('location-permission-prompt'),
        permissionAllowButton: document.getElementById('location-permission-allow'),
        permissionLaterButton: document.getElementById('location-permission-later'),
        locationHint: document.getElementById('location-hint'),
        checkoutSubmitButton: document.getElementById('checkout-submit-button'),
        shippingCostText: document.getElementById('shipping-cost-text'),
        orderTotalText: document.getElementById('order-total-text'),
        coordinateText: document.getElementById('address-coordinate-text'),
        latitudeInput: document.getElementById('delivery-latitude'),
        longitudeInput: document.getElementById('delivery-longitude'),
        addressInput: document.getElementById('formatted-address-input'),
        placeIdInput: document.getElementById('delivery-place-id'),
        provinceInput: document.getElementById('delivery-province'),
        cityInput: document.getElementById('delivery-city'),
        districtInput: document.getElementById('delivery-district'),
        subdistrictInput: document.getElementById('delivery-subdistrict'),
        postalCodeInput: document.getElementById('delivery-postal-code'),
        addressDetailInput: document.querySelector('[name="address_detail"]'),
        labelInputs: Array.from(document.querySelectorAll('input[name="address_label"]')),
        mapModal: document.getElementById('map-picker-modal'),
        mapContainer: document.getElementById('map-picker-canvas'),
        mapSearchInput: document.getElementById('map-search-input'),
        mapSearchResults: document.getElementById('map-search-results'),
        mapConfirmButton: document.getElementById('map-confirm-button'),
        mapCloseButtons: [
            document.getElementById('map-close-button'),
            document.getElementById('map-cancel-button'),
        ],
    };

    const coverage = createCoverageEvaluator(deliveryCoverageConfig, outOfCoverageMessage);

    let isResolvingAddress = false;
    let isValidatingCoverage = false;
    let hasValidLocation = false;
    let coverageState = { within: true, message: null, active: false };
    let mapThumbnail = null;
    let mapPicker = null;
    let locationResolveSeq = 0;
    let coverageSeq = 0;
    const locationPromptKey = 'keijora_delivery_location_prompt_seen';

    function formatRupiah(value) {
        return 'Rp ' + Number(value || 0).toLocaleString('id-ID');
    }

    function updateTotals(deliveryFee, total) {
        if (elements.shippingCostText && deliveryFee !== null && deliveryFee !== undefined) {
            elements.shippingCostText.textContent = formatRupiah(deliveryFee);
            elements.shippingCostText.dataset.shippingCost = deliveryFee;
        }

        if (elements.orderTotalText && total !== null && total !== undefined) {
            elements.orderTotalText.textContent = formatRupiah(total);
            elements.orderTotalText.dataset.total = total;
        }
    }

    async function syncCoverageForCoordinates(latitude, longitude) {
        const localCoverage = coverage.evaluate(latitude, longitude);
        applyCoverageState(localCoverage, { latitude, longitude });

        if (!deliveryCoverageUrl || !latitude || !longitude) {
            return localCoverage;
        }

        const seq = ++coverageSeq;
        setCoverageLoading(true);

        try {
            const params = new URLSearchParams({
                latitude: Number(latitude).toFixed(7),
                longitude: Number(longitude).toFixed(7),
            });
            const response = await fetch(`${deliveryCoverageUrl}?${params.toString()}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/json',
                },
            });
            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                throw new Error(payload.message || 'Validasi area pengiriman gagal.');
            }

            if (seq !== coverageSeq) {
                return localCoverage;
            }

            const serverCoverage = coverage.normalize(payload);
            applyCoverageState(serverCoverage, { latitude, longitude });
            updateTotals(payload.delivery_fee ?? totals.shippingCost, payload.total ?? totals.total);

            return serverCoverage;
        } catch (error) {
            if (seq !== coverageSeq) {
                return localCoverage;
            }

            const failedCoverage = {
                within: false,
                active: true,
                message: error.message || 'Validasi area pengiriman gagal. Pilih alamat lagi.',
            };
            applyCoverageState(failedCoverage, { latitude, longitude });

            return failedCoverage;
        } finally {
            if (seq === coverageSeq) {
                setCoverageLoading(false);
            }
        }
    }

    function setAddressLoading(isLoading) {
        isResolvingAddress = isLoading;
        elements.addressSkeleton?.classList.toggle('hidden', !isLoading);
        elements.addressText?.classList.toggle('opacity-0', isLoading);
        elements.addressText?.classList.toggle('animate-pulse', isLoading);
    }

    function setCoverageLoading(isLoading) {
        isValidatingCoverage = isLoading;
        elements.coverageLoading?.classList.toggle('hidden', !isLoading);
        elements.mapCoverageLoading?.classList.toggle('hidden', !isLoading);
        syncCheckoutButton();
    }

    function updateAddressComponents(components) {
        elements.provinceInput.value = components.province ?? '';
        elements.cityInput.value = components.city ?? '';
        elements.districtInput.value = components.district ?? '';
        elements.subdistrictInput.value = components.subdistrict ?? components.village ?? '';
        elements.postalCodeInput.value = components.postalCode ?? components.postal_code ?? '';
    }

    function updateCoordinates(latitude, longitude, placeId = null, { refreshThumbnail = true } = {}) {
        elements.latitudeInput.value = Number(latitude).toFixed(7);
        elements.longitudeInput.value = Number(longitude).toFixed(7);

        if (placeId !== undefined) {
            elements.placeIdInput.value = placeId ?? '';
        }

        if (refreshThumbnail) {
            mapThumbnail?.update(latitude, longitude);
        }

        if (elements.coordinateText) {
            elements.coordinateText.textContent = `Lat ${Number(latitude).toFixed(6)}, Lng ${Number(longitude).toFixed(6)}`;
        }
    }

    function applyFormattedAddress(address) {
        elements.addressInput.value = address ?? '';
        if (elements.addressText) {
            elements.addressText.textContent = address || 'Alamat belum tersedia';
        }
    }

    function showSelectedState() {
        elements.initialState?.classList.add('hidden');
        elements.selectedState?.classList.remove('hidden');
        elements.selectedState?.classList.add('animate-fade-in');
    }

    function showInitialState() {
        elements.initialState?.classList.remove('hidden');
        elements.selectedState?.classList.add('hidden');
        mapThumbnail?.hide();
    }

    function applyCoverageState(nextCoverage, previewCoords = null) {
        coverageState = {
            within: nextCoverage?.within ?? nextCoverage?.within_coverage ?? false,
            message: nextCoverage?.message ?? null,
            active: nextCoverage?.active ?? true,
        };

        const restrictionActive = coverageState.active ?? coverage.isRestrictionActive();
        const blocked = restrictionActive && !coverageState.within;
        const hasCoords = previewCoords?.latitude && previewCoords?.longitude
            ? true
            : Boolean(elements.latitudeInput.value && elements.longitudeInput.value);

        elements.coverageBanner?.classList.toggle('hidden', isValidatingCoverage || !blocked || !coverageState.message);
        if (elements.coverageBanner) {
            elements.coverageBanner.innerHTML = `
                <span class="flex items-center justify-between gap-3">
                    <span>${coverageState.message || ''}</span>
                    <button type="button" data-pick-address-again class="shrink-0 rounded-lg bg-white px-3 py-2 text-xs font-extrabold text-[#93000a]">Pilih Alamat Lain</button>
                </span>
            `;
            elements.coverageBanner.querySelector('[data-pick-address-again]')?.addEventListener('click', openMapPicker);
        }

        elements.mapCoverageBanner?.classList.toggle('hidden', isValidatingCoverage || !blocked || !coverageState.message);
        if (elements.mapCoverageBanner) {
            elements.mapCoverageBanner.textContent = coverageState.message || '';
        }

        const showCheckoutSuccess = hasCoords && hasValidLocation && !blocked && restrictionActive && !isValidatingCoverage;
        const showMapSuccess = hasCoords && !blocked && restrictionActive && !isValidatingCoverage;

        elements.coverageSuccess?.classList.toggle('hidden', !showCheckoutSuccess);
        elements.mapCoverageSuccess?.classList.toggle('hidden', !showMapSuccess);

        elements.mapConfirmButton?.toggleAttribute('disabled', blocked);
        elements.mapConfirmButton?.classList.toggle('opacity-60', blocked);
        elements.mapConfirmButton?.classList.toggle('pointer-events-none', blocked);

        if (elements.locationHint) {
            if (!hasCoords) {
                elements.locationHint.textContent = 'Lokasi perangkat belum diaktifkan. Silakan pilih alamat pengiriman.';
            } else if (isValidatingCoverage) {
                elements.locationHint.textContent = 'Memvalidasi area pengiriman...';
            } else if (blocked) {
                elements.locationHint.textContent = 'Di luar Area Pengiriman. Pilih alamat lain untuk melanjutkan checkout.';
            } else if (isResolvingAddress) {
                elements.locationHint.textContent = 'Menentukan alamat dari koordinat...';
            } else {
                elements.locationHint.textContent = 'Alamat valid dan berada dalam area pengiriman.';
            }
        }

        syncCheckoutButton();
    }

    function isLabelSelected() {
        return elements.labelInputs.some((input) => input.checked);
    }

    function syncCheckoutButton() {
        if (!elements.checkoutSubmitButton) {
            return;
        }

        const cartEmpty = elements.checkoutSubmitButton.dataset.cartEmpty === '1';
        const locationReady = hasValidLocation
            && Boolean(elements.latitudeInput.value)
            && Boolean(elements.longitudeInput.value)
            && Boolean(elements.addressInput.value.trim())
            && isLabelSelected();
        const blocked = coverage.isRestrictionActive() && !coverageState.within;
        const disabled = cartEmpty || !locationReady || blocked || isValidatingCoverage;

        elements.checkoutSubmitButton.disabled = disabled;
        elements.checkoutSubmitButton.classList.toggle('pointer-events-none', disabled);
        elements.checkoutSubmitButton.classList.toggle('opacity-60', disabled);
    }

    async function resolveAddressFromCoordinates(latitude, longitude, {
        placeId = null,
        formattedAddress = null,
        skipReverseGeocode = false,
        refreshThumbnail = true,
    } = {}) {
        const seq = ++locationResolveSeq;

        updateCoordinates(latitude, longitude, placeId, { refreshThumbnail });
        showSelectedState();
        setAddressLoading(true);

        try {
            if (skipReverseGeocode && formattedAddress) {
                if (seq !== locationResolveSeq) {
                    return;
                }

                applyFormattedAddress(formattedAddress);
            } else {
                const geocoded = await reverseGeocode(geoapifyApiKey, latitude, longitude);

                if (seq !== locationResolveSeq) {
                    return;
                }

                applyFormattedAddress(geocoded.formattedAddress);
                updateAddressComponents(geocoded);

                if (geocoded.placeId) {
                    elements.placeIdInput.value = geocoded.placeId;
                }
            }

            hasValidLocation = true;
            await syncCoverageForCoordinates(latitude, longitude);
        } catch (error) {
            if (seq !== locationResolveSeq) {
                return;
            }

            hasValidLocation = Boolean(formattedAddress);
            applyFormattedAddress(formattedAddress || '');
            await syncCoverageForCoordinates(latitude, longitude);

            if (!formattedAddress) {
                elements.locationHint.textContent = error.message || 'Alamat tidak dapat ditentukan. Coba geser pin di peta.';
            }
        } finally {
            if (seq !== locationResolveSeq) {
                return;
            }

            setAddressLoading(false);
            syncCheckoutButton();
        }
    }

    async function detectMyLocation() {
        if (!isGeolocationSupported()) {
            elements.locationHint.textContent = 'Browser ini belum mendukung deteksi lokasi.';
            return;
        }

        elements.useLocationButton.disabled = true;
        elements.useLocationButton.classList.add('opacity-70');
        elements.locationHint.textContent = 'Mengambil lokasi GPS...';

        try {
            const position = await getCurrentPosition();
            const { latitude, longitude } = position.coords;
            await resolveAddressFromCoordinates(latitude, longitude);
        } catch (error) {
            elements.locationHint.textContent = getGeolocationErrorMessage(error, {
                coverageRequired: coverage.isRestrictionActive(),
            });
            syncCheckoutButton();
        } finally {
            elements.useLocationButton.disabled = false;
            elements.useLocationButton.classList.remove('opacity-70');
        }
    }

    async function openMapPicker() {
        const latitude = elements.latitudeInput.value;
        const longitude = elements.longitudeInput.value;

        await mapPicker.open(latitude && longitude ? {
            latitude,
            longitude,
            placeId: elements.placeIdInput.value || null,
            formattedAddress: elements.addressInput.value || null,
        } : null);
    }

    mapPicker = createLeafletMapPicker({
        geoapifyApiKey,
        modal: elements.mapModal,
        mapContainer: elements.mapContainer,
        searchInput: elements.mapSearchInput,
        searchResultsContainer: elements.mapSearchResults,
        confirmButton: elements.mapConfirmButton,
        closeButtons: elements.mapCloseButtons,
        onOpen: () => {
            mapThumbnail?.hide();
        },
        onCoordinatesPreview: ({ latitude, longitude }) => {
            syncCoverageForCoordinates(latitude, longitude);
        },
        onClose: () => {
            mapThumbnail?.show(
                elements.latitudeInput.value,
                elements.longitudeInput.value,
            );
        },
        initialPosition: oldValues.latitude && oldValues.longitude
            ? { latitude: oldValues.latitude, longitude: oldValues.longitude, placeId: oldValues.placeId }
            : null,
        onLocationChange: async ({ latitude, longitude, placeId, formattedAddress, confirmed }) => {
            if (confirmed) {
                await resolveAddressFromCoordinates(latitude, longitude, {
                    placeId,
                    formattedAddress,
                    skipReverseGeocode: Boolean(formattedAddress),
                    refreshThumbnail: false,
                });
                return;
            }

            const seq = ++locationResolveSeq;

            if (formattedAddress) {
                applyFormattedAddress(formattedAddress);
            }

            updateCoordinates(latitude, longitude, placeId, { refreshThumbnail: false });
            showSelectedState();
            setAddressLoading(true);

            try {
                if (!formattedAddress) {
                    const geocoded = await reverseGeocode(geoapifyApiKey, latitude, longitude);

                    if (seq !== locationResolveSeq) {
                        return;
                    }

                    applyFormattedAddress(geocoded.formattedAddress);
                    updateAddressComponents(geocoded);

                    if (geocoded.placeId) {
                        elements.placeIdInput.value = geocoded.placeId;
                    }
                }

                hasValidLocation = true;
                await syncCoverageForCoordinates(latitude, longitude);
            } catch {
                if (seq !== locationResolveSeq) {
                    return;
                }

                await syncCoverageForCoordinates(latitude, longitude);
            } finally {
                if (seq !== locationResolveSeq) {
                    return;
                }

                setAddressLoading(false);
                syncCheckoutButton();
            }
        },
    });

    mapThumbnail = createMapThumbnail(elements.addressMapThumbnail, {
        canvas: elements.addressMapThumbnailCanvas,
        onClick: () => openMapPicker(),
    });

    elements.useLocationButton?.addEventListener('click', detectMyLocation);

    elements.chooseAddressButton?.addEventListener('click', openMapPicker);

    elements.changeOnMapButton?.addEventListener('click', openMapPicker);

    elements.labelInputs.forEach((input) => {
        input.addEventListener('change', syncCheckoutButton);
    });

  // Restore old form values after validation error
    if (oldValues.latitude && oldValues.longitude && oldValues.address) {
        updateCoordinates(oldValues.latitude, oldValues.longitude, oldValues.placeId ?? null);
        applyFormattedAddress(oldValues.address);
        updateAddressComponents({
            province: oldValues.province,
            city: oldValues.city,
            district: oldValues.district,
            subdistrict: oldValues.subdistrict ?? oldValues.village,
            postalCode: oldValues.postalCode,
        });
        showSelectedState();
        hasValidLocation = true;
        syncCoverageForCoordinates(oldValues.latitude, oldValues.longitude);
    } else {
        // Check for confirmed address from address confirmation page
        const confirmedAddress = localStorage.getItem(`confirmed_address_${tenant.id}`);
        if (confirmedAddress) {
            try {
                const addressData = JSON.parse(confirmedAddress);
                if (addressData.latitude && addressData.longitude && addressData.formattedAddress) {
                    updateCoordinates(addressData.latitude, addressData.longitude, addressData.placeId ?? null);
                    applyFormattedAddress(addressData.formattedAddress);
                    updateAddressComponents({
                        province: addressData.province,
                        city: addressData.city,
                        district: addressData.district,
                        subdistrict: addressData.subdistrict,
                        postalCode: addressData.postalCode,
                    });
                    showSelectedState();
                    hasValidLocation = true;
                    syncCoverageForCoordinates(addressData.latitude, addressData.longitude);
                    // Clear the confirmed address after using it
                    localStorage.removeItem(`confirmed_address_${tenant.id}`);
                    return;
                }
            } catch (e) {
                console.error('Failed to parse confirmed address:', e);
            }
        }

        // Check for auto-detected location from navbar
        const storedLocation = localStorage.getItem(`delivery_location_${tenant.id}`);
        if (storedLocation) {
            try {
                const locationData = JSON.parse(storedLocation);
                // Only use if detected within last 30 minutes
                const detectedTime = new Date(locationData.detectedAt);
                const now = new Date();
                const ageMinutes = (now - detectedTime) / (1000 * 60);

                if (ageMinutes < 30 && locationData.latitude && locationData.longitude) {
                    updateCoordinates(locationData.latitude, locationData.longitude, locationData.placeId ?? null);
                    applyFormattedAddress(locationData.formattedAddress);
                    showSelectedState();
                    hasValidLocation = true;
                    syncCoverageForCoordinates(locationData.latitude, locationData.longitude);
                    return;
                }
            } catch (e) {
                console.error('Failed to parse stored location:', e);
            }
        }

        showInitialState();
        applyCoverageState(coverage.evaluate(null, null));
    }

    function showLocationPromptOnce() {
        if (!elements.permissionPrompt || localStorage.getItem(locationPromptKey) === '1' || oldValues.latitude) {
            return;
        }

        window.setTimeout(() => {
            elements.permissionPrompt.classList.remove('hidden');
        }, 500);
    }

    function closeLocationPrompt() {
        localStorage.setItem(locationPromptKey, '1');
        elements.permissionPrompt?.classList.add('hidden');
    }

    elements.permissionAllowButton?.addEventListener('click', async () => {
        closeLocationPrompt();
        await detectMyLocation();
    });

    elements.permissionLaterButton?.addEventListener('click', closeLocationPrompt);

    showLocationPromptOnce();

    syncCheckoutButton();

    return {
        detectMyLocation,
        mapPicker,
    };
}
