<?php

namespace App\Filament\Widgets;

use App\Enums\ProjectStatus;
use App\Filament\Resources\ProjectResource;
use App\Models\Project;
use Filament\Actions\Action;
use Filament\Widgets\ChartWidget;
use Illuminate\Contracts\View\View;

class ProjectsByStatus extends ChartWidget
{
    protected ?string $pollingInterval = null;

    protected ?string $heading = 'Projects by status';

    protected ?string $emptyStateHeading = 'No projects yet';

    protected ?string $emptyStateDescription = 'Create a project to see its delivery status here.';

    protected ?string $maxHeight = '300px';

    public static function canView(): bool
    {
        return auth()->user()?->can('project.view') ?? false;
    }

    public function getChartAssistiveContent(): ?View
    {
        $data = $this->getCachedData();

        return $data ? view('filament.chart-summary', ['data' => $data]) : null;
    }

    public function getEmptyStateActions(): array
    {
        return [
            Action::make('createProject')->label('Create project')->icon('heroicon-o-plus')->url(ProjectResource::getUrl('index', ['tableAction' => 'create'])),
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getData(): array
    {
        $counts = Project::query()->selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status');
        if ($counts->isEmpty()) {
            return [];
        }

        return ['labels' => array_map(fn ($status) => $status->getLabel(), ProjectStatus::cases()), 'datasets' => [['label' => 'Projects', 'data' => array_map(fn ($status) => (int) ($counts[$status->value] ?? 0), ProjectStatus::cases()), 'backgroundColor' => ['#06477F', '#0870BD', '#65727E', '#9CA3AF']]]];
    }
}
