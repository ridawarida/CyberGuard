<?php

use App\Models\PanicEvent;
use App\Models\PanicSetting;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Quick Escape Panic Button - Module 1 backend tests.
 * Feature owner: Johra-E-Jannat Oishy.
 */

function activePanicSetting(array $overrides = []): PanicSetting
{
    return PanicSetting::create(array_merge(PanicSetting::FALLBACK, [
        'log_events' => true,
        'is_active' => true,
    ], $overrides));
}

it('serves the panic configuration to anonymous visitors', function () {
    activePanicSetting(['decoy_url' => 'https://www.wikipedia.org']);

    $this->getJson('/panic/config')
        ->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.decoy_url', 'https://www.wikipedia.org')
        ->assertJsonPath('data.hotkey_press_count', 2);
});

it('falls back to safe defaults when the settings table is empty', function () {
    PanicSetting::query()->delete();

    $this->getJson('/panic/config')
        ->assertStatus(200)
        ->assertJsonPath('data.decoy_url', PanicSetting::FALLBACK['decoy_url']);
});

it('never lets the config response be cached', function () {
    activePanicSetting();

    $response = $this->getJson('/panic/config');

    expect($response->headers->get('Cache-Control'))->toContain('no-store');
});

it('clears wizard draft data from the session when triggered', function () {
    activePanicSetting();

    $this->withSession([
        'incident_wizard' => ['description' => 'he keeps messaging me'],
        'timeline_incidents' => [['summary' => 'threatening dm']],
    ]);

    $response = $this->postJson('/panic/trigger', [
        'source' => 'hotkey',
        'context' => 'wizard',
    ]);

    $response->assertStatus(200)
        ->assertJsonPath('data.session_cleared', true)
        ->assertJsonPath('data.redirect_url', PanicSetting::FALLBACK['decoy_url']);

    $this->assertNull(session('incident_wizard'));
    $this->assertNull(session('timeline_incidents'));
});

it('asks the browser to wipe its own storage', function () {
    activePanicSetting();

    $response = $this->postJson('/panic/trigger', ['source' => 'click']);

    expect($response->headers->get('Clear-Site-Data'))->toContain('cookies');
});

it('records an anonymous event without any identifying column', function () {
    activePanicSetting(['log_events' => true]);

    $this->postJson('/panic/trigger', ['source' => 'hotkey', 'context' => 'timeline']);

    $event = PanicEvent::latest('id')->first();

    expect($event)->not->toBeNull()
        ->and($event->trigger_source)->toBe('hotkey')
        ->and($event->context)->toBe('timeline')
        ->and(array_keys($event->getAttributes()))
        ->not->toContain('ip_address', 'user_id', 'session_id');
});

it('skips logging when the admin turns the counter off', function () {
    activePanicSetting(['log_events' => false]);
    PanicEvent::query()->delete();

    $this->postJson('/panic/trigger', ['source' => 'click']);

    expect(PanicEvent::count())->toBe(0);
});

it('rejects an unknown trigger source', function () {
    activePanicSetting();

    $this->postJson('/panic/trigger', ['source' => 'robot'])
        ->assertStatus(422);
});

it('logs an authenticated moderator out on escape', function () {
    activePanicSetting();

    $moderator = User::factory()->create([
        'password' => Hash::make('mod123'),
        'role' => 'moderator',
    ]);

    $this->actingAs($moderator, 'web')
        ->postJson('/panic/trigger', ['source' => 'click'])
        ->assertStatus(200);

    $this->assertGuest('web');
});

it('redirects the no javascript fallback straight to the decoy site', function () {
    activePanicSetting(['decoy_url' => 'https://www.wikipedia.org']);

    $this->post('/panic/escape')
        ->assertStatus(302)
        ->assertRedirect('https://www.wikipedia.org');
});

it('blocks non admins from reading the panic settings', function () {
    activePanicSetting();

    $moderator = User::factory()->create(['role' => 'moderator']);

    $this->actingAs($moderator, 'sanctum')
        ->getJson('/panic/admin/settings')
        ->assertStatus(403);
});

it('lets an admin change the decoy site', function () {
    activePanicSetting();

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin, 'sanctum')
        ->putJson('/panic/admin/settings', [
            'decoy_url' => 'https://www.google.com',
            'decoy_label' => 'Google',
        ])
        ->assertStatus(200)
        ->assertJsonPath('data.decoy_url', 'https://www.google.com');
});

it('refuses an insecure or self referencing decoy url', function () {
    activePanicSetting();

    $admin = User::factory()->create(['role' => 'admin']);

    $this->actingAs($admin, 'sanctum')
        ->putJson('/panic/admin/settings', ['decoy_url' => 'http://www.google.com'])
        ->assertStatus(422);

    config(['app.url' => 'https://cyberguard.test']);

    $this->actingAs($admin, 'sanctum')
        ->putJson('/panic/admin/settings', ['decoy_url' => 'https://cyberguard.test/reports'])
        ->assertStatus(422)
        ->assertJsonStructure(['errors' => ['decoy_url']]);
});
