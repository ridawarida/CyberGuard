<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HelpCenter;
use App\Models\Hotline;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class HotlineController extends Controller
{
    /**
     * Display a listing of hotlines.
     */
    public function index(Request $request)
    {
        $query = Hotline::with('helpCenter');

        if ($request->has('help_center_id')) {
            $query->where('help_center_id', $request->help_center_id);
        }

        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        }

        $hotlines = $query->paginate(20);

        return response()->json([
            'status' => 'success',
            'data' => $hotlines
        ]);
    }

    /**
     * Store a newly created hotline.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'help_center_id' => 'required|exists:help_centers,id',
            'name' => 'nullable|string|max:255',
            'phone_number' => 'required|string|max:20',
            'is_toll_free' => 'boolean',
            'description' => 'nullable|string|max:500',
            'operating_hours' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $hotline = Hotline::create([
            'help_center_id' => $request->help_center_id,
            'name' => $request->name,
            'phone_number' => $request->phone_number,
            'is_toll_free' => $request->is_toll_free ?? false,
            'description' => $request->description,
            'operating_hours' => $request->operating_hours,
            'is_active' => $request->is_active ?? true,
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Hotline created successfully',
            'data' => $hotline->load('helpCenter')
        ], 201);
    }

    /**
     * Update the specified hotline.
     */
    public function update(Request $request, $id)
    {
        $hotline = Hotline::find($id);

        if (!$hotline) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hotline not found'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'name' => 'nullable|string|max:255',
            'phone_number' => 'sometimes|string|max:20',
            'is_toll_free' => 'boolean',
            'description' => 'nullable|string|max:500',
            'operating_hours' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $hotline->update($request->all());

        return response()->json([
            'status' => 'success',
            'message' => 'Hotline updated successfully',
            'data' => $hotline->load('helpCenter')
        ]);
    }

    /**
     * Remove the specified hotline.
     */
    public function destroy($id)
    {
        $hotline = Hotline::find($id);

        if (!$hotline) {
            return response()->json([
                'status' => 'error',
                'message' => 'Hotline not found'
            ], 404);
        }

        $hotline->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Hotline deleted successfully'
        ]);
    }
}