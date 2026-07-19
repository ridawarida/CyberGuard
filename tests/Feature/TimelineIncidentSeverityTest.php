<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('returns incident severity in the timeline detail payload', function () {
    Schema::dropIfExists('timeline_reports');
    Schema::dropIfExists('incidents');
    Schema::dropIfExists('timelines');

    Schema::create('timelines', function (Blueprint $table) {
        $table->id();
        $table->string('tracking_id')->unique();
        $table->text('description');
        $table->string('category');
        $table->timestamps();
    });

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

    Schema::create('timeline_reports', function (Blueprint $table) {
        $table->id();
        $table->foreignId('timeline_id');
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

    DB::table('timelines')->insert([
        'tracking_id' => 'tl-test-001',
        'description' => 'Test timeline',
        'category' => 'Threats',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $timelineId = DB::table('timelines')->where('tracking_id', 'tl-test-001')->value('id');

    DB::table('incidents')->insert([
        'tracking_id' => 'rp-test-001',
        'platform' => 'Instagram',
        'region' => 'Dhaka',
        'description' => 'Threatening message',
        'incident_date' => now(),
        'behavior_type' => 'Threats',
        'severity' => 'High',
        'overview' => 'Threatening message received',
        'status' => 'New',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('timeline_reports')->insert([
        'timeline_id' => $timelineId,
        'report_tracking_id' => 'rp-test-001',
        'report_overview' => 'Threatening message received',
        'report_incident_date' => now(),
        'report_platform' => 'Instagram',
        'report_region' => 'Dhaka',
        'behavior_type' => 'Threats',
        'severity' => 'High',
        'added_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->getJson('/api/timeline/tl-test-001');

    $response
        ->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.reports.0.severity', 'High');
});
