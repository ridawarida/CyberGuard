<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Adds the case-ownership and review fields used by the moderator
     * incident assessment workspace.
     */
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            // Which moderator has claimed (locked) this case. Null = still in the open pool.
            $table->unsignedBigInteger('assigned_moderator_id')->nullable()->after('status');
            $table->timestamp('claimed_at')->nullable()->after('assigned_moderator_id');

            // Internal evaluation notes, never shown to the anonymous reporter.
            $table->text('moderator_notes')->nullable()->after('claimed_at');
            $table->timestamp('reviewed_at')->nullable()->after('moderator_notes');

            $table->index('assigned_moderator_id');
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropIndex(['assigned_moderator_id']);
            $table->dropIndex(['status']);

            $table->dropColumn([
                'assigned_moderator_id',
                'claimed_at',
                'moderator_notes',
                'reviewed_at',
            ]);
        });
    }
};
