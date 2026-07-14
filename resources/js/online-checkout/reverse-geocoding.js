/**
 * Reverse geocoding via Geoapify API.
 */

export async function reverseGeocode(geoapifyApiKey, latitude, longitude) {
    const params = new URLSearchParams({
        lat: Number(latitude).toFixed(7),
        lon: Number(longitude).toFixed(7),
        apiKey: geoapifyApiKey,
        lang: 'id',
    });

    const response = await fetch(`https://api.geoapify.com/v1/geocode/reverse?${params.toString()}`);

    const payload = await response.json().catch(() => ({}));

    if (!response.ok || !payload.features || !payload.features.length) {
        throw new Error('Alamat tidak dapat ditentukan dari lokasi ini.');
    }

    const feature = payload.features[0];
    const props = feature.properties;

    return {
        formattedAddress: props.formatted || '',
        address: props.formatted || '',
        province: props.state ?? '',
        city: props.city ?? props.county ?? '',
        district: props.district ?? props.suburb ?? '',
        subdistrict: props.subdistrict ?? props.village ?? '',
        village: props.village ?? props.subdistrict ?? '',
        postalCode: props.postcode ?? '',
        placeId: props.place_id ?? null,
        coverage: null,
    };
}
