<?php

namespace App\Http\Controllers;

use App\Models\Consultation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Victim's own side of the consultation chat.
 * Module 3 feature owner: Johra-E-Jannat Oishy.
 *
 * Every method reads consultation_id from the session (set by
 * ConsultationAccessController after a valid key) rather than from any
 * request input - a victim can only ever act on their own thread, never
 * on one they merely guessed the ID of.
 */
class ConsultationController extends Controller
{
    public function show(Request $request): View
    {
        $consultation = Consultation::with('messages')
            ->findOrFail($request->session()->get('consultation_id'));

        return view('consult.chat', ['consultation' => $consultation]);
    }

    /**
     * Polled every few seconds by consultation-chat.js. ?after={id} keeps
     * the response tiny - only messages newer than what the browser
     * already has.
     */
    public function poll(Request $request): JsonResponse
    {
        $consultation = Consultation::findOrFail($request->session()->get('consultation_id'));

        $messages = $consultation->messages()
            ->when($request->integer('after'), fn ($query, $after) => $query->where('id', '>', $after))
            ->get(['id', 'sender_type', 'body', 'created_at']);

        return response()->json(['status' => 'success', 'data' => $messages])
            ->header('Cache-Control', 'no-store');
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        $body = trim($request->input('body', ''));

        // The JS client already trims and blocks whitespace-only sends,
        // but that's a UI courtesy, not a security boundary - a
        // whitespace string still passes Laravel's `required` rule, so
        // it's re-checked here for anyone posting to this endpoint
        // directly.
        

        if ($body === '') {
            return response()->json(['status' => 'error', 'message' => 'Message cannot be empty.'], 422);
        }

        $consultation = Consultation::findOrFail($request->session()->get('consultation_id'));

        $message = $consultation->messages()->create([
            'sender_type' => 'victim',
            'body' => $body,
        ]);

        return response()->json(['status' => 'success', 'data' => $message]);
    }
}
