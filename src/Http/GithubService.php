<?php

namespace ClausMunch\FilamentChangelog\Http;

use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GithubService
{
    protected string $repository;
    protected ?string $token;
    protected int $cacheDuration;
    protected int $maxReleases;

    public function __construct()
    {
        $this->repository = config('filament-changelog.repository');
        $this->token = config('filament-changelog.github_token');
        $this->cacheDuration = config('filament-changelog.cache_duration', 3600);
        $this->maxReleases = config('filament-changelog.max_releases', 10);
    }

    public function getChangelog(): ?array
    {
        return $this->getReleases();
    }

    /**
     * Fetches releases from the configured GitHub repository.
     *
     * @return array{error?: string, releases?: array}|null Returns array with releases or error, or null if repo not configured.
     */
    public function getReleases(): ?array
    {
        if (empty($this->repository)) {
            Log::warning('[Filament Changelog] GitHub repository is not configured.');
            return ['error' => 'GitHub repository not configured.'];
        }

        $cacheKey = 'filament-changelog::' . str_replace('/', '-', $this->repository);

        if ($this->cacheDuration <= 0) {
            // Cache disabled, fetch directly
            Cache::forget($cacheKey);
            return $this->fetchFromApi();
        }

        // Remember uses seconds
        return Cache::remember($cacheKey, $this->cacheDuration, function () {
            return $this->fetchFromApi();
        });
    }

    protected function fetchFromApi(): array
    {
        try {
            $response = $this->buildRequest()->get($this->getApiUrl());

            if (! $response->successful()) {
                return $this->handleErrorResponse($response);
            }

            // Limit the number of releases after fetching (API might return more per page)
            $releases = array_slice($response->json(), 0, $this->maxReleases);

            return ['releases' => $releases];

        } catch (\Throwable $exception) {
            Log::error('[Filament Changelog] Failed to fetch releases: ' . $exception->getMessage());
            return ['error' => 'Could not connect to GitHub API or unexpected error occurred.'];
        }
    }

    protected function buildRequest(): PendingRequest
    {
        $request = Http::accept('application/vnd.github.v3+json')
                       ->timeout(15); // Set a reasonable timeout

        if (! empty($this->token)) {
            $request->withToken($this->token);
            Log::info('[Filament Changelog] Using token for authentication');
        } else {
             Log::warning('[Filament Changelog] No GitHub token provided. Accessing public repo or may encounter rate limits/private repo issues.');
        }

        Log::info('[Filament Changelog] Repository: ' . $this->repository);
Log::info('[Filament Changelog] Token length: ' . (strlen($this->token) ?? 'null'));
        return $request;
    }

    protected function getApiUrl(): string
    {
        // Ensure per_page doesn't exceed 100 (GitHub limit) and fetch enough
        // if max_releases is high, though we slice later. Usually fetching 30 is fine.
        $perPage = min(max($this->maxReleases, 10), 100);
        return "https://api.github.com/repos/{$this->repository}/releases?per_page={$perPage}";
    }

    protected function handleErrorResponse(Response $response): array
    {
        $errorMessage = "Failed to fetch releases. Status: {$response->status()}";
        $body = $response->json();
        if (isset($body['message'])) {
            $errorMessage .= " - {$body['message']}";
        }

        Log::error("[Filament Changelog] {$errorMessage}");

        if ($response->status() === 401) {
             return ['error' => 'Authentication failed. Check your GitHub token (PAT).'];
        } elseif ($response->status() === 404) {
             return ['error' => 'Repository not found. Check the owner/repo name.'];
        } elseif ($response->status() === 403) {
             return ['error' => 'Access forbidden. Check token permissions or rate limits.'];
        } else {
             return ['error' => "GitHub API error (Status: {$response->status()})."];
        }
    }
}