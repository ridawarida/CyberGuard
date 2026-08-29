
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
        Schema::create('platform_policies', function (Blueprint $table) {
            $table->id();

            // Platform name, e.g. Instagram, TikTok, YouTube
            $table->string('platform');

            // Official external reporting/safety URL
            $table->string('reporting_url');

            // Instructions for moderators/users
            $table->text('instructions')->nullable();

            // Date when the moderator last verified the link/instructions
            $table->date('last_verified_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('platform_policies');
    }
};