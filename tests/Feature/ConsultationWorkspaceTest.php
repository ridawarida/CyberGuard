<?php

use App\Models\Consultation;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Secure Consultation Workspace - Module 3 backend tests.
 * Feature owner: Johra-E-Jannat Oishy.
 *
 * Assumes an `incidents` table already exists (Ishrat's Module 1) with at
 * least id, tracking_id, platform, incident_date, description, status
 * and timestamps. If her migration has other required columns, add them
 * to makeIncidentId() below.
 */

function makeIncidentId(array $overrides = []): int
{
    return DB::table('incidents')->insertGetId(array_merge([
        'tracking_id' => 'TRK-'.uniqid(),
        'platform' => 'Instagram',
        'region' => 'Dhaka',
        'incident_date' => now()->subDays(3)->toDateString(),
        'behavior_type' => 'Harassment',
        'severity' => 'Medium',
        'description' => 'Repeated threatening messages.',
        'status' => 'New',
        'created_at' => now(),
        'updated_at' => now(),
    ], $overrides));
}

it('auto-generates a unique access key when a consultation is created', function () {
    $consultation = Consultation::create(['incident_id' => makeIncidentId()]);

    expect($consultation->access_key)->not->toBeEmpty()
        ->and(strlen($consultation->access_key))->toBeGreaterThanOrEqual(32)
        ->and($consultation->status)->toBe('open');
});

it('lets a victim in with a valid access key and rejects an invalid one', function () {
    $consultation = Consultation::create(['incident_id' => makeIncidentId()]);

    $this->post('/consult', ['access_key' => 'not-a-real-key'])
        ->assertSessionHasErrors('access_key');

    $this->post('/consult', ['access_key' => $consultation->access_key])
        ->assertRedirect(route('consult.session'));

    $this->assertEquals($consultation->id, session('consultation_id'));
});

it('does not let the public status tracking code double as a chat key', function () {
    $incidentId = makeIncidentId(['tracking_id' => 'PUBLIC-CODE-123']);
    Consultation::create(['incident_id' => $incidentId]);

    $this->post('/consult', ['access_key' => 'PUBLIC-CODE-123'])
        ->assertSessionHasErrors('access_key');
});

it('blocks the chat session route until a key has been submitted', function () {
    $this->get('/consult/session')->assertRedirect(route('consult.access'));
});

it('lets a victim post a message and then see it in the thread', function () {
    $consultation = Consultation::create(['incident_id' => makeIncidentId()]);

    $this->withSession(['consultation_id' => $consultation->id, 'incident_id' => $consultation->incident_id]);

    $this->postJson('/consult/session/messages', ['body' => 'Hello, I need help.'])
        ->assertStatus(200)
        ->assertJsonPath('data.sender_type', 'victim')
        ->assertJsonPath('data.body', 'Hello, I need help.');

    $this->getJson('/consult/session/messages')
        ->assertStatus(200)
        ->assertJsonCount(1, 'data');
});

it('rejects a whitespace-only message even though the client normally blocks it first', function () {
    $consultation = Consultation::create(['incident_id' => makeIncidentId()]);

    $this->withSession(['consultation_id' => $consultation->id, 'incident_id' => $consultation->incident_id]);

    $this->postJson('/consult/session/messages', ['body' => '   '])
        ->assertStatus(422)
        ->assertJsonPath('status', 'error');
});

it('bumps the consultation\'s updated_at when a new message arrives', function () {
    $consultation = Consultation::create(['incident_id' => makeIncidentId()]);
    $originalTimestamp = $consultation->updated_at;

    $this->travel(1)->hours();

    $this->withSession(['consultation_id' => $consultation->id, 'incident_id' => $consultation->incident_id]);
    $this->postJson('/consult/session/messages', ['body' => 'Checking in.']);

    expect($consultation->fresh()->updated_at->gt($originalTimestamp))->toBeTrue();
});

it('only ever shows a victim their own thread, never another one', function () {
    $mine = Consultation::create(['incident_id' => makeIncidentId()]);
    $theirs = Consultation::create(['incident_id' => makeIncidentId()]);
    $theirs->messages()->create(['sender_type' => 'victim', 'body' => 'A different case entirely.']);

    // Victim routes never take a consultation id from the request - only
    // from session - so there is no id here to guess in the first place.
    $this->withSession(['consultation_id' => $mine->id, 'incident_id' => $mine->incident_id]);

    $this->getJson('/consult/session/messages')
        ->assertStatus(200)
        ->assertJsonCount(0, 'data');
});

it('blocks unauthenticated requests from the moderator consultation list', function () {
    $this->getJson('/moderator/consultations')->assertStatus(401);
});

it('blocks a non moderator from the moderator consultation list', function () {
    $notModerator = User::factory()->create(['role' => 'admin']);

    $this->actingAs($notModerator, 'sanctum')
        ->getJson('/moderator/consultations')
        ->assertStatus(403);
});

it('lets a moderator view a thread and reply to it', function () {
    $consultation = Consultation::create(['incident_id' => makeIncidentId()]);
    $consultation->messages()->create(['sender_type' => 'victim', 'body' => 'Can you help?']);

    $moderator = User::factory()->create(['role' => 'moderator']);

    $this->actingAs($moderator, 'sanctum')
        ->get("/moderator/consultations/{$consultation->id}")
        ->assertStatus(200)
        ->assertSee('Can you help?');

    $this->actingAs($moderator, 'sanctum')
        ->postJson("/moderator/consultations/{$consultation->id}/messages", ['body' => 'We are looking into this.'])
        ->assertStatus(200)
        ->assertJsonPath('data.sender_type', 'moderator')
        ->assertJsonPath('data.sender_id', $moderator->id);
});
