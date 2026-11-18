<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PositionResource\Pages;
use App\Models\Department;
use App\Models\Position;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Validation\Rule;

class PositionResource extends Resource
{
    protected static ?string $model = Position::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Organization';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'Positions';
    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Position')->schema([
                TextInput::make('title')->required()->maxLength(160), TextInput::make('code')->maxLength(30),
                Select::make('department_id')->relationship('department', 'name')->searchable()->preload()->required(),
                Select::make('reports_to_position_id')->label('Reports to')->options(fn (?Position $record) => Position::query()->with('department')->when($record, fn ($q) => $q->where('id', '!=', $record->id))->where('active', true)->orderBy('title')->get()->mapWithKeys(fn ($position) => [$position->id => $position->title.' · '.($position->department?->name ?? '')]))->searchable()->preload()->nullable()->helperText('A position cannot report to itself.'),
                TextInput::make('level')->maxLength(80), TextInput::make('display_order')->numeric()->integer()->minValue(0)->default(0)->required(),
                Textarea::make('description')->rows(4)->maxLength(5000)->columnSpanFull(), Toggle::make('active')->default(true),
            ])->columns(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable()->sortable(), TextColumn::make('code')->searchable(), TextColumn::make('department.name')->sortable(), TextColumn::make('reportsTo.title')->label('Reports to')->placeholder('Top level'), TextColumn::make('team_members_count')->counts('teamMembers')->label('Staff'), IconColumn::make('active')->boolean(), TextColumn::make('display_order')->sortable(),
        ])->defaultSort('display_order')->filters([SelectFilter::make('department_id')->relationship('department', 'name'), TernaryFilter::make('active'), TrashedFilter::make()])->recordActions([ViewAction::make(), EditAction::make()])->headerActions([CmsCreateAction::make(static::class, true)]);
    }
    public static function getEloquentQuery(): Builder { return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]); }
    public static function getPages(): array { return ['index' => Pages\ListPositions::route('/'), 'create' => Pages\CreatePosition::route('/create'), 'edit' => Pages\EditPosition::route('/{record}/edit')]; }
}
