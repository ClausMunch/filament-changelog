<?php

namespace ClausMunch\FilamentChangelog;

use Filament\Contracts\Plugin;
use Filament\Panel;

class ChangelogPlugin implements Plugin
{
    public function getId(): string
    {
        return 'filament-changelog';
    }

    public function register(Panel $panel): void
    {
        $panel->widgets([
            Widgets\ChangelogWidget::class,
        ]);
    }
}