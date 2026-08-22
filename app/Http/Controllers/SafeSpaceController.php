<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

/**
 * Interactive "Digital Safe Space" Calming Dashboard.
 * Module 2 feature owner: Johra-E-Jannat Oishy.
 */
class SafeSpaceController extends Controller
{
    /**
     * A local fallback keeps the calming page useful when ZenQuotes is
     * temporarily unavailable or the deployment has no outbound network.
     */
    private const FALLBACK_QUOTES = [
        ['q' => 'You do not have to solve everything in this moment. Take one slow breath at a time.', 'a' => 'CyberGuard'],
        ['q' => 'This moment can pass. Give yourself permission to slow down and breathe.', 'a' => 'CyberGuard'],
        ['q' => 'Small, steady breaths are enough for right now.', 'a' => 'CyberGuard'],
    ];

    public function index(): View
    {
        return view('safe-space.index');
    }

    /**
     * Return one quote to the browser.
     *
     * Why proxy through Laravel instead of calling ZenQuotes directly from
     * the browser? The free ZenQuotes API does not reliably send CORS headers.
     * The browser still performs an asynchronous fetch, but this endpoint
     * safely talks to ZenQuotes on the server and normalizes the response.
     */
    public function quote(): JsonResponse
    {
        $quotes = Cache::get('cyberguard.safe-space.zenquotes');

        if (!is_array($quotes) || count($quotes) === 0) {
            $quotes = $this->fetchQuoteBatch();

            if (count($quotes) > 0) {
                // ZenQuotes recommends caching a batch instead of requesting
                // a new external quote on every button press.
                Cache::put('cyberguard.safe-space.zenquotes', $quotes, now()->addHour());
            }
        }

        if (count($quotes) === 0) {
            $quotes = self::FALLBACK_QUOTES;
        }

        $quote = $quotes[array_rand($quotes)];

        return response()
            ->json([
                'status' => 'success',
                'data' => [
                    'quote' => $quote['q'],
                    'author' => $quote['a'],
                ],
            ])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    private function fetchQuoteBatch(): array
    {
        $baseUrl = rtrim(
            (string) config('services.zenquotes.base_url', 'https://zenquotes.io/api/quotes'),
            '/'
        );

        $apiKey = trim((string) config('services.zenquotes.key', ''));
        $endpoint = $apiKey !== ''
            ? $baseUrl . '/' . rawurlencode($apiKey)
            : $baseUrl;

        try {
            $request = Http::acceptJson()
                ->timeout(6)
                ->retry(2, 250);

            // Keyword filtering is a ZenQuotes API-key feature. Without a key,
            // use the free general quote batch.
            $response = $apiKey !== ''
                ? $request->get($endpoint, ['keyword' => 'anxiety'])
                : $request->get($endpoint);

            if (!$response->successful()) {
                return [];
            }

            $payload = $response->json();

            if (!is_array($payload)) {
                return [];
            }

            $quotes = [];

            foreach ($payload as $item) {
                if (!is_array($item)) {
                    continue;
                }

                $text = trim((string) ($item['q'] ?? ''));
                $author = trim((string) ($item['a'] ?? 'Unknown'));

                if ($text === '') {
                    continue;
                }

                $quotes[] = [
                    'q' => $text,
                    'a' => $author !== '' ? $author : 'Unknown',
                ];
            }

            return $quotes;
        } catch (\Throwable $exception) {
            // The calming dashboard must still load if the external API fails.
            return [];
        }
    }
}
