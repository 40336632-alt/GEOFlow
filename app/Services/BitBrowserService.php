<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BitBrowserService
{
    protected string $apiUrl;
    protected string $apiKey;

    public function __construct()
    {
        $this->apiUrl = config('services.bitbrowser.url', 'http://127.0.0.1:54345');
        $this->apiKey = config('services.bitbrowser.api_key', '');
    }

    protected function authHeaders(): array
    {
        $headers = ['Content-Type' => 'application/json'];
        if ($this->apiKey) {
            $headers['x-api-key'] = $this->apiKey;
        }
        return $headers;
    }

    public function getProfiles(): array
    {
        try {
            $response = Http::withHeaders($this->authHeaders())
                ->timeout(10)
                ->post($this->buildUrl('/browser/list'), ['page' => 0, 'pageSize' => 100]);

            if ($response->successful()) {
                $data = $response->json()['data'] ?? [];
                return $data['list'] ?? $data ?? [];
            }

            return [];
        } catch (\Exception $e) {
            Log::error('BitBrowser Error', ['message' => $e->getMessage()]);
            return [];
        }
    }

    public function openProfile(string $profileId): ?array
    {
        try {
            $response = Http::withHeaders($this->authHeaders())
                ->timeout(30)
                ->post($this->buildUrl('/browser/open'), ['id' => $profileId]);

            if ($response->successful()) {
                return $response->json()['data'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('BitBrowser Error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    public function closeProfile(string $profileId): bool
    {
        try {
            $response = Http::withHeaders($this->authHeaders())
                ->timeout(10)
                ->post($this->buildUrl('/browser/close'), ['id' => $profileId]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('BitBrowser Error', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public function createProfile(array $params): ?array
    {
        try {
            $response = Http::withHeaders($this->authHeaders())
                ->timeout(30)
                ->post($this->buildUrl('/browser/create'), $params);

            if ($response->successful()) {
                return $response->json()['data'] ?? null;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('BitBrowser Error', ['message' => $e->getMessage()]);
            return null;
        }
    }

    public function deleteProfile(string $profileId): bool
    {
        try {
            $response = Http::withHeaders($this->authHeaders())
                ->timeout(10)
                ->post($this->buildUrl('/browser/delete'), ['id' => $profileId]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('BitBrowser Error', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public function isRunning(): bool
    {
        try {
            $response = Http::withHeaders($this->authHeaders())
                ->timeout(5)
                ->post($this->buildUrl('/browser/list'), ['page' => 0, 'pageSize' => 1]);
            return $response->successful();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function syncProfiles(int $userId): array
    {
        $profiles = $this->getProfiles();
        $synced = 0;

        foreach ($profiles as $profile) {
            \App\Models\BrowserProfile::updateOrCreate(
                [
                    'user_id' => $userId,
                    'profile_id' => $profile['id'] ?? '',
                ],
                [
                    'profile_name' => $profile['name'] ?? '',
                    'status' => 'authorized',
                ]
            );
            $synced++;
        }

        return [
            'total' => count($profiles),
            'synced' => $synced,
        ];
    }

    protected function buildUrl(string $path): string
    {
        return rtrim($this->apiUrl, '/') . '/' . ltrim($path, '/');
    }
}
