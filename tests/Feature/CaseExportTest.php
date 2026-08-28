<?php

use App\Models\Consultation;
use Illuminate\Support\Facades\DB;

/**
 * Automated Legal and Institutional PDF Case Exporter - Module 3 backend tests.
 * Feature owner: Johra-E-Jannat Oishy.
 *
 * Requires: composer require barryvdh/laravel-dompdf
 *
 * DomPDF's output is a binary PDF stream, not easily string-matched, so
 * these check access control, validation, and the response envelope
 * (status, Content-Type, Content-Disposition) rather than parsing PDF
 * content - that would need a separate PDF-text-extraction dependency
 * this suite doesn't otherwise need.
 *
 * Assumes `incidents` and `timeline_events` tables already exist (see
 * the note in ConsultationWorkspaceTest.php re: incidents; timeline_events
 * is Nahin's Module 1). Adjust the two helpers below if her real column
 * names differ.
 */

function makeExportIncidentId(array $overrides = []): int
{
    return DB::table('incidents')->insertGetId(array_merge([
        'tracking_id' => 'TRK-'.uniqid(),
        'platform' => 'Instagram',
        'region' => 'Dhaka',
        'incident_date' => now()->subDays(5)->toDateString(),
        'behavior_type' => 'Harassment',
        'severity' => 'Medium',
        'description' => 'Repeated threatening messages from a fake account.',
        'status' => 'New',
    ], $overrides));
}

function makeTimelineEventId(int $incidentId, array $overrides = []): int
{
    return DB::table('timeline_events')->insertGetId(array_merge([
        'incident_id' => $incidentId,
        'event_date' => now()->subDays(2)->toDateString(),
        'event_time' => '14:30',
        'behavior_type' => 'Threatening direct message',
        'summary' => 'Received a message threatening to share private photos.',
        'created_at' => now(),
        'updated_at' => now(),
    ], $overrides));
}

it('blocks the export form and the download until a victim session exists', function () {
    $this->get('/consult/session/export')->assertRedirect(route('consult.access'));
    $this->post('/consult/session/export')->assertRedirect(route('consult.access'));
});

it("shows the export form with the incident's own timeline events", function () {
    $incidentId = makeExportIncidentId();
    makeTimelineEventId($incidentId);
    $consultation = Consultation::create(['incident_id' => $incidentId]);

    $this->withSession(['consultation_id' => $consultation->id, 'incident_id' => $incidentId]);

    $this->get('/consult/session/export')
        ->assertStatus(200)
        ->assertSee('Threatening direct message');
});

it('downloads a pdf named after the tracking code when generated', function () {
    $incidentId = makeExportIncidentId(['tracking_id' => 'TRK-EXPORT-TEST']);
    $consultation = Consultation::create(['incident_id' => $incidentId]);

    $this->withSession(['consultation_id' => $consultation->id, 'incident_id' => $incidentId]);

    $response = $this->post('/consult/session/export', [
        'include_description' => '1',
        'timeline_event_ids' => [],
    ]);

    $response->assertStatus(200);
    expect($response->headers->get('Content-Type'))->toContain('application/pdf');
    expect($response->headers->get('Content-Disposition'))->toContain('TRK-EXPORT-TEST');
});

it('validates that timeline_event_ids must be integers', function () {
    $incidentId = makeExportIncidentId();
    $consultation = Consultation::create(['incident_id' => $incidentId]);

    $this->withSession(['consultation_id' => $consultation->id, 'incident_id' => $incidentId]);

    $this->post('/consult/session/export', ['timeline_event_ids' => ['not-an-id']])
        ->assertStatus(422);
});
