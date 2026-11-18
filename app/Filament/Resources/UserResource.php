<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\Department;
use App\Models\TeamMember;
use App\Models\User;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Infolists\Components\TextEntry;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Password;

class UserResource extends Resource
{
    protected static ?string $model = User::class;
    protected static string|\UnitEnum|null $navigationGroup = 'System';
    protected static ?string $navigationParentItem = 'Settings';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-users';
    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        $canAssignRoles = fn (): bool => auth()->user()->can('role.manage') || auth()->user()->can('user.assign_role');
        $canAssignDepartments = fn (): bool => auth()->user()->can('user.assign_department') || auth()->user()->can('organization.full_access');

        return $schema->components([
            Section::make('Personal information')->description('Account identity and contact information.')->schema([
                TextInput::make('first_name')->required()->maxLength(100),
                TextInput::make('middle_name')->maxLength(100),
                TextInput::make('last_name')->required()->maxLength(100),
                TextInput::make('username')->maxLength(100)->alphaDash()->unique(ignoreRecord: true),
                TextInput::make('email')->email()->required()->unique(ignoreRecord: true)->maxLength(255),
                TextInput::make('phone')->tel()->maxLength(50),
                FileUpload::make('profile_photo')->label('Profile photo')->image()->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])->disk('public')->directory('user-profiles')->visibility('public')->maxSize(2048)
                    ->placeholder('Click the button below to upload<br><span class="filepond--label-action">Browse file</span>')->helperText('JPEG, PNG or WebP. Maximum file size: 2 MB.'),
            ])->columns(2)->columnSpanFull(),
            Section::make('Team member')->description('User access is optional and remains separate from the staff profile.')->schema([
                Select::make('team_member_id')->label('Linked team member')->options(fn (?User $record) => TeamMember::query()->where('active', true)
                    ->where(fn (Builder $query) => $query->whereNull('user_id')->orWhere('user_id', $record?->id ?? -1))
                    ->where(fn (Builder $query) => $query->whereDoesntHave('user')->when($record?->team_member_id, fn ($q, $id) => $q->orWhereKey($id)))
                    ->orderBy('name')->get()->mapWithKeys(fn (TeamMember $member) => [$member->id => $member->name.' · '.($member->department?->name ?? 'No department')]))
                    ->searchable()->preload()->nullable(),
                TextInput::make('team_member_id_display')->label('Linked profile details')->dehydrated(false)->disabled()->visible(fn (Get $get) => filled($get('team_member_id')))
                    ->formatStateUsing(fn (Get $get) => TeamMember::with(['position', 'department'])->find($get('team_member_id'))?->position?->title),
            ])->columns(2)->columnSpanFull(),
            Section::make('Organization access')->description('Primary and additional department access are stored in the existing user_departments pivot.')->schema([
                Select::make('primary_department_id')->label('Primary department')->options(fn () => Department::query()->where('active', true)->orderBy('name')->pluck('name', 'id'))
                    ->searchable()->preload()->nullable()->visible($canAssignDepartments)->dehydrated($canAssignDepartments),
                Select::make('additional_department_ids')->label('Additional departments')->multiple()->options(fn (Get $get) => Department::query()->where('active', true)
                    ->when($get('primary_department_id'), fn ($query, $id) => $query->whereKeyNot($id))->orderBy('name')->pluck('name', 'id'))
                    ->searchable()->preload()->visible($canAssignDepartments)->dehydrated($canAssignDepartments),
            ])->columns(2)->columnSpanFull(),
            Section::make('Role and account')->schema([
                Select::make('roles')->relationship('roles', 'name')->multiple()->preload()->visible($canAssignRoles)->dehydrated($canAssignRoles),
                Select::make('status')->options(collect(User::STATUSES)->mapWithKeys(fn ($status) => [$status => str($status)->lower()->replace('_', ' ')->title()->toString()]))
                    ->default(fn () => auth()->user()->can('user.activate') || auth()->user()->can('user.manage') ? 'ACTIVE' : 'PENDING')->required()
                    ->disabled(fn (?User $record) => $record?->is(auth()->user()) || (! auth()->user()->can('user.activate') && ! auth()->user()->can('user.disable') && ! auth()->user()->can('user.manage')))
                    ->dehydrated(fn (?User $record) => ! $record?->is(auth()->user()) && (auth()->user()->can('user.activate') || auth()->user()->can('user.disable') || auth()->user()->can('user.manage'))),
            ])->columns(2)->columnSpanFull(),
            Section::make('Preferences')->schema([
                Select::make('locale')->options(['en' => 'English', 'sw' => 'Kiswahili'])->default('en')->required(),
                Select::make('timezone')->options(['Africa/Dar_es_Salaam' => 'Africa / Dar es Salaam', 'UTC' => 'UTC', 'Africa/Nairobi' => 'Africa / Nairobi', 'Africa/Kampala' => 'Africa / Kampala'])->default('Africa/Dar_es_Salaam')->required(),
            ])->columns(2)->columnSpanFull(),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components(static::detailInfolistComponents());
    }

    public static function detailInfolistComponents(): array
    {
        return [
            Tabs::make('User account details')->tabs([
                Tab::make('Overview')->icon('heroicon-o-identification')->schema([
                    Section::make('Account identity')->schema([
                        TextEntry::make('name')->label('Full name'), TextEntry::make('username')->placeholder('Not set'),
                        TextEntry::make('email'), TextEntry::make('phone')->placeholder('Not set'),
                        TextEntry::make('teamMember.name')->label('Linked staff profile')->placeholder('No staff profile linked'),
                        TextEntry::make('status')->badge()->color(fn (string $state) => match ($state) { 'ACTIVE' => 'success', 'PENDING' => 'warning', 'SUSPENDED', 'LOCKED' => 'danger', default => 'gray' }),
                        TextEntry::make('created_at')->dateTime(), TextEntry::make('last_login_at')->dateTime()->placeholder('Never logged in'),
                    ])->columns(2),
                ]),
                Tab::make('Access')->icon('heroicon-o-shield-check')->schema([
                    Section::make('Roles and permissions')->schema([
                        TextEntry::make('roles.name')->label('Assigned roles')->badge()->placeholder('No roles assigned'),
                        TextEntry::make('permissions.name')->label('Direct permissions')->badge()->placeholder('None'),
                        TextEntry::make('organization_access')->label('Organization-wide access')->state(fn (User $record) => $record->can('organization.full_access') ? 'Enabled' : 'Department scoped'),
                    ]),
                ]),
                Tab::make('Departments')->icon('heroicon-o-building-office-2')->schema([
                    Section::make('Department access')->schema([
                        TextEntry::make('department_access')->label('Assigned departments')->state(fn (User $record) => $record->departments()->wherePivot('active', true)->get()->map(fn ($department) => ($department->pivot->is_primary ? '**Primary:** ' : '').$department->name.($department->pivot->department_role ? ' · '.$department->pivot->department_role : ''))->join("\n"))->markdown()->placeholder('No department access assigned'),
                        TextEntry::make('leadership_access')->label('Leadership appointments')->state(fn (User $record) => $record->teamMember?->leadershipAppointments()->where('active', true)->whereIn('appointment_status', ['ACTIVE', 'ACTING'])->with('department')->get()->map(fn ($appointment) => $appointment->department->name.' · '.str($appointment->leadership_role)->lower()->replace('_', ' ')->title())->join("\n") ?: 'No active leadership appointments'),
                    ])->columns(2),
                ]),
                Tab::make('Activity')->icon('heroicon-o-clock')->visible(fn () => auth()->user()->can('user.view_activity') || auth()->user()->can('user.manage'))->schema([
                    Section::make('Recent account activity')->schema([
                        TextEntry::make('activity_history')->hiddenLabel()->state(fn (User $record) => $record->auditActivities()->latest()->limit(30)->get()->map(fn ($activity) => $activity->created_at->format('Y-m-d H:i').' · '.$activity->event)->join("\n") ?: 'No account activity recorded.'),
                    ]),
                ]),
                Tab::make('Security')->icon('heroicon-o-lock-closed')->visible(fn () => auth()->user()->can('user.manage_security') || auth()->user()->can('user.manage') || auth()->user()->can('user.sessions.view'))->schema([
                    Section::make('Security status')->schema([
                        TextEntry::make('email_verified_at')->label('Email verified')->dateTime()->placeholder('Not verified'),
                        TextEntry::make('phone_verified_at')->label('Phone verified')->dateTime()->placeholder('Not verified'),
                        TextEntry::make('last_login_ip')->label('Last login IP')->placeholder('Not recorded'),
                        TextEntry::make('failed_login_attempts')->label('Recent failed attempts'),
                        TextEntry::make('locked_until')->label('Locked until')->dateTime()->placeholder('Not locked'),
                        TextEntry::make('password_changed_at')->label('Password last changed')->dateTime(),
                        TextEntry::make('must_change_password')->label('Password change required')->formatStateUsing(fn (bool $state) => $state ? 'Yes' : 'No'),
                        TextEntry::make('active_session_count')->label('Active sessions')->visible(fn () => auth()->user()->can('user.sessions.view') || auth()->user()->can('user.manage_security') || auth()->user()->can('user.manage'))
                            ->state(fn (User $record) => app(\App\Services\UserSessionManager::class)->available()
                                ? app(\App\Services\UserSessionManager::class)->countFor($record)
                                : 'Database session driver required'),
                    ])->columns(2),
                ]),
            ])->vertical()->persistTabInQueryString()->columnSpanFull(),
        ];
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('profile_photo')->disk('public')->circular()->size(40)->label('Photo'),
            TextColumn::make('name')->label('Full name')->searchable()->sortable()->description(fn (User $record) => $record->email)->weight('semibold'),
            TextColumn::make('username')->searchable()->toggleable(isToggledHiddenByDefault: true),
            TextColumn::make('phone')->searchable()->toggleable(),
            TextColumn::make('teamMember.name')->label('Team member')->searchable()->placeholder('No linked profile'),
            TextColumn::make('primaryDepartment.name')->label('Primary department')->placeholder('Not assigned'),
            TextColumn::make('teamMember.position.title')->label('Position')->placeholder('—'),
            TextColumn::make('roles.name')->label('Roles')->badge(),
            TextColumn::make('status')->badge()->formatStateUsing(fn (string $state) => str($state)->lower()->replace('_', ' ')->title()->toString())
                ->color(fn (string $state) => match ($state) { 'ACTIVE' => 'success', 'PENDING' => 'warning', 'SUSPENDED', 'LOCKED' => 'danger', default => 'gray' }),
            TextColumn::make('last_login_at')->label('Last login')->dateTime()->sortable()->placeholder('Never logged in'),
            TextColumn::make('created_at')->label('Created')->dateTime()->sortable(),
        ])->extraAttributes(['class' => 'ughi-people-table'])->defaultSort('name')->filters([
            SelectFilter::make('departments')->label('Department')->relationship('departments', 'name', fn ($query) => $query->where('departments.active', true)),
            SelectFilter::make('roles')->relationship('roles', 'name')->label('Role'),
            SelectFilter::make('status')->options(collect(User::STATUSES)->mapWithKeys(fn ($status) => [$status => str($status)->lower()->replace('_', ' ')->title()->toString()])),
            TernaryFilter::make('team_member_id')->label('Linked team member')->queries(
                true: fn (Builder $query) => $query->whereNotNull('team_member_id'),
                false: fn (Builder $query) => $query->whereNull('team_member_id'),
            ),
            TernaryFilter::make('last_login_at')->label('Never logged in')->queries(
                true: fn (Builder $query) => $query->whereNull('last_login_at'),
                false: fn (Builder $query) => $query->whereNotNull('last_login_at'),
            ),
            TernaryFilter::make('leadership')->label('Has active leadership access')->queries(
                true: fn (Builder $query) => $query->whereHas('teamMember.leadershipAppointments', fn ($appointments) => $appointments->where('active', true)->whereIn('appointment_status', ['ACTIVE', 'ACTING'])),
                false: fn (Builder $query) => $query->whereDoesntHave('teamMember.leadershipAppointments', fn ($appointments) => $appointments->where('active', true)->whereIn('appointment_status', ['ACTIVE', 'ACTING'])),
            ),
        ])->recordActions([
            ActionGroup::make([
                ViewAction::make()->modal()->modalWidth('5xl'),
                (function () {
                    $editScope = [];

                    return EditAction::make()
                        ->modal()
                        ->modalWidth('7xl')
                        ->stickyModalHeader()
                        ->stickyModalFooter()
                        ->steps(fn (Schema $schema): array => array_map(
                            fn (Section $section): Step => Step::make($section->getHeading())
                                ->description($section->getDescription())
                                ->schema($section->getDefaultChildComponents())
                                ->columns($section->getColumns()),
                            static::form($schema)->getComponents(),
                        ))
                        ->mutateRecordDataUsing(function (array $data, User $record): array {
                            $activeDepartmentIds = $record->departments()
                                ->wherePivot('active', true)
                                ->pluck('departments.id');
                            $primaryDepartmentId = $record->departments()
                                ->wherePivot('active', true)
                                ->wherePivot('is_primary', true)
                                ->value('departments.id');

                            return [
                                ...$data,
                                'primary_department_id' => $primaryDepartmentId,
                                'additional_department_ids' => $activeDepartmentIds
                                    ->reject(fn ($id) => (int) $id === (int) $primaryDepartmentId)
                                    ->values()
                                    ->all(),
                            ];
                        })
                        ->mutateDataUsing(function (array $data, User $record) use (&$editScope): array {
                            $canManageDepartments = auth()->user()->can('user.assign_department') || auth()->user()->can('organization.full_access');
                            $canManageRoles = auth()->user()->can('role.manage') || auth()->user()->can('user.assign_role');
                            $canManageStatus = auth()->user()->can('user.activate') || auth()->user()->can('user.disable') || auth()->user()->can('user.manage');

                            $primaryDepartmentId = filled($data['primary_department_id'] ?? null) ? (int) $data['primary_department_id'] : null;
                            $departmentIds = collect([$primaryDepartmentId, ...((array) ($data['additional_department_ids'] ?? []))])
                                ->filter()
                                ->map(fn ($id) => (int) $id)
                                ->unique()
                                ->values();

                            $editScope = [
                                'status' => $record->status,
                                'team_member_id' => $record->team_member_id,
                                'department_ids' => $record->departments()->wherePivot('active', true)->pluck('departments.id')->map(fn ($id) => (int) $id)->all(),
                                'primary_department_id' => $record->departments()->wherePivot('active', true)->wherePivot('is_primary', true)->value('departments.id'),
                                'roles' => $record->roles()->pluck('name')->all(),
                                'changed_fields' => array_values(array_diff(array_keys($data), ['password', 'password_confirmation', 'primary_department_id', 'additional_department_ids', 'team_member_id_display'])),
                                'department_ids_to_sync' => $departmentIds->all(),
                                'primary_department_id_to_sync' => $primaryDepartmentId,
                                'sync_departments' => $canManageDepartments && ! $record->is(auth()->user()),
                            ];

                            unset($data['primary_department_id'], $data['additional_department_ids'], $data['team_member_id_display']);

                            if (! $canManageDepartments || $record->is(auth()->user())) {
                                unset($data['primary_department_id'], $data['additional_department_ids']);
                                $editScope['department_ids_to_sync'] = $editScope['department_ids'];
                                $editScope['primary_department_id_to_sync'] = $editScope['primary_department_id'];
                                $editScope['sync_departments'] = false;
                            }

                            if (! $canManageRoles || $record->is(auth()->user())) {
                                unset($data['roles']);
                            }

                            if (! $canManageStatus || $record->is(auth()->user())) {
                                unset($data['status'], $data['is_active']);
                            }

                            if ($record->is(auth()->user())) {
                                unset($data['team_member_id']);
                            }

                            $newTeamMemberId = filled($data['team_member_id'] ?? null) ? (int) $data['team_member_id'] : null;
                            if ($newTeamMemberId) {
                                $member = TeamMember::query()->whereKey($newTeamMemberId)->first();
                                abort_if(! $member || ($member->user_id && $member->user_id !== $record->id), 422, 'This team member is already linked to another account.');
                            }

                            return $data;
                        })
                        ->after(function (EditAction $action) use (&$editScope): void {
                            /** @var User $user */
                            $user = $action->getRecord();
                            $user->unsetRelation('teamMember');

                            \Illuminate\Support\Facades\DB::transaction(function () use ($user, &$editScope): void {
                                if ($editScope['sync_departments'] ?? false) {
                                    $ids = collect($editScope['department_ids_to_sync'] ?? []);
                                    $existing = $user->departments()->get()->keyBy('id');
                                    $user->departments()->sync($ids->mapWithKeys(fn ($id) => [$id => [
                                        'is_primary' => (int) $id === (int) ($editScope['primary_department_id_to_sync'] ?? null),
                                        'active' => true,
                                        'department_role' => $existing->get($id)?->pivot?->department_role,
                                    ]])->all());
                                }

                                if (($editScope['team_member_id'] ?? null) && (int) $editScope['team_member_id'] !== (int) $user->team_member_id) {
                                    TeamMember::whereKey($editScope['team_member_id'])->where('user_id', $user->id)->update(['user_id' => null]);
                                }

                                if ($member = $user->teamMember) {
                                    abort_if($member->user_id && $member->user_id !== $user->id, 422, 'This team member is already linked to another account.');
                                    TeamMember::whereKey($member->id)->update(['user_id' => $user->id]);
                                }
                            });

                            if (($editScope['status'] ?? null) !== $user->status) {
                                \App\Services\DepartmentActivity::record(null, auth()->id(), 'user.status_changed', $user, ['status' => $user->status]);
                                if ($user->status !== 'ACTIVE') {
                                    $count = app(\App\Services\UserSessionManager::class)->revokeAll($user);
                                    \App\Services\DepartmentActivity::record(null, auth()->id(), 'user.sessions_revoked_after_status_change', $user, ['count' => $count]);
                                }
                            }
                            $departmentIds = $user->departments()->wherePivot('active', true)->pluck('departments.id')->map(fn ($id) => (int) $id)->all();
                            if (($editScope['department_ids'] ?? []) !== $departmentIds) {
                                \App\Services\DepartmentActivity::record(null, auth()->id(), 'user.departments_changed', $user, ['department_ids' => $departmentIds]);
                            }
                            $roles = $user->roles()->pluck('name')->all();
                            if (($editScope['roles'] ?? []) !== $roles) {
                                \App\Services\DepartmentActivity::record(null, auth()->id(), 'user.roles_changed', $user, ['roles' => $roles]);
                            }
                            if ($editScope['changed_fields'] ?? []) {
                                \App\Services\DepartmentActivity::record(null, auth()->id(), 'user.updated', $user, ['fields' => $editScope['changed_fields']]);
                            }
                        });
                })(),
                Action::make('sendPasswordReset')->label('Send Password Reset')->icon('heroicon-o-key')->color('warning')
                    ->visible(fn (User $record) => auth()->user()->can('user.password_reset.send')
                        && (! $record->hasRole('Super Admin') || auth()->user()->can('role.manage')))
                    ->authorize(fn (User $record) => auth()->user()->can('user.password_reset.send')
                        && (! $record->hasRole('Super Admin') || auth()->user()->can('role.manage')))
                    ->requiresConfirmation()
                    ->action(function (User $record): void {
                        abort_unless(auth()->user()->can('user.password_reset.send'), 403);
                        abort_if($record->hasRole('Super Admin') && ! auth()->user()->can('role.manage'), 403);

                        try {
                            $sent = Password::sendResetLink(['email' => $record->email]) === Password::RESET_LINK_SENT;
                        } catch (\Throwable $exception) {
                            $sent = false;
                            \Illuminate\Support\Facades\Log::warning('Unable to send an administrator-initiated password reset email.', [
                                'user_id' => $record->getKey(),
                                'exception' => $exception::class,
                            ]);
                        }

                        \App\Services\DepartmentActivity::record(null, auth()->id(), 'user.password_reset_requested_by_admin', $record);

                        $notification = \Filament\Notifications\Notification::make()
                            ->title($sent ? 'Password reset link requested' : 'Password reset email could not be sent')
                            ->body($sent ? 'If the account can receive mail, a secure reset link has been sent.' : 'Check mail configuration or wait before requesting another reset link.');

                        if ($sent) {
                            $notification->success();
                        } else {
                            $notification->warning();
                        }

                        $notification->send();
                    }),
                Action::make('endSessions')->label('End all sessions')->icon('heroicon-o-computer-desktop')->color('danger')
                    ->visible(fn (User $record) => $record->id !== auth()->id()
                        && (auth()->user()->can('user.sessions.revoke') || auth()->user()->can('user.manage_security') || auth()->user()->can('user.manage'))
                        && (! $record->hasRole('Super Admin') || auth()->user()->can('role.manage')))
                    ->authorize(fn (User $record) => $record->id !== auth()->id()
                        && (auth()->user()->can('user.sessions.revoke') || auth()->user()->can('user.manage_security') || auth()->user()->can('user.manage'))
                        && (! $record->hasRole('Super Admin') || auth()->user()->can('role.manage')))
                    ->requiresConfirmation()
                    ->modalDescription('All sessions for this account and remembered sign-ins on all devices will be ended.')
                    ->action(function (User $record): void {
                        abort_unless($record->id !== auth()->id() && (auth()->user()->can('user.sessions.revoke') || auth()->user()->can('user.manage_security') || auth()->user()->can('user.manage')), 403);
                        abort_if($record->hasRole('Super Admin') && ! auth()->user()->can('role.manage'), 403);

                        $manager = app(\App\Services\UserSessionManager::class);
                        if (! $manager->available()) {
                            \Filament\Notifications\Notification::make()->title('Session management unavailable')
                                ->body('Configure the database session driver before ending user sessions.')
                                ->warning()->send();

                            return;
                        }

                        $count = $manager->revokeAll($record);
                        \App\Services\DepartmentActivity::record(null, auth()->id(), 'user.sessions_revoked_by_admin', $record, ['count' => $count]);
                        \Filament\Notifications\Notification::make()->title('User sessions ended')->body("Ended {$count} stored session(s).")
                            ->success()->send();
                    }),
                Action::make('unlock')->label('Unlock account')->icon('heroicon-o-lock-open')->color('success')
                    ->visible(fn (User $record) => $record->status === 'LOCKED' && auth()->user()->can('user.manage_security')
                        && (! $record->hasRole('Super Admin') || auth()->user()->can('role.manage')))
                    ->authorize(fn (User $record) => $record->status === 'LOCKED' && auth()->user()->can('user.manage_security')
                        && (! $record->hasRole('Super Admin') || auth()->user()->can('role.manage')))
                    ->requiresConfirmation()->action(function (User $record): void {
                        abort_unless(auth()->user()->can('user.manage_security') && $record->id !== auth()->id(), 403);
                        abort_if($record->hasRole('Super Admin') && ! auth()->user()->can('role.manage'), 403);
                        $record->update(['status' => 'ACTIVE', 'locked_until' => null, 'failed_login_attempts' => 0]);
                        \App\Services\DepartmentActivity::record(null, auth()->id(), 'user.unlocked', $record);
                    }),
                Action::make('toggleStatus')->label(fn (User $record) => $record->status === 'ACTIVE' ? 'Deactivate account' : 'Activate account')
                    ->icon(fn (User $record) => $record->status === 'ACTIVE' ? 'heroicon-o-no-symbol' : 'heroicon-o-check-circle')
                    ->color(fn (User $record) => $record->status === 'ACTIVE' ? 'danger' : 'success')
                    ->visible(fn (User $record) => $record->id !== auth()->id()
                        && ($record->status === 'ACTIVE' ? auth()->user()->can('user.disable') : auth()->user()->can('user.activate'))
                        && (! $record->hasRole('Super Admin') || auth()->user()->can('role.manage')))
                    ->authorize(fn (User $record) => $record->id !== auth()->id()
                        && ($record->status === 'ACTIVE' ? auth()->user()->can('user.disable') : auth()->user()->can('user.activate'))
                        && (! $record->hasRole('Super Admin') || auth()->user()->can('role.manage')))
                    ->requiresConfirmation()->action(function (User $record): void {
                        abort_unless($record->id !== auth()->id() && ($record->status === 'ACTIVE' ? auth()->user()->can('user.disable') : auth()->user()->can('user.activate')), 403);
                        abort_if($record->hasRole('Super Admin') && ! auth()->user()->can('role.manage'), 403);
                        $status = $record->status === 'ACTIVE' ? 'INACTIVE' : 'ACTIVE';
                        $record->update(['status' => $status]);
                        if ($status !== 'ACTIVE') {
                            $count = app(\App\Services\UserSessionManager::class)->revokeAll($record);
                            \App\Services\DepartmentActivity::record(null, auth()->id(), 'user.sessions_revoked_after_deactivation', $record, ['count' => $count]);
                        }
                        \App\Services\DepartmentActivity::record(null, auth()->id(), 'user.status_changed', $record, ['status' => $status]);
                    }),
            ])->icon('heroicon-m-ellipsis-vertical')->tooltip('User actions'),
        ]);
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['teamMember.position', 'teamMember.department', 'departments', 'primaryDepartment']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }
}
