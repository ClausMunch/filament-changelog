<?php

namespace ClausMunch\FilamentChangelog;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Illuminate\Support\ServiceProvider;

class ChangelogPluginServiceProvider extends ServiceProvider implements Plugin
{
    public const PACKAGE_NAME = 'filament-changelog';

    public function registerServices(): void
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
                __DIR__.'/../config/filament-git-changelog.php' => config_path(self::PACKAGE_NAME . '.php'),
            ], self::PACKAGE_NAME . '-config');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/' . self::PACKAGE_NAME),
            ], self::PACKAGE_NAME . '-views');
        }
    }

    public function getId(): string
    {
        return self::PACKAGE_NAME;
    }

    public function register(Panel $panel): void
    {
        // Register plugin-specific functionality here
        $this->registerWidgets($panel);
        $this->registerPages($panel);
        $this->registerResources($panel);
        $this->registerPermissions($panel);
    }

    public function registerPages(Panel $panel): void
    {
        // No pages to register for this plugin
    }

    public function registerResources(Panel $panel): void
    {
        // No resources to register for this plugin
    }

    public function registerWidgets(Panel $panel): void
    {
        $panel->registerWidgets([
            Widgets\ChangelogWidget::class,
        ]);
    }

    public function registerPermissions(Panel $panel): void
    {
        // No permissions to register for this plugin
    }
}