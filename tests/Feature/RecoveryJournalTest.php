<?php

use App\Models\RecoveryJournal;
use App\Models\RecoveryJournalEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

it('shows the anonymous journal start and unlock page without caching it', function () {
    $response = $this->get(route('recovery-journal.index'));

    $response->assertOk()
        ->assertSee('Start my recovery journal')
        ->assertSee('Unlock with your key')
        ->assertHeader('Referrer-Policy', 'no-referrer');

    expect($response->headers->get('Cache-Control'))
        ->toContain('no-store')
        ->toContain('no-cache');
});

it('creates a journal and displays its random key only once', function () {
    $response = $this->post(route('recovery-journal.start'));

    $response->assertRedirect(route('recovery-journal.index'));
    $journal = RecoveryJournal::firstOrFail();
    $code = session('recovery_journal_created_code');

    expect($code)->toMatch('/^CJ-[A-Z0-9]{16}$/')
        ->and(Hash::check($code, $journal->access_code_hash))->toBeTrue();

    $this->get(route('recovery-journal.index'))
        ->assertSee($code)
        ->assertSee('Your journal key');

    $this->get(route('recovery-journal.index'))
        ->assertDontSee($code);
});

it('unlocks with the correct key and rejects an incorrect key', function () {
    $code = 'CJ-' . str_repeat('A', 16);
    $journal = RecoveryJournal::create(['access_code_hash' => Hash::make($code)]);

    $this->from(route('recovery-journal.index'))
        ->post(route('recovery-journal.unlock'), ['access_code' => 'CJ-' . str_repeat('B', 16)])
        ->assertRedirect(route('recovery-journal.index'))
        ->assertSessionHasErrors('access_code');

    $this->post(route('recovery-journal.unlock'), ['access_code' => $code])
        ->assertRedirect(route('recovery-journal.index'));

    expect(session('recovery_journal_id'))->toBe($journal->id);
});

it('supports multiple entries and full entry CRUD only inside the unlocked journal', function () {
    $journal = RecoveryJournal::create(['access_code_hash' => Hash::make('unused')]);
    $otherJournal = RecoveryJournal::create(['access_code_hash' => Hash::make('other')]);
    $this->withSession(['recovery_journal_id' => $journal->id]);

    $first = $this->post(route('recovery-journal.entries.store'), [
        'summary' => 'I took a break and felt more grounded.',
        'stress_level' => 7,
    ]);
    $first->assertRedirect()->assertSessionHas('status', 'Journal entry saved.');

    $second = $this->post(route('recovery-journal.entries.store'), [
        'summary' => 'The evening felt calmer than the morning.',
        'stress_level' => 4,
    ]);
    $second->assertRedirect();

    $entries = $journal->entries()->get();
    expect($entries)->toHaveCount(2);

    $entry = $entries->first();
    $this->put(route('recovery-journal.entries.update', $entry), [
        'summary' => 'Updated reflection.',
        'stress_level' => 3,
    ])->assertRedirect();

    expect($entry->fresh()->summary)->toBe('Updated reflection.')
        ->and($entry->fresh()->stress_level)->toBe(3);

    $otherEntry = $otherJournal->entries()->create(['summary' => 'Private note', 'stress_level' => 2]);
    $this->delete(route('recovery-journal.entries.destroy', $otherEntry))
        ->assertNotFound();
    expect(RecoveryJournalEntry::find($otherEntry->id))->not->toBeNull();

    $this->delete(route('recovery-journal.entries.destroy', $entry))
        ->assertRedirect();
    expect(RecoveryJournalEntry::find($entry->id))->toBeNull();
});

it('validates summary and stress level', function () {
    $journal = RecoveryJournal::create(['access_code_hash' => Hash::make('unused')]);
    $this->withSession(['recovery_journal_id' => $journal->id])
        ->post(route('recovery-journal.entries.store'), [
            'summary' => 'x',
            'stress_level' => 11,
        ])
        ->assertSessionHasErrors(['summary', 'stress_level']);
});

it('forgets unlock access without deleting the journal', function () {
    $journal = RecoveryJournal::create(['access_code_hash' => Hash::make('unused')]);
    $journal->entries()->create(['summary' => 'Keep this history.', 'stress_level' => 5]);

    $this->withSession(['recovery_journal_id' => $journal->id])
        ->post(route('recovery-journal.forget'))
        ->assertRedirect(route('recovery-journal.index'))
        ->assertSessionMissing('recovery_journal_id');

    expect($journal->fresh()->entries)->toHaveCount(1);
});
