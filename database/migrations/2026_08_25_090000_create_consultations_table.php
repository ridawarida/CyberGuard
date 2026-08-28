<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Digital Safe Space consultations.
 * Module 3 feature owner: Johra-E-Jannat Oishy.
 *
 * ASSUMPTION: this assumes an existing `incidents` table with an
 * auto-incrementing `id` (Ishrat's Module 1 intake wizard - the PDF spec
 * confirms the table is literally named "incidents"). If your real table
 * uses a different name, change the foreignId()->constrained() call below
 * to point at it.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('incident_id')->constrained()->cascadeOnDelete();

            // Deliberately NOT the same as incidents.tracking_code - that
            // code is designed to be pasted into a public status-lookup
            // page, so it can't double as a private chat credential.
            $table->string('access_key', 64)->unique();

            $table->string('status', 20)->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultations');
    }
};
