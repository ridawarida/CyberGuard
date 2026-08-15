<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PanicEvent;
use App\Models\PanicSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

/**
 * Admin control panel for the Quick Escape Panic Button.
 * Guarded by auth:sanctum plus role:admin in routes/panic.php.
 */
class PanicSettingController extends Controller
{
    /**
     * GET /panic/admin/settings
     */
    public function show(): JsonResponse
    {
        return response()->json([
            'status' => 'success',
            'data' => PanicSetting::active(),
        ]);
    }

    /**
     * PUT /panic/admin/settings
     */
    public function update(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'decoy_url' => 'sometimes|required|url|starts_with:https://|max:255',
            'decoy_label' => 'sometimes|required|string|max:100',
            'hotkey_enabled' => 'sometimes|boolean',
            'hotkey_press_count' => 'sometimes|integer|min:1|max:5',
            'hotkey_window_ms' => 'sometimes|integer|min:200|max:5000',
            'clear_form_fields' => 'sometimes|boolean',
            'clear_local_storage' => 'sometimes|boolean',
            'replace_history_entry' => 'sometimes|boolean',
            'log_events' => 'sometimes|boolean',
        ]);

        $validator->after(function ($validator) use ($request) {
            if (! $request->filled('decoy_url')) {
                return;
            }

            // A decoy that points back at CyberGuard would defeat the feature.
            $decoyHost = parse_url($request->input('decoy_url'), PHP_URL_HOST);
            $appHost = parse_url(config('app.url'), PHP_URL_HOST);

            if ($decoyHost && $appHost && strcasecmp($decoyHost, $appHost) === 0) {
                $validator->errors()->add(
                    'decoy_url',
                    'The decoy site cannot be CyberGuard itself.'
                );
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors(),
            ], 422);
        }

        $setting = PanicSetting::where('is_active', true)->orderByDesc('id')->first();

        if (! $setting) {
            $setting = PanicSetting::create(PanicSetting::FALLBACK);
        }

        $setting->fill($validator->validated())->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Panic button settings updated',
            'data' => $setting->fresh(),
        ]);
    }

    /**
     * GET /panic/admin/stats
     *
     * Aggregate counts only. Feeds the Module 3 metrics workspace.
     */
    public function stats(Request $request): JsonResponse
    {
        $months = (int) $request->query('months', 6);
        $months = max(1, min($months, 24));
        $since = now()->subMonths($months)->startOfMonth();

        $byMonth = PanicEvent::query()
            ->where('created_at', '>=', $since)
            ->selectRaw('DATE_FORMAT(created_at, "%Y-%m") as period, COUNT(*) as total')
            ->groupBy('period')
            ->orderBy('period')
            ->get();

        $bySource = PanicEvent::query()
            ->where('created_at', '>=', $since)
            ->selectRaw('trigger_source, COUNT(*) as total')
            ->groupBy('trigger_source')
            ->pluck('total', 'trigger_source');

        return response()->json([
            'status' => 'success',
            'data' => [
                'total_escapes' => PanicEvent::where('created_at', '>=', $since)->count(),
                'by_month' => $byMonth,
                'by_source' => $bySource,
            ],
        ]);
    }
}
