<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FinancialYearResource\Pages;
use App\Models\FinancialYear;
use App\Support\CmsCreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class FinancialYearResource extends Resource
{
    protected static ?string $model = FinancialYear::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Finance';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Financial Years';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Section::make('Financial period')->schema([
            TextInput::make('name')->required()->maxLength(80)->unique(ignoreRecord: true),
            DatePicker::make('start_date')->required(), DatePicker::make('end_date')->required()->afterOrEqual('start_date'),
            Select::make('status')->options(fn (?FinancialYear $record) => $record?->status === 'CLOSED' ? ['CLOSED' => 'Closed'] : ['OPEN' => 'Open', 'CLOSING' => 'Closing'])->default('OPEN')->required(),
        ])->columns(2)]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([TextColumn::make('name')->searchable()->sortable(), TextColumn::make('start_date')->date()->sortable(), TextColumn::make('end_date')->date()->sortable(), TextColumn::make('status')->badge(), TextColumn::make('closed_at')->dateTime()->placeholder('—'), TextColumn::make('closedBy.name')->label('Closed by')->placeholder('—')])
            ->filters([SelectFilter::make('status')->options(['OPEN' => 'Open', 'CLOSING' => 'Closing', 'CLOSED' => 'Closed'])])->recordActions([\Filament\Actions\EditAction::make()])->headerActions([CmsCreateAction::make(static::class)]);
    }

    public static function getPages(): array { return ['index' => Pages\ListFinancialYears::route('/'), 'create' => Pages\CreateFinancialYear::route('/create'), 'edit' => Pages\EditFinancialYear::route('/{record}/edit')]; }
}
