<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Privacy note: this table is deliberately anonymous. No IP address, no
     * session id, no user id, no URL and no user agent is stored. It only
     * answers the question "how often is the escape hatch being used", which
     * feeds the admin metrics screen in Module 3.
     */
    public function up(): void
    {
        Schema::create('panic_events', function (Blueprint $table) {
            $table->id();

            // How it was fired: mouse click, keyboard hotkey, or no script fallback.
            $table->enum('trigger_source', ['click', 'hotkey', 'fallback'])->default('click');

            // Coarse area of the site only, never a full path or query string.
            $table->enum('context', ['public', 'wizard', 'case_file', 'dashboard', 'unknown'])
                ->default('unknown');

            $table->timestamps();

            $table->index(['created_at', 'trigger_source']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('panic_events');
    }
};
