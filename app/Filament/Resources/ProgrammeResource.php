<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProgrammeResource\Pages;
use App\Models\Programme;
use App\Support\CmsFields;
use App\Support\CmsCreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProgrammeResource extends Resource
{
    protected static ?string $model = Programme::class;

    protected static string|\UnitEnum|null $navigationGroup = 'Our Work';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-heart';

    protected static ?string $recordTitleAttribute = 'slug';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Programme content')->schema([
                TextInput::make('title.en')->label('Title · English')->required()->maxLength(255),
                TextInput::make('title.sw')->label('Title · Kiswahili')->maxLength(255),
                TextInput::make('slug')->required(fn (string $operation) => $operation === 'edit')->unique(ignoreRecord: true)->rules(['regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'])->maxLength(255)->helperText('Leave blank on creation to generate a unique URL. Keep established URLs stable.'),
                Textarea::make('short_description.en')->label('Summary · English')->required()->maxLength(600)->rows(3),
                Textarea::make('short_description.sw')->label('Summary · Kiswahili')->maxLength(600)->rows(3),
                Textarea::make('description.en')->label('Full description · English')->rows(8),
                Textarea::make('description.sw')->label('Full description · Kiswahili')->rows(8),
            ])->columnSpanFull(),
            Section::make('Presentation')->schema([
                CmsFields::image('image'),
                TextInput::make('image_alt')->label('Image alternative text')->maxLength(255)->requiredWith('image'),
                Select::make('icon')->options(['heart' => 'Heart', 'shield' => 'Shield', 'activity' => 'Activity', 'users' => 'People', 'sun' => 'Well-being', 'droplet' => 'Water', 'book' => 'Knowledge'])->default('heart')->required(),
                TextInput::make('display_order')->numeric()->integer()->minValue(0)->default(0)->required(),
                Toggle::make('is_active')->label('Published on the public website')->default(true),
            ])->columns(2)->columnSpanFull(),
            Section::make('Supporting departments')->schema([
                Select::make('departments')->relationship('departments', 'name')->multiple()->searchable()->preload()->visible(fn () => auth()->user()->can('department.assign_programme'))->dehydrated(fn () => auth()->user()->can('department.assign_programme'))->helperText('Internal support structure; departments remain separate from programmes.'),
            ])->columnSpanFull(),
            Section::make('Search engines')->schema([
                TextInput::make('meta_title')->maxLength(70),
                Textarea::make('meta_description')->maxLength(180),
            ])->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title.en')->label('Programme')->searchable()->wrap(),
            TextColumn::make('display_order')->sortable()->label('Order'),
            IconColumn::make('is_active')->boolean()->label('Published'),
            TextColumn::make('updated_at')->dateTime()->sortable(),
        ])->defaultSort('display_order')->filters([TrashedFilter::make()])
            ->recordActions([ViewAction::make(), EditAction::make()])->headerActions([CmsCreateAction::make(static::class, true)]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListProgrammes::route('/'), 'create' => Pages\CreateProgramme::route('/create'), 'edit' => Pages\EditProgramme::route('/{record}/edit')];
    }
}
