<?php

namespace ClausMunch\FilamentChangelog;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use MyVendor\FilamentChangelog\Widgets\ChangelogWidget;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ChangelogPluginServiceProvider extends PackageServiceProvider implements Plugin
{
    public static string $name = 'filament-changelog';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile() // expects config/filament-changelog.php
            ->hasViews();     // expects resources/views
    }

    // Filament Plugin Interface Methods
    public function getId(): string
    {
        return static::$name;
    }

    public function register(Panel $panel): void
    {
        if (config('filament-changelog.widget.enabled', true)) {
            $panel->widgets([
                ChangelogWidget::class,
            ]);
        }

        // Optionally register CSS if needed for styling markdown,
        // though Filament's built-in prose classes often suffice.
        // FilamentAsset::register([
        //     Css::make('filament-changelog-styles', __DIR__ . '/../resources/dist/filament-changelog.css'),
        // ], 'my-vendor/filament-changelog'); // Use package name
    }

    public function boot(Panel $panel): void
    {
        // Optional: Boot logic, e.g., publishing assets
    }

    // Optional: Define plugin instance for PanelProvider
    public static function make(): static
    {
        return app(static::class);
    }
}