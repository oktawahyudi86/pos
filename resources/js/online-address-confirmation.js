/**
 * Address confirmation page logic
 * Similar to delivery-location.js but for address confirmation before checkout
 */

import { createLeafletMapPicker } from './online-checkout/leaflet-map-picker';
import { createMapThumbnail } from './online-checkout/map-thumbnail';

export function initAddressConfirmation(tenant, config) {
    const elements = {
        initialState: document.getElementById('location-initial-state'),
        selectedState: document.getElementById('location-selected-state'),
        latitudeInput: document.getElementById('delivery-latitude'),
        longitudeInput: document.getElementById('delivery-longitude'),
        placeIdInput: document.getElementById('delivery-place-id'),
        addressInput: document.getElementById('formatted-address-input'),
        provinceInput: document.getElementById('province-input'),
        cityInput: document.getElementById('city-input'),
        districtInput: document.getElementById('district-input'),
        subdistrictInput: document.getElementById('subdistrict-input'),
        postalCodeInput: document.getElementById('postal-code-input'),
        addressText: document.getElementById('address-text'),
        addressSkeleton: document.getElementById('address-display-skeleton'),
        addressContent: document.getElementById('address-display-content'),
        addressThumbnail: document.getElementById('address-map-thumbnail'),
        addressThumbnailCanvas: document.getElementById('address-map-thumbnail-canvas'),
        chooseAddressButton: document.getElementById('choose-address-button'),
        useLocationButton: document.getElementById('use-my-location-button'),
        changeOnMapButton: document.getElementById('change-on-map-button'),
        confirmAddressButton: document.getElementById('confirm-address-button'),
        coverageBanner: document.getElementById('coverage-banner'),
        coverageSuccess: document.getElementById('coverage-success'),
        coverageLoading: document.getElementById('coverage-loading'),
        permissionPrompt: document.getElementById('location-permission-prompt'),
        permissionAllowButton: document.getElementById('location-permission-allow'),
        permissionLaterButton: document.getElementById('location-permission-later'),
        mapModal: document.getElementById('map-picker-modal'),
        mapCanvas: document.getElementById('map-picker-canvas'),
        mapSearchInput: document.getElementById('map-search-input'),
        mapSearchResults: document.getElementById('map-search-results'),
        mapConfirmButton: document.getElementById('map-confirm-button'),
        mapCancelButton: document.getElementById('map-cancel-button'),
        mapCloseButton: document.getElementById('map-close-button'),
        mapCoverageBanner: document.getElementById('map-coverage-banner'),
        mapCoverageSuccess: document.getElementById('map-coverage-success'),
        mapCoverageLoading: document.getElementById('map-coverage-loading'),
    };

    const {
        geoapifyApiKey,
        deliveryCoverageConfig,
        outOfCoverageMessage,
        checkoutUrl,
        oldValues,
    } = config;

    let mapThumbnail = null;
    let mapPicker = null;
    let isResolvingAddress = false;
    let isValidatingCoverage = false;
    let hasValidLocation = false;
    let coverageState = { within: true, message: null, active: false };

    // Initialize map thumbnail
    if (elements.addressThumbnail && elements.addressThumbnailCanvas) {
        mapThumbnail = createMapThumbnail(elements.addressThumbnail, {
            canvas: elements.addressThumbnailCanvas,
            onClick: () => openMapPicker(),
        });
    }

    // Initialize map picker
    if (elements.mapCanvas && elements.mapSearchInput) {
        mapPicker = createLeafletMapPicker({
            geoapifyApiKey,
            modal: elements.mapModal,
            mapContainer: elements.mapCanvas,
            searchInput: elements.mapSearchInput,
            searchResultsContainer: elements.mapSearchResults,
            confirmButton: elements.mapConfirmButton,
            closeButtons: [elements.mapCloseButton, elements.mapCancelButton],
            onLocationChange: handleMapLocationChange,
            onCoordinatesPreview: ({ latitude, longitude }) => {
                syncCoverageForCoordinates(latitude, longitude);
            },
            initialPosition: oldValues.latitude && oldValues.longitude
                ? { latitude: oldValues.latitude, longitude: oldValues.longitude, placeId: oldValues.placeId }
                : null,
        });
    }

    function handleMapLocationChange({ latitude, longitude, placeId, formattedAddress, confirmed }) {
        if (confirmed) {
            updateCoordinates(latitude, longitude, placeId);
            applyFormattedAddress(formattedAddress);
            showSelectedState();
            hasValidLocation = true;
            syncCoverageForCoordinates(latitude, longitude);
            return;
        }

        updateCoordinates(latitude, longitude, placeId);
        showSelectedState();
        hasValidLocation = true;
        syncCoverageForCoordinates(latitude, longitude);
    }

    function updateCoordinates(latitude, longitude, placeId = null) {
        elements.latitudeInput.value = latitude;
        elements.longitudeInput.value = longitude;
        if (placeId) {
            elements.placeIdInput.value = placeId;
        }
        mapThumbnail?.show(latitude, longitude);
    }

    function applyFormattedAddress(address) {
        elements.addressInput.value = address;
        elements.addressText.textContent = address;
    }

    function showInitialState() {
        elements.initialState?.classList.remove('hidden');
        elements.selectedState?.classList.add('hidden');
        mapThumbnail?.hide();
    }

    function showSelectedState() {
        elements.initialState?.classList.add('hidden');
        elements.selectedState?.classList.remove('hidden');
    }

    function openMapPicker() {
        mapPicker?.open({
            latitude: elements.latitudeInput.value,
            longitude: elements.longitudeInput.value,
            placeId: elements.placeIdInput.value,
            formattedAddress: elements.addressInput.value,
        });
    }

    async function detectMyLocation() {
        if (!navigator.geolocation) {
            alert('Geolocation tidak didukung oleh browser Anda.');
            return;
        }

        try {
            const position = await new Promise((resolve, reject) => {
                navigator.geolocation.getCurrentPosition(
                    resolve,
                    reject,
                    {
                        enableHighAccuracy: true,
                        timeout: 10000,
                        maximumAge: 0,
                    }
                );
            });

            const { latitude, longitude } = position.coords;

            // Reverse geocode
            const response = await fetch(
                `https://api.geoapify.com/v1/geocode/reverse?lat=${latitude}&lon=${longitude}&apiKey=${geoapifyApiKey}&lang=id`
            );
            const data = await response.json();

            if (data.features && data.features.length > 0) {
                const feature = data.features[0];
                const formattedAddress = feature.properties.formatted;
                const placeId = feature.properties.place_id;

                updateCoordinates(latitude, longitude, placeId);
                applyFormattedAddress(formattedAddress);
                showSelectedState();
                hasValidLocation = true;
                syncCoverageForCoordinates(latitude, longitude);
            } else {
                updateCoordinates(latitude, longitude);
                showSelectedState();
                hasValidLocation = true;
                syncCoverageForCoordinates(latitude, longitude);
            }
        } catch (error) {
            console.error('Location detection failed:', error);
            alert('Gagal mendeteksi lokasi. Silakan pilih alamat secara manual.');
        }
    }

    async function syncCoverageForCoordinates(latitude, longitude) {
        if (!latitude || !longitude) {
            return;
        }

        isValidatingCoverage = true;
        elements.coverageLoading?.classList.remove('hidden');
        elements.coverageBanner?.classList.add('hidden');
        elements.coverageSuccess?.classList.add('hidden');
        syncConfirmButton();

        try {
            const response = await fetch(
                `${config.deliveryCoverageUrl}?latitude=${latitude}&longitude=${longitude}`,
                {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json',
                    },
                }
            );
            const data = await response.json();

            coverageState = {
                within: data.within_coverage ?? data.within ?? false,
                message: data.message ?? null,
                active: data.active ?? true,
            };

            applyCoverageState();
        } catch (error) {
            console.error('Coverage check failed:', error);
            coverageState = { within: false, message: 'Gagal memvalidasi area pengiriman.', active: true };
            applyCoverageState();
        } finally {
            isValidatingCoverage = false;
            elements.coverageLoading?.classList.add('hidden');
            syncConfirmButton();
        }
    }

    function applyCoverageState() {
        const blocked = coverageState.active && !coverageState.within;

        elements.coverageBanner?.classList.toggle('hidden', !blocked || !coverageState.message);
        if (elements.coverageBanner) {
            elements.coverageBanner.textContent = coverageState.message;
        }

        elements.coverageSuccess?.classList.toggle('hidden', blocked || !coverageState.active);
        syncConfirmButton();
    }

    function syncConfirmButton() {
        const hasCoords = elements.latitudeInput.value && elements.longitudeInput.value;
        const blocked = coverageState.active && !coverageState.within;

        elements.confirmAddressButton?.toggleAttribute('disabled', !hasCoords || blocked);
        elements.confirmAddressButton?.classList.toggle('opacity-60', !hasCoords || blocked);
        elements.confirmAddressButton?.classList.toggle('pointer-events-none', !hasCoords || blocked);
    }

    // Event listeners
    elements.chooseAddressButton?.addEventListener('click', openMapPicker);
    elements.useLocationButton?.addEventListener('click', detectMyLocation);
    elements.changeOnMapButton?.addEventListener('click', openMapPicker);

    elements.confirmAddressButton?.addEventListener('click', () => {
        // Store confirmed address in localStorage and redirect to checkout
        const confirmedAddress = {
            latitude: elements.latitudeInput.value,
            longitude: elements.longitudeInput.value,
            placeId: elements.placeIdInput.value,
            formattedAddress: elements.addressInput.value,
            province: elements.provinceInput.value,
            city: elements.cityInput.value,
            district: elements.districtInput.value,
            subdistrict: elements.subdistrictInput.value,
            postalCode: elements.postalCodeInput.value,
        };

        localStorage.setItem(`confirmed_address_${tenant.id}`, JSON.stringify(confirmedAddress));
        window.location.href = checkoutUrl;
    });

    // Check for auto-detected location from navbar
    const storedLocation = localStorage.getItem(`delivery_location_${tenant.id}`);
    if (storedLocation) {
        try {
            const locationData = JSON.parse(storedLocation);
            const detectedTime = new Date(locationData.detectedAt);
            const now = new Date();
            const ageMinutes = (now - detectedTime) / (1000 * 60);

            if (ageMinutes < 30 && locationData.latitude && locationData.longitude) {
                updateCoordinates(locationData.latitude, locationData.longitude, locationData.placeId ?? null);
                applyFormattedAddress(locationData.formattedAddress);
                showSelectedState();
                hasValidLocation = true;
                syncCoverageForCoordinates(locationData.latitude, locationData.longitude);
            }
        } catch (e) {
            console.error('Failed to parse stored location:', e);
            showInitialState();
        }
    } else {
        showInitialState();
    }
}
