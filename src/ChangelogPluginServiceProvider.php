<?php

namespace ClausMunch\FilamentChangelog;

use Illuminate\Support\ServiceProvider;

class ChangelogPluginServiceProvider extends ServiceProvider
{
    public const PACKAGE_NAME = 'filament-changelog';

    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__.'/../config/filament-changelog.php',
            self::PACKAGE_NAME
        );

        $this->app->singleton(Http\GithubService::class, function ($app) {
            return new Http\GithubService();
        });
    }

    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', self::PACKAGE_NAME);

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/filament-changelog.php' => config_path(self::PACKAGE_NAME . '.php'),
            ], self::PACKAGE_NAME . '-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/' . self::PACKAGE_NAME),
            ], self::PACKAGE_NAME . '-views');
        }
    }
}