<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HelpCenter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HelpDirectoryController extends Controller
{
    private const TYPES = [
        'hospital' => 'Hospital',
        'police_station' => 'Police Station',
        'clinic' => 'Clinic',
        'crisis_center' => 'Crisis Center',
        'hotline_only' => 'Hotline Only',
    ];

    private const DAYS = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];

    public function index(Request $request)
    {
        return view('admin.help-directory', [
            'helpCenters' => HelpCenter::with('hotlines')->latest()->get(),
            'editingCenter' => $request->integer('edit')
                ? HelpCenter::with('hotlines')->find($request->integer('edit'))
                : null,
            'types' => self::TYPES,
            'days' => self::DAYS,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validateDirectory($request);

        DB::transaction(function () use ($validated) {
            $helpCenter = HelpCenter::create($this->centerData($validated));
            $this->syncHotlines($helpCenter, $validated['hotlines'] ?? []);
        });

        return redirect()->route('admin.help-directory.index')->with('success', 'Help center registered successfully.');
    }

    public function update(Request $request, HelpCenter $helpCenter)
    {
        $validated = $this->validateDirectory($request);

        DB::transaction(function () use ($validated, $helpCenter) {
            $helpCenter->update($this->centerData($validated));
            $helpCenter->hotlines()->delete();
            $this->syncHotlines($helpCenter, $validated['hotlines'] ?? []);
        });

        return redirect()->route('admin.help-directory.index')->with('success', 'Help center changes saved.');
    }

    public function destroy(HelpCenter $helpCenter)
    {
        $helpCenter->delete();

        return redirect()->route('admin.help-directory.index')->with('success', 'Help center deleted.');
    }

    private function validateDirectory(Request $request): array
    {
        return $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:' . implode(',', array_keys(self::TYPES)),
            'address' => 'nullable|string|max:500',
            'city' => 'required|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'working_hours' => 'nullable|array',
            'working_hours.*' => 'nullable|string|max:100',
            'hotlines' => 'nullable|array',
            'hotlines.*.name' => 'nullable|string|max:255',
            'hotlines.*.phone_number' => 'required|string|max:30',
            'hotlines.*.is_toll_free' => 'nullable|boolean',
            'hotlines.*.description' => 'nullable|string|max:500',
            'hotlines.*.operating_hours' => 'nullable|array',
            'hotlines.*.operating_hours.*' => 'nullable|string|max:100',
        ]);
    }

    private function centerData(array $validated): array
    {
        return [
            'name' => $validated['name'],
            'type' => $validated['type'],
            'address' => $validated['address'] ?? null,
            'city' => $validated['city'],
            'state' => $validated['state'] ?? null,
            'zip_code' => $validated['zip_code'] ?? null,
            'latitude' => $validated['latitude'] ?? null,
            'longitude' => $validated['longitude'] ?? null,
            'working_hours' => $validated['working_hours'] ?? [],
            'is_active' => true,
        ];
    }

    private function syncHotlines(HelpCenter $helpCenter, array $hotlines): void
    {
        foreach ($hotlines as $hotline) {
            if (empty($hotline['phone_number'])) {
                continue;
            }

            $helpCenter->hotlines()->create([
                'name' => $hotline['name'] ?? null,
                'phone_number' => $hotline['phone_number'],
                'is_toll_free' => ! empty($hotline['is_toll_free']),
                'description' => $hotline['description'] ?? null,
                'operating_hours' => $hotline['operating_hours'] ?? [],
                'is_active' => true,
            ]);
        }
    }
}
