<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BitBrowserService
{
    protected string $apiUrl;

    public function __construct()
    {
        $this->apiUrl = config('services.bitbrowser.url', 'http://127.0.0.1:54345');
    }

    public function getProfiles(): array
    {
        try {
            $response = Http::timeout(10)->get($this->buildUrl('/api/v1/profile/list'));

            if ($response->successful()) {
                return $response->json()['data'] ?? [];
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
            $response = Http::timeout(30)->post($this->buildUrl('/api/v1/profile/open'), [
                'profileId' => $profileId,
            ]);

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
            $response = Http::timeout(10)->post($this->buildUrl('/api/v1/profile/close'), [
                'profileId' => $profileId,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('BitBrowser Error', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public function createProfile(array $params): ?array
    {
        try {
            $response = Http::timeout(30)->post($this->buildUrl('/api/v1/profile/create'), $params);

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
            $response = Http::timeout(10)->post($this->buildUrl('/api/v1/profile/delete'), [
                'profileId' => $profileId,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('BitBrowser Error', ['message' => $e->getMessage()]);
            return false;
        }
    }

    public function isRunning(): bool
    {
        try {
            $response = Http::timeout(5)->get($this->buildUrl('/api/v1/profile/list'));
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
