<?php

namespace App\Filament\Resources;

use App\Filament\Resources\InvolvementApplicationResource\Pages;
use App\Models\InvolvementApplication;
use App\Models\User;
use App\Policies\InvolvementApplicationPolicy;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class InvolvementApplicationResource extends Resource
{
    protected static ?string $model = InvolvementApplication::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Organization';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-hand-raised';
    protected static ?string $navigationLabel = 'Get Involved Applications';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Submission')->schema([
                TextInput::make('type')->formatStateUsing(fn ($state) => InvolvementApplication::TYPES[$state] ?? $state)->disabled()->dehydrated(false),
                TextInput::make('submitted_at')->formatStateUsing(fn ($state) => $state?->format('d M Y, H:i'))->disabled()->dehydrated(false),
                TextInput::make('name')->disabled()->dehydrated(false),
                TextInput::make('email')->disabled()->dehydrated(false),
                TextInput::make('phone')->disabled()->dehydrated(false),
                TextInput::make('organization')->disabled()->dehydrated(false),
                TextInput::make('interest_area')->disabled()->dehydrated(false),
                TextInput::make('availability')->disabled()->dehydrated(false),
                Textarea::make('message')->disabled()->dehydrated(false)->rows(6)->columnSpanFull(),
            ])->columns(2)->columnSpanFull(),
            Section::make('Internal follow-up')->schema([
                Select::make('status')->options(array_combine(InvolvementApplication::STATUSES, InvolvementApplication::STATUSES))->required(),
                Select::make('assigned_to_user_id')->label('Assigned to')->options(fn () => User::where('is_active', true)->orderBy('name')->pluck('name', 'id'))->searchable()->preload()->nullable(),
                Textarea::make('internal_notes')->label('Staff notes')->rows(5)->maxLength(5000)->columnSpanFull(),
            ])->columns(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('submitted_at')->label('Submitted')->dateTime()->sortable(),
            TextColumn::make('type')->formatStateUsing(fn ($state) => InvolvementApplication::TYPES[$state] ?? $state)->badge()->sortable(),
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('email')->searchable(),
            TextColumn::make('organization')->toggleable(),
            TextColumn::make('status')->badge()->color(fn ($state) => match ($state) { 'ACCEPTED' => 'success', 'DECLINED', 'SPAM' => 'danger', 'CLOSED' => 'gray', 'REVIEWING' => 'warning', default => 'info' }),
            TextColumn::make('assignedTo.name')->label('Assigned to')->placeholder('Unassigned'),
        ])->defaultSort('submitted_at', 'desc')->filters([
            SelectFilter::make('type')->options(InvolvementApplication::TYPES),
            SelectFilter::make('status')->options(array_combine(InvolvementApplication::STATUSES, InvolvementApplication::STATUSES)),
        ])->recordActions([ViewAction::make(), EditAction::make()]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $types = collect(array_keys(InvolvementApplication::TYPES))->filter(fn (string $type) => $user && InvolvementApplicationPolicy::canViewType($user, $type))->all();

        return parent::getEloquentQuery()->whereIn('type', $types);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListInvolvementApplications::route('/'),
            'view' => Pages\ViewInvolvementApplication::route('/{record}'),
            'edit' => Pages\EditInvolvementApplication::route('/{record}/edit'),
        ];
    }
}
