<?php

namespace App\Http\Controllers;

use App\Services\HelpCenterLocatorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class HelpCenterDirectoryController extends Controller
{
    public function __construct(private readonly HelpCenterLocatorService $locator)
    {
    }

    public function index(): View
    {
        return view('help-centers.index');
    }

    public function nearby(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'city' => ['nullable', 'string', 'max:100'],
        ]);

        try {
            return response()->json([
                'status' => 'success',
                'data' => $this->locator->locate($request->ip(), $validated['city'] ?? null),
            ]);
        } catch (\Throwable $exception) {
            Log::warning('Help center location lookup failed.', [
                'message' => $exception->getMessage(),
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'We could not find your approximate location. Try searching by city instead.',
            ], 503);
        }
    }
}