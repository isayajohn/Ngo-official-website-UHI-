<?php

namespace App\Filament\Resources;

use App\Enums\IndicatorCalculationType;
use App\Enums\IndicatorType;
use App\Enums\PerformanceDirection;
use App\Enums\ReportingFrequency;
use App\Filament\Resources\PerformanceIndicatorResource\Pages;
use App\Models\Department;
use App\Models\PerformanceIndicator;
use App\Models\Programme;
use App\Models\Project;
use App\Models\ResultsFrameworkItem;
use App\Services\DepartmentAccessService;
use App\Services\PerformanceScopeService;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Gate;

class PerformanceIndicatorResource extends Resource
{
    protected static ?string $model = PerformanceIndicator::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Monitoring & Evaluation';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';
    protected static ?string $navigationLabel = 'Indicators';
    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Basic information')->schema([
                TextInput::make('code')->required()->maxLength(80)->unique(ignoreRecord: true)->alphaDash(),
                TextInput::make('name')->required()->maxLength(180),
                TextInput::make('short_name')->maxLength(100),
                Select::make('indicator_type')->options(collect(IndicatorType::cases())->mapWithKeys(fn ($type) => [$type->value => str($type->value)->lower()->title()->toString()])->all())->required(),
                Textarea::make('description')->rows(3)->maxLength(5000)->columnSpanFull(),
                Textarea::make('definition_notes')->label('Indicator definition')->rows(2)->maxLength(3000)->columnSpanFull(),
            ])->columns(2),
            Section::make('Results framework')->schema([
                Select::make('results_framework_item_id')->label('Results framework level')->options(fn () => ResultsFrameworkItem::query()->where(fn (Builder $query) => $query->where(fn (Builder $global) => $global->whereNull('programme_id')->whereNull('project_id'))->orWhereIn('programme_id', app(PerformanceScopeService::class)->programmeIds(auth()->user()))->orWhereIn('project_id', app(PerformanceScopeService::class)->projectIds(auth()->user())))->orderBy('title')->pluck('title', 'id'))->searchable()->preload()->nullable(),
                Select::make('level')->options(array_combine(PerformanceIndicator::LEVELS, array_map(fn ($value) => str($value)->lower()->replace('_', ' ')->title()->toString(), PerformanceIndicator::LEVELS)))->required()->live(),
                Select::make('department_id')->label('Department')->options(fn () => Department::query()->where('active', true)->orderBy('name')->get()->filter(fn (Department $d) => auth()->user()->can('organization.full_access') || app(DepartmentAccessService::class)->canAccessDepartment(auth()->user(), $d))->pluck('name', 'id'))->searchable()->visible(fn (Get $get) => $get('level') === 'DEPARTMENT')->required(fn (Get $get) => $get('level') === 'DEPARTMENT'),
                Select::make('programme_id')->label('Programme')->options(fn () => Programme::query()->whereIn('id', app(PerformanceScopeService::class)->programmeIds(auth()->user()))->get()->mapWithKeys(fn (Programme $programme) => [$programme->id => $programme->translated('title')]))->searchable()->visible(fn (Get $get) => $get('level') === 'PROGRAMME')->required(fn (Get $get) => $get('level') === 'PROGRAMME'),
                Select::make('project_id')->label('Project')->options(fn () => Project::query()->whereIn('id', app(PerformanceScopeService::class)->projectIds(auth()->user()))->get()->mapWithKeys(fn (Project $project) => [$project->id => $project->translated('title')]))->searchable()->visible(fn (Get $get) => $get('level') === 'PROJECT')->required(fn (Get $get) => $get('level') === 'PROJECT'),
            ])->columns(2),
            Section::make('Measurement')->schema([
                Select::make('measurement_type')->options(array_combine(PerformanceIndicator::MEASUREMENT_TYPES, array_map(fn ($value) => str($value)->lower()->title()->toString(), PerformanceIndicator::MEASUREMENT_TYPES)))->required(),
                Select::make('calculation_type')->options(collect(IndicatorCalculationType::cases())->mapWithKeys(fn ($type) => [$type->value => str($type->value)->lower()->title()->toString()])->all())->required()->default(IndicatorCalculationType::Manual->value)->live(),
                TextInput::make('unit')->maxLength(40)->helperText('For example: people, visits, percent, TZS, or days.'),
                Select::make('direction')->options(collect(PerformanceDirection::cases())->mapWithKeys(fn ($direction) => [$direction->value => str($direction->value)->lower()->replace('_', ' ')->title()->toString()])->all())->required()->live(),
                TextInput::make('weight')->numeric()->minValue(0)->maxValue(100)->default(0)->required()->suffix('%'),
                TextInput::make('target_value')->numeric()->nullable()->visible(fn (Get $get) => $get('direction') !== 'TARGET_RANGE'),
                TextInput::make('minimum_value')->numeric()->nullable()->visible(fn (Get $get) => $get('direction') === 'TARGET_RANGE'),
                TextInput::make('maximum_value')->numeric()->nullable()->visible(fn (Get $get) => $get('direction') === 'TARGET_RANGE'),
                TextInput::make('baseline_value')->numeric()->nullable(),
                DatePicker::make('baseline_date')->nullable(),
                Textarea::make('calculation_description')->rows(2)->maxLength(3000)->columnSpanFull(),
                Textarea::make('numerator_definition')->rows(2)->maxLength(3000)->columnSpanFull()->visible(fn (Get $get) => in_array($get('calculation_type'), ['PERCENTAGE', 'RATE'])),
                Textarea::make('denominator_definition')->rows(2)->maxLength(3000)->columnSpanFull()->visible(fn (Get $get) => in_array($get('calculation_type'), ['PERCENTAGE', 'RATE'])),
            ])->columns(2),
            Section::make('Data collection and verification')->schema([
                Select::make('reporting_frequency')->options(collect(ReportingFrequency::cases())->mapWithKeys(fn ($frequency) => [$frequency->value => str($frequency->value)->lower()->replace('_', ' ')->title()->toString()])->all())->default(ReportingFrequency::Quarterly->value)->required(),
                Select::make('data_source')->options(['MANUAL' => 'Manual entry', 'PROJECT_ACTIVITY' => 'Project activities', 'TASK' => 'Department tasks', 'MEL_INDICATOR' => 'M&E indicator', 'FINANCE' => 'Finance', 'REPORT_SUBMISSION' => 'Report submission', 'HR' => 'HR', 'PROCUREMENT' => 'Procurement', 'CUSTOM' => 'Other'])->default('MANUAL')->required(),
                TextInput::make('collection_method')->maxLength(180),
                Textarea::make('verification_method')->rows(2)->maxLength(3000)->columnSpanFull(),
                Select::make('responsible_user_id')->label('Responsible user')->relationship('responsibleUser', 'name')->searchable()->preload()->nullable(),
            ])->columns(2),
            Section::make('Status')->schema([
                Toggle::make('active')->default(true)->required(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->searchable()->sortable(), TextColumn::make('name')->searchable()->sortable()->wrap(),
            TextColumn::make('indicator_type')->badge()->formatStateUsing(fn ($state) => $state?->value ?? $state), TextColumn::make('resultsFrameworkItem.title')->label('Results level')->placeholder('—'), TextColumn::make('level')->label('Management scope')->badge(), TextColumn::make('direction')->label('Performance direction')->badge(),
            TextColumn::make('weight')->suffix('%'), IconColumn::make('active')->boolean(),
        ])->filters([SelectFilter::make('level')->options(array_combine(PerformanceIndicator::LEVELS, PerformanceIndicator::LEVELS)), SelectFilter::make('data_source')->options(array_combine(PerformanceIndicator::SOURCES, PerformanceIndicator::SOURCES)), TernaryFilter::make('active')])
            ->recordActions([EditAction::make()])->headerActions([CreateAction::make()->mutateDataUsing(function (array $data): array {
                Gate::authorize('create', PerformanceIndicator::class);
                Gate::authorize('view', new PerformanceIndicator($data));
                return $data;
            })])->defaultSort('name');
    }

    public static function getPages(): array { return ['index' => Pages\ListPerformanceIndicators::route('/')]; }

    public static function getEloquentQuery(): Builder
    {
        return app(PerformanceScopeService::class)->scopeIndicators(parent::getEloquentQuery(), auth()->user());
    }
}
