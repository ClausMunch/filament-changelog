<?php

// config/filament-changelog.php

return [

    /*
    |--------------------------------------------------------------------------
    | GitHub Repository
    |--------------------------------------------------------------------------
    |
    | Specify the GitHub repository in 'owner/repo' format.
    | Example: 'laravel/framework' or 'my-organization/my-private-repo'
    | This will be pulled from the .env file.
    |
    */
    'repository' => env('CHANGELOG_GITHUB_REPOSITORY'),

    /*
    |--------------------------------------------------------------------------
    | GitHub Personal Access Token (PAT)
    |--------------------------------------------------------------------------
    |
    | Required for accessing private repositories and to increase rate limits
    | for public repositories. Generate a token with 'repo' scope (or minimal
    | required scope) on GitHub. Store this securely in your .env file.
    | NEVER commit your token directly into configuration or code.
    |
    */
    'github_token' => env('CHANGELOG_GITHUB_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Cache Duration
    |--------------------------------------------------------------------------
    |
    | How long (in seconds) the fetched releases should be cached to avoid
    | hitting GitHub API rate limits and improve performance.
    | Default: 3600 seconds (1 hour). Set to 0 to disable caching (not recommended).
    |
    */
    'cache_duration' => env('CHANGELOG_CACHE_DURATION', 3600),

    /*
    |--------------------------------------------------------------------------
    | Number of Releases
    |--------------------------------------------------------------------------
    |
    | The maximum number of recent releases to fetch and display.
    | GitHub API default is 30 per page, max is 100.
    |
    */
    'max_releases' => env('CHANGELOG_MAX_RELEASES', 10),

    /*
    |--------------------------------------------------------------------------
    | Widget Configuration
    |--------------------------------------------------------------------------
    */
    'widget' => [
        // Should the widget be enabled and registered automatically?
        'enabled' => true,

        // Sort order for the widget on the dashboard.
        'sort' => -1,

        // Column span (options: 1-12, 'full', etc. based on Filament grid).
        'column_span' => 'full', // or 'md' => 6, 'lg' => 4 etc.
    ],

    /*
    |--------------------------------------------------------------------------
    | Date Format
    |--------------------------------------------------------------------------
    |
    | The format for displaying the release published date. Uses PHP date() format.
    | See: https://www.php.net/manual/en/datetime.format.php
    |
    */
    'date_format' => 'M j, Y H:i T',

];