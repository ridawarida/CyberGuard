<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Digital Safe Space - Secure Consultation Workspace.
 * Module 3 feature owner: Johra-E-Jannat Oishy.
 *
 * Design rule: the raw access_key never sits in a URL. It is accepted
 * once, from a POSTed form, and then only its session pointer
 * (consultation_id) is kept - so it can't leak through browser history,
 * a shared screenshot of the address bar, or a referrer header the way a
 * key embedded in GET /consult/{key} could.
 */
class ConsultationAccessController extends Controller
{
    public function show(): View
    {
        return view('consult.access');
    }

    public function authenticate(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'access_key' => ['required', 'string'],
        ]);

        $consultation = Consultation::findByAccessKey(trim($validated['access_key']));

        if (! $consultation) {
            return back()->withErrors([
                'access_key' => 'That access key was not recognized. Please check it and try again.',
            ]);
        }

        // Regenerate on privilege change, same reasoning as any login.
        // regenerate()'s default $destroy=false keeps session data (the
        // intended-URL below included) - only the session ID rotates.
        $request->session()->regenerate();
        $request->session()->put('consultation_id', $consultation->id);
        $request->session()->put('incident_id', $consultation->incident_id);

        // Sends a bookmarked/direct link (e.g. straight to the PDF
        // export page) back to itself instead of always landing on chat.
        $intended = $request->session()->pull('victim.intended_url');

        return redirect($intended ?: route('consult.session'));
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget(['consultation_id', 'incident_id']);
        $request->session()->regenerate();

        return redirect()->route('consult.access');
    }
}
