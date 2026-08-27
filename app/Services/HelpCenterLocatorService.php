<?php

namespace App\Services;

use App\Models\HelpCenter;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class HelpCenterLocatorService
{
    public function locate(?string $ipAddress, ?string $city = null): array
    {
        $location = $city !== null && trim($city) !== ''
            ? ['city' => trim($city), 'country' => null, 'latitude' => null, 'longitude' => null, 'source' => 'manual']
            : $this->locateIp($ipAddress);

        $centers = HelpCenter::with(['hotlines' => function ($query) {
            $query->where('is_active', true);
        }])
            ->where('is_active', true)
            ->whereNotNull('latitude')
            ->whereNotNull('longitude')
            ->whereRaw('LOWER(city) = ?', [mb_strtolower($location['city'])])
            ->get()
            ->map(function (HelpCenter $center) use ($location) {
                $distance = $location['latitude'] !== null && $location['longitude'] !== null
                    ? $this->distanceInKilometers(
                        (float) $location['latitude'],
                        (float) $location['longitude'],
                        (float) $center->latitude,
                        (float) $center->longitude
                    )
                    : null;

                return [
                    'id' => $center->id,
                    'name' => $center->name,
                    'type' => $center->type,
                    'type_label' => $center->type_label,
                    'address' => $center->address,
                    'city' => $center->city,
                    'state' => $center->state,
                    'zip_code' => $center->zip_code,
                    'working_hours' => $center->working_hours,
                    'latitude' => (float) $center->latitude,
                    'longitude' => (float) $center->longitude,
                    'distance_km' => $distance !== null ? round($distance, 1) : null,
                    'hotlines' => $center->hotlines->map(fn ($hotline) => [
                        'id' => $hotline->id,
                        'name' => $hotline->name,
                        'phone_number' => $hotline->phone_number,
                        'is_toll_free' => $hotline->is_toll_free,
                        'description' => $hotline->description,
                        'operating_hours' => $hotline->operating_hours,
                    ])->values()->all(),
                ];
            })
            ->sortBy(fn (array $center) => $center['distance_km'] ?? PHP_FLOAT_MAX)
            ->take((int) config('services.help_centers.result_limit', 10))
            ->values()
            ->all();

        return [
            'location' => [
                'city' => $location['city'],
                'country' => $location['country'],
                'source' => $location['source'],
                'approximate' => $location['source'] === 'ip',
            ],
            'centers' => $centers,
        ];
    }

    private function locateIp(?string $ipAddress): array
    {
        if ($ipAddress === null || filter_var($ipAddress, FILTER_VALIDATE_IP) === false) {
            throw new \RuntimeException('We could not determine your approximate location.');
        }

        $cacheKey = 'cyberguard.help-centers.location.' . sha1($ipAddress);
        $location = Cache::remember($cacheKey, now()->addHour(), function () use ($ipAddress) {
            $baseUrl = rtrim((string) config('services.help_centers.geolocation_url', 'http://ip-api.com/json'), '/');

            $response = Http::acceptJson()
                ->timeout((int) config('services.help_centers.timeout', 5))
                ->retry(2, 200)
                ->get($baseUrl . '/' . rawurlencode($ipAddress), [
                    'fields' => 'status,message,city,country,lat,lon',
                ]);

            $payload = $response->json();

            if (!$response->successful() || !is_array($payload) || ($payload['status'] ?? null) !== 'success') {
                throw new \RuntimeException('We could not determine your approximate location.');
            }

            $city = trim((string) ($payload['city'] ?? ''));

            if ($city === '' || !isset($payload['lat'], $payload['lon'])) {
                throw new \RuntimeException('We could not determine your approximate location.');
            }

            return [
                'city' => $city,
                'country' => trim((string) ($payload['country'] ?? '')) ?: null,
                'latitude' => (float) $payload['lat'],
                'longitude' => (float) $payload['lon'],
                'source' => 'ip',
            ];
        });

        return $location;
    }

    private function distanceInKilometers(float $latitudeOne, float $longitudeOne, float $latitudeTwo, float $longitudeTwo): float
    {
        $earthRadius = 6371;
        $latitudeDelta = deg2rad($latitudeTwo - $latitudeOne);
        $longitudeDelta = deg2rad($longitudeTwo - $longitudeOne);
        $a = sin($latitudeDelta / 2) ** 2
            + cos(deg2rad($latitudeOne)) * cos(deg2rad($latitudeTwo)) * sin($longitudeDelta / 2) ** 2;

        return $earthRadius * 2 * asin(min(1, sqrt($a)));
    }
}