<?php

namespace App\Filament\Resources;

use App\Models\PerformanceResult;
use App\Models\PerformanceTarget;
use App\Services\PerformanceCalculationService;
use App\Services\PerformanceScopeService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class PerformanceResultResource extends Resource
{
    protected static ?string $model = PerformanceResult::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Reports & Performance';
    protected static ?string $navigationLabel = 'Performance Results';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-pie';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Section::make('KPI result')->schema([
            Select::make('performance_target_id')->label('KPI target')->options(fn () => app(PerformanceScopeService::class)->scopeTargets(PerformanceTarget::query()->with(['indicator', 'reportingPeriod', 'department', 'project']), auth()->user())->get()->mapWithKeys(fn (PerformanceTarget $target) => [$target->id => $target->indicator->code.' · '.$target->reportingPeriod->name.' · '.($target->department?->name ?? $target->project?->translated('title') ?? 'Organization')]))
                ->searchable()->preload()->required()->visible(fn (string $operation) => $operation === 'create')
                ->afterStateUpdated(function ($state, Set $set): void {
                    $target = PerformanceTarget::with('indicator')->find($state);
                    if (! $target) return;
                    $set('actual_value', null);
                })->live(),
            TextInput::make('actual_value')->label('Actual value')->numeric()->required(),
            Textarea::make('comments')->rows(3)->maxLength(5000)->columnSpanFull(),
            TextInput::make('status')->disabled()->dehydrated(false)->formatStateUsing(fn (Get $get, string $operation, ?PerformanceResult $record) => $operation === 'edit' ? $record?->status : 'DRAFT'),
        ])->columns(2)]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('indicator.code')->label('KPI')->searchable()->sortable(),
            TextColumn::make('indicator.name')->label('Indicator')->searchable()->wrap(),
            TextColumn::make('reportingPeriod.name')->label('Period')->sortable(),
            TextColumn::make('department.name')->placeholder('Organization / project'),
            TextColumn::make('actual_value')->numeric(decimalPlaces: 2),
            TextColumn::make('achievement_percentage')->label('Achievement')->suffix('%')->placeholder('Not calculated'),
            TextColumn::make('score')->numeric(decimalPlaces: 2)->placeholder('Not verified'),
            TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) { 'VERIFIED' => 'success', 'SUBMITTED' => 'info', 'RETURNED' => 'warning', 'REJECTED' => 'danger', default => 'gray' }),
            TextColumn::make('enteredBy.name')->label('Entered by')->placeholder('System'),
        ])->filters([
            SelectFilter::make('status')->options(array_combine(PerformanceResult::STATUSES, array_map(fn ($status) => str($status)->lower()->title()->toString(), PerformanceResult::STATUSES))),
            SelectFilter::make('reporting_period_id')->relationship('reportingPeriod', 'name'),
        ])->recordActions([
            EditAction::make()->visible(fn (PerformanceResult $record) => Gate::allows('update', $record)),
            Action::make('submit')->label('Submit result')->color('info')->icon('heroicon-o-paper-airplane')
                ->visible(fn (PerformanceResult $record) => in_array($record->status, ['DRAFT', 'RETURNED'], true) && auth()->user()->can('performance.enter_result'))
                ->authorize(fn (PerformanceResult $record) => Gate::allows('update', $record))
                ->requiresConfirmation()->action(function (PerformanceResult $record): void { Gate::authorize('update', $record); $record->update(['status' => 'SUBMITTED']); }),
            Action::make('verify')->label('Verify')->color('success')->icon('heroicon-o-check-circle')
                ->visible(fn (PerformanceResult $record) => $record->status === 'SUBMITTED' && auth()->user()->can('performance.verify_result'))
                ->authorize(fn (PerformanceResult $record) => Gate::allows('verify', $record))
                ->requiresConfirmation()->action(function (PerformanceResult $record, PerformanceCalculationService $calculator): void {
                    Gate::authorize('verify', $record);
                    $indicator = $record->indicator;
                    $target = $record->target?->target_value ?? $indicator->target_value;
                    $achievement = $calculator->calculateAchievement((float) $record->actual_value, $target === null ? null : (float) $target, $indicator->direction, $indicator->minimum_value === null ? null : (float) $indicator->minimum_value, $indicator->maximum_value === null ? null : (float) $indicator->maximum_value);
                    $score = $calculator->calculateScore((float) $record->actual_value, $target === null ? null : (float) $target, $indicator->direction, null, $indicator->minimum_value === null ? null : (float) $indicator->minimum_value, $indicator->maximum_value === null ? null : (float) $indicator->maximum_value);
                    $record->update(['status' => 'VERIFIED', 'verified_by_user_id' => auth()->id(), 'verified_at' => now(), 'achievement_percentage' => $achievement, 'score' => $score]);
                }),
            Action::make('return')->label('Return for revision')->color('warning')->icon('heroicon-o-arrow-uturn-left')->form([Textarea::make('comments')->required()->maxLength(5000)])
                ->visible(fn (PerformanceResult $record) => $record->status === 'SUBMITTED' && auth()->user()->can('performance.verify_result'))
                ->authorize(fn (PerformanceResult $record) => Gate::allows('verify', $record))
                ->action(function (PerformanceResult $record, array $data): void { Gate::authorize('verify', $record); $record->update(['status' => 'RETURNED', 'comments' => $data['comments']]); }),
            Action::make('reject')->label('Reject')->color('danger')->icon('heroicon-o-x-circle')->form([Textarea::make('comments')->required()->maxLength(5000)])
                ->visible(fn (PerformanceResult $record) => $record->status === 'SUBMITTED' && auth()->user()->can('performance.verify_result'))
                ->authorize(fn (PerformanceResult $record) => Gate::allows('verify', $record))
                ->action(function (PerformanceResult $record, array $data): void { Gate::authorize('verify', $record); $record->update(['status' => 'REJECTED', 'comments' => $data['comments']]); }),
        ])->headerActions([
            CreateAction::make()->label('Enter KPI result')->mutateDataUsing(function (array $data): array {
                abort_unless(auth()->user()->can('performance.enter_result'), 403);
                $target = PerformanceTarget::with(['indicator'])->findOrFail($data['performance_target_id']);
                Gate::authorize('view', $target);
                abort_unless($target->indicator->data_source === 'MANUAL', 422, 'This KPI is populated from operational data and does not accept manual results.');
                return [
                    ...$data,
                    'performance_indicator_id' => $target->performance_indicator_id,
                    'reporting_period_id' => $target->reporting_period_id,
                    'department_id' => $target->department_id,
                    'programme_id' => $target->programme_id,
                    'project_id' => $target->project_id,
                    'team_member_id' => $target->team_member_id,
                    'source_type' => $target->indicator->data_source,
                    'entered_by_user_id' => auth()->id(),
                    'status' => 'DRAFT',
                ];
            }),
        ])->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array { return ['index' => \App\Filament\Resources\PerformanceResultResource\Pages\ListPerformanceResults::route('/')]; }

    public static function getEloquentQuery(): Builder
    {
        return app(PerformanceScopeService::class)->scopeResults(parent::getEloquentQuery(), auth()->user())->with(['indicator', 'reportingPeriod', 'department', 'programme', 'project', 'enteredBy', 'target']);
    }
}
