<?php

namespace App\Services\GeoFlow;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

/**
 * AutoGEO Integration Service
 *
 * This service integrates AutoGEO's content optimization capabilities
 * into GEOFlow's content generation pipeline.
 */
class AutoGeoIntegrationService
{
    private string $baseUrl;
    private int $timeout;
    private int $retries;

    public function __construct()
    {
        $this->baseUrl = config('autogeo.base_url', 'http://localhost:5000');
        $this->timeout = config('autogeo.timeout', 300);
        $this->retries = config('autogeo.retries', 3);
    }

    /**
     * Check if AutoGEO service is available.
     */
    public function isAvailable(): bool
    {
        try {
            $response = Http::timeout(5)->get("{$this->baseUrl}/api/health");
            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('AutoGEO health check failed', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Optimize content using AutoGEO.
     *
     * @param string $content Content to optimize
     * @param string $dataset Dataset/domain (default, medical, ecommerce, research)
     * @param string $engineLlm LLM to use (gemini, openai, anthropic)
     * @param bool $evaluate Whether to calculate GEO scores
     * @return array{success: bool, content: string, scores: array|null, error: string|null}
     */
    public function optimizeContent(
        string $content,
        string $dataset = 'default',
        string $engineLlm = 'gemini',
        bool $evaluate = true
    ): array {
        if (empty(trim($content))) {
            return [
                'success' => false,
                'content' => $content,
                'scores' => null,
                'error' => 'Content is empty',
            ];
        }

        $payload = [
            'content' => $content,
            'dataset' => $dataset,
            'engine_llm' => $engineLlm,
            'evaluate' => $evaluate,
        ];

        $result = $this->callApi('POST', '/api/rewrite', $payload);

        if (!$result['success']) {
            return [
                'success' => false,
                'content' => $content,
                'scores' => null,
                'error' => $result['error'] ?? 'Unknown error',
            ];
        }

        $rewritten = $result['rewritten_content'] ?? $content;
        $rewritten = preg_replace('/<think>.*?<\/think>/s', '', $rewritten);

        return [
            'success' => true,
            'content' => trim($rewritten),
            'scores' => $result['geo_scores'] ?? null,
            'error' => null,
        ];
    }

    /**
     * Optimize multiple contents in batch.
     *
     * @param array $contents List of contents to optimize
     * @param string $dataset Dataset/domain
     * @param string $engineLlm LLM to use
     * @return array{success: bool, results: array, error: string|null}
     */
    public function optimizeBatch(
        array $contents,
        string $dataset = 'default',
        string $engineLlm = 'gemini'
    ): array {
        if (empty($contents)) {
            return [
                'success' => false,
                'results' => [],
                'error' => 'No contents provided',
            ];
        }

        $payload = [
            'documents' => $contents,
            'dataset' => $dataset,
            'engine_llm' => $engineLlm,
        ];

        $result = $this->callApi('POST', '/api/rewrite/batch', $payload);

        if (!$result['success']) {
            return [
                'success' => false,
                'results' => [],
                'error' => $result['error'] ?? 'Unknown error',
            ];
        }

        return [
            'success' => true,
            'results' => $result['results'] ?? [],
            'error' => null,
        ];
    }

    /**
     * Get available rules for a dataset.
     *
     * @param string $dataset Dataset name
     * @return array{success: bool, rules: array, error: string|null}
     */
    public function getRules(string $dataset = 'default'): array
    {
        $result = $this->callApi('GET', "/api/rules/{$dataset}");

        if (!$result['success']) {
            return [
                'success' => false,
                'rules' => [],
                'error' => $result['error'] ?? 'Unknown error',
            ];
        }

        return [
            'success' => true,
            'rules' => $result['rules'] ?? [],
            'error' => null,
        ];
    }

    /**
     * Call AutoGEO API with retry logic.
     */
    private function callApi(string $method, string $endpoint, array $payload = []): array
    {
        $url = "{$this->baseUrl}{$endpoint}";
        $lastError = null;

        for ($attempt = 1; $attempt <= $this->retries; $attempt++) {
            try {
                $response = Http::timeout($this->timeout)
                    ->withHeaders(['Content-Type' => 'application/json'])
                    ->{$method}($url, $payload);

                if ($response->successful()) {
                    return $response->json();
                }

                $lastError = $response->body();
                Log::warning("AutoGEO API attempt {$attempt} failed", [
                    'url' => $url,
                    'status' => $response->status(),
                    'response' => $lastError,
                ]);

            } catch (\Exception $e) {
                $lastError = $e->getMessage();
                Log::warning("AutoGEO API attempt {$attempt} exception", [
                    'url' => $url,
                    'error' => $lastError,
                ]);
            }

            // Wait before retry (exponential backoff)
            if ($attempt < $this->retries) {
                sleep(pow(2, $attempt));
            }
        }

        return [
            'success' => false,
            'error' => "Failed after {$this->retries} attempts: {$lastError}",
        ];
    }
}
