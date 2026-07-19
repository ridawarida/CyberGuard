<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class CyberGuardDataSeeder extends Seeder
{
    public function run(): void
    {
        // Insert behavior categories (if not already seeded)
        if (DB::table('behavior_categories')->count() == 0) {
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

        // Insert incidents
        $incidents = [
            [
                'tracking_id' => 'rp4X9b2K1pQ4z8w3M',
                'platform' => 'Instagram',
                'region' => 'Dhaka',
                'description' => 'User sent threatening messages about personal information',
                'incident_date' => Carbon::parse('2025-01-05 10:30:00'),
                'behavior_type' => 'Threats',
                'severity' => 'High',
                'overview' => 'Received a direct message saying "I know where you live"',
                'evidence_image' => 'uploads/threat1.jpg',
                'status' => 'New',
                'created_at' => Carbon::parse('2025-01-05 11:00:00'),
                'updated_at' => Carbon::parse('2025-01-05 11:00:00'),
            ],
            [
                'tracking_id' => 'rp7Y3c4L2qR5a9x4N',
                'platform' => 'Facebook',
                'region' => 'Chattogram',
                'description' => 'Fake account using my photos and harassing friends',
                'incident_date' => Carbon::parse('2025-01-10 15:20:00'),
                'behavior_type' => 'Impersonation',
                'severity' => 'Medium',
                'overview' => 'Someone created a fake account with my name and photos',
                'evidence_image' => 'uploads/fake1.jpg',
                'status' => 'Investigating',
                'created_at' => Carbon::parse('2025-01-10 16:00:00'),
                'updated_at' => Carbon::parse('2025-01-12 09:30:00'),
            ],
            [
                'tracking_id' => 'rp2Z8d5M3sT6b1y7P',
                'platform' => 'Instagram',
                'region' => 'Dhaka',
                'description' => 'Received follow-up death threat after blocking user',
                'incident_date' => Carbon::parse('2025-01-15 14:30:00'),
                'behavior_type' => 'Threats',
                'severity' => 'High',
                'overview' => 'User created new account to continue harassment',
                'evidence_image' => 'uploads/threat2.jpg',
                'status' => 'Resolved',
                'created_at' => Carbon::parse('2025-01-15 15:00:00'),
                'updated_at' => Carbon::parse('2025-01-18 10:00:00'),
            ],
            [
                'tracking_id' => 'rp9X4c7N2qR8b2y5Q',
                'platform' => 'TikTok',
                'region' => 'Sylhet',
                'description' => 'Viral video of me edited maliciously to shame me',
                'incident_date' => Carbon::parse('2025-01-20 09:15:00'),
                'behavior_type' => 'Harassment',
                'severity' => 'Medium',
                'overview' => 'Someone took my video and added defamatory text overlay',
                'evidence_image' => 'uploads/tiktok1.jpg',
                'status' => 'New',
                'created_at' => Carbon::parse('2025-01-20 09:45:00'),
                'updated_at' => Carbon::parse('2025-01-20 09:45:00'),
            ],
            [
                'tracking_id' => 'rp6K3d8M1sT7c3z6R',
                'platform' => 'WhatsApp',
                'region' => 'Rajshahi',
                'description' => 'Receiving unsolicited explicit images in group chat',
                'incident_date' => Carbon::parse('2025-01-22 20:00:00'),
                'behavior_type' => 'Sexual Harassment',
                'severity' => 'High',
                'overview' => 'Group member sending pornographic content in study group',
                'evidence_image' => 'uploads/whatsapp1.jpg',
                'status' => 'Investigating',
                'created_at' => Carbon::parse('2025-01-22 20:30:00'),
                'updated_at' => Carbon::parse('2025-01-23 08:00:00'),
            ],
            [
                'tracking_id' => 'rp1W5e9N3qT4d8x2S',
                'platform' => 'Twitter',
                'region' => 'Khulna',
                'description' => 'Multiple tweets targeting me with hate speech',
                'incident_date' => Carbon::parse('2025-01-25 12:00:00'),
                'behavior_type' => 'Discrimination',
                'severity' => 'Medium',
                'overview' => 'Racist and xenophobic comments on my timeline',
                'evidence_image' => 'uploads/twitter1.jpg',
                'status' => 'New',
                'created_at' => Carbon::parse('2025-01-25 12:30:00'),
                'updated_at' => Carbon::parse('2025-01-25 12:30:00'),
            ],
            [
                'tracking_id' => 'rp8V6f2M4sU5e9y3T',
                'platform' => 'Instagram',
                'region' => 'Barishal',
                'description' => 'Stalking my stories and sending threatening DMs weekly',
                'incident_date' => Carbon::parse('2025-01-28 18:45:00'),
                'behavior_type' => 'Stalking',
                'severity' => 'High',
                'overview' => 'Someone consistently monitors my Instagram activity and messages threats',
                'evidence_image' => 'uploads/stalker1.jpg',
                'status' => 'Resolved',
                'created_at' => Carbon::parse('2025-01-28 19:00:00'),
                'updated_at' => Carbon::parse('2025-02-01 14:00:00'),
            ],
        ];

        foreach ($incidents as $incident) {
            DB::table('incidents')->updateOrInsert(
                ['tracking_id' => $incident['tracking_id']],
                $incident
            );
        }

        // Insert timelines
        $timelines = [
            [
                'tracking_id' => 'tl7X9b2K1pQ4z8w3M',
                'description' => 'Instagram stalking and harassment from January 2025',
                'category' => 'Stalking',
                'created_at' => Carbon::parse('2025-01-20 14:30:00'),
                'updated_at' => Carbon::parse('2025-01-20 15:00:00'),
            ],
            [
                'tracking_id' => 'tl4Y8c3L2qR5a9x4N',
                'description' => 'Social media impersonation incidents across multiple platforms',
                'category' => 'Impersonation',
                'created_at' => Carbon::parse('2025-01-21 10:00:00'),
                'updated_at' => Carbon::parse('2025-01-21 10:30:00'),
            ],
            [
                'tracking_id' => 'tl2Z8d5M3sT6b1y7P',
                'description' => 'Ongoing threats and harassment timeline',
                'category' => 'Threats',
                'created_at' => Carbon::parse('2025-01-22 08:00:00'),
                'updated_at' => Carbon::parse('2025-01-22 09:00:00'),
            ],
        ];

        foreach ($timelines as $timeline) {
            DB::table('timelines')->updateOrInsert(
                ['tracking_id' => $timeline['tracking_id']],
                $timeline
            );
        }

        // Insert timeline_reports
        $timelineReports = [
            // Timeline 1 (Stalking)
            [
                'timeline_id' => 1,
                'report_tracking_id' => 'rp4X9b2K1pQ4z8w3M',
                'report_overview' => 'First threatening message received',
                'report_incident_date' => Carbon::parse('2025-01-05 10:30:00'),
                'report_platform' => 'Instagram',
                'report_region' => 'Dhaka',
                'behavior_type' => 'Threats',
                'severity' => 'High',
                'added_at' => Carbon::parse('2025-01-20 14:35:00'),
                'created_at' => Carbon::parse('2025-01-20 14:35:00'),
                'updated_at' => Carbon::parse('2025-01-20 14:35:00'),
            ],
            [
                'timeline_id' => 1,
                'report_tracking_id' => 'rp2Z8d5M3sT6b1y7P',
                'report_overview' => 'Escalation after blocking first account',
                'report_incident_date' => Carbon::parse('2025-01-15 14:30:00'),
                'report_platform' => 'Instagram',
                'report_region' => 'Dhaka',
                'behavior_type' => 'Threats',
                'severity' => 'High',
                'added_at' => Carbon::parse('2025-01-20 14:40:00'),
                'created_at' => Carbon::parse('2025-01-20 14:40:00'),
                'updated_at' => Carbon::parse('2025-01-20 14:40:00'),
            ],
            [
                'timeline_id' => 1,
                'report_tracking_id' => 'rp8V6f2M4sU5e9y3T',
                'report_overview' => 'Continued stalking and threats',
                'report_incident_date' => Carbon::parse('2025-01-28 18:45:00'),
                'report_platform' => 'Instagram',
                'report_region' => 'Barishal',
                'behavior_type' => 'Stalking',
                'severity' => 'High',
                'added_at' => Carbon::parse('2025-01-28 19:00:00'),
                'created_at' => Carbon::parse('2025-01-28 19:00:00'),
                'updated_at' => Carbon::parse('2025-01-28 19:00:00'),
            ],
            // Timeline 2 (Impersonation)
            [
                'timeline_id' => 2,
                'report_tracking_id' => 'rp7Y3c4L2qR5a9x4N',
                'report_overview' => 'Fake Instagram account discovered',
                'report_incident_date' => Carbon::parse('2025-01-10 15:20:00'),
                'report_platform' => 'Facebook',
                'report_region' => 'Chattogram',
                'behavior_type' => 'Impersonation',
                'severity' => 'Medium',
                'added_at' => Carbon::parse('2025-01-21 10:05:00'),
                'created_at' => Carbon::parse('2025-01-21 10:05:00'),
                'updated_at' => Carbon::parse('2025-01-21 10:05:00'),
            ],
            [
                'timeline_id' => 2,
                'report_tracking_id' => 'rp9X4c7N2qR8b2y5Q',
                'report_overview' => 'Malicious video posted on TikTok',
                'report_incident_date' => Carbon::parse('2025-01-20 09:15:00'),
                'report_platform' => 'TikTok',
                'report_region' => 'Sylhet',
                'behavior_type' => 'Harassment',
                'severity' => 'Medium',
                'added_at' => Carbon::parse('2025-01-21 10:10:00'),
                'created_at' => Carbon::parse('2025-01-21 10:10:00'),
                'updated_at' => Carbon::parse('2025-01-21 10:10:00'),
            ],
            // Timeline 3 (Threats)
            [
                'timeline_id' => 3,
                'report_tracking_id' => 'rp4X9b2K1pQ4z8w3M',
                'report_overview' => 'First threatening message',
                'report_incident_date' => Carbon::parse('2025-01-05 10:30:00'),
                'report_platform' => 'Instagram',
                'report_region' => 'Dhaka',
                'behavior_type' => 'Threats',
                'severity' => 'High',
                'added_at' => Carbon::parse('2025-01-22 08:10:00'),
                'created_at' => Carbon::parse('2025-01-22 08:10:00'),
                'updated_at' => Carbon::parse('2025-01-22 08:10:00'),
            ],
            [
                'timeline_id' => 3,
                'report_tracking_id' => 'rp2Z8d5M3sT6b1y7P',
                'report_overview' => 'Second threatening message after block',
                'report_incident_date' => Carbon::parse('2025-01-15 14:30:00'),
                'report_platform' => 'Instagram',
                'report_region' => 'Dhaka',
                'behavior_type' => 'Threats',
                'severity' => 'High',
                'added_at' => Carbon::parse('2025-01-22 08:15:00'),
                'created_at' => Carbon::parse('2025-01-22 08:15:00'),
                'updated_at' => Carbon::parse('2025-01-22 08:15:00'),
            ],
            [
                'timeline_id' => 3,
                'report_tracking_id' => 'rp1W5e9N3qT4d8x2S',
                'report_overview' => 'Hate speech tweets on Twitter',
                'report_incident_date' => Carbon::parse('2025-01-25 12:00:00'),
                'report_platform' => 'Twitter',
                'report_region' => 'Khulna',
                'behavior_type' => 'Discrimination',
                'severity' => 'Medium',
                'added_at' => Carbon::parse('2025-01-25 12:30:00'),
                'created_at' => Carbon::parse('2025-01-25 12:30:00'),
                'updated_at' => Carbon::parse('2025-01-25 12:30:00'),
            ],
        ];

        foreach ($timelineReports as $report) {
            DB::table('timeline_reports')->insert($report);
        }
    }
}