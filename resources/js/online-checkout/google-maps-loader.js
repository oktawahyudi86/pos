let googleMapsPromise = null;

export function loadGoogleMaps(apiKey) {
    if (window.google?.maps?.places) {
        return Promise.resolve(window.google.maps);
    }

    if (!apiKey) {
        return Promise.reject(new Error('Google Maps API key belum dikonfigurasi.'));
    }

    if (googleMapsPromise) {
        return googleMapsPromise;
    }

    googleMapsPromise = new Promise((resolve, reject) => {
        const callbackName = `initGoogleMaps${Date.now()}`;
        const script = document.createElement('script');
        const params = new URLSearchParams({
            key: apiKey,
            libraries: 'places',
            language: 'id',
            region: 'ID',
            callback: callbackName,
        });

        window[callbackName] = () => {
            delete window[callbackName];
            resolve(window.google.maps);
        };

        script.src = `https://maps.googleapis.com/maps/api/js?${params.toString()}`;
        script.async = true;
        script.defer = true;
        script.onerror = () => {
            delete window[callbackName];
            googleMapsPromise = null;
            reject(new Error('Google Maps gagal dimuat.'));
        };

        document.head.appendChild(script);
    });

    return googleMapsPromise;
}
