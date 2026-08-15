<?php

use App\Models\Incident;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

function makeModerator(string $email = 'mod@cyberguard.com'): User
{
    return User::create([
        'name' => 'Test Moderator',
        'email' => $email,
        'password' => Hash::make('secret123'),
        'role' => 'moderator',
    ]);
}

function makeIncident(array $overrides = []): Incident
{
    return Incident::create(array_merge([
        'tracking_id' => 'inc' . strtoupper(uniqid()),
        'platform' => 'Instagram',
        'region' => 'Dhaka',
        'description' => 'Repeated threatening messages after I blocked the account.',
        'incident_date' => now()->subDays(3),
        'behavior_type' => 'Threats',
        'severity' => 'Unassigned',
        'overview' => 'Threats sent by direct message.',
        'evidence_image' => null,
        'status' => 'New',
    ], $overrides));
}

it('redirects guests away from the moderation workspace', function () {
    $this->get('/moderator/incidents')->assertRedirect(route('staff.login'));
});

it('shows only unclaimed incidents in the open pool', function () {
    $moderator = makeModerator();
    $other = makeModerator('other@cyberguard.com');

    $open = makeIncident(['tracking_id' => 'incOPEN123']);
    makeIncident(['tracking_id' => 'incTAKEN123', 'assigned_moderator_id' => $other->id, 'claimed_at' => now()]);

    $this->actingAs($moderator)
        ->get('/moderator/incidents?scope=pool')
        ->assertOk()
        ->assertSee('incOPEN123')
        ->assertDontSee('incTAKEN123');
});

it('filters incidents by platform', function () {
    $moderator = makeModerator();

    makeIncident(['tracking_id' => 'incINSTA1', 'platform' => 'Instagram']);
    makeIncident(['tracking_id' => 'incTIKTOK1', 'platform' => 'TikTok']);

    $this->actingAs($moderator)
        ->get('/moderator/incidents?scope=pool&platform=TikTok')
        ->assertOk()
        ->assertSee('incTIKTOK1')
        ->assertDontSee('incINSTA1');
});

it('locks an incident to the moderator who claims it', function () {
    $moderator = makeModerator();
    $incident = makeIncident();

    $this->actingAs($moderator)
        ->post("/moderator/incidents/{$incident->id}/claim")
        ->assertRedirect(route('moderator.incidents.show', $incident->id));

    $incident->refresh();

    expect($incident->assigned_moderator_id)->toBe($moderator->id)
        ->and($incident->claimed_at)->not->toBeNull();
});

it('refuses a second claim on an already claimed incident', function () {
    $first = makeModerator();
    $second = makeModerator('second@cyberguard.com');

    $incident = makeIncident(['assigned_moderator_id' => $first->id, 'claimed_at' => now()]);

    $this->actingAs($second)
        ->post("/moderator/incidents/{$incident->id}/claim")
        ->assertRedirect(route('moderator.incidents.index'));

    expect($incident->fresh()->assigned_moderator_id)->toBe($first->id);
});

it('blocks a moderator from opening a case claimed by someone else', function () {
    $owner = makeModerator();
    $intruder = makeModerator('intruder@cyberguard.com');

    $incident = makeIncident(['assigned_moderator_id' => $owner->id, 'claimed_at' => now()]);

    $this->actingAs($intruder)
        ->get("/moderator/incidents/{$incident->id}")
        ->assertRedirect(route('moderator.incidents.index'));
});

it('asks the moderator to claim before opening an unclaimed case', function () {
    $moderator = makeModerator();
    $incident = makeIncident();

    $this->actingAs($moderator)
        ->get("/moderator/incidents/{$incident->id}")
        ->assertRedirect(route('moderator.incidents.index'));
});

it('saves severity, status and internal notes from the assessment form', function () {
    $moderator = makeModerator();
    $incident = makeIncident(['assigned_moderator_id' => $moderator->id, 'claimed_at' => now()]);

    $this->actingAs($moderator)
        ->put("/moderator/incidents/{$incident->id}", [
            'severity' => 'High',
            'status' => 'Investigating',
            'moderator_notes' => 'Screenshot verified, escalating to the legal desk.',
        ])
        ->assertRedirect(route('moderator.incidents.show', $incident->id));

    $incident->refresh();

    expect($incident->severity)->toBe('High')
        ->and($incident->status)->toBe('Investigating')
        ->and($incident->moderator_notes)->toBe('Screenshot verified, escalating to the legal desk.')
        ->and($incident->reviewed_at)->not->toBeNull();
});

it('rejects an unknown status value', function () {
    $moderator = makeModerator();
    $incident = makeIncident(['assigned_moderator_id' => $moderator->id, 'claimed_at' => now()]);

    $this->actingAs($moderator)
        ->put("/moderator/incidents/{$incident->id}", [
            'severity' => 'High',
            'status' => 'Deleted',
        ])
        ->assertSessionHasErrors('status');
});

it('returns a released case to the open pool', function () {
    $moderator = makeModerator();
    $incident = makeIncident(['assigned_moderator_id' => $moderator->id, 'claimed_at' => now()]);

    $this->actingAs($moderator)
        ->post("/moderator/incidents/{$incident->id}/release")
        ->assertRedirect(route('moderator.incidents.index'));

    expect($incident->fresh()->assigned_moderator_id)->toBeNull();
});

it('lets an admin open any claimed case', function () {
    $moderator = makeModerator();

    $admin = User::create([
        'name' => 'Test Admin',
        'email' => 'admin@cyberguard.com',
        'password' => Hash::make('secret123'),
        'role' => 'admin',
    ]);

    $incident = makeIncident(['assigned_moderator_id' => $moderator->id, 'claimed_at' => now()]);

    $this->actingAs($admin)
        ->get("/moderator/incidents/{$incident->id}")
        ->assertOk()
        ->assertSee($incident->tracking_id);
});
