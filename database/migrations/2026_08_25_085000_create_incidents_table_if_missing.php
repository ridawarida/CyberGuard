<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * SAFE FALLBACK - only creates `incidents` if it does not already exist.
 * Module 3 feature owner: Johra-E-Jannat Oishy.
 *
 * Ishrat's Module 1 intake wizard should already create this table with
 * its own migration. This one checks at runtime (Schema::hasTable), not
 * by migration filename, so it is a genuine no-op once her real table
 * exists - safe to leave in the codebase permanently. It exists only so
 * Module 3 can run and be tested on its own before the team's code is
 * merged.
 *
 * Columns are inferred from the assignment spec text, not from Ishrat's
 * actual migration. If her real columns differ, this fallback schema
 * simply won't match them - but since it never runs once her table
 * exists, it also can't conflict with it.
 */
return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('incidents')) {
            return;
        }

        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_code')->unique();
            $table->string('platform')->nullable();
            $table->date('incident_date')->nullable();
            $table->text('description')->nullable();
            $table->string('status')->default('New');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        // Deliberately a no-op. If this migration is the one that created
        // the table, dropping it here could also destroy real data added
        // later by Ishrat's actual module. Drop it manually if you are
        // certain it should go.
    }
};
