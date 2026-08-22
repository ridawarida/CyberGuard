<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

it('returns incident severity in the case file detail payload', function () {
    Schema::dropIfExists('case_file_incidents');
    Schema::dropIfExists('incidents');
    Schema::dropIfExists('case_files');

    Schema::create('case_files', function (Blueprint $table) {
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

    Schema::create('case_file_incidents', function (Blueprint $table) {
        $table->id();
        $table->foreignId('case_file_id');
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

    DB::table('case_files')->insert([
        'tracking_id' => 'cf-test-001',
        'description' => 'Test case file',
        'category' => 'Threats',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $caseFileId = DB::table('case_files')->where('tracking_id', 'cf-test-001')->value('id');

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

    DB::table('case_file_incidents')->insert([
        'case_file_id' => $caseFileId,
        'incident_tracking_id' => 'rp-test-001',
        'incident_overview' => 'Threatening message received',
        'incident_date' => now(),
        'incident_platform' => 'Instagram',
        'incident_region' => 'Dhaka',
        'behavior_type' => 'Threats',
        'severity' => 'High',
        'added_at' => now(),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $response = $this->getJson('/api/case-files/cf-test-001');

    $response
        ->assertStatus(200)
        ->assertJsonPath('status', 'success')
        ->assertJsonPath('data.incidents.0.severity', 'High');
});
