<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ReportingPeriodResource\Pages;
use App\Models\FinancialYear;
use App\Models\ReportingPeriod;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ReportingPeriodResource extends Resource
{
    protected static ?string $model = ReportingPeriod::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Monitoring & Evaluation';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar-days';
    protected static ?string $navigationLabel = 'Reporting Periods';
    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return $schema->components([Section::make('Reporting period')->schema([
            TextInput::make('name')->required()->maxLength(120),
            Select::make('period_type')->options(array_combine(['MONTHLY', 'QUARTERLY', 'SEMI_ANNUAL', 'ANNUAL', 'PROJECT_BASED', 'CUSTOM'], ['Monthly', 'Quarterly', 'Semi-annual', 'Annual', 'Project based', 'Custom']))->required(),
            Select::make('financial_year_id')->label('Financial year')->options(FinancialYear::query()->orderByDesc('start_date')->pluck('name', 'id'))->searchable()->nullable(),
            DatePicker::make('start_date')->required(),
            DatePicker::make('end_date')->required()->afterOrEqual('start_date'),
            DatePicker::make('submission_deadline')->afterOrEqual('start_date'),
            DatePicker::make('data_entry_start')->afterOrEqual('start_date'),
            DatePicker::make('data_entry_deadline')->afterOrEqual('start_date'),
            DatePicker::make('verification_deadline')->afterOrEqual('start_date'),
            Select::make('status')->options(['UPCOMING' => 'Upcoming', 'OPEN' => 'Open', 'DATA_ENTRY_CLOSED' => 'Data entry closed', 'UNDER_VERIFICATION' => 'Under verification', 'CLOSED' => 'Closed', 'ARCHIVED' => 'Archived', 'ACTIVE' => 'Active (legacy)'])->default('OPEN')->required(),
        ])->columns(2)]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('period_type')->badge(),
            TextColumn::make('financialYear.name')->label('Financial year')->placeholder('—'),
            TextColumn::make('start_date')->date()->sortable(),
            TextColumn::make('end_date')->date()->sortable(),
            TextColumn::make('data_entry_deadline')->label('Data entry deadline')->date()->sortable()->placeholder('Not set'),
            TextColumn::make('status')->badge()->color(fn (string $state) => match ($state) { 'OPEN' => 'success', 'CLOSED' => 'gray', 'UPCOMING' => 'info', default => 'warning' }),
        ])->filters([SelectFilter::make('period_type')->options(array_combine(['MONTHLY', 'QUARTERLY', 'SEMI_ANNUAL', 'ANNUAL', 'PROJECT_BASED', 'CUSTOM'], ['Monthly', 'Quarterly', 'Semi-annual', 'Annual', 'Project based', 'Custom'])), SelectFilter::make('status')->options(['UPCOMING' => 'Upcoming', 'OPEN' => 'Open', 'DATA_ENTRY_CLOSED' => 'Data entry closed', 'UNDER_VERIFICATION' => 'Under verification', 'CLOSED' => 'Closed', 'ARCHIVED' => 'Archived', 'ACTIVE' => 'Active (legacy)'])])
            ->recordActions([EditAction::make()])->headerActions([CreateAction::make()])->defaultSort('start_date', 'desc');
    }

    public static function getPages(): array { return ['index' => Pages\ListReportingPeriods::route('/')]; }
}
