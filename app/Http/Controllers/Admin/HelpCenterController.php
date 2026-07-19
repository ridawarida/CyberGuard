<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HelpCenter;
use App\Models\Hotline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class HelpCenterController extends Controller
{
    /**
     * Display a listing of help centers.
     */
    public function index(Request $request)
    {
        $query = HelpCenter::with('hotlines');

        // Filter by type
        if ($request->has('type') && $request->type) {
            $query->where('type', $request->type);
        }

        // Filter by city
        if ($request->has('city') && $request->city) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        // Filter by active status
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $helpCenters = $query->paginate(15);

        return response()->json([
            'status' => 'success',
            'data' => $helpCenters
        ]);
    }

    /**
     * Store a newly created help center.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'type' => 'required|in:hospital,police_station,clinic,crisis_center,hotline_only',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'working_hours' => 'nullable|array',
            'is_active' => 'boolean',
            'hotlines' => 'nullable|array',
            'hotlines.*.name' => 'nullable|string|max:255',
            'hotlines.*.phone_number' => 'required|string|max:20',
            'hotlines.*.is_toll_free' => 'boolean',
            'hotlines.*.description' => 'nullable|string|max:500',
            'hotlines.*.operating_hours' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        DB::beginTransaction();

        try {
            // Create help center
            $helpCenter = HelpCenter::create([
                'name' => $request->name,
                'type' => $request->type,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'zip_code' => $request->zip_code,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'working_hours' => $request->working_hours,
                'is_active' => $request->is_active ?? true,
            ]);

            // Create hotlines if provided
            if ($request->has('hotlines')) {
                foreach ($request->hotlines as $hotlineData) {
                    $helpCenter->hotlines()->create([
                        'name' => $hotlineData['name'] ?? null,
                        'phone_number' => $hotlineData['phone_number'],
                        'is_toll_free' => $hotlineData['is_toll_free'] ?? false,
                        'description' => $hotlineData['description'] ?? null,
                        'operating_hours' => $hotlineData['operating_hours'] ?? null,
                        'is_active' => true,
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Help center created successfully',
                'data' => $helpCenter->load('hotlines')
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to create help center: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified help center.
     */
    public function show($id)
    {
        $helpCenter = HelpCenter::with('hotlines')->find($id);

        if (!$helpCenter) {
            return response()->json([
                'status' => 'error',
                'message' => 'Help center not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $helpCenter
        ]);
    }

    /**
     * Update the specified help center.
     */
    public function update(Request $request, $id)
    {
        $helpCenter = HelpCenter::find($id);

        if (!$helpCenter) {
            return response()->json([
                'status' => 'error',
                'message' => 'Help center not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:255',
            'type' => 'sometimes|in:hospital,police_station,clinic,crisis_center,hotline_only',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'zip_code' => 'nullable|string|max:20',
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
            'working_hours' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $helpCenter->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Help center updated successfully',
            'data' => $helpCenter->load('hotlines')
        ]);
    }

    /**
     * Remove the specified help center.
     */
    public function destroy($id)
    {
        $helpCenter = HelpCenter::find($id);

        if (!$helpCenter) {
            return response()->json([
                'status' => 'error',
                'message' => 'Help center not found'
            ], 404);
        }

        $helpCenter->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Help center deleted successfully'
        ]);
    }
}