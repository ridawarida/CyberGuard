<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('incidents', function (Blueprint $table) {

            // Separate text analysis
            $table->unsignedTinyInteger('ai_text_risk_score')
                ->nullable()
                ->after('ai_scanned_at');

            $table->string('ai_text_risk_level')
                ->nullable()
                ->after('ai_text_risk_score');

            $table->text('ai_text_reason')
                ->nullable()
                ->after('ai_text_risk_level');

            // Separate evidence/image analysis
            $table->unsignedTinyInteger('ai_image_risk_score')
                ->nullable()
                ->after('ai_text_reason');

            $table->string('ai_image_risk_level')
                ->nullable()
                ->after('ai_image_risk_score');

            $table->text('ai_image_reason')
                ->nullable()
                ->after('ai_image_risk_level');
        });
    }

    public function down(): void
    {
        Schema::table('incidents', function (Blueprint $table) {

            $table->dropColumn([
                'ai_text_risk_score',
                'ai_text_risk_level',
                'ai_text_reason',
                'ai_image_risk_score',
                'ai_image_risk_level',
                'ai_image_reason',
            ]);
        });
    }
};