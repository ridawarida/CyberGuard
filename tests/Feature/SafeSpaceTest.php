<?php

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

/**
 * Digital Safe Space - Module 2 tests.
 * Feature owner: Johra-E-Jannat Oishy.
 */

beforeEach(function () {
    Cache::forget('cyberguard.safe-space.zenquotes');
});

it('renders the digital safe space for anonymous visitors', function () {
    $this->get('/safe-space')
        ->assertStatus(200)
        ->assertSee('Digital Safe Space')
        ->assertSee('data-breath-circle', false)
        ->assertSee('data-new-quote', false)
        ->assertSee('js/safe-space.js', false)
        ->assertSee('Quick Escape');
});

it('returns a normalized quote from ZenQuotes', function () {
    Http::fake([
        'zenquotes.io/*' => Http::response([
            [
                'q' => 'A calm mind brings inner strength.',
                'a' => 'Example Author',
                'h' => '<blockquote>ignored html</blockquote>',
            ],
        ], 200),
    ]);

    $this->getJson('/safe-space/quote')
        ->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.quote', 'A calm mind brings inner strength.')
        ->assertJsonPath('data.author', 'Example Author');
});

it('falls back gracefully when ZenQuotes is unavailable', function () {
    Http::fake([
        'zenquotes.io/*' => Http::response([], 503),
    ]);

    $this->getJson('/safe-space/quote')
        ->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonStructure([
            'data' => ['quote', 'author'],
        ]);
});

it('does not cache the browser quote response', function () {
    Http::fake([
        'zenquotes.io/*' => Http::response([
            ['q' => 'Keep going.', 'a' => 'Example Author'],
        ], 200),
    ]);

    $response = $this->getJson('/safe-space/quote');

    expect($response->headers->get('Cache-Control'))->toContain('no-store');
});
