/**
 * Client-side delivery coverage evaluation (mirrors DeliveryCoverageService).
 */

export function createCoverageEvaluator(config, outOfCoverageMessage) {
    function isRestrictionActive() {
        return Boolean(config?.enabled)
            && Number(config?.max_radius_km) > 0
            && config?.store_latitude !== null
            && config?.store_longitude !== null;
    }

    function distanceKm(fromLat, fromLng, toLat, toLng) {
        const earthRadius = 6371;
        const latDelta = (toLat - fromLat) * Math.PI / 180;
        const lngDelta = (toLng - fromLng) * Math.PI / 180;
        const a = Math.sin(latDelta / 2) ** 2
            + Math.cos(fromLat * Math.PI / 180) * Math.cos(toLat * Math.PI / 180) * Math.sin(lngDelta / 2) ** 2;

        return earthRadius * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
    }

    function evaluate(latitude, longitude) {
        if (!isRestrictionActive()) {
            return { within: true, message: null, active: false };
        }

        if (!latitude || !longitude) {
            return {
                within: false,
                active: true,
                message: 'Aktifkan lokasi HP untuk memastikan alamat pengantaran berada dalam jangkauan kami.',
            };
        }

        const distance = distanceKm(
            Number(config.store_latitude),
            Number(config.store_longitude),
            Number(latitude),
            Number(longitude),
        );

        if (distance > Number(config.max_radius_km)) {
            return {
                within: false,
                active: true,
                message: outOfCoverageMessage,
                distanceKm: distance,
            };
        }

        return {
            within: true,
            active: true,
            message: null,
            distanceKm: distance,
        };
    }

    function normalize(serverCoverage) {
        return {
            within: serverCoverage?.within_coverage ?? serverCoverage?.within ?? true,
            message: serverCoverage?.message ?? null,
            active: serverCoverage?.active ?? isRestrictionActive(),
        };
    }

    return {
        isRestrictionActive,
        evaluate,
        normalize,
    };
}
