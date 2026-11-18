<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PerformanceTargetResource\Pages;
use App\Models\Department;
use App\Models\PerformanceIndicator;
use App\Models\PerformanceTarget;
use App\Models\Programme;
use App\Models\Project;
use App\Models\ReportingPeriod;
use App\Models\TeamMember;
use App\Services\DepartmentAccessService;
use App\Services\PerformanceScopeService;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class PerformanceTargetResource extends Resource
{
    protected static ?string $model = PerformanceTarget::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Monitoring & Evaluation';
    protected static ?string $navigationLabel = 'Targets';
    protected static ?int $navigationSort = 4;
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-flag';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Section::make('Period target')->schema([
            Select::make('performance_indicator_id')->label('KPI')->options(fn () => app(PerformanceScopeService::class)->scopeIndicators(PerformanceIndicator::query()->where('active', true)->orderBy('name'), auth()->user())->get()->mapWithKeys(fn ($indicator) => [$indicator->id => $indicator->code.' · '.$indicator->name]))->searchable()->preload()->required(),
            Select::make('reporting_period_id')->options(ReportingPeriod::query()->orderByDesc('start_date')->pluck('name', 'id'))->searchable()->required(),
            Select::make('department_id')->options(Department::query()->where('active', true)->orderBy('name')->get()->filter(fn (Department $d) => auth()->user()->can('organization.full_access') || app(DepartmentAccessService::class)->canAccessDepartment(auth()->user(), $d))->pluck('name', 'id'))->searchable()->nullable(),
            Select::make('programme_id')->options(fn () => Programme::query()->whereIn('id', app(PerformanceScopeService::class)->programmeIds(auth()->user()))->orderBy('id')->get()->mapWithKeys(fn (Programme $programme) => [$programme->id => $programme->translated('title')]))->searchable()->nullable(),
            Select::make('project_id')->options(fn () => Project::query()->whereIn('id', app(PerformanceScopeService::class)->projectIds(auth()->user()))->orderBy('id')->get()->mapWithKeys(fn (Project $project) => [$project->id => $project->translated('title')]))->searchable()->nullable(),
            Select::make('district_id')->label('District')->relationship('district', 'name')->searchable()->preload()->nullable(),
            Select::make('team_member_id')->label('Staff member')->options(fn () => TeamMember::query()->where('active', true)->whereIn('department_id', app(PerformanceScopeService::class)->departmentIds(auth()->user()))->orderBy('name')->pluck('name', 'id'))->searchable()->nullable(),
            TextInput::make('target_value')->numeric()->required(),
            TextInput::make('weight_override')->numeric()->minValue(0)->maxValue(100)->suffix('%')->nullable(),
            Textarea::make('notes')->rows(3)->maxLength(5000)->columnSpanFull(),
        ])->columns(2)]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('indicator.code')->label('Indicator code')->searchable()->sortable(),
            TextColumn::make('indicator.name')->label('Indicator')->searchable()->wrap(),
            TextColumn::make('reportingPeriod.name')->label('Period')->sortable(),
            TextColumn::make('department.name')->placeholder('Organization'),
            TextColumn::make('programme.title.en')->label('Programme')->placeholder('—'),
            TextColumn::make('project.title.en')->label('Project')->placeholder('—'),
            TextColumn::make('district.name')->label('District')->placeholder('—'),
            TextColumn::make('target_value')->numeric(decimalPlaces: 2),
            TextColumn::make('weight_override')->suffix('%')->placeholder('KPI default'),
        ])->filters([
            SelectFilter::make('reporting_period_id')->relationship('reportingPeriod', 'name'),
            SelectFilter::make('performance_indicator_id')->label('Indicator')->options(fn () => app(PerformanceScopeService::class)->scopeIndicators(PerformanceIndicator::query()->orderBy('name'), auth()->user())->get()->mapWithKeys(fn (PerformanceIndicator $indicator) => [$indicator->id => $indicator->code.' · '.$indicator->name])),
        ])
            ->recordActions([
                EditAction::make()->authorize(fn (PerformanceTarget $record) => Gate::allows('update', $record))
                    ->mutateDataUsing(function (array $data, PerformanceTarget $record): array {
                        $candidate = $record->replicate();
                        $candidate->fill($data);
                        Gate::authorize('view', $candidate);
                        return $data;
                    }),
            ])->headerActions([
                CreateAction::make()->mutateDataUsing(function (array $data): array {
                    Gate::authorize('view', new PerformanceTarget($data));
                    $data['created_by_user_id'] = auth()->id();
                    return $data;
                }),
            ])->defaultSort('reporting_period_id', 'desc');
    }

    public static function getPages(): array { return ['index' => Pages\ListPerformanceTargets::route('/')]; }

    public static function getEloquentQuery(): Builder
    {
        return app(PerformanceScopeService::class)->scopeTargets(parent::getEloquentQuery(), auth()->user())->with(['indicator', 'reportingPeriod', 'department', 'programme', 'project']);
    }
}
