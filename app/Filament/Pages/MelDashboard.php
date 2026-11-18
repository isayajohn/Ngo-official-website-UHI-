<?php

namespace App\Filament\Pages;

use App\Models\PerformanceIndicator;
use App\Models\ReportingPeriod;
use App\Models\ResultsFrameworkItem;
use App\Services\PerformanceScopeService;
use Filament\Pages\Page;

class MelDashboard extends Page
{
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';
    protected static string|\UnitEnum|null $navigationGroup = 'Monitoring & Evaluation';
    protected static ?string $navigationLabel = 'Dashboard';
    protected static ?string $title = 'Monitoring & Evaluation';
    protected static ?int $navigationSort = 1;
    protected string $view = 'filament.pages.mel-dashboard';

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user && ($user->can('organization.full_access') || $user->can('mel.dashboard.view') || $user->can('performance.view_organization') || $user->can('performance.view_department') || $user->can('performance.view_project') || $user->can('performance.view_programme'));
    }

    protected function getViewData(): array
    {
        abort_unless(static::canAccess(), 403);
        $user = auth()->user();
        $scope = app(PerformanceScopeService::class);
        $indicators = $scope->scopeIndicators(PerformanceIndicator::query(), $user);
        $frameworkItems = ResultsFrameworkItem::query();
        if (! $user->can('organization.full_access')) {
            $frameworkItems->where(fn ($query) => $query->where(fn ($global) => $global->whereNull('programme_id')->whereNull('project_id'))
                ->orWhereIn('programme_id', $scope->programmeIds($user))
                ->orWhereIn('project_id', $scope->projectIds($user)));
        }
        $periods = ReportingPeriod::query()->orderByDesc('start_date')->get();

        return [
            'totalIndicators' => (clone $indicators)->count(),
            'activeIndicators' => (clone $indicators)->where('active', true)->count(),
            'frameworkItems' => $frameworkItems->count(),
            'reportingPeriods' => $periods->count(),
            'openPeriods' => $periods->whereIn('status', ['OPEN', 'ACTIVE'])->count(),
            'latestPeriod' => $periods->first(),
        ];
    }
}
