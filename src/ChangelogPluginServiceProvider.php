<?php

namespace ClausMunch\FilamentChangelog;

use Illuminate\Support\ServiceProvider; // Extends this

// **DOES NOT** implement Plugin anymore
class ChangelogPluginServiceProvider extends ServiceProvider
{
    // Package name constant for consistency
    public const PACKAGE_NAME = 'filament-changelog';

    // Standard Laravel register method (bindings, merge config)
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/filament-git-changelog.php',
            self::PACKAGE_NAME // Use const for config key
        );

        $this->app->singleton(Http\GithubService::class, function ($app) {
            return new Http\GithubService();
        });
    }

    // Standard Laravel boot method (publishing, load views)
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', self::PACKAGE_NAME);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/filament-git-changelog.php' => config_path(self::PACKAGE_NAME . '.php'),
            ], self::PACKAGE_NAME . '-config'); // Tag: filament-git-changelog-config

            $this->publishes([
                 __DIR__.'/../resources/views' => resource_path('views/vendor/' . self::PACKAGE_NAME),
            ], self::PACKAGE_NAME . '-views'); // Tag: filament-git-changelog-views

            // Optional assets publishing
            // $this->publishes([...], self::PACKAGE_NAME . '-assets');
        }
    }
}