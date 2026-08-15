<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PanicSetting extends Model
{
    use HasFactory;

    protected $table = 'panic_settings';

    protected $fillable = [
        'decoy_url',
        'decoy_label',
        'hotkey_enabled',
        'hotkey_press_count',
        'hotkey_window_ms',
        'clear_form_fields',
        'clear_local_storage',
        'replace_history_entry',
        'log_events',
        'is_active',
    ];

    protected $casts = [
        'hotkey_enabled' => 'boolean',
        'hotkey_press_count' => 'integer',
        'hotkey_window_ms' => 'integer',
        'clear_form_fields' => 'boolean',
        'clear_local_storage' => 'boolean',
        'replace_history_entry' => 'boolean',
        'log_events' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Hard coded fallback used when the table is empty or unreachable.
     * The panic button must never fail just because the database is not seeded.
     */
    public const FALLBACK = [
        'decoy_url' => 'https://www.wikipedia.org',
        'decoy_label' => 'Wikipedia',
        'hotkey_enabled' => true,
        'hotkey_press_count' => 2,
        'hotkey_window_ms' => 800,
        'clear_form_fields' => true,
        'clear_local_storage' => true,
        'replace_history_entry' => true,
        'log_events' => false,
        'is_active' => true,
    ];

    /**
     * Return the active settings row, or an unsaved model carrying the
     * fallback values so callers always get a usable object.
     */
    public static function active(): self
    {
        try {
            $setting = static::where('is_active', true)->orderByDesc('id')->first();
        } catch (\Throwable $e) {
            $setting = null;
        }

        return $setting ?: new static(self::FALLBACK);
    }

    /**
     * Shape the settings for the browser. Only presentation flags leave the
     * server, never database ids or timestamps.
     */
    public function toClientPayload(): array
    {
        return [
            'decoy_url' => $this->decoy_url,
            'decoy_label' => $this->decoy_label,
            'hotkey_enabled' => (bool) $this->hotkey_enabled,
            'hotkey_press_count' => (int) $this->hotkey_press_count,
            'hotkey_window_ms' => (int) $this->hotkey_window_ms,
            'clear_form_fields' => (bool) $this->clear_form_fields,
            'clear_local_storage' => (bool) $this->clear_local_storage,
            'replace_history_entry' => (bool) $this->replace_history_entry,
        ];
    }
}
