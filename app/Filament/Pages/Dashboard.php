<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\FoundationOverview;
use App\Filament\Widgets\ImpactByProgramme;
use App\Filament\Widgets\ProjectsByStatus;

class Dashboard extends \Filament\Pages\Dashboard
{
    protected string $view = 'filament.dashboard';

    public function getHeading(): string
    {
        return '';
    }

    public function getWidgets(): array
    {
        return [FoundationOverview::class, ProjectsByStatus::class, ImpactByProgramme::class];
    }
}
