/**
 * Server-side reverse geocoding via the tenant reverse-geocode endpoint.
 */

export async function reverseGeocode(endpoint, latitude, longitude) {
    const params = new URLSearchParams({
        latitude: Number(latitude).toFixed(7),
        longitude: Number(longitude).toFixed(7),
    });

    const response = await fetch(`${endpoint}?${params.toString()}`, {
        headers: {
            Accept: 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
        },
    });

    const payload = await response.json().catch(() => ({}));

    if (!response.ok) {
        throw new Error(payload.message || 'Alamat tidak dapat ditentukan dari lokasi ini.');
    }

    return {
        formattedAddress: payload.formatted_address || payload.address || '',
        address: payload.address || payload.formatted_address || '',
        province: payload.province ?? '',
        city: payload.city ?? '',
        district: payload.district ?? '',
        subdistrict: payload.subdistrict ?? payload.village ?? '',
        village: payload.village ?? payload.subdistrict ?? '',
        postalCode: payload.postal_code ?? '',
        placeId: payload.place_id ?? null,
        coverage: payload.coverage ?? null,
    };
}
