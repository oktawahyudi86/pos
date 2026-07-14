import { loadGoogleMaps } from './google-maps-loader';

const DEFAULT_CENTER = { latitude: -7.7956, longitude: 110.3695 };

export function createMapPicker({
    googleMapsApiKey,
    modal,
    mapContainer,
    searchInput,
    searchResultsContainer,
    confirmButton,
    closeButtons = [],
    onLocationChange,
    onCoordinatesPreview = null,
    onOpen = null,
    onClose = null,
    initialPosition = null,
}) {
    let maps = null;
    let map = null;
    let marker = null;
    let geocoder = null;
    let autocomplete = null;
    let currentPlaceId = null;
    let currentFormattedAddress = null;
    let isOpen = false;
    let isConfirming = false;
    let resolveMoveTimer = null;

    function setSearchMessage(message, tone = 'muted') {
        if (!searchResultsContainer) {
            return;
        }

        searchResultsContainer.innerHTML = `<p class="map-search-empty ${tone === 'error' ? 'text-[#93000a]' : ''}">${message}</p>`;
        searchResultsContainer.classList.remove('hidden');
    }

    function clearSearchMessage() {
        if (!searchResultsContainer) {
            return;
        }

        searchResultsContainer.innerHTML = '';
        searchResultsContainer.classList.add('hidden');
    }

    function setModalActionsDisabled(disabled) {
        closeButtons.forEach((button) => {
            if (!button) return;
            button.disabled = disabled;
            button.classList.toggle('opacity-50', disabled);
            button.classList.toggle('pointer-events-none', disabled);
        });
    }

    function setConfirmLoading(isLoading) {
        if (!confirmButton) return;

        isConfirming = isLoading;
        confirmButton.disabled = isLoading;
        confirmButton.dataset.loading = isLoading ? '1' : '0';
        confirmButton.classList.toggle('opacity-80', isLoading);
        confirmButton.classList.toggle('pointer-events-none', isLoading);

        const label = confirmButton.querySelector('[data-map-confirm-label]');
        const loading = confirmButton.querySelector('[data-map-confirm-loading]');

        label?.classList.toggle('hidden', isLoading);
        loading?.classList.toggle('hidden', !isLoading);
        loading?.classList.toggle('flex', isLoading);

        setModalActionsDisabled(isLoading);
    }

    function getCenterCoordinates() {
        if (!map) {
            return null;
        }

        const center = map.getCenter();

        return {
            latitude: center.lat(),
            longitude: center.lng(),
            placeId: currentPlaceId,
            formattedAddress: currentFormattedAddress,
        };
    }

    function moveTo(latitude, longitude, zoom = 17) {
        const position = { lat: Number(latitude), lng: Number(longitude) };

        map.setCenter(position);
        map.setZoom(zoom);
        marker.setPosition(position);
    }

    function previewCoordinates(extra = {}) {
        const coordinates = getCenterCoordinates();

        if (!coordinates || !isOpen) {
            return;
        }

        onCoordinatesPreview?.({
            ...coordinates,
            ...extra,
        });
    }

    function reverseCurrentCenter() {
        const coordinates = getCenterCoordinates();

        if (!coordinates || !geocoder || !isOpen) {
            return;
        }

        clearTimeout(resolveMoveTimer);
        resolveMoveTimer = setTimeout(() => {
            const location = {
                lat: Number(coordinates.latitude),
                lng: Number(coordinates.longitude),
            };

            geocoder.geocode({ location }, (results, status) => {
                if (status !== 'OK' || !results?.length || !isOpen) {
                    currentPlaceId = null;
                    currentFormattedAddress = null;
                    onLocationChange?.({
                        ...coordinates,
                    });
                    return;
                }

                const result = results[0];
                currentPlaceId = result.place_id ?? null;
                currentFormattedAddress = result.formatted_address ?? null;

                onLocationChange?.({
                    latitude: coordinates.latitude,
                    longitude: coordinates.longitude,
                    placeId: currentPlaceId,
                    formattedAddress: currentFormattedAddress,
                });
            });
        }, 350);
    }

    async function ensureMapReady() {
        if (map) {
            return map;
        }

        maps = await loadGoogleMaps(googleMapsApiKey);
        geocoder = new maps.Geocoder();

        const defaultCenter = initialPosition
            ? { lat: Number(initialPosition.latitude), lng: Number(initialPosition.longitude) }
            : { lat: DEFAULT_CENTER.latitude, lng: DEFAULT_CENTER.longitude };

        map = new maps.Map(mapContainer, {
            center: defaultCenter,
            zoom: initialPosition ? 17 : 12,
            mapTypeControl: false,
            fullscreenControl: false,
            streetViewControl: false,
            clickableIcons: false,
        });

        marker = new maps.Marker({
            map,
            position: defaultCenter,
        });

        map.addListener('click', (event) => {
            currentPlaceId = null;
            currentFormattedAddress = null;
            moveTo(event.latLng.lat(), event.latLng.lng(), Math.max(map.getZoom(), 16));
            previewCoordinates();
            reverseCurrentCenter();
        });

        map.addListener('dragstart', () => {
            currentPlaceId = null;
            currentFormattedAddress = null;
        });

        map.addListener('center_changed', () => {
            marker.setPosition(map.getCenter());
        });

        map.addListener('idle', () => {
            previewCoordinates();
            reverseCurrentCenter();
        });

        if (searchInput) {
            autocomplete = new maps.places.Autocomplete(searchInput, {
                fields: ['formatted_address', 'geometry', 'place_id', 'name'],
                componentRestrictions: { country: 'id' },
            });

            autocomplete.addListener('place_changed', () => {
                const place = autocomplete.getPlace();

                if (!place.geometry?.location) {
                    setSearchMessage('Alamat tidak ditemukan. Coba kata kunci lain.', 'error');
                    return;
                }

                clearSearchMessage();
                currentPlaceId = place.place_id ?? null;
                currentFormattedAddress = place.formatted_address || place.name || null;

                moveTo(place.geometry.location.lat(), place.geometry.location.lng(), 17);
                previewCoordinates({
                    placeId: currentPlaceId,
                    formattedAddress: currentFormattedAddress,
                });
                onLocationChange?.({
                    latitude: place.geometry.location.lat(),
                    longitude: place.geometry.location.lng(),
                    placeId: currentPlaceId,
                    formattedAddress: currentFormattedAddress,
                });
            });
        }

        return map;
    }

    async function open(position = null) {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        isOpen = true;
        setConfirmLoading(false);
        setSearchMessage('Memuat Google Maps...');
        onOpen?.();

        try {
            await ensureMapReady();
            clearSearchMessage();
        } catch (error) {
            setSearchMessage(error.message || 'Google Maps belum bisa dimuat.', 'error');
            confirmButton?.setAttribute('disabled', 'disabled');
            confirmButton?.classList.add('opacity-60', 'pointer-events-none');
            return;
        }

        const latitude = position ? Number(position.latitude) : DEFAULT_CENTER.latitude;
        const longitude = position ? Number(position.longitude) : DEFAULT_CENTER.longitude;
        const zoom = position ? 17 : 12;

        currentPlaceId = position?.placeId ?? null;
        currentFormattedAddress = position?.formattedAddress ?? null;

        if (searchInput) {
            searchInput.value = position?.formattedAddress ?? '';
        }

        window.requestAnimationFrame(() => {
            maps.event.trigger(map, 'resize');
            moveTo(latitude, longitude, zoom);
            previewCoordinates();
            reverseCurrentCenter();
        });
    }

    function close() {
        isOpen = false;
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        clearSearchMessage();
        setConfirmLoading(false);
        onClose?.();
    }

    closeButtons.forEach((button) => {
        button?.addEventListener('click', () => {
            if (!isConfirming) {
                close();
            }
        });
    });

    confirmButton?.addEventListener('click', async () => {
        if (isConfirming || confirmButton?.disabled) {
            return;
        }

        const coordinates = getCenterCoordinates();

        if (!coordinates) {
            return;
        }

        setConfirmLoading(true);

        try {
            await onLocationChange?.({
                ...coordinates,
                confirmed: true,
            });
            close();
        } catch {
            setConfirmLoading(false);
        }
    });

    return {
        open,
        close,
        isApiAvailable: Boolean(googleMapsApiKey),
    };
}
