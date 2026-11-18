<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DistrictResource\Pages;
use App\Models\District;
use App\Support\CmsCreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rule;

class DistrictResource extends Resource
{
    protected static ?string $model = District::class;

    protected static string|\UnitEnum|null $navigationGroup = 'System';

    protected static ?string $navigationParentItem = 'Settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-map-pin';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')->required()->maxLength(100)->rules(fn (Get $get, ?District $record) => [Rule::unique('districts', 'name')->where('region_id', $get('region_id'))->ignore($record?->id)]),
            TextInput::make('slug')->required(fn (string $operation) => $operation === 'edit')->maxLength(255)->unique(ignoreRecord: true)->rules(['regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/'])->helperText('Generated automatically on creation if left blank.'),
            Select::make('region_id')->relationship('region', 'name')->searchable()->preload()->required(),
            TextInput::make('display_order')->numeric()->integer()->minValue(0)->default(0)->required(),
            Toggle::make('is_active')->label('Visible on the website')->default(true)->helperText('Hiding a location also hides its dependent locations and public projects.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->searchable()->sortable(), TextColumn::make('region.name')->sortable(), IconColumn::make('is_active')->boolean()])
            ->defaultSort('name')->filters([TrashedFilter::make()])->recordActions([ViewAction::make(), EditAction::make()])->headerActions([CmsCreateAction::make(static::class)]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListDistricts::route('/'), 'create' => Pages\CreateDistrict::route('/create'), 'edit' => Pages\EditDistrict::route('/{record}/edit')];
    }
}
