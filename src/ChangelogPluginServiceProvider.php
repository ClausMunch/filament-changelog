<?php

namespace ClausMunch\FilamentChangelog;

use Filament\Contracts\Plugin;
use Filament\Panel;
use Filament\Support\Assets\Css;
use Filament\Support\Facades\FilamentAsset;
use ClausMunch\FilamentChangelog\Widgets\ChangelogWidget; // make sure namespace is correct
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class ChangelogPluginServiceProvider extends PackageServiceProvider implements Plugin
{
    public static string $name = 'filament-changelog';

    public function configurePackage(Package $package): void
    {
        $package
            ->name(static::$name)
            ->hasConfigFile()
            ->hasViews();
    }

    // Filament Plugin interface
    public function getId(): string
    {
        return static::$name;
    }

    public function configurePanel(Panel $panel): void
    {
        if (config('filament-changelog.widget.enabled', true)) {
            $panel->widgets([
                ChangelogWidget::class,
            ]);
        }

        // Optional: Register custom CSS
        // FilamentAsset::register([
        //     Css::make('filament-changelog-styles', __DIR__ . '/../resources/dist/filament-changelog.css'),
        // ], static::$name);
    }

    public static function make(): static
    {
        return app(static::class);
    }
}
