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
        Schema::create('timeline_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('timeline_id')->constrained()->onDelete('cascade');
            $table->string('report_tracking_id');
            $table->text('report_overview');
            $table->dateTime('report_incident_date');
            $table->string('report_platform');
            $table->string('report_region');
            $table->string('behavior_type');
            $table->string('severity')->nullable();
            $table->timestamp('added_at')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('timeline_reports');
    }
};