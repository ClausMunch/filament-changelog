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
    protected int $maxItems;
    protected string $fetchType;

    public function __construct()
    {
        $this->repository = config('filament-changelog.repository');
        $this->token = config('filament-changelog.github_token');
        $this->cacheDuration = config('filament-changelog.cache_duration', 3600);
        $this->maxItems = config('filament-changelog.max_items', 10);
        $this->fetchType = config('filament-changelog.fetch_type', 'releases');
    }

    public function getChangelog(): ?array
    {
        if ($this->fetchType === 'commits') {
            return $this->getCommits();
        }
        return $this->getReleases();
    }

    public function getReleases(): ?array
    {
        return $this->fetchItems('releases');
    }

    public function getCommits(): ?array
    {
        return $this->fetchItems('commits');
    }

    protected function fetchItems(string $type): ?array
    {
        if (empty($this->repository)) {
            Log::warning('[Filament Changelog] GitHub repository is not configured.');
            return ['error' => 'GitHub repository not configured.'];
        }

        $cacheKey = "filament-changelog::{$type}::" . str_replace('/', '-', $this->repository);

        if ($this->cacheDuration <= 0) {
            Cache::forget($cacheKey);
            return $this->fetchFromApi($type);
        }

        return Cache::remember($cacheKey, $this->cacheDuration, function () use ($type) {
            return $this->fetchFromApi($type);
        });
    }

    protected function fetchFromApi(string $type): array
    {
        try {
            $response = $this->buildRequest()->get($this->getApiUrl($type));

            if (!$response->successful()) {
                return $this->handleErrorResponse($response);
            }

            $items = array_slice($response->json(), 0, $this->maxItems);

            if ($type === 'commits') {
                $items = array_map(function ($commit) {
                    return [
                        'name' => substr($commit['sha'], 0, 7),
                        'tag_name' => substr($commit['sha'], 0, 7),
                        'body' => $commit['commit']['message'],
                        'published_at' => $commit['commit']['author']['date'],
                        'html_url' => $commit['html_url'],
                    ];
                }, $items);
            }

            return ['releases' => $items];

        } catch (\Throwable $exception) {
            Log::error('[Filament Changelog] Failed to fetch ' . $type . ': ' . $exception->getMessage());
            return ['error' => 'Could not connect to GitHub API or unexpected error occurred.'];
        }
    }

    protected function buildRequest(): PendingRequest
    {
        Log::info('[Filament Changelog] Building request...');
        Log::info('[Filament Changelog] Repository: ' . $this->repository);
        Log::info('[Filament Changelog] Token present: ' . (!empty($this->token) ? 'Yes' : 'No'));
        
        $request = Http::accept('application/vnd.github.v3+json')
                       ->timeout(15);

        if (!empty($this->token)) {
            Log::info('[Filament Changelog] Adding token to request');
            // Create a new request instance with the token
            $request = Http::withToken($this->token)
                          ->accept('application/vnd.github.v3+json')
                          ->timeout(15);
            
            Log::info('[Filament Changelog] Token length: ' . strlen($this->token));
            
            // Test the token validity
            $testResponse = Http::withToken($this->token)
                ->get('https://api.github.com/user');
            Log::info('[Filament Changelog] Token test status: ' . $testResponse->status());
            
            if (!$testResponse->successful()) {
                Log::error('[Filament Changelog] Token validation failed: ' . $testResponse->body());
            }
        } else {
            Log::warning('[Filament Changelog] No GitHub token provided for repository: ' . $this->repository);
        }

        return $request;
    }

    protected function getApiUrl(string $type): string
    {
        $perPage = min(max($this->maxItems, 10), 100);
        $endpoint = $type === 'commits' ? 'commits' : 'releases';
        return "https://api.github.com/repos/{$this->repository}/{$endpoint}?per_page={$perPage}";
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