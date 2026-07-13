/**
 * Small read-only OpenStreetMap preview for the selected delivery location.
 */

import L from 'leaflet';

export function createMapThumbnail(wrapper, { canvas, onClick } = {}) {
    const mapContainer = canvas ?? wrapper;
    let map = null;
    let marker = null;

    const pinIcon = L.divIcon({
        className: 'map-thumbnail-pin',
        html: '<span class="material-symbols-outlined">location_on</span>',
        iconSize: [32, 32],
        iconAnchor: [16, 32],
    });

    function ensureMap() {
        if (map || !mapContainer) {
            return map;
        }

        map = L.map(mapContainer, {
            zoomControl: false,
            attributionControl: false,
            dragging: false,
            touchZoom: false,
            doubleClickZoom: false,
            scrollWheelZoom: false,
            boxZoom: false,
            keyboard: false,
        });

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
        }).addTo(map);

        return map;
    }

    function update(latitude, longitude) {
        if (!wrapper) {
            return;
        }

        const lat = Number(latitude);
        const lng = Number(longitude);

        if (!latitude || !longitude || Number.isNaN(lat) || Number.isNaN(lng)) {
            wrapper.classList.add('hidden');
            return;
        }

        wrapper.classList.remove('hidden');
        ensureMap();

        map.setView([lat, lng], 16, { animate: false });

        if (!marker) {
            marker = L.marker([lat, lng], { icon: pinIcon, interactive: false }).addTo(map);
        } else {
            marker.setLatLng([lat, lng]);
        }

        window.requestAnimationFrame(() => {
            map.invalidateSize();
        });
    }

    function hide() {
        wrapper?.classList.add('hidden');
    }

    if (onClick && wrapper) {
        wrapper.addEventListener('click', onClick);
        wrapper.setAttribute('role', 'button');
        wrapper.setAttribute('tabindex', '0');
        wrapper.setAttribute('aria-label', 'Lihat atau ubah lokasi di peta');

        wrapper.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                onClick();
            }
        });
    }

    return {
        update,
        hide,
    };
}
