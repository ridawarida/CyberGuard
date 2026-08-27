<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recovery_journal_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('recovery_journal_id')->constrained()->cascadeOnDelete();
            $table->text('summary');
            $table->unsignedTinyInteger('stress_level');
            $table->timestamps();

            $table->index(['recovery_journal_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recovery_journal_entries');
    }
};