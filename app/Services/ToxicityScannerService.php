<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ToxicityScannerService
{
    /**
     * Scan incident text and/or evidence image separately.
     */
    public function scanText(string $text, ?string $imagePath = null): array
    {
        $apiKey = config('services.hive.api_key');

        if (!$apiKey) {
            return [
                'text' => $text,

                'text_risk_score' => 0,
                'text_risk_level' => 'Not Scanned',
                'text_reason' => 'Hive API key missing.',

                'image_risk_score' => 0,
                'image_risk_level' => 'Not Scanned',
                'image_reason' => 'Hive API key missing.',

                'overall_risk_score' => 0,
                'overall_risk_level' => 'Not Scanned',

                'risk_score' => 0,
                'risk_level' => 'Not Scanned',
                'reason' => 'Hive API key missing.',

                'source' => 'Hive API key missing',
                'image_scanned' => false,
            ];
        }

        // Limit text size.
        $text = mb_substr($text, 0, 1024);

        /*
        |--------------------------------------------------------------------------
        | TEXT ANALYSIS
        |--------------------------------------------------------------------------
        */

        $textResult = null;

        if (trim($text) !== '') {
            $textResult = $this->analyzeText($apiKey, $text);
        }

        /*
        |--------------------------------------------------------------------------
        | IMAGE ANALYSIS
        |--------------------------------------------------------------------------
        */

        $imageResult = null;

        if ($imagePath && file_exists($imagePath)) {
            $imageResult = $this->analyzeImage($apiKey, $imagePath);
        }

        /*
        |--------------------------------------------------------------------------
        | EXTRACT TEXT RESULT
        |--------------------------------------------------------------------------
        */

        $textScore = $textResult['risk_score'] ?? null;
        $textLevel = $textResult['risk_level'] ?? 'Not Scanned';
        $textReason = $textResult['reason'] ?? 'Text was not scanned.';

        /*
        |--------------------------------------------------------------------------
        | EXTRACT IMAGE RESULT
        |--------------------------------------------------------------------------
        */

        $imageScore = $imageResult['risk_score'] ?? null;
        $imageLevel = $imageResult['risk_level'] ?? 'Not Scanned';
        $imageReason = $imageResult['reason'] ?? 'Evidence image was not scanned.';

        /*
        |--------------------------------------------------------------------------
        | CALCULATE OVERALL SCORE
        |--------------------------------------------------------------------------
        */

        $availableScores = [];

        if ($textScore !== null) {
            $availableScores[] = $textScore;
        }

        if ($imageScore !== null) {
            $availableScores[] = $imageScore;
        }

        if (count($availableScores) > 0) {
            $overallScore = (int) round(
                array_sum($availableScores) / count($availableScores)
            );

            $overallLevel = $this->getRiskLevel($overallScore);
        } else {
            $overallScore = 0;
            $overallLevel = 'Not Scanned';
        }

        return [
            'text' => $text,

            // Text result
            'text_risk_score' => $textScore ?? 0,
            'text_risk_level' => $textLevel,
            'text_reason' => $textReason,

            // Image result
            'image_risk_score' => $imageScore ?? 0,
            'image_risk_level' => $imageLevel,
            'image_reason' => $imageReason,

            // Overall result
            'overall_risk_score' => $overallScore,
            'overall_risk_level' => $overallLevel,

            // Backward-compatible fields
            'risk_score' => $overallScore,
            'risk_level' => $overallLevel,

            'reason' => $this->buildOverallReason(
                $textReason,
                $imageReason,
                $textScore,
                $imageScore
            ),

            'source' => 'Hive V3 API',

            'image_scanned' =>
                $imagePath !== null &&
                file_exists($imagePath),
        ];
    }

    /**
     * Analyze text only.
     */
    private function analyzeText(string $apiKey, string $text): ?array
    {
        $prompt =
            'Analyze ONLY the following incident text for harmful or risky content. ' .
            'Consider cybersecurity threats, violence, hate, bullying, harassment, ' .
            'sexual content, dangerous content, threats, intimidation, or other harmful content. ' .
            'Return ONLY valid JSON in exactly this format: ' .
            '{"risk_score": 0, "risk_level": "Low", "reason": "Short explanation here"}. ' .
            'risk_score MUST be a whole number from 0 to 100. ' .
            'risk_level MUST be exactly Low, Medium, or High. ' .
            'reason MUST be a short explanation. ' .
            'Do not return markdown or any text outside the JSON. ' .
            'Incident text: ' . $text;

        try {
            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(60)
                ->post(
                    'https://api.thehive.ai/api/v3/chat/completions',
                    [
                        'model' => 'hive/vision-language-model',
                        'max_tokens' => 100,
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => [
                                    [
                                        'type' => 'text',
                                        'text' => $prompt,
                                    ],
                                ],
                            ],
                        ],
                    ]
                );

            Log::info('Hive text analysis response', [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            if (!$response->successful()) {
                Log::warning('Hive text analysis failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            return $this->parseHiveResult(
                $response->json(),
                'text'
            );

        } catch (\Throwable $e) {
            Log::error('Hive text analysis exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Analyze image only.
     */
    private function analyzeImage(string $apiKey, string $imagePath): ?array
    {
        try {
            $mimeType = mime_content_type($imagePath);

            $base64Image = base64_encode(
                file_get_contents($imagePath)
            );

            $prompt =
                'Analyze ONLY the attached evidence image for harmful or risky content. ' .
                'Look for cyberbullying, threats, harassment, hate, violence, sexual content, ' .
                'dangerous content, intimidation, abusive messages, or other harmful material ' .
                'visible in the image. ' .
                'Return ONLY valid JSON in exactly this format: ' .
                '{"risk_score": 0, "risk_level": "Low", "reason": "Short explanation here"}. ' .
                'risk_score MUST be a whole number from 0 to 100. ' .
                'risk_level MUST be exactly Low, Medium, or High. ' .
                'reason MUST be a short explanation of what was detected in the image. ' .
                'Do not return markdown or any text outside the JSON.';

            $content = [
                [
                    'type' => 'text',
                    'text' => $prompt,
                ],
                [
                    'type' => 'image_url',
                    'image_url' => [
                        'url' =>
                            'data:' .
                            $mimeType .
                            ';base64,' .
                            $base64Image,
                    ],
                ],
            ];

            $response = Http::withToken($apiKey)
                ->acceptJson()
                ->timeout(60)
                ->post(
                    'https://api.thehive.ai/api/v3/chat/completions',
                    [
                        'model' => 'hive/vision-language-model',
                        'max_tokens' => 100,
                        'messages' => [
                            [
                                'role' => 'user',
                                'content' => $content,
                            ],
                        ],
                    ]
                );

            Log::info('Hive image analysis response', [
                'status' => $response->status(),
                'body' => $response->body(),
                'image_sent' => true,
            ]);

            if (!$response->successful()) {
                Log::warning('Hive image analysis failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                return null;
            }

            return $this->parseHiveResult(
                $response->json(),
                'image'
            );

        } catch (\Throwable $e) {
            Log::error('Hive image analysis exception', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * Parse Hive JSON response.
     */
    private function parseHiveResult(array $data, string $type): ?array
    {
        $contentResponse =
            $data['choices'][0]['message']['content'] ?? '';

        $contentResponse = trim($contentResponse);

        // Remove markdown code fences.
        $contentResponse = preg_replace(
            '/^```json\s*/i',
            '',
            $contentResponse
        );

        $contentResponse = preg_replace(
            '/\s*```$/',
            '',
            $contentResponse
        );

        $contentResponse = trim($contentResponse);

        $result = json_decode(
            $contentResponse,
            true
        );

        // Try to extract JSON if extra text exists.
        if (!is_array($result)) {
            $start = strpos($contentResponse, '{');
            $end = strrpos($contentResponse, '}');

            if ($start !== false && $end !== false) {
                $jsonString = substr(
                    $contentResponse,
                    $start,
                    $end - $start + 1
                );

                $result = json_decode(
                    $jsonString,
                    true
                );
            }
        }

        if (!is_array($result)) {
            Log::warning('Hive returned invalid JSON', [
                'type' => $type,
                'content' => $contentResponse,
            ]);

            return null;
        }

        $riskScore = $result['risk_score'] ?? 0;

        if (!is_numeric($riskScore)) {
            $riskScore = 0;
        }

        $riskScore = (int) $riskScore;
        $riskScore = max(0, min(100, $riskScore));

        $riskLevel = $result['risk_level'] ?? null;

        if (!in_array(
            $riskLevel,
            ['Low', 'Medium', 'High'],
            true
        )) {
            $riskLevel = $this->getRiskLevel($riskScore);
        }

        return [
            'risk_score' => $riskScore,
            'risk_level' => $riskLevel,
            'reason' =>
                $result['reason'] ??
                'No explanation provided.',
        ];
    }

    private function getRiskLevel(int $score): string
    {
        if ($score >= 70) {
            return 'High';
        }

        if ($score >= 40) {
            return 'Medium';
        }

        return 'Low';
    }

    private function buildOverallReason(
        string $textReason,
        string $imageReason,
        ?int $textScore,
        ?int $imageScore
    ): string {
        $parts = [];

        if ($textScore !== null) {
            $parts[] = 'Text analysis: ' . $textReason;
        }

        if ($imageScore !== null) {
            $parts[] = 'Evidence analysis: ' . $imageReason;
        }

        if (empty($parts)) {
            return 'No AI analysis result was available.';
        }

        return implode(' ', $parts);
    }
}