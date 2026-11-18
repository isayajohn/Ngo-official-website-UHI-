<?php

namespace App\Filament\Widgets;

use App\Models\ImpactStatistic;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\View\View;

class ImpactByProgramme extends ChartWidget
{
    protected ?string $pollingInterval = null;

    protected ?string $heading = 'People reached by programme';

    protected ?string $description = 'Latest verified, published programme-level figure. Project figures and organization totals are excluded; these figures may overlap.';

    protected ?string $emptyStateHeading = 'No verified programme-level reach figures';

    protected ?string $emptyStateDescription = 'Verify and publish a People reached indicator scoped to a programme to populate this chart.';

    protected ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        return auth()->user()?->can('impact.view') ?? false;
    }

    public function getChartAssistiveContent(): ?View
    {
        $data = $this->getCachedData();

        return $data ? view('filament.chart-summary', ['data' => $data]) : null;
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getData(): array
    {
        $statistics = ImpactStatistic::published()->where('metric', 'people_reached')->whereNotNull('programme_id')->whereNull('project_id')->with('programme')->latest('as_of_date')->orderByDesc('id')->limit(500)->get()->unique('programme_id');
        if ($statistics->isEmpty()) {
            return [];
        }

        return ['labels' => $statistics->map(fn ($s) => $s->programme->translated('title'))->values()->all(), 'datasets' => [['label' => 'People reached', 'data' => $statistics->pluck('value')->values()->all(), 'backgroundColor' => '#0870BD']]];
    }
}
