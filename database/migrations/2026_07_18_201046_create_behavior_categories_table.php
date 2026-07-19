<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('behavior_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // Seed data
        DB::table('behavior_categories')->insert([
            ['name' => 'Stalking', 'description' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Harassment', 'description' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Cyberbullying', 'description' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Impersonation', 'description' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Doxing', 'description' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Threats', 'description' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Discrimination', 'description' => null, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Sexual Harassment', 'description' => null, 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('behavior_categories');
    }
};