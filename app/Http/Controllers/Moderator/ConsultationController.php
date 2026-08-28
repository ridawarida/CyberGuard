<?php

namespace App\Http\Controllers\Moderator;

use App\Http\Controllers\Controller;
use App\Models\Consultation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Moderator's side of the consultation chat.
 * Module 3 feature owner: Johra-E-Jannat Oishy.
 *
 * Namespaced under Moderator\ the same way Module 1's admin settings
 * controller sits under Admin\ - a role-specific subnamespace, not a
 * new convention.
 */
class ConsultationController extends Controller
{
    public function index(): View
    {
        $consultations = Consultation::with('incident')
            ->withCount('messages')
            ->latest('updated_at')
            ->paginate(15);

        return view('moderator.consultations.index', compact('consultations'));
    }

    public function show(Consultation $consultation): View
    {
        $consultation->load('messages', 'incident');

        return view('moderator.consultations.show', compact('consultation'));
    }

    public function poll(Request $request, Consultation $consultation): JsonResponse
    {
        $messages = $consultation->messages()
            ->when($request->integer('after'), fn ($query, $after) => $query->where('id', '>', $after))
            ->get(['id', 'sender_type', 'body', 'created_at']);

        return response()->json(['status' => 'success', 'data' => $messages])
            ->header('Cache-Control', 'no-store');
    }

    public function store(Request $request, Consultation $consultation): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $body = trim($validated['body']);

        if ($body === '') {
            return response()->json(['status' => 'error', 'message' => 'Message cannot be empty.'], 422);
        }

        $message = $consultation->messages()->create([
            'sender_type' => 'moderator',
            'sender_id' => $request->user()->id,
            'body' => $body,
        ]);

        return response()->json(['status' => 'success', 'data' => $message]);
    }
}
