import L from 'leaflet';

const DEFAULT_CENTER = { latitude: -7.7956, longitude: 110.3695 };

export function createLeafletMapPicker({
    geoapifyApiKey,
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
    let map = null;
    let marker = null;
    let currentPlaceId = null;
    let currentFormattedAddress = null;
    let isOpen = false;
    let isConfirming = false;
    let resolveMoveTimer = null;
    let searchAbortController = null;

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
            latitude: center.lat,
            longitude: center.lng,
            placeId: currentPlaceId,
            formattedAddress: currentFormattedAddress,
        };
    }

    function moveTo(latitude, longitude, zoom = 17) {
        const position = [Number(latitude), Number(longitude)];

        map.setView(position, zoom);
        if (marker) {
            marker.setLatLng(position);
        }
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

    async function reverseCurrentCenter() {
        const coordinates = getCenterCoordinates();

        if (!coordinates || !isOpen) {
            return;
        }

        clearTimeout(resolveMoveTimer);
        resolveMoveTimer = setTimeout(async () => {
            try {
                const response = await fetch(
                    `https://api.geoapify.com/v1/geocode/reverse?lat=${coordinates.latitude}&lon=${coordinates.longitude}&apiKey=${geoapifyApiKey}&lang=id`
                );
                const data = await response.json();

                if (!data.features || !data.features.length || !isOpen) {
                    currentPlaceId = null;
                    currentFormattedAddress = null;
                    onLocationChange?.({
                        ...coordinates,
                    });
                    return;
                }

                const feature = data.features[0];
                currentPlaceId = feature.properties.place_id || null;
                currentFormattedAddress = feature.properties.formatted || null;

                onLocationChange?.({
                    latitude: coordinates.latitude,
                    longitude: coordinates.longitude,
                    placeId: currentPlaceId,
                    formattedAddress: currentFormattedAddress,
                });
            } catch (error) {
                currentPlaceId = null;
                currentFormattedAddress = null;
                onLocationChange?.({
                    ...coordinates,
                });
            }
        }, 350);
    }

    async function searchAddress(query) {
        if (!query || query.length < 3) {
            clearSearchMessage();
            return;
        }

        // Abort previous search
        if (searchAbortController) {
            searchAbortController.abort();
        }

        searchAbortController = new AbortController();

        try {
            const response = await fetch(
                `https://api.geoapify.com/v1/geocode/autocomplete?text=${encodeURIComponent(query)}&apiKey=${geoapifyApiKey}&lang=id&limit=5`,
                { signal: searchAbortController.signal }
            );

            const data = await response.json();

            if (!data.features || data.features.length === 0) {
                setSearchMessage('Alamat tidak ditemukan. Coba kata kunci lain.', 'error');
                return;
            }

            renderSearchResults(data.features);
        } catch (error) {
            if (error.name !== 'AbortError') {
                setSearchMessage('Pencarian gagal. Coba lagi.', 'error');
            }
        }
    }

    function renderSearchResults(features) {
        if (!searchResultsContainer) {
            return;
        }

        searchResultsContainer.innerHTML = features.map((feature, index) => {
            const props = feature.properties;
            const address = props.formatted || props.name || 'Alamat tidak diketahui';
            return `
                <button type="button" class="map-search-result" data-index="${index}" data-lat="${props.lat}" data-lon="${props.lon}" data-place-id="${props.place_id || ''}" data-address="${address.replace(/"/g, '&quot;')}">
                    <span class="material-symbols-outlined map-search-result-icon">location_on</span>
                    <span class="map-search-result-text">${address}</span>
                </button>
            `;
        }).join('');

        searchResultsContainer.classList.remove('hidden');

        // Add click handlers
        searchResultsContainer.querySelectorAll('.map-search-result').forEach((button) => {
            button.addEventListener('click', () => {
                const lat = parseFloat(button.dataset.lat);
                const lon = parseFloat(button.dataset.lon);
                const placeId = button.dataset.placeId || null;
                const address = button.dataset.address;

                selectSearchResult(lat, lon, placeId, address);
            });
        });
    }

    function selectSearchResult(lat, lon, placeId, address) {
        clearSearchMessage();
        currentPlaceId = placeId;
        currentFormattedAddress = address;

        if (searchInput) {
            searchInput.value = address;
        }

        moveTo(lat, lon, 17);
        previewCoordinates({
            placeId: currentPlaceId,
            formattedAddress: currentFormattedAddress,
        });
        onLocationChange?.({
            latitude: lat,
            longitude: lon,
            placeId: currentPlaceId,
            formattedAddress: currentFormattedAddress,
        });
    }

    function ensureMapReady() {
        if (map) {
            return map;
        }

        const defaultCenter = initialPosition
            ? [Number(initialPosition.latitude), Number(initialPosition.longitude)]
            : [DEFAULT_CENTER.latitude, DEFAULT_CENTER.longitude];

        map = L.map(mapContainer, {
            center: defaultCenter,
            zoom: initialPosition ? 17 : 12,
            zoomControl: false,
        });

        // Add zoom control to bottom-right
        L.control.zoom({
            position: 'bottomright'
        }).addTo(map);

        // Add OpenStreetMap tiles
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
            maxZoom: 19,
        }).addTo(map);

        // Create custom marker icon
        const customIcon = L.divIcon({
            className: 'custom-map-marker',
            html: '<span class="material-symbols-outlined" style="font-size: 42px; color: #ce2418; font-variation-settings: \'FILL\' 1;">location_on</span>',
            iconSize: [42, 42],
            iconAnchor: [21, 42],
        });

        marker = L.marker(defaultCenter, { icon: customIcon, draggable: false }).addTo(map);

        // Click on map to move marker
        map.on('click', (event) => {
            currentPlaceId = null;
            currentFormattedAddress = null;
            const { lat, lng } = event.latlng;
            moveTo(lat, lng, Math.max(map.getZoom(), 16));
            previewCoordinates();
            reverseCurrentCenter();
        });

        // Update marker position when map is dragged
        map.on('move', () => {
            if (marker) {
                marker.setLatLng(map.getCenter());
            }
        });

        // Trigger preview and reverse geocoding when map becomes idle
        map.on('moveend', () => {
            previewCoordinates();
            reverseCurrentCenter();
        });

        // Initialize custom search
        if (searchInput) {
            let searchDebounce = null;

            searchInput.addEventListener('input', (e) => {
                clearTimeout(searchDebounce);
                searchDebounce = setTimeout(() => {
                    searchAddress(e.target.value.trim());
                }, 300);
            });

            searchInput.addEventListener('keydown', (e) => {
                if (e.key === 'Escape') {
                    clearSearchMessage();
                }
            });
        }

        // Close search results when clicking outside
        document.addEventListener('click', (e) => {
            if (searchInput && searchResultsContainer &&
                !searchInput.contains(e.target) &&
                !searchResultsContainer.contains(e.target)) {
                clearSearchMessage();
            }
        });

        return map;
    }

    async function open(position = null) {
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        isOpen = true;
        setConfirmLoading(false);
        setSearchMessage('Memuat peta...');
        onOpen?.();

        try {
            ensureMapReady();
            clearSearchMessage();
        } catch (error) {
            setSearchMessage(error.message || 'Peta belum bisa dimuat.', 'error');
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

        // Wait for modal to be visible before invalidating map size
        setTimeout(() => {
            map.invalidateSize();
            moveTo(latitude, longitude, zoom);
            // Trigger preview to update coverage state and enable button
            previewCoordinates();
            reverseCurrentCenter();
        }, 100);
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
        isApiAvailable: Boolean(geoapifyApiKey),
    };
}
