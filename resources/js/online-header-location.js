/**
 * Header location auto-detect and change functionality
 */

export function initHeaderLocation(geoapifyApiKey, tenant) {
    console.log('initHeaderLocation called with:', { geoapifyApiKey, tenant });

    // Wait for DOM to be ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', () => {
            initializeLocationDetection(geoapifyApiKey, tenant);
        });
    } else {
        initializeLocationDetection(geoapifyApiKey, tenant);
    }
}

function initializeLocationDetection(geoapifyApiKey, tenant) {
    const locationText = document.getElementById('header-location-text');

    console.log('Elements found:', { locationText });

    if (!locationText) {
        console.warn('Header location text element not found');
        return;
    }

    // Auto-detect location on page load
    detectLocation();

    async function detectLocation() {
        console.log('Starting location detection...');

        // First, try to load from localStorage
        const storedLocation = localStorage.getItem(`delivery_location_${tenant.id}`);
        if (storedLocation) {
            try {
                const locationData = JSON.parse(storedLocation);
                const detectedTime = new Date(locationData.detectedAt);
                const now = new Date();
                const ageMinutes = (now - detectedTime) / (1000 * 60);

                if (ageMinutes < 30 && locationData.city) {
                    locationText.textContent = locationData.city;
                    console.log('Using stored location from localStorage:', locationData.city);
                    return;
                }
            } catch (e) {
                console.error('Failed to parse stored location:', e);
            }
        }

        if (!navigator.geolocation) {
            console.warn('Geolocation not supported');
            locationText.textContent = 'Pilih lokasi';
            return;
        }

        try {
            console.log('Requesting geolocation...');
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
            console.log('Location obtained:', { latitude, longitude });

            // Reverse geocode to get address
            console.log('Starting reverse geocoding...');
            const response = await fetch(
                `https://api.geoapify.com/v1/geocode/reverse?lat=${latitude}&lon=${longitude}&apiKey=${geoapifyApiKey}&lang=id`
            );
            const data = await response.json();
            console.log('Reverse geocoding result:', data);

            if (data.features && data.features.length > 0) {
                const feature = data.features[0];
                const city = feature.properties.city || feature.properties.county || feature.properties.state;
                const address = feature.properties.formatted || city || 'Lokasi terdeteksi';
                const placeId = feature.properties.place_id || null;

                // Show city or short address
                locationText.textContent = city || address.split(',')[0] || 'Lokasi terdeteksi';
                console.log('Location text updated:', locationText.textContent);

                // Store location data for auto-fill in checkout
                const locationData = {
                    latitude,
                    longitude,
                    formattedAddress: address,
                    placeId,
                    city,
                    detectedAt: new Date().toISOString(),
                };

                // Store in localStorage with tenant-specific key
                localStorage.setItem(`delivery_location_${tenant.id}`, JSON.stringify(locationData));
                console.log('Location stored to localStorage:', locationData);
            } else {
                locationText.textContent = 'Lokasi terdeteksi';
                console.log('No features in reverse geocoding result');
            }
        } catch (error) {
            console.error('Location detection failed:', error);
            // Show user-friendly message when geolocation is blocked
            if (error.code === 1) { // PERMISSION_DENIED
                locationText.textContent = 'Pilih lokasi';
                console.log('Geolocation permission denied, showing default text');
            } else {
                locationText.textContent = 'Lokasi tidak diketahui';
            }
        }
    }
}
