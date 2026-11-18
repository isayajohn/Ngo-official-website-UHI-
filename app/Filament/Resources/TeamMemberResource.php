<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TeamMemberResource\Pages;
use App\Filament\Resources\UserInvitationResource;
use App\Filament\Resources\UserResource;
use App\Models\Department;
use App\Models\Position;
use App\Models\TeamMember;
use App\Support\CmsCreateAction;
use App\Support\CmsFields;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TeamMemberResource extends Resource
{
    protected static ?string $model = TeamMember::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Organization';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Staff & Leadership';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Profile')->schema([
                TextInput::make('name')->required()->maxLength(160), TextInput::make('slug')->required(fn (string $operation) => $operation === 'edit')->unique(ignoreRecord: true)->maxLength(255),
                TextInput::make('email')->email()->maxLength(255), TextInput::make('phone')->tel()->maxLength(50), TextInput::make('employment_type')->maxLength(50),
                CmsFields::image('photo'), TextInput::make('photo_alt')->maxLength(255)->requiredWith('photo'), Textarea::make('short_bio')->rows(3)->maxLength(600), Textarea::make('bio')->rows(6)->maxLength(5000)->columnSpanFull(),
            ])->columns(2)->columnSpanFull(),
            Section::make('Organization')->schema([
                Select::make('department_id')->relationship('department', 'name')->searchable()->preload()->live()->afterStateUpdated(fn (\Filament\Schemas\Components\Utilities\Set $set) => $set('position_id', null)),
                Select::make('position_id')->options(fn (Get $get) => Position::query()->where('active', true)->when($get('department_id'), fn ($q, $id) => $q->where('department_id', $id))->orderBy('title')->pluck('title', 'id'))->searchable()->preload(),
                TextInput::make('account_link')->label('User account')->formatStateUsing(fn (?TeamMember $record) => $record?->user?->email ?? 'No linked user account')->disabled()->dehydrated(false)->helperText('Invite a linked user from the staff actions menu.'),
                TextInput::make('display_order')->numeric()->integer()->minValue(0)->default(0)->required(),
                Toggle::make('show_on_website')->default(true), Toggle::make('active')->default(true),
                Toggle::make('is_leader')->label('Show in Leadership')->live()->helperText('Use this for board, executive and other public leadership profiles.'),
                TextInput::make('leadership_role')->label('Leadership role')->maxLength(160)->visible(fn (Get $get): bool => (bool) $get('is_leader'))->dehydrated(),
            ])->columns(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('photo')->disk('public')->circular()->size(40)->label('Photo'),
            TextColumn::make('name')->searchable()->sortable()->description(fn (TeamMember $record) => $record->email ?: 'Staff profile')->weight('semibold'),
            TextColumn::make('department.name')->sortable()->searchable()->placeholder('Unassigned'),
            TextColumn::make('position.title')->label('Position')->placeholder('Unassigned'),
            TextColumn::make('leadership_role')->label('Leadership role')->badge()->placeholder('Staff member'),
            TextColumn::make('employment_type')->badge()->placeholder('—'),
            TextColumn::make('active')->label('Status')->badge()->formatStateUsing(fn (bool $state) => $state ? 'Active' : 'Inactive')->color(fn (bool $state) => $state ? 'success' : 'gray'),
            IconColumn::make('is_leader')->boolean()->label('Leadership profile'),
            IconColumn::make('show_on_website')->boolean()->label('Website'),
        ])->defaultSort('display_order')->filters([SelectFilter::make('department_id')->relationship('department', 'name'), TernaryFilter::make('is_leader')->label('Leadership'), TernaryFilter::make('active'), TernaryFilter::make('show_on_website')->label('Website visible'), TrashedFilter::make()])
            ->extraAttributes(['class' => 'ughi-people-table'])
            ->recordActions([ActionGroup::make([
                ViewAction::make(), EditAction::make(),
                Action::make('createUserAccount')->label('Invite to create account')->icon('heroicon-o-user-plus')
                    ->url(fn (TeamMember $record) => UserInvitationResource::getUrl('index', ['team_member_id' => $record->id]))
                    ->visible(fn (TeamMember $record) => ! $record->user && ! $record->legacyUser && auth()->user()->can('user.invite')),
                Action::make('viewUserAccount')->label('View User Account')->icon('heroicon-o-user-circle')
                    ->url(fn (TeamMember $record) => UserResource::getUrl('view', ['record' => ($record->user ?? $record->legacyUser)->getKey()]))
                    ->visible(fn (TeamMember $record) => ($record->user || $record->legacyUser)
                        && (auth()->user()->can('user.view') || auth()->user()->can('user.manage'))),
            ])->icon('heroicon-m-ellipsis-vertical')->tooltip('Staff actions')])
            ->headerActions([CmsCreateAction::make(static::class, true)]);
    }
    public static function getEloquentQuery(): Builder { return parent::getEloquentQuery()->withoutGlobalScopes([SoftDeletingScope::class]); }
    public static function getPages(): array { return ['index' => Pages\ListTeamMembers::route('/'), 'create' => Pages\CreateTeamMember::route('/create'), 'edit' => Pages\EditTeamMember::route('/{record}/edit')]; }
}
