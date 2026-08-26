<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->unsignedTinyInteger('ai_risk_score')
                ->nullable()
                ->after('evidence_image');

            $table->string('ai_risk_level')
                ->nullable()
                ->after('ai_risk_score');

            $table->text('ai_reason')
                ->nullable()
                ->after('ai_risk_level');

            $table->timestamp('ai_scanned_at')
                ->nullable()
                ->after('ai_reason');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {
            $table->dropColumn([
                'ai_risk_score',
                'ai_risk_level',
                'ai_reason',
                'ai_scanned_at',
            ]);
        });
    }
};