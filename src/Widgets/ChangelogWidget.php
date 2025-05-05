<?php

namespace ClausMunch\FilamentChangelog\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\HtmlString;
use Illuminate\Support\Str;
use MyVendor\FilamentChangelog\Http\GithubService;

class ChangelogWidget extends Widget
{
    protected static string $view = 'filament-changelog::widgets.changelog-widget';

    protected int | string | array $columnSpan = 'full'; // Default column span

    protected static ?int $sort = -1; // Default sort order

    public ?array $data = null; // Holds fetched data {error?: string, releases?: array}

    public static function canView(): bool
    {
        // Only show if explicitly enabled and repository is set
        return config('filament-changelog.widget.enabled', true)
            && !empty(config('filament-changelog.repository'));
    }

    public function mount(GithubService $githubService): void
    {
        $this->data = $githubService->getReleases();

        // Apply config overrides for widget properties
        $this->columnSpan = config('filament-changelog.widget.column_span', 'full');
        static::$sort = config('filament-changelog.widget.sort', -1);

         if (!isset($this->data['releases']) && !isset($this->data['error'])) {
             Log::warning('[Filament Changelog] Widget received unexpected data structure from GithubService.');
             $this->data = ['error' => 'Internal error fetching changelog data.'];
         }
    }

     public function getHeading(): string
     {
         return 'Changelog';
     }

    // Helper function for the view to parse markdown
    public function parseMarkdown(?string $markdown): HtmlString
    {
        if (empty($markdown)) {
            return new HtmlString('');
        }

        // Use Laravel's Str::markdown helper (requires commonmark)
        // Add appropriate classes for styling (Tailwind prose)
        return new HtmlString(
            Str::markdown($markdown, [
                'html_input' => 'strip', // Basic XSS protection
                'allow_unsafe_links' => false,
            ])
        );
    }

     // Helper for date formatting
     public function formatDate(?string $dateString): string
     {
         if (empty($dateString)) {
             return 'N/A';
         }
         try {
             return \Carbon\Carbon::parse($dateString)
                 ->format(config('filament-changelog.date_format', 'M j, Y H:i T'));
         } catch (\Exception $e) {
             return $dateString; // Return original if parsing fails
         }
     }
}