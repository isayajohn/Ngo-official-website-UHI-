<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PerformanceRatingScaleResource\Pages\ListPerformanceRatingScales;
use App\Models\PerformanceRatingScale;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PerformanceRatingScaleResource extends Resource
{
    protected static ?string $model = PerformanceRatingScale::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Reports & Performance';
    protected static ?string $navigationLabel = 'Performance Rating Scale';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-star';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Section::make('Editable rating band')->schema([
            TextInput::make('name')->default('UGHI Performance Rating')->required()->maxLength(120),
            TextInput::make('label')->required()->maxLength(80),
            TextInput::make('minimum_score')->numeric()->minValue(0)->maxValue(200)->required(),
            TextInput::make('maximum_score')->numeric()->minValue(0)->maxValue(200)->required(),
            TextInput::make('display_order')->numeric()->integer()->minValue(0)->default(0)->required(),
            Textarea::make('description')->rows(2)->maxLength(1000)->columnSpanFull(),
            Toggle::make('active')->default(true)->required(),
        ])->columns(2)]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->searchable(), TextColumn::make('label')->searchable()->weight('semibold'), TextColumn::make('minimum_score')->label('From'), TextColumn::make('maximum_score')->label('To'), TextColumn::make('description')->wrap()->limit(80), IconColumn::make('active')->boolean()])
            ->recordActions([EditAction::make()])->headerActions([CreateAction::make()])->defaultSort('display_order');
    }

    public static function getPages(): array { return ['index' => ListPerformanceRatingScales::route('/')]; }
}
