<?php

namespace Database\Seeders;

use App\Models\PlatformPolicy;
use Illuminate\Database\Seeder;

class PlatformPolicySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $policies = [
            [
                'platform' => 'Instagram',
                'reporting_url' => 'https://help.instagram.com/contact/584460464953450',
                'instructions' => "1. Tap the three dots (...) in the top right corner of the post or message.\n2. Tap 'Report' and choose 'It's inappropriate'.\n3. Select 'Bullying or harassment'.\n4. Specify whether the abuse targets you or someone you know, then submit.",
                'last_verified_at' => now()->subDays(15), // Current (< 90 days)
            ],
            [
                'platform' => 'TikTok',
                'reporting_url' => 'https://www.tiktok.com/safety/en/harassment-bullying/',
                'instructions' => "1. Long press on the video or comment you wish to report.\n2. Tap 'Report' from the action menu.\n3. Select 'Harassment or bullying'.\n4. Follow on-screen instructions to select relevant harassment sub-categories.",
                'last_verified_at' => now()->subDays(105), // Needs Review (> 90 days)
            ],
            [
                'platform' => 'Facebook',
                'reporting_url' => 'https://www.facebook.com/help/contact/274459462613911',
                'instructions' => "1. Click the three dots (...) next to the abusive post, comment, or message.\n2. Click 'Find support or report post'.\n3. Select 'Harassment' and choose who is being targeted.\n4. Complete the prompts to block the harasser and request content takedown.",
                'last_verified_at' => now()->subDays(30), // Current (< 90 days)
            ],
            [
                'platform' => 'YouTube',
                'reporting_url' => 'https://www.youtube.com/howyoutubeworks/policies/community-guidelines/harassment-cyberbullying/',
                'instructions' => "1. Click the three dots icon under the video player or next to the comment.\n2. Select 'Report'.\n3. Choose 'Harassment or bullying' as the violation reason.\n4. Provide timestamps where abusive behavior occurs in the video.",
                'last_verified_at' => now()->subDays(45), // Current (< 90 days)
            ],
            [
                'platform' => 'X (Twitter)',
                'reporting_url' => 'https://help.twitter.com/en/safety-and-security/report-abusive-behavior',
                'instructions' => "1. Navigate to the abusive post.\n2. Click the three dots (...) icon in the top corner.\n3. Select 'Report post' and choose 'Abuse and Harassment'.\n4. Select all harassing posts in the thread to provide full context to safety reviewers.",
                'last_verified_at' => now()->subDays(120), // Needs Review (> 90 days)
            ],
            [
                'platform' => 'Discord',
                'reporting_url' => 'https://support.discord.com/hc/en-us/requests/new?ticket_form_id=360000029753',
                'instructions' => "1. Enable Developer Mode in Discord settings to copy Message ID and User ID.\n2. Right click the harassing message and click 'Copy Message Link'.\n3. Open Discord Trust & Safety ticket portal and select 'Report Harassment'.\n4. Paste message links and submit.",
                'last_verified_at' => now()->subDays(10), // Current (< 90 days)
            ],
        ];

        foreach ($policies as $policy) {
            PlatformPolicy::updateOrCreate(
                ['platform' => $policy['platform']],
                $policy
            );
        }
    }
}
