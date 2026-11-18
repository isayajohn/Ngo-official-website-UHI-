<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentLeadershipResource\Pages;
use App\Models\DepartmentLeadership;
use App\Models\Department;
use App\Models\TeamMember;
use App\Support\CmsCreateAction;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DepartmentLeadershipResource extends Resource
{
    protected static ?string $model = DepartmentLeadership::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Organization';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-identification';
    protected static ?string $navigationLabel = 'Leadership Appointments';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Appointment')->schema([
                Select::make('department_id')->options(fn () => Department::query()->where('active', true)->orderBy('name')->get()->filter(fn (Department $department) => auth()->user()->can('organization.full_access') || app(\App\Services\DepartmentAccessService::class)->canAccessDepartment(auth()->user(), $department))->pluck('name', 'id'))->searchable()->required()->live(),
                Select::make('team_member_id')->options(fn ($get) => TeamMember::query()->where('active', true)->where('department_id', $get('department_id'))->orderBy('name')->pluck('name', 'id'))->searchable()->required(),
                Select::make('leadership_role')->options(['HEAD' => 'Department Head', 'DEPUTY_HEAD' => 'Deputy Head', 'MANAGER' => 'Manager', 'COORDINATOR' => 'Coordinator', 'ACTING_HEAD' => 'Acting Head'])->required(),
                Select::make('appointment_status')->options(['ACTIVE' => 'Active', 'ACTING' => 'Acting', 'ENDED' => 'Ended', 'SUSPENDED' => 'Suspended'])->default('ACTIVE')->required(),
                Toggle::make('is_primary')->label('Primary department head')->default(false),
                DatePicker::make('start_date'), DatePicker::make('end_date')->afterOrEqual('start_date'),
            ])->columns(2),
            Section::make('Operational permissions')->description('These appointment flags do not replace the user’s system permissions.')->schema([
                Toggle::make('can_approve')->default(false), Toggle::make('can_assign_tasks')->default(false), Toggle::make('can_manage_staff')->default(false),
                Toggle::make('can_manage_projects')->default(false), Toggle::make('can_view_reports')->default(true), Toggle::make('active')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            ImageColumn::make('teamMember.photo')->disk('public')->circular()->size(40)->label('Photo'),
            TextColumn::make('teamMember.name')->label('Name')->searchable()->sortable()->description(fn (DepartmentLeadership $record) => $record->teamMember?->email ?: 'Leadership appointment')->weight('semibold'),
            TextColumn::make('teamMember.position.title')->label('Position')->searchable()->placeholder('Unassigned'),
            TextColumn::make('department.name')->sortable()->searchable(), TextColumn::make('leadership_role')->badge()->formatStateUsing(fn (string $state) => str_replace('_', ' ', $state)), TextColumn::make('appointment_status')->badge()->color(fn (string $state) => match ($state) { 'ACTIVE' => 'success', 'ACTING' => 'warning', 'ENDED', 'SUSPENDED' => 'gray', default => 'info' }),
            TextColumn::make('start_date')->date()->placeholder('—'), TextColumn::make('end_date')->date()->placeholder('—'),
            TextColumn::make('can_approve')->label('Permissions')->formatStateUsing(fn ($record) => collect(['Approves' => $record->can_approve, 'Assigns tasks' => $record->can_assign_tasks, 'Manages staff' => $record->can_manage_staff, 'Manages projects' => $record->can_manage_projects, 'Reports' => $record->can_view_reports])->filter()->keys()->join(', ')),
            IconColumn::make('active')->boolean(),
        ])->filters([SelectFilter::make('department_id')->relationship('department', 'name'), SelectFilter::make('leadership_role')->options(['HEAD' => 'Head', 'DEPUTY_HEAD' => 'Deputy Head', 'MANAGER' => 'Manager', 'COORDINATOR' => 'Coordinator', 'ACTING_HEAD' => 'Acting Head']), TernaryFilter::make('active')])
            ->extraAttributes(['class' => 'ughi-people-table'])
            ->recordActions([ActionGroup::make([EditAction::make()])->icon('heroicon-m-ellipsis-vertical')->tooltip('Appointment actions')])
            ->headerActions([CmsCreateAction::make(static::class, true)]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery()->with(['department', 'teamMember.position']);
        if ($user && ! $user->can('organization.full_access')) $query->whereHas('department', fn ($q) => $q->whereIn('departments.id', $user->departments()->wherePivot('active', true)->select('departments.id'))->orWhereHas('activeLeadership.teamMember', fn ($member) => $member->where('user_id', $user->id)));
        return $query;
    }

    public static function getPages(): array { return ['index' => Pages\ListDepartmentLeadership::route('/'), 'create' => Pages\CreateDepartmentLeadership::route('/create'), 'edit' => Pages\EditDepartmentLeadership::route('/{record}/edit')]; }
}
