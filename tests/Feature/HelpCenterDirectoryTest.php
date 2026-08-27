<?php

use App\Models\HelpCenter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

uses(RefreshDatabase::class);

beforeEach(function () {
    Cache::flush();
});

it('renders the public help center directory for anonymous visitors', function () {
    $this->get(route('help-centers.index'))
        ->assertOk()
        ->assertSee('Find Nearby Help-centers')
        ->assertSee('help-centers.js', false)
        ->assertSee('Search another city');
});

it('returns active help centers in the detected city sorted by distance', function () {
    Http::fake([
        'ip-api.com/*' => Http::response([
            'status' => 'success',
            'city' => 'Dhaka',
            'country' => 'Bangladesh',
            'lat' => 23.8103,
            'lon' => 90.4125,
        ], 200),
    ]);

    $nearest = HelpCenter::create([
        'name' => 'Nearest Crisis Center',
        'type' => 'crisis_center',
        'city' => 'Dhaka',
        'latitude' => 23.8110,
        'longitude' => 90.4130,
        'is_active' => true,
    ]);
    $farther = HelpCenter::create([
        'name' => 'Farther Crisis Center',
        'type' => 'clinic',
        'city' => 'Dhaka',
        'latitude' => 23.9000,
        'longitude' => 90.5000,
        'is_active' => true,
    ]);
    HelpCenter::create([
        'name' => 'Inactive Center',
        'type' => 'hospital',
        'city' => 'Dhaka',
        'latitude' => 23.8110,
        'longitude' => 90.4130,
        'is_active' => false,
    ]);
    HelpCenter::create([
        'name' => 'Other City Center',
        'type' => 'clinic',
        'city' => 'Chattogram',
        'latitude' => 22.3569,
        'longitude' => 91.7832,
        'is_active' => true,
    ]);
    $nearest->hotlines()->create([
        'name' => 'Crisis line',
        'phone_number' => '999',
        'is_active' => true,
    ]);
    $nearest->hotlines()->create([
        'name' => 'Inactive line',
        'phone_number' => '000',
        'is_active' => false,
    ]);

    $response = $this->withServerVariables(['REMOTE_ADDR' => '8.8.8.8'])
        ->getJson('/api/help-centers/nearby');

    $response->assertOk()
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.location.city', 'Dhaka')
        ->assertJsonPath('data.location.country', 'Bangladesh')
        ->assertJsonPath('data.location.approximate', true)
        ->assertJsonPath('data.centers.0.name', 'Nearest Crisis Center')
        ->assertJsonPath('data.centers.1.name', 'Farther Crisis Center')
        ->assertJsonPath('data.centers.0.hotlines.0.phone_number', '999')
        ->assertJsonMissing(['name' => 'Inactive line'])
        ->assertJsonMissing(['name' => 'Inactive Center'])
        ->assertJsonMissing(['name' => 'Other City Center']);

    Http::assertSent(fn ($request) => str_contains($request->url(), 'ip-api.com/json/8.8.8.8'));
});

it('supports a manual city search when IP geolocation is unavailable', function () {
    Http::fakeSequence('ip-api.com/*')
        ->push(['status' => 'fail', 'message' => 'private range'], 200);

    HelpCenter::create([
        'name' => 'City Clinic',
        'type' => 'clinic',
        'city' => 'Dhaka',
        'latitude' => 23.8,
        'longitude' => 90.4,
        'is_active' => true,
    ]);

    $response = $this->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
        ->getJson('/api/help-centers/nearby?city=Dhaka');

    $response->assertOk()
        ->assertJsonPath('data.location.city', 'Dhaka')
        ->assertJsonPath('data.location.source', 'manual')
        ->assertJsonPath('data.location.approximate', false)
        ->assertJsonPath('data.centers.0.name', 'City Clinic');

    Http::assertNothingSent();
});

it('returns a clear error when IP geolocation fails', function () {
    Http::fake([
        'ip-api.com/*' => Http::response(['status' => 'fail'], 200),
    ]);

    $this->withServerVariables(['REMOTE_ADDR' => '8.8.4.4'])
        ->getJson('/api/help-centers/nearby')
        ->assertStatus(503)
        ->assertJsonPath('status', 'error')
        ->assertJsonPath('message', 'We could not find your approximate location. Try searching by city instead.');
});
