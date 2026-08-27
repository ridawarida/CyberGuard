<?php

namespace App\Http\Controllers;

use App\Models\PanicEvent;
use App\Models\PanicSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\Log;

/**
 * Quick Escape "Panic Button" backend.
 *
 * Module 1 feature owner: Johra-E-Jannat Oishy.
 *
 * The visible button is client side, but the browser alone cannot be trusted
 * to leave a victim safe. This controller is the server half of the feature:
 *
 *  1. It hands the browser its configuration, so an admin can change the decoy
 *     site or the hotkey behaviour without anybody editing JavaScript.
 *  2. It destroys the server side session, so an abuser who presses the back
 *     button or reopens the tab cannot resume a half finished report.
 *  3. It sends wipe and no-store headers, so nothing sensitive survives in the
 *     browser cache or the back forward cache.
 */
class PanicController extends Controller
{
    /**
     * GET /panic/config
     *
     * Public. Returns only display flags, never ids or timestamps.
     */
    public function config(Request $request): JsonResponse|RedirectResponse
    {
        if (! $request->expectsJson()) {
            return redirect()->route('home');
        }

        $setting = PanicSetting::active();

        return response()
            ->json([
                'status' => 'success',
                'data' => $setting->toClientPayload(),
            ])
            ->withHeaders($this->noStoreHeaders());
    }

    /**
     * POST /panic/trigger
     *
     * Wipes the session on the server and tells the browser where to go.
     * The client redirects on its own regardless of this response, so a slow
     * or failed request never blocks the escape.
     */
    public function trigger(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'source' => 'nullable|string|in:' . implode(',', PanicEvent::SOURCES),
            'context' => 'nullable|string|in:' . implode(',', PanicEvent::CONTEXTS),
        ]);

        $setting = PanicSetting::active();

        $this->wipeSession($request);
        $this->recordEvent($setting, $validated);

        $response = response()->json([
            'status' => 'success',
            'message' => 'Session cleared',
            'data' => [
                'redirect_url' => $setting->decoy_url,
                'session_cleared' => true,
            ],
        ]);

        // Forget the framework cookies explicitly. Queued cookies are attached
        // to the outgoing response, so this must happen on the response object.
        $response->withCookie(Cookie::forget(config('session.cookie')));
        $response->withCookie(Cookie::forget('XSRF-TOKEN'));

        // Ask the browser to drop its own copy of everything for this origin.
        // Honoured on secure contexts, which includes localhost.
        $response->headers->set('Clear-Site-Data', '"cache", "cookies", "storage"');

        return $response->withHeaders($this->noStoreHeaders());
    }

    /**
     * POST /panic/escape
     *
     * No JavaScript fallback. A plain Blade form can post here and the browser
     * is redirected straight to the decoy site by the server.
     */
    public function escape(Request $request)
    {
        $setting = PanicSetting::active();

        $this->wipeSession($request);
        $this->recordEvent($setting, ['source' => 'fallback', 'context' => 'unknown']);

        return redirect()->away($setting->decoy_url)
            ->withCookie(Cookie::forget(config('session.cookie')))
            ->withCookie(Cookie::forget('XSRF-TOKEN'))
            ->withHeaders($this->noStoreHeaders());
    }

    /**
     * Remove every trace of the visit from the server session store.
     */
    protected function wipeSession(Request $request): void
    {
        // Named keys first, so half finished drafts from the other Module 1
        // features cannot be recovered even if the driver misbehaves.
        $request->session()->forget([
            'incident_wizard',
            'incident_wizard.platform',
            'incident_wizard.region',
            'incident_wizard.description',
            'incident_wizard.incident_date',
            'incident_wizard.behavior_type',
            'incident_wizard.overview',
            'incident_wizard.evidence_image',
            'incident_wizard.tracking_id',
            'case_file_wizard',
            'case_file_incidents',
            'recovery_journal_id',
            'recovery_journal_created_code',
        ]);

        if (Auth::guard('web')->check()) {
            Auth::guard('web')->logout();
        }

        $request->session()->flush();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }

    /**
     * Anonymous counter only. Never records who, where or from which address.
     */
    protected function recordEvent(PanicSetting $setting, array $validated): void
    {
        if (! $setting->log_events) {
            return;
        }

        try {
            PanicEvent::create([
                'trigger_source' => $validated['source'] ?? 'click',
                'context' => $validated['context'] ?? 'unknown',
            ]);
        } catch (\Throwable $e) {
            // A logging failure must never stop somebody from escaping.
            Log::warning('Panic event could not be recorded: ' . $e->getMessage());
        }
    }

    /**
     * Stop the page from being restored by the back button or bfcache.
     */
    protected function noStoreHeaders(): array
    {
        return [
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ];
    }
}
