<?php

namespace App\Filament\Resources;

use App\Enums\UserInvitationStatus;
use App\Filament\Resources\UserInvitationResource\Pages;
use App\Models\Department;
use App\Models\TeamMember;
use App\Models\User;
use App\Models\UserInvitation;
use App\Services\UserInvitationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification as FilamentNotification;
use Filament\Resources\Resource;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserInvitationResource extends Resource
{
    protected static ?string $model = UserInvitation::class;
    protected static string|\UnitEnum|null $navigationGroup = 'System';
    protected static ?string $navigationParentItem = 'Users';
    protected static ?string $navigationLabel = 'Invitations';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-envelope';

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('user.invitation.view') ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('user.invite') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Name')->searchable(['first_name', 'last_name'])->weight('semibold'),
                TextColumn::make('email')->searchable()->copyable(),
                TextColumn::make('teamMember.name')->label('Team Member')->placeholder('—'),
                TextColumn::make('role.name')->label('Role')->placeholder('Not assigned'),
                TextColumn::make('primaryDepartment.name')->label('Primary Department')->placeholder('Not assigned'),
                TextColumn::make('inviter.name')->label('Invited By'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->state(fn (UserInvitation $record): string => $record->effectiveStatus()->value)
                    ->formatStateUsing(fn (string $state): string => str($state)->lower()->replace('_', ' ')->title()->toString())
                    ->color(fn (string $state): string => match ($state) {
                        'ACCEPTED' => 'success',
                        'PENDING' => 'warning',
                        'EXPIRED' => 'gray',
                        'REVOKED', 'CANCELLED' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')->label('Sent At')->dateTime()->sortable(),
                TextColumn::make('expires_at')->dateTime()->sortable(),
                TextColumn::make('accepted_at')->dateTime()->placeholder('—'),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->options(collect(UserInvitationStatus::cases())
                        ->mapWithKeys(fn (UserInvitationStatus $status): array => [$status->value => str($status->value)->lower()->replace('_', ' ')->title()->toString()]))
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'PENDING' => $query->where('status', 'PENDING')->where('expires_at', '>', now()),
                            'EXPIRED' => $query->where('status', 'PENDING')->where('expires_at', '<=', now()),
                            default => filled($data['value'] ?? null) ? $query->where('status', $data['value']) : $query,
                        };
                    }),
                SelectFilter::make('primary_department_id')->label('Department')->options(fn () => Department::query()->orderBy('name')->pluck('name', 'id')),
                SelectFilter::make('role_id')->label('Role')->options(fn () => Role::query()->where('guard_name', 'web')->orderBy('name')->pluck('name', 'id')),
                SelectFilter::make('invited_by_user_id')->label('Invited By')->options(fn () => User::query()->orderBy('name')->pluck('name', 'id')),
                Filter::make('invitation_date_range')
                    ->form([
                        DatePicker::make('from')->label('Invited from'),
                        DatePicker::make('until')->label('Invited until'),
                    ])
                    ->query(fn (Builder $query, array $data): Builder => $query
                        ->when($data['from'] ?? null, fn (Builder $query, $date) => $query->whereDate('created_at', '>=', $date))
                        ->when($data['until'] ?? null, fn (Builder $query, $date) => $query->whereDate('created_at', '<=', $date))),
            ])
            ->headerActions([
                Action::make('invite')
                    ->label('Invite User')
                    ->icon('heroicon-o-user-plus')
                    ->visible(fn (): bool => auth()->user()->can('user.invite'))
                    ->authorize(fn (): bool => auth()->user()->can('user.invite'))
                    ->modalWidth('3xl')
                    ->schema([
                        Select::make('team_member_id')
                            ->label('Team Member (optional)')
                            ->options(fn () => TeamMember::query()->where('active', true)
                                ->whereDoesntHave('user')->whereDoesntHave('legacyUser')
                                ->orderBy('name')->pluck('name', 'id'))
                            ->searchable()->live()->nullable()
                            ->default(fn () => static::requestedTeamMember()?->getKey())
                            ->afterStateUpdated(function (Set $set, mixed $state): void {
                                $member = $state ? TeamMember::query()->with('department')->find($state) : null;
                                if (! $member) {
                                    return;
                                }

                                $nameParts = preg_split('/\s+/', trim($member->name), 2) ?: [];
                                $set('first_name', $nameParts[0] ?? '');
                                $set('last_name', $nameParts[1] ?? '');
                                $set('email', $member->email);
                                $set('primary_department_id', $member->department_id);
                            }),
                        TextInput::make('first_name')->default(fn () => static::requestedTeamMemberNameParts()[0] ?? null)->required()->maxLength(100),
                        TextInput::make('last_name')->default(fn () => static::requestedTeamMemberNameParts()[1] ?? null)->required()->maxLength(100),
                        TextInput::make('email')->default(fn () => static::requestedTeamMember()?->email)->email()->required()->maxLength(255)
                            ->rules([Rule::unique('users', 'email')]),
                        Select::make('role_id')->label('Role')->options(fn () => Role::query()
                            ->where('guard_name', 'web')->orderBy('name')->pluck('name', 'id'))
                            ->searchable()->required(fn (): bool => auth()->user()->can('user.assign_role') || auth()->user()->can('role.manage'))
                            ->visible(fn (): bool => auth()->user()->can('user.assign_role') || auth()->user()->can('role.manage'))
                            ->dehydrated(fn (): bool => auth()->user()->can('user.assign_role') || auth()->user()->can('role.manage')),
                        Select::make('primary_department_id')->label('Primary Department')->options(fn () => Department::query()
                            ->where('active', true)->orderBy('name')->pluck('name', 'id'))
                            ->searchable()->nullable()->default(fn () => static::requestedTeamMember()?->department_id)
                            ->visible(fn (): bool => auth()->user()->can('user.assign_department') || auth()->user()->can('organization.full_access'))
                            ->dehydrated(fn (): bool => auth()->user()->can('user.assign_department') || auth()->user()->can('organization.full_access')),
                        Select::make('department_ids')->label('Additional Departments')->multiple()
                            ->options(fn (Get $get) => Department::query()->where('active', true)
                                ->when($get('primary_department_id'), fn (Builder $query, $id) => $query->whereKeyNot($id))
                                ->orderBy('name')->pluck('name', 'id'))
                            ->searchable()->preload()
                            ->visible(fn (): bool => auth()->user()->can('user.assign_department') || auth()->user()->can('organization.full_access'))
                            ->dehydrated(fn (): bool => auth()->user()->can('user.assign_department') || auth()->user()->can('organization.full_access')),
                    ])
                    ->action(function (array $data, UserInvitationService $service): void {
                        abort_unless(auth()->user()->can('user.invite'), 403);

                        if (($data['role_id'] ?? null) && Role::query()->whereKey($data['role_id'])->where('name', 'Super Admin')->exists()) {
                            abort_unless(auth()->user()->can('role.manage'), 403);
                        }

                        [$invitation, $sent] = $service->invite($data, auth()->user());
                        $notification = FilamentNotification::make()
                            ->title($sent ? 'Invitation sent' : 'Invitation saved')
                            ->body($sent ? "An invitation was sent to {$invitation->email}." : 'The invitation is saved. Email delivery failed; you can resend it from this list.');

                        if ($sent) {
                            $notification->success();
                        } else {
                            $notification->warning();
                        }

                        $notification->send();
                    }),
            ])
            ->recordActions([
                Action::make('view')
                    ->label('View Invitation')
                    ->icon('heroicon-o-eye')
                    ->infolist([
                        TextEntry::make('name')->label('Name'),
                        TextEntry::make('email')->copyable(),
                        TextEntry::make('teamMember.name')->label('Team Member')->placeholder('Not linked'),
                        TextEntry::make('role.name')->label('Role')->placeholder('Not assigned'),
                        TextEntry::make('primaryDepartment.name')->label('Primary Department')->placeholder('Not assigned'),
                        TextEntry::make('inviter.name')->label('Invited By'),
                        TextEntry::make('status')->state(fn (UserInvitation $record): string => $record->effectiveStatus()->value)->badge(),
                        TextEntry::make('created_at')->label('Sent At')->dateTime(),
                        TextEntry::make('expires_at')->dateTime(),
                        TextEntry::make('accepted_at')->dateTime()->placeholder('—'),
                    ]),
                Action::make('resend')
                    ->label('Resend Invitation')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('primary')
                    ->visible(fn (UserInvitation $record): bool => auth()->user()->can('user.invitation.resend')
                        && in_array($record->effectiveStatus(), [UserInvitationStatus::Pending, UserInvitationStatus::Expired], true))
                    ->authorize(fn (UserInvitation $record): bool => auth()->user()->can('user.invitation.resend')
                        && in_array($record->effectiveStatus(), [UserInvitationStatus::Pending, UserInvitationStatus::Expired], true))
                    ->requiresConfirmation()
                    ->action(function (UserInvitation $record, UserInvitationService $service): void {
                        abort_unless(auth()->user()->can('user.invitation.resend'), 403);
                        $sent = $service->resend($record, auth()->user());
                        FilamentNotification::make()->title($sent ? 'Invitation resent' : 'Email could not be sent')
                            ->body($sent ? 'A fresh invitation link has been emailed.' : 'The invitation remains pending. Check mail configuration and try again.')
                            ->{$sent ? 'success' : 'warning'}()->send();
                    }),
                Action::make('revoke')
                    ->label('Revoke Invitation')
                    ->icon('heroicon-o-no-symbol')
                    ->color('danger')
                    ->visible(fn (UserInvitation $record): bool => auth()->user()->can('user.invitation.revoke')
                        && $record->status === UserInvitationStatus::Pending
                        && (! $record->role?->name || $record->role->name !== 'Super Admin' || auth()->user()->can('role.manage')))
                    ->authorize(fn (UserInvitation $record): bool => auth()->user()->can('user.invitation.revoke')
                        && $record->status === UserInvitationStatus::Pending
                        && (! $record->role?->name || $record->role->name !== 'Super Admin' || auth()->user()->can('role.manage')))
                    ->requiresConfirmation()
                    ->action(function (UserInvitation $record, UserInvitationService $service): void {
                        abort_unless(auth()->user()->can('user.invitation.revoke'), 403);
                        abort_if($record->role?->name === 'Super Admin' && ! auth()->user()->can('role.manage'), 403);
                        $service->revoke($record, auth()->user());
                    }),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading('No invitations yet')
            ->emptyStateDescription('Invite internal users to activate their own UGHI accounts.');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['teamMember', 'role', 'primaryDepartment', 'inviter']);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListUserInvitations::route('/')];
    }

    private static function requestedTeamMember(): ?TeamMember
    {
        $teamMemberId = request()->integer('team_member_id');

        if (! $teamMemberId) {
            return null;
        }

        return TeamMember::query()
            ->where('active', true)
            ->whereDoesntHave('user')
            ->whereDoesntHave('legacyUser')
            ->find($teamMemberId);
    }

    private static function requestedTeamMemberNameParts(): array
    {
        return preg_split('/\s+/', trim((string) static::requestedTeamMember()?->name), 2) ?: [];
    }
}
