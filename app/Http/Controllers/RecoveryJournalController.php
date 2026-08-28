<?php

namespace App\Http\Controllers;

use App\Models\RecoveryJournal;
use App\Models\RecoveryJournalEntry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class RecoveryJournalController extends Controller
{
    public function index(Request $request): Response
    {
        $journal = $this->sessionJournal($request);
        $createdCode = $request->session()->pull('recovery_journal_created_code');

        if ($journal) {
            $journal->load(['entries' => fn ($query) => $query->latest()]);
        }

        return response()
            ->view('recovery-journal.index', compact('journal', 'createdCode'))
            ->withHeaders($this->privacyHeaders());
    }

    public function start(Request $request): RedirectResponse
    {
        $accessCode = 'CJ-' . Str::upper(Str::random(16));
        $journal = RecoveryJournal::create([
            'access_code_hash' => Hash::make($accessCode),
        ]);

        $request->session()->put('recovery_journal_id', $journal->id);
        $request->session()->put('recovery_journal_created_code', $accessCode);

        return redirect()->route('recovery-journal.index')->withHeaders($this->privacyHeaders());
    }

    public function unlock(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'access_code' => ['required', 'string', 'size:19', 'regex:/^CJ-[A-Z0-9]{16}$/'],
        ]);

        $journal = RecoveryJournal::query()->get()->first(
            fn (RecoveryJournal $candidate) => $candidate->matchesAccessCode($validated['access_code'])
        );

        if (!$journal) {
            return back()->withErrors(['access_code' => 'That journal key is not recognized. Check it and try again.']);
        }

        $request->session()->put('recovery_journal_id', $journal->id);

        return redirect()->route('recovery-journal.index')->withHeaders($this->privacyHeaders());
    }

    public function storeEntry(Request $request): RedirectResponse
    {
        $journal = $this->requireJournal($request);
        $validated = $this->validateEntry($request);
        $journal->entries()->create($validated);

        return back()->with('status', 'Journal entry saved.')->withHeaders($this->privacyHeaders());
    }

    public function updateEntry(Request $request, RecoveryJournalEntry $entry): RedirectResponse
    {
        $this->requireJournal($request, $entry);
        $entry->update($this->validateEntry($request));

        return back()->with('status', 'Journal entry updated.')->withHeaders($this->privacyHeaders());
    }

    public function destroyEntry(Request $request, RecoveryJournalEntry $entry): RedirectResponse
    {
        $this->requireJournal($request, $entry);
        $entry->delete();

        return back()->with('status', 'Journal entry deleted.')->withHeaders($this->privacyHeaders());
    }

    public function forget(Request $request): RedirectResponse
    {
        $request->session()->forget(['recovery_journal_id', 'recovery_journal_created_code']);

        return redirect()->route('recovery-journal.index')->withHeaders($this->privacyHeaders());
    }

    private function validateEntry(Request $request): array
    {
        return $request->validate([
            'summary' => ['required', 'string', 'min:2', 'max:1000'],
            'stress_level' => ['required', 'integer', 'between:1,10'],
        ]);
    }

    private function sessionJournal(Request $request): ?RecoveryJournal
    {
        $journalId = $request->session()->get('recovery_journal_id');

        return $journalId ? RecoveryJournal::find($journalId) : null;
    }

    private function requireJournal(Request $request, ?RecoveryJournalEntry $entry = null): RecoveryJournal
    {
        $journal = $this->sessionJournal($request);

        abort_unless($journal !== null, 403);

        if ($entry) {
            abort_unless((int) $entry->recovery_journal_id === (int) $journal->id, 404);
        }

        return $journal;
    }

    private function privacyHeaders(): array
    {
        return [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
            'Referrer-Policy' => 'no-referrer',
        ];
    }
}