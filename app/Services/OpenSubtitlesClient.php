<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class OpenSubtitlesClient
{
    private string $baseUrl;

    private ?string $apiKey;

    private ?string $username;

    private ?string $password;

    public function __construct()
    {
        $this->baseUrl = config('services.opensubtitles.base_url', 'https://api.opensubtitles.com/api/v1');
        $this->apiKey = config('services.opensubtitles.api_key');
        $this->username = config('services.opensubtitles.username');
        $this->password = config('services.opensubtitles.password');
    }

    public function isConfigured(): bool
    {
        return ! empty($this->apiKey);
    }

    /**
     * Search subtitles by TMDB id.
     *
     * @return array{data: array, total_pages: int, total_count: int}|array{error: string}
     */
    public function search(int $tmdbId, string $languages = 'en', string $type = 'movie'): array
    {
        if (! $this->isConfigured()) {
            return ['data' => [], 'total_pages' => 0, 'total_count' => 0, 'error' => 'OpenSubtitles API key not configured. Set OPENSUBTITLES_API_KEY in .env.'];
        }

        try {
            $response = Http::withHeaders([
                'Api-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'Streamku v1',
            ])->get("{$this->baseUrl}/subtitles", [
                'tmdb_id' => $tmdbId,
                'languages' => $languages,
            ]);

            if ($response->failed()) {
                Log::warning('OpenSubtitles search failed', ['status' => $response->status(), 'body' => $response->body()]);

                return ['data' => [], 'total_pages' => 0, 'total_count' => 0, 'error' => 'OpenSubtitles API error: '.$response->status()];
            }

            $json = $response->json();

            return [
                'data' => $json['data'] ?? [],
                'total_pages' => $json['total_pages'] ?? 1,
                'total_count' => $json['total_count'] ?? count($json['data'] ?? []),
            ];
        } catch (\Throwable $e) {
            Log::error('OpenSubtitles search exception', ['error' => $e->getMessage()]);

            return ['data' => [], 'total_pages' => 0, 'total_count' => 0, 'error' => $e->getMessage()];
        }
    }

    /**
     * Download subtitle file content. Returns link to download, then fetches content.
     */
    public function download(int $fileId): ?string
    {
        if (! $this->isConfigured()) {
            return null;
        }

        try {
            $response = Http::withHeaders([
                'Api-Key' => $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
                'User-Agent' => 'Streamku v1',
            ])->post("{$this->baseUrl}/download", [
                'file_id' => $fileId,
            ]);

            if ($response->failed()) {
                Log::warning('OpenSubtitles download failed', ['status' => $response->status(), 'body' => $response->body()]);

                return null;
            }

            $json = $response->json();

            return $json['link'] ?? null;
        } catch (\Throwable $e) {
            Log::error('OpenSubtitles download exception', ['error' => $e->getMessage()]);

            return null;
        }
    }

    /** Fetch raw subtitle file content from download link */
    public function fetchContent(string $link): ?string
    {
        try {
            $response = Http::get($link);

            if ($response->failed()) {
                return null;
            }

            return $response->body();
        } catch (\Throwable $e) {
            Log::error('OpenSubtitles fetch content failed', ['error' => $e->getMessage()]);

            return null;
        }
    }
}
