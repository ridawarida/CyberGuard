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
        Schema::create('case_file_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('case_file_id')->constrained('case_files')->onDelete('cascade');
            $table->string('incident_tracking_id');
            $table->text('incident_overview');
            $table->dateTime('incident_date');
            $table->string('incident_platform');
            $table->string('incident_region');
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
        Schema::dropIfExists('case_file_incidents');
    }
};