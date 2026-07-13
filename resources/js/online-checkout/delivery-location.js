import { createCoverageEvaluator } from './coverage';
import { getCurrentPosition, getGeolocationErrorMessage, isGeolocationSupported } from './geolocation';
import { createMapPicker } from './map-picker';
import { createMapThumbnail } from './map-thumbnail';
import { reverseGeocode } from './reverse-geocoding';

const LABEL_OPTIONS = [
    { value: 'rumah', label: 'Rumah', icon: 'home' },
    { value: 'kantor', label: 'Kantor', icon: 'work' },
    { value: 'lainnya', label: 'Lainnya', icon: 'location_on' },
];

export function initDeliveryLocation(config) {
    const {
        reverseGeocodeUrl,
        geocodeSearchUrl,
        deliveryCoverageConfig,
        outOfCoverageMessage,
        oldValues = {},
    } = config;

    const elements = {
        initialState: document.getElementById('location-initial-state'),
        selectedState: document.getElementById('location-selected-state'),
        useLocationButton: document.getElementById('use-my-location-button'),
        changeOnMapButton: document.getElementById('change-on-map-button'),
        addressCard: document.getElementById('address-display-card'),
        addressText: document.getElementById('address-display-text'),
        addressSkeleton: document.getElementById('address-display-skeleton'),
        addressMapThumbnail: document.getElementById('address-map-thumbnail'),
        addressMapThumbnailCanvas: document.getElementById('address-map-thumbnail-canvas'),
        coverageBanner: document.getElementById('coverage-banner'),
        coverageSuccess: document.getElementById('coverage-success'),
        locationHint: document.getElementById('location-hint'),
        checkoutSubmitButton: document.getElementById('checkout-submit-button'),
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
    let hasValidLocation = false;
    let coverageState = { within: true, message: null };
    let mapThumbnail = null;
    let mapPicker = null;

    function setAddressLoading(isLoading) {
        isResolvingAddress = isLoading;
        elements.addressSkeleton?.classList.toggle('hidden', !isLoading);
        elements.addressText?.classList.toggle('opacity-0', isLoading);
        elements.addressText?.classList.toggle('animate-pulse', isLoading);
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

    function applyCoverageState(nextCoverage) {
        coverageState = {
            within: nextCoverage?.within ?? true,
            message: nextCoverage?.message ?? null,
        };

        const blocked = coverage.isRestrictionActive() && !coverageState.within;
        const hasCoords = Boolean(elements.latitudeInput.value && elements.longitudeInput.value);

        elements.coverageBanner?.classList.toggle('hidden', !blocked || !coverageState.message);
        if (elements.coverageBanner) {
            elements.coverageBanner.textContent = coverageState.message || '';
        }

        const showSuccess = hasCoords && hasValidLocation && !blocked && coverage.isRestrictionActive();
        elements.coverageSuccess?.classList.toggle('hidden', !showSuccess);

        if (elements.locationHint) {
            if (!hasCoords) {
                elements.locationHint.textContent = 'Pilih lokasi pengantaran agar kurir dapat menemukan alamat Anda dengan akurat.';
            } else if (blocked) {
                elements.locationHint.textContent = 'Lokasi di luar jangkauan pengantaran. Geser pin di peta atau pilih titik lain.';
            } else if (isResolvingAddress) {
                elements.locationHint.textContent = 'Menentukan alamat dari koordinat...';
            } else {
                elements.locationHint.textContent = 'Alamat diambil otomatis dari koordinat GPS. Tambahkan detail alamat jika perlu.';
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
        const disabled = cartEmpty || !locationReady || blocked;

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
        updateCoordinates(latitude, longitude, placeId, { refreshThumbnail });
        showSelectedState();
        setAddressLoading(true);

        try {
            if (skipReverseGeocode && formattedAddress) {
                applyFormattedAddress(formattedAddress);
                applyCoverageState(coverage.evaluate(latitude, longitude));
            } else {
                const geocoded = await reverseGeocode(reverseGeocodeUrl, latitude, longitude);
                applyFormattedAddress(geocoded.formattedAddress);
                updateAddressComponents(geocoded);

                if (geocoded.placeId) {
                    elements.placeIdInput.value = geocoded.placeId;
                }

                if (geocoded.coverage) {
                    applyCoverageState(coverage.normalize(geocoded.coverage));
                } else {
                    applyCoverageState(coverage.evaluate(latitude, longitude));
                }
            }

            hasValidLocation = true;
        } catch (error) {
            hasValidLocation = Boolean(formattedAddress);
            applyFormattedAddress(formattedAddress || '');
            applyCoverageState(coverage.evaluate(latitude, longitude));

            if (!formattedAddress) {
                elements.locationHint.textContent = error.message || 'Alamat tidak dapat ditentukan. Coba geser pin di peta.';
            }
        } finally {
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
            applyCoverageState({
                within: false,
                message: coverage.isRestrictionActive()
                    ? 'Aktifkan lokasi HP untuk memastikan alamat pengantaran berada dalam jangkauan kami.'
                    : null,
            });
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
        } : null);
    }

    mapPicker = createMapPicker({
        geocodeSearchUrl,
        modal: elements.mapModal,
        mapContainer: elements.mapContainer,
        searchInput: elements.mapSearchInput,
        searchResultsContainer: elements.mapSearchResults,
        confirmButton: elements.mapConfirmButton,
        closeButtons: elements.mapCloseButtons,
        onOpen: () => {
            mapThumbnail?.hide();
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

            if (formattedAddress) {
                applyFormattedAddress(formattedAddress);
            }

            updateCoordinates(latitude, longitude, placeId, { refreshThumbnail: false });
            showSelectedState();
            setAddressLoading(true);

            try {
                const geocoded = await reverseGeocode(reverseGeocodeUrl, latitude, longitude);
                applyFormattedAddress(geocoded.formattedAddress);
                updateAddressComponents(geocoded);

                if (geocoded.placeId) {
                    elements.placeIdInput.value = geocoded.placeId;
                }

                applyCoverageState(geocoded.coverage ? coverage.normalize(geocoded.coverage) : coverage.evaluate(latitude, longitude));
                hasValidLocation = true;
            } catch {
                applyCoverageState(coverage.evaluate(latitude, longitude));
            } finally {
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
        applyCoverageState(coverage.evaluate(oldValues.latitude, oldValues.longitude));
    } else {
        showInitialState();
        applyCoverageState(coverage.evaluate(null, null));
    }

    syncCheckoutButton();

    return {
        detectMyLocation,
        mapPicker,
    };
}
