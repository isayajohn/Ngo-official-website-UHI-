<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactMessageResource\Pages;
use App\Models\ContactMessage;
use App\Models\Department;
use App\Models\User;
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

class ContactMessageResource extends Resource
{
    protected static ?string $model = ContactMessage::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Organization';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox';
    protected static ?string $navigationLabel = 'Contact Messages';
    protected static ?string $recordTitleAttribute = 'subject';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Message')->schema([
                TextInput::make('name')->disabled(), TextInput::make('email')->disabled(), TextInput::make('phone')->disabled(), TextInput::make('subject')->disabled(), Textarea::make('message')->disabled()->rows(8)->columnSpanFull(),
            ])->columns(2)->columnSpanFull(),
            Section::make('Internal routing')->schema([
                Select::make('department_id')->options(fn () => Department::orderBy('name')->pluck('name', 'id'))->searchable()->preload()->nullable(),
                Select::make('assigned_to_user_id')->label('Assigned to')->options(fn () => User::where('is_active', true)->orderBy('name')->pluck('name', 'id'))->searchable()->preload()->nullable(),
                Select::make('status')->options(array_combine(ContactMessage::STATUSES, ContactMessage::STATUSES))->required(),
            ])->columns(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('created_at')->dateTime()->sortable(), TextColumn::make('name')->searchable(), TextColumn::make('subject')->placeholder('General enquiry')->searchable(), TextColumn::make('department.name')->label('Department')->placeholder('General'), TextColumn::make('status')->badge(), TextColumn::make('assignedTo.name')->label('Assigned to')->placeholder('Unassigned'),
        ])->defaultSort('created_at', 'desc')->filters([SelectFilter::make('status')->options(array_combine(ContactMessage::STATUSES, ContactMessage::STATUSES)), SelectFilter::make('department_id')->relationship('department', 'name')])->recordActions([ViewAction::make(), EditAction::make()]);
    }

    public static function getPages(): array { return ['index' => Pages\ListContactMessages::route('/'), 'view' => Pages\ViewContactMessage::route('/{record}'), 'edit' => Pages\EditContactMessage::route('/{record}/edit')]; }
}
