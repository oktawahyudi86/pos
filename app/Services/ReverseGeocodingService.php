<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class ReverseGeocodingService
{
    public function resolve(float $latitude, float $longitude): array
    {
        $response = Http::timeout(10)
            ->withHeaders([
                'User-Agent' => config('app.name', 'POS-Laravel').' Reverse Geocoding',
                'Accept-Language' => 'id',
            ])
            ->get('https://nominatim.openstreetmap.org/reverse', [
                'format' => 'jsonv2',
                'lat' => $latitude,
                'lon' => $longitude,
                'addressdetails' => 1,
                'zoom' => 18,
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Reverse geocoding request failed.');
        }

        $payload = $response->json();

        if (! is_array($payload) || empty($payload['address'])) {
            throw new RuntimeException('Reverse geocoding returned an invalid response.');
        }

        $address = $payload['address'];

        $province = $this->firstNonEmpty($address, ['state', 'region']);
        $city = $this->firstNonEmpty($address, ['city', 'town', 'municipality', 'county']);
        $district = $this->firstNonEmpty($address, ['city_district', 'district', 'suburb', 'borough']);
        $village = $this->firstNonEmpty($address, ['village', 'hamlet', 'neighbourhood', 'quarter', 'residential']);
        $postalCode = $this->firstNonEmpty($address, ['postcode']);
        $formattedAddress = $this->buildAddress($address, $payload['display_name'] ?? null);

        return [
            'address' => $formattedAddress,
            'formatted_address' => $formattedAddress,
            'province' => $province,
            'city' => $city,
            'district' => $district,
            'village' => $village,
            'subdistrict' => $village,
            'postal_code' => $postalCode,
            'place_id' => $this->buildPlaceId($payload),
        ];
    }

    public function search(string $query, int $limit = 5): array
    {
        $response = Http::timeout(10)
            ->withHeaders([
                'User-Agent' => config('app.name', 'POS-Laravel').' Geocoding Search',
                'Accept-Language' => 'id',
            ])
            ->get('https://nominatim.openstreetmap.org/search', [
                'format' => 'jsonv2',
                'q' => $query,
                'addressdetails' => 1,
                'limit' => $limit,
                'countrycodes' => 'id',
            ]);

        if (! $response->successful()) {
            throw new RuntimeException('Geocoding search request failed.');
        }

        $results = $response->json();

        if (! is_array($results)) {
            return [];
        }

        return collect($results)
            ->map(function (array $item) {
                $address = is_array($item['address'] ?? null) ? $item['address'] : [];

                return [
                    'place_id' => $this->buildPlaceId($item),
                    'formatted_address' => $this->buildAddress($address, $item['display_name'] ?? null),
                    'latitude' => isset($item['lat']) ? (float) $item['lat'] : null,
                    'longitude' => isset($item['lon']) ? (float) $item['lon'] : null,
                ];
            })
            ->filter(fn (array $item) => $item['latitude'] !== null && $item['longitude'] !== null)
            ->values()
            ->all();
    }

    private function buildPlaceId(array $payload): ?string
    {
        $osmType = $payload['osm_type'] ?? null;
        $osmId = $payload['osm_id'] ?? null;

        if (! is_string($osmType) || $osmType === '' || $osmId === null) {
            return null;
        }

        return 'osm:'.$osmType.':'.$osmId;
    }

    private function buildAddress(array $address, ?string $displayName): string
    {
        $segments = collect([
            trim(($address['house_number'] ?? '').' '.($address['road'] ?? '')),
            $address['neighbourhood'] ?? null,
            $address['hamlet'] ?? null,
            $address['village'] ?? null,
            $address['suburb'] ?? null,
            $address['city_district'] ?? null,
            $address['city'] ?? $address['town'] ?? $address['municipality'] ?? null,
            $address['state'] ?? null,
            $address['postcode'] ?? null,
        ])
            ->map(fn ($value) => is_string($value) ? trim($value) : '')
            ->filter()
            ->unique()
            ->values();

        if ($segments->isNotEmpty()) {
            return $segments->join(', ');
        }

        return is_string($displayName) ? trim($displayName) : '';
    }

    private function firstNonEmpty(array $address, array $keys): ?string
    {
        foreach ($keys as $key) {
            $value = $address[$key] ?? null;

            if (is_string($value) && trim($value) !== '') {
                return trim($value);
            }
        }

        return null;
    }
}
