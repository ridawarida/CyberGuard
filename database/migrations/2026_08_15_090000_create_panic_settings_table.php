<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Holds the admin configurable behaviour of the Quick Escape Panic Button.
     * Only one row is expected to be active at a time.
     */
    public function up(): void
    {
        Schema::create('panic_settings', function (Blueprint $table) {
            $table->id();

            // Where the browser tab is sent after the panic button fires.
            $table->string('decoy_url')->default('https://www.wikipedia.org');
            $table->string('decoy_label')->default('Wikipedia');

            // Keyboard trigger configuration (Escape pressed N times within a window).
            $table->boolean('hotkey_enabled')->default(true);
            $table->unsignedTinyInteger('hotkey_press_count')->default(2);
            $table->unsignedSmallInteger('hotkey_window_ms')->default(800);

            // Client side wipe toggles read by the panic script.
            $table->boolean('clear_form_fields')->default(true);
            $table->boolean('clear_local_storage')->default(true);
            $table->boolean('replace_history_entry')->default(true);

            // Anonymous usage counter toggle. No user data is ever recorded.
            $table->boolean('log_events')->default(true);

            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panic_settings');
    }
};
