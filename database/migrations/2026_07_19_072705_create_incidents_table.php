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
        Schema::create('incidents', function (Blueprint $table) {
            $table->id();
            $table->string('tracking_id')->unique();
            $table->string('platform');
            $table->string('region');
            $table->text('description');
            $table->dateTime('incident_date');
            $table->string('behavior_type');
            $table->string('severity');
            $table->text('overview')->nullable();
            $table->string('evidence_image')->nullable();
            $table->string('status')->default('New');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('incidents');
    }
};
