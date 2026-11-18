<?php

namespace App\Filament\Resources;

use App\Enums\ResultsFrameworkLevel;
use App\Models\Programme;
use App\Models\Project;
use App\Models\ResultsFrameworkItem;
use App\Services\PerformanceScopeService;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
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

class ResultsFrameworkResource extends Resource
{
    protected static ?string $model = ResultsFrameworkItem::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Monitoring & Evaluation';
    protected static ?string $navigationLabel = 'Results Framework';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Results framework item')->schema([
                TextInput::make('title')->required()->maxLength(180),
                TextInput::make('code')->maxLength(80),
                Select::make('level')->options(collect(ResultsFrameworkLevel::cases())->mapWithKeys(fn ($level) => [$level->value => str($level->value)->lower()->title()->toString()])->all())->required()->live(),
                Select::make('programme_id')->label('Programme')->options(fn () => Programme::query()->whereIn('id', app(PerformanceScopeService::class)->programmeIds(auth()->user()))->get()->mapWithKeys(fn (Programme $programme) => [$programme->id => $programme->translated('title')]))->searchable()->live()->nullable(),
                Select::make('project_id')->label('Project')->options(fn () => Project::query()->whereIn('id', app(PerformanceScopeService::class)->projectIds(auth()->user()))->with('programme')->get()->mapWithKeys(fn (Project $project) => [$project->id => $project->translated('title')]))->searchable()->live()->nullable(),
                Select::make('parent_id')->label('Parent item')->options(function (Get $get, ?ResultsFrameworkItem $record): array {
                    $user = auth()->user();
                    $scope = app(PerformanceScopeService::class);
                    $query = ResultsFrameworkItem::query()->where(fn (Builder $items) => $items->where(fn (Builder $global) => $global->whereNull('programme_id')->whereNull('project_id'))->orWhereIn('programme_id', $scope->programmeIds($user))->orWhereIn('project_id', $scope->projectIds($user)))->orderBy('display_order')->orderBy('title');
                    if ($get('programme_id')) $query->where(fn (Builder $q) => $q->whereNull('programme_id')->orWhere('programme_id', $get('programme_id')));
                    if ($get('project_id')) $query->where(fn (Builder $q) => $q->whereNull('project_id')->orWhere('project_id', $get('project_id')));
                    if ($record) $query->whereKeyNot($record->getKey())->whereNotIn('id', $record->descendants()->select('id'));
                    return $query->get()->mapWithKeys(fn (ResultsFrameworkItem $item) => [$item->id => $item->title.' · '.str($item->level instanceof ResultsFrameworkLevel ? $item->level->value : $item->level)->lower()->title()])->all();
                })->searchable()->nullable(),
                DatePicker::make('start_date'),
                DatePicker::make('end_date')->afterOrEqual('start_date'),
                Textarea::make('description')->rows(3)->maxLength(5000)->columnSpanFull(),
                TextInput::make('display_order')->numeric()->minValue(0)->default(0)->required(),
                Toggle::make('active')->default(true)->required(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable()->sortable()->description(fn (ResultsFrameworkItem $record) => $record->parent?->title),
            TextColumn::make('level')->badge()->formatStateUsing(fn ($state) => $state instanceof ResultsFrameworkLevel ? str($state->value)->lower()->title() : str($state)->lower()->title()),
            TextColumn::make('code')->placeholder('—')->searchable(),
            TextColumn::make('programme.title.en')->label('Programme')->placeholder('—'),
            TextColumn::make('project.title.en')->label('Project')->placeholder('—'),
            TextColumn::make('indicators_count')->counts('indicators')->label('Indicators'),
            IconColumn::make('active')->boolean(),
        ])->filters([
            SelectFilter::make('level')->options(collect(ResultsFrameworkLevel::cases())->mapWithKeys(fn ($level) => [$level->value => $level->value])->all()),
            TernaryFilter::make('active'),
        ])->recordActions([EditAction::make()])->headerActions([CreateAction::make()->mutateDataUsing(function (array $data): array {
            $item = new ResultsFrameworkItem($data);
            Gate::authorize('create', ResultsFrameworkItem::class);
            Gate::authorize('view', $item);
            return $data;
        })])->defaultSort('display_order');
    }

    public static function getPages(): array
    {
        return ['index' => \App\Filament\Resources\ResultsFrameworkResource\Pages\ListResultsFrameworkItems::route('/')];
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        if ($user->can('organization.full_access')) return parent::getEloquentQuery()->with(['parent', 'programme', 'project'])->withCount('indicators');
        $scope = app(PerformanceScopeService::class);
        return parent::getEloquentQuery()->where(fn (Builder $query) => $query->where(fn (Builder $global) => $global->whereNull('programme_id')->whereNull('project_id'))->orWhereIn('programme_id', $scope->programmeIds($user))->orWhereIn('project_id', $scope->projectIds($user)))->with(['parent', 'programme', 'project'])->withCount('indicators');
    }
}
