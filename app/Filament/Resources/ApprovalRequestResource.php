<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ApprovalRequestResource\Pages;
use App\Models\ApprovalRequest;
use App\Models\Department;
use App\Services\ApprovalWorkflowService;
use App\Services\DepartmentAccessService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Resources\Resource;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ApprovalRequestResource extends Resource
{
    protected static ?string $model = ApprovalRequest::class;
    protected static string|\UnitEnum|null $navigationGroup = 'Organization';
    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-inbox-stack';
    protected static ?string $navigationLabel = 'My Approvals';

    public static function table(Table $table): Table
    {
        $access = app(DepartmentAccessService::class);
        $workflow = app(ApprovalWorkflowService::class);
        return $table->columns([
            TextColumn::make('workflow.name')->label('Type')->sortable(), TextColumn::make('approvable.title')->label('Title')->placeholder(fn ($record) => class_basename($record->approvable_type).' #'.$record->approvable_id),
            TextColumn::make('department.name')->placeholder('Organization'), TextColumn::make('requestedBy.name')->label('Submitted by'),
            TextColumn::make('submitted_at')->dateTime()->sortable(), TextColumn::make('current_step')->label('Step'), TextColumn::make('status')->badge(),
        ])->filters([SelectFilter::make('status')->options(['UNDER_REVIEW' => 'Pending', 'APPROVED' => 'Approved', 'RETURNED' => 'Returned', 'REJECTED' => 'Rejected', 'CANCELLED' => 'Cancelled'])])
            ->recordActions([
                Action::make('approve')->label('Approve')->color('success')->requiresConfirmation()->visible(fn (ApprovalRequest $record) => in_array($record->status, ['SUBMITTED', 'UNDER_REVIEW'], true) && $access->canApprove(auth()->user(), $record))->action(fn (ApprovalRequest $record) => $workflow->act($record, auth()->user(), 'APPROVED')),
                Action::make('return')->label('Return')->color('warning')->schema([Textarea::make('comment')->required()->maxLength(10000)])->visible(fn (ApprovalRequest $record) => in_array($record->status, ['SUBMITTED', 'UNDER_REVIEW'], true) && $record->workflow->steps->firstWhere('step_order', $record->current_step)?->can_return && $access->canApprove(auth()->user(), $record))->action(fn (ApprovalRequest $record, array $data) => $workflow->act($record, auth()->user(), 'RETURNED', $data['comment'])),
                Action::make('reject')->label('Reject')->color('danger')->schema([Textarea::make('comment')->required()->maxLength(10000)])->visible(fn (ApprovalRequest $record) => in_array($record->status, ['SUBMITTED', 'UNDER_REVIEW'], true) && $record->workflow->steps->firstWhere('step_order', $record->current_step)?->can_reject && $access->canApprove(auth()->user(), $record))->action(fn (ApprovalRequest $record, array $data) => $workflow->act($record, auth()->user(), 'REJECTED', $data['comment'])),
            ])->defaultSort('submitted_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        $user = auth()->user();
        $query = parent::getEloquentQuery()->with(['workflow.steps', 'department', 'requestedBy', 'actions']);
        if ($user && ! $user->can('organization.full_access')) {
            $departments = $user->departments()->wherePivot('active', true)->select('departments.id');
            $leadershipDepartments = Department::query()->whereHas('activeLeadership', fn ($leadership) => $leadership->where('can_approve', true)->whereHas('teamMember', fn ($member) => $member->where('user_id', $user->id)))->select('id');
            $query->where(fn ($q) => $q->whereIn('department_id', $departments)->orWhereIn('department_id', $leadershipDepartments)->orWhere('requested_by_user_id', $user->id));
        }
        return $query;
    }

    public static function getPages(): array { return ['index' => Pages\ListApprovalRequests::route('/')]; }
}
