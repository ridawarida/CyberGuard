<?php

namespace Database\Seeders;

use App\Models\PanicSetting;
use Illuminate\Database\Seeder;

class PanicSettingSeeder extends Seeder
{
    /**
     * Seed the single active configuration row for the panic button.
     */
    public function run(): void
    {
        PanicSetting::updateOrCreate(
            ['id' => 1],
            [
                'decoy_url' => 'https://www.wikipedia.org',
                'decoy_label' => 'Wikipedia',
                'hotkey_enabled' => true,
                'hotkey_press_count' => 2,
                'hotkey_window_ms' => 800,
                'clear_form_fields' => true,
                'clear_local_storage' => true,
                'replace_history_entry' => true,
                'log_events' => true,
                'is_active' => true,
            ]
        );
    }
}
