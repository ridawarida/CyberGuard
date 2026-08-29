<?php

namespace App\Http\Controllers;

use App\Models\PlatformPolicy;
use Illuminate\Http\Request;

class PlatformPolicyController extends Controller
{
    public function index()
    {
        $policies = PlatformPolicy::orderBy('platform')->get();

        return view('moderator.platform_policies.index', compact('policies'));
    }

    public function create()
    {
        return view('moderator.platform_policies.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'platform' => 'required|string|max:255',
            'reporting_url' => 'required|url|max:255',
            'instructions' => 'required|string',
            'last_verified_at' => 'nullable|date',
        ]);

        PlatformPolicy::create($validated);

        return redirect()
            ->route('moderator.platform-policies.index')
            ->with('success', 'Platform policy created successfully.');
    }

    public function edit(PlatformPolicy $platformPolicy)
    {
        return view('moderator.platform_policies.edit', compact('platformPolicy'));
    }

    public function update(Request $request, PlatformPolicy $platformPolicy)
    {
        $validated = $request->validate([
            'platform' => 'required|string|max:255',
            'reporting_url' => 'required|url|max:255',
            'instructions' => 'required|string',
            'last_verified_at' => 'nullable|date',
        ]);

        $platformPolicy->update($validated);

        return redirect()
            ->route('moderator.platform-policies.index')
            ->with('success', 'Platform policy updated successfully.');
    }

    public function destroy(PlatformPolicy $platformPolicy)
    {
        $platformPolicy->delete();

        return redirect()
            ->route('moderator.platform-policies.index')
            ->with('success', 'Platform policy deleted successfully.');
    }
    // User-facing platform policies
    public function userIndex()
    {
        $policies = PlatformPolicy::orderBy('platform')->get();

        return view('platform_policies.index', compact('policies'));
    }
}