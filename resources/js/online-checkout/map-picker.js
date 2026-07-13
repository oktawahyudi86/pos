/**
 * Fullscreen OpenStreetMap location picker (Leaflet) with Nominatim search.
 */

import L from 'leaflet';
import 'leaflet/dist/leaflet.css';
import { createPlaceSearch } from './place-search';

const DEFAULT_CENTER = { latitude: -7.7956, longitude: 110.3695 };

export function createMapPicker({
    geocodeSearchUrl,
    modal,
    mapContainer,
    searchInput,
    searchResultsContainer,
    confirmButton,
    closeButtons = [],
    onLocationChange,
    onOpen = null,
    onClose = null,
    initialPosition = null,
}) {
    let map = null;
    let placeSearch = null;
    let currentPlaceId = null;
    let isOpen = false;
    let debounceTimer = null;

    function getCenterCoordinates() {
        if (!map) {
            return null;
        }

        const center = map.getCenter();

        return {
            latitude: center.lat,
            longitude: center.lng,
            placeId: currentPlaceId,
        };
    }

    function emitLocationChange(extra = {}) {
        const coordinates = getCenterCoordinates();

        if (!coordinates || !isOpen) {
            return;
        }

        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            onLocationChange?.({
                ...coordinates,
                ...extra,
            });
        }, 450);
    }

    function ensureMapReady() {
        if (map) {
            return map;
        }

        const defaultCenter = initialPosition
            ? [Number(initialPosition.latitude), Number(initialPosition.longitude)]
            : [DEFAULT_CENTER.latitude, DEFAULT_CENTER.longitude];

        map = L.map(mapContainer, {
            zoomControl: true,
            attributionControl: true,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a>',
            maxZoom: 19,
        }).addTo(map);

        map.setView(defaultCenter, initialPosition ? 17 : 12);

        map.on('movestart', () => {
            currentPlaceId = null;
        });

        map.on('moveend', () => emitLocationChange());

        if (searchInput && searchResultsContainer && geocodeSearchUrl) {
            placeSearch = createPlaceSearch({
                searchInput,
                resultsContainer: searchResultsContainer,
                searchUrl: geocodeSearchUrl,
                onSelect: (result) => {
                    currentPlaceId = result.place_id ?? null;
                    map.setView([result.latitude, result.longitude], 17);

                    onLocationChange?.({
                        latitude: result.latitude,
                        longitude: result.longitude,
                        placeId: currentPlaceId,
                        formattedAddress: result.formatted_address,
                    });
                },
            });
        }

        return map;
    }

    function open(position = null) {
        ensureMapReady();
        isOpen = true;
        modal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden');
        onOpen?.();

        const latitude = position ? Number(position.latitude) : DEFAULT_CENTER.latitude;
        const longitude = position ? Number(position.longitude) : DEFAULT_CENTER.longitude;
        const zoom = position ? 17 : 12;

        currentPlaceId = position?.placeId ?? null;
        placeSearch?.clear();

        window.requestAnimationFrame(() => {
            map.invalidateSize();
            map.setView([latitude, longitude], zoom, { animate: false });
            emitLocationChange();
        });
    }

    function close() {
        isOpen = false;
        modal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden');
        placeSearch?.close();
        onClose?.();
    }

    closeButtons.forEach((button) => {
        button?.addEventListener('click', close);
    });

    confirmButton?.addEventListener('click', async () => {
        const coordinates = getCenterCoordinates();

        if (coordinates) {
            await onLocationChange?.({
                ...coordinates,
                confirmed: true,
            });
        }

        close();
    });

    return {
        open,
        close,
        isApiAvailable: true,
    };
}
