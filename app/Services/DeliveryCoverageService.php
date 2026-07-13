<?php

namespace App\Services;

use App\Models\Setting;

class DeliveryCoverageService
{
    public const OUT_OF_COVERAGE_MESSAGE = 'Batas wilayah Anda belum tercover. Tunggu ekspansi kami ke wilayah Anda.';

    public function settingsForTenant(int $tenantId): array
    {
        $stored = Setting::getValue('online_delivery', [], $tenantId);

        return array_merge([
            'enabled' => false,
            'max_radius_km' => null,
            'store_latitude' => null,
            'store_longitude' => null,
        ], is_array($stored) ? $stored : []);
    }

    public function isRestrictionActive(array $settings): bool
    {
        return (bool) ($settings['enabled'] ?? false)
            && is_numeric($settings['max_radius_km'] ?? null)
            && (float) $settings['max_radius_km'] > 0
            && is_numeric($settings['store_latitude'] ?? null)
            && is_numeric($settings['store_longitude'] ?? null);
    }

    public function distanceKm(float $fromLat, float $fromLng, float $toLat, float $toLng): float
    {
        $earthRadius = 6371.0;
        $latDelta = deg2rad($toLat - $fromLat);
        $lngDelta = deg2rad($toLng - $fromLng);
        $a = sin($latDelta / 2) ** 2
            + cos(deg2rad($fromLat)) * cos(deg2rad($toLat)) * sin($lngDelta / 2) ** 2;

        return $earthRadius * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    /**
     * @return array{
     *     active: bool,
     *     within_coverage: bool,
     *     distance_km: float|null,
     *     max_radius_km: float|null,
     *     message: string|null
     * }
     */
    public function evaluate(?float $deliveryLat, ?float $deliveryLng, array $settings): array
    {
        if (! $this->isRestrictionActive($settings)) {
            return [
                'active' => false,
                'within_coverage' => true,
                'distance_km' => null,
                'max_radius_km' => null,
                'message' => null,
            ];
        }

        $maxRadius = (float) $settings['max_radius_km'];
        $storeLat = (float) $settings['store_latitude'];
        $storeLng = (float) $settings['store_longitude'];

        if ($deliveryLat === null || $deliveryLng === null) {
            return [
                'active' => true,
                'within_coverage' => false,
                'distance_km' => null,
                'max_radius_km' => $maxRadius,
                'message' => 'Aktifkan lokasi HP untuk memastikan alamat pengantaran berada dalam jangkauan kami.',
            ];
        }

        $distance = $this->distanceKm($storeLat, $storeLng, $deliveryLat, $deliveryLng);
        $within = $distance <= $maxRadius;

        return [
            'active' => true,
            'within_coverage' => $within,
            'distance_km' => round($distance, 2),
            'max_radius_km' => $maxRadius,
            'message' => $within ? null : self::OUT_OF_COVERAGE_MESSAGE,
        ];
    }
}
