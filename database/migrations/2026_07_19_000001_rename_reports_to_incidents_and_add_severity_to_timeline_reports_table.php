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
        if (Schema::hasTable('reports') && ! Schema::hasTable('incidents')) {
            Schema::rename('reports', 'incidents');
        }

        if (Schema::hasTable('incidents') && ! Schema::hasColumn('incidents', 'severity')) {
            Schema::table('incidents', function (Blueprint $table) {
                $table->string('severity')->nullable()->after('behavior_type');
            });
        }

        if (Schema::hasTable('timeline_reports') && ! Schema::hasColumn('timeline_reports', 'severity')) {
            Schema::table('timeline_reports', function (Blueprint $table) {
                $table->string('severity')->nullable()->after('behavior_type');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('incidents') && ! Schema::hasTable('reports')) {
            Schema::rename('incidents', 'reports');
        }

        if (Schema::hasTable('timeline_reports') && Schema::hasColumn('timeline_reports', 'severity')) {
            Schema::table('timeline_reports', function (Blueprint $table) {
                $table->dropColumn('severity');
            });
        }
    }
};
