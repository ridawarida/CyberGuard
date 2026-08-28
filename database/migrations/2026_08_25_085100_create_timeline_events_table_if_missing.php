<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SAFE FALLBACK - only creates `timeline_events` if it does not already
 * exist. Same reasoning as 2026_08_25_085000_create_incidents_table_if_missing.php,
 * this time standing in for Nahin's Module 1 timeline builder.
 *
 * Module 3 feature owner: Johra-E-Jannat Oishy.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('timeline_events')) {
            return;
        }

        Schema::create('timeline_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();
            $table->date('event_date');
            $table->string('event_time')->nullable();
            $table->string('behavior_type');
            $table->text('summary')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Deliberately a no-op - see the incidents fallback migration.
    }
};
