<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentTaskResource\Pages;
use App\Models\DepartmentTask;
use App\Models\Department;
use App\Models\TeamMember;
use App\Models\User;
use App\Support\CmsCreateAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
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

class DepartmentTaskResource extends Resource
{
    protected static ?string $model = DepartmentTask::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Organization';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel = 'Department Tasks';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Task')->schema([
                Select::make('department_id')->options(fn () => Department::query()->where('active', true)->orderBy('name')->get()->filter(fn (Department $department) => app(\App\Services\DepartmentAccessService::class)->canAssignTask(auth()->user(), $department))->pluck('name', 'id'))->default(fn () => request()->query('department_id'))->searchable()->required()->live(),
                TextInput::make('title')->required()->maxLength(200), Textarea::make('description')->rows(4)->columnSpanFull(),
                Select::make('assigned_to_team_member_id')->label('Assigned staff member')->options(fn ($get) => TeamMember::query()->where('active', true)->where('department_id', $get('department_id'))->orderBy('name')->pluck('name', 'id'))->searchable()->nullable(),
                Select::make('assigned_to_user_id')->label('Assigned user')->options(fn ($get) => User::query()->when(! auth()->user()->can('organization.full_access'), fn ($query) => $query->whereHas('departments', fn ($departments) => $departments->where('departments.id', $get('department_id'))->where('user_departments.active', true)))->orderBy('name')->pluck('name', 'id'))->searchable()->nullable(),
                Select::make('priority')->options(['LOW' => 'Low', 'MEDIUM' => 'Medium', 'HIGH' => 'High', 'URGENT' => 'Urgent'])->default('MEDIUM')->required(),
                Select::make('status')->options(['PENDING' => 'Pending', 'IN_PROGRESS' => 'In progress', 'COMPLETED' => 'Completed', 'CANCELLED' => 'Cancelled', 'RETURNED' => 'Returned'])->default('PENDING')->required(),
                DatePicker::make('start_date'), DatePicker::make('due_date')->afterOrEqual('start_date'), Textarea::make('completion_note')->rows(3)->columnSpanFull(),
            ])->columns(2),
            Section::make('Attachments')->description('Optional; stored privately. Allowed: PDF, DOC/DOCX, XLS/XLSX, PNG, JPG.')->schema([
                FileUpload::make('attachments_upload')->multiple()->disk('local')->directory('private/department-tasks')->visibility('private')->acceptedFileTypes(['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'application/vnd.ms-excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'image/png', 'image/jpeg'])->maxSize(10240)->dehydrated(false)
                    ->placeholder('Click the button below to upload<br><span class="filepond--label-action">Browse file</span>')->helperText('PDF, DOC, DOCX, XLS, XLSX, PNG or JPG. Maximum file size: 10 MB per file.'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable()->sortable(), TextColumn::make('department.name')->sortable(), TextColumn::make('assignedToUser.name')->label('Assigned user')->placeholder('—'),
            TextColumn::make('assignedToTeamMember.name')->label('Staff member')->placeholder('—'), TextColumn::make('priority')->badge(),
            TextColumn::make('effective_status')->label('Status')->badge()->color(fn ($state) => ['OVERDUE' => 'danger', 'COMPLETED' => 'success', 'IN_PROGRESS' => 'warning'][$state] ?? 'gray'),
            TextColumn::make('due_date')->date()->sortable()->color(fn ($record) => $record->is_overdue ? 'danger' : null), TextColumn::make('assignedBy.name')->label('Assigned by'),
        ])->filters([SelectFilter::make('department_id')->relationship('department', 'name'), SelectFilter::make('status')->options(['PENDING' => 'Pending', 'IN_PROGRESS' => 'In progress', 'COMPLETED' => 'Completed', 'CANCELLED' => 'Cancelled', 'RETURNED' => 'Returned'])])
            ->recordActions([EditAction::make()->after(function (EditAction $action): void {
                $task = $action->getRecord();
                if ($task instanceof DepartmentTask) app(\App\Services\DepartmentTaskAttachmentService::class)->store($task, (array) ($action->getData()['attachments_upload'] ?? []), auth()->id());
            })])->headerActions([CmsCreateAction::make(static::class, true)]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery()->with(['department', 'assignedBy', 'assignedToUser', 'assignedToTeamMember']);
        if ($user && ! $user->can('organization.full_access')) {
            $departmentIds = $user->departments()->wherePivot('active', true)->select('departments.id');
            $teamMemberIds = TeamMember::where('user_id', $user->id)->select('id');
            $leadershipDepartments = Department::query()->whereHas('activeLeadership.teamMember', fn ($q) => $q->where('user_id', $user->id))->select('id');
            $query->where(fn ($q) => $q->whereIn('department_id', $departmentIds)->orWhereIn('department_id', $leadershipDepartments)->orWhere('assigned_to_user_id', $user->id)->orWhereIn('assigned_to_team_member_id', $teamMemberIds));
        }
        return $query;
    }

    public static function getPages(): array { return ['index' => Pages\ListDepartmentTasks::route('/'), 'create' => Pages\CreateDepartmentTask::route('/create'), 'edit' => Pages\EditDepartmentTask::route('/{record}/edit')]; }
}
