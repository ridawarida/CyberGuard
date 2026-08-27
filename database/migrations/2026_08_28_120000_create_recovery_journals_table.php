<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('recovery_journals', function (Blueprint $table) {
            $table->id();
            $table->string('access_code_hash', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('recovery_journals');
    }
};