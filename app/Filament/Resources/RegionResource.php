<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegionResource\Pages;
use App\Models\Region;
use App\Support\CmsCreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rule;

class RegionResource extends Resource
{
    protected static ?string $model = Region::class;

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?string $navigationParentItem = 'Settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    public static function form(Schema $schema): Schema
    {
        $fields = [
            TextInput::make('name')->required()->maxLength(100)->rules(fn (Get $get, ?Region $record) => [Rule::unique('regions', 'name')->where('country_id', $get('country_id'))->ignore($record?->id)]),
            TextInput::make('slug')->required(fn (string $operation) => $operation === 'edit')->maxLength(255)->unique(ignoreRecord: true)->rules(['regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'])->helperText('Generated automatically on creation if left blank.'),
            Select::make('country_id')->relationship('country', 'name')->searchable()->preload()->required(),
            Textarea::make('description.en')->label('Description · English')->maxLength(3000), Textarea::make('description.sw')->label('Description · Kiswahili')->maxLength(3000),
            TextInput::make('display_order')->numeric()->integer()->minValue(0)->default(0)->required(),
            Toggle::make('is_active')->label('Visible on the website')->default(true)->helperText('Hiding a location also hides its dependent locations and public projects.'),
        ];

        return $schema->components([
            Section::make('Location')->schema(array_intersect_key($fields, array_flip([0, 1, 2])))->columns(2)->columnSpanFull(),
            Section::make('Details')->schema(array_intersect_key($fields, array_flip([3, 4, 5, 6])))->columns(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->searchable()->sortable(), TextColumn::make('country.name')->sortable(), IconColumn::make('is_active')->boolean()])
            ->defaultSort('name')->filters([TrashedFilter::make()])->recordActions([ViewAction::make(), EditAction::make()])->headerActions([CmsCreateAction::make(static::class, true)]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListRegions::route('/'), 'create' => Pages\CreateRegion::route('/create'), 'edit' => Pages\EditRegion::route('/{record}/edit')];
    }
}
