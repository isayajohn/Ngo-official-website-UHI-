<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ImpactStatisticResource\Pages;
use App\Models\ImpactStatistic;
use App\Models\Project;
use App\Support\CmsCreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ImpactStatisticResource extends Resource
{
    protected static ?string $model = ImpactStatistic::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Our Work';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar';

    public static function form(Schema $schema): Schema
    {
        $fields = [
                Select::make('metric')->options(ImpactStatistic::METRICS)->required(),
                TextInput::make('label.en')->label('Label · English')->required()->maxLength(100), TextInput::make('label.sw')->label('Label · Kiswahili')->maxLength(100),
                TextInput::make('value')->numeric()->integer()->minValue(0)->maxValue(1000000000000)->default(0)->required()->helperText('Enter the actual count, e.g. 10000. K+/M+ only change display formatting.'),
                Select::make('suffix')->options(['' => 'None', '+' => '+', 'K+' => 'K+ (thousands)', 'M+' => 'M+ (millions)'])->default('')->dehydrateStateUsing(fn ($state) => $state ?? ''),
                Select::make('icon')->options(['users' => 'People', 'heart' => 'Health', 'shield' => 'Protection', 'book' => 'Knowledge', 'activity' => 'Activity'])->default('users')->required(),
                Select::make('programme_id')->relationship('programme', 'slug')->getOptionLabelFromRecordUsing(fn ($record) => $record->translated('title'))->searchable()->preload()->live()->afterStateUpdated(fn (Set $set) => $set('project_id', null))->helperText('Leave programme and project blank for an organization-wide indicator.'),
                Select::make('project_id')->options(fn (Get $get) => Project::query()->when($get('programme_id'), fn ($q, $id) => $q->where('programme_id', $id))->limit(100)->get()->mapWithKeys(fn ($p) => [$p->id => $p->translated('title')]))->searchable(),
                DatePicker::make('as_of_date')->label('As of')->maxDate(today()),
                Textarea::make('verification_source')->rows(4)->maxLength(10000)->helperText('Internal evidence reference. Never shown on public pages.'),
                TextInput::make('display_order')->numeric()->integer()->minValue(0)->default(0)->required(),
                Toggle::make('is_active')->label('Publish verified indicator')->default(false)->visible(fn (string $operation) => $operation === 'edit' && auth()->user()->can('impact.publish'))->dehydrated(fn () => auth()->user()->can('impact.publish')),
        ];

        return $schema->components([
            Section::make('Indicator')->schema(array_intersect_key($fields, array_flip([0, 1, 2, 3, 4, 5])))->columns(2)->columnSpanFull(),
            Section::make('Scope')->schema(array_intersect_key($fields, array_flip([6, 7])))->columns(2)->columnSpanFull(),
            Section::make('Evidence')->description('Save the indicator as a draft, then verify the evidence before publishing.')->schema(array_intersect_key($fields, array_flip([8, 9, 10, 11])))->columns(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('label.en')->label('Indicator')->searchable(), TextColumn::make('value')->numeric(), TextColumn::make('programme.title.en')->label('Programme')->wrap(),
            TextColumn::make('as_of_date')->date()->sortable(), TextColumn::make('verified_at')->dateTime()->placeholder('Not verified'), IconColumn::make('is_active')->boolean()->label('Published'),
        ])->defaultSort('display_order')->filters([SelectFilter::make('metric')->options(ImpactStatistic::METRICS), SelectFilter::make('programme_id')->relationship('programme', 'slug'), TernaryFilter::make('is_active')->label('Published'), TrashedFilter::make()])
            ->recordActions([ViewAction::make(), EditAction::make()])->headerActions([CmsCreateAction::make(static::class, true)]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListImpactStatistics::route('/'), 'create' => Pages\CreateImpactStatistic::route('/create'), 'view' => Pages\ViewImpactStatistic::route('/{record}'), 'edit' => Pages\EditImpactStatistic::route('/{record}/edit')];
    }
}
