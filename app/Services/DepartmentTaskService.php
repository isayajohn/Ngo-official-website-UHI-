<?php

namespace App\Services;

use App\Models\Department;
use App\Models\DepartmentTask;
use App\Models\DepartmentTaskComment;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DepartmentTaskService
{
    public function __construct(private DepartmentAccessService $access) {}

    public function assign(User $actor, Department $department, array $data): DepartmentTask
    {
        abort_unless($this->access->canAssignTask($actor, $department), 403);
        if (! $this->access->hasOrganizationAccess($actor) && ! empty($data['assigned_to_team_member_id'])) {
            $belongsToDepartment = \App\Models\TeamMember::query()->whereKey($data['assigned_to_team_member_id'])->where('department_id', $department->id)->exists();
            if (! $belongsToDepartment) throw ValidationException::withMessages(['assigned_to_team_member_id' => 'Tasks can only be assigned to staff in the selected department.']);
        }
        if (! $this->access->hasOrganizationAccess($actor) && ! empty($data['assigned_to_user_id']) && ! $department->users()->whereKey($data['assigned_to_user_id'])->wherePivot('active', true)->exists()) {
            throw ValidationException::withMessages(['assigned_to_user_id' => 'The assignee must have active access to this department.']);
        }
        return DB::transaction(function () use ($actor, $department, $data): DepartmentTask {
            $task = $department->tasks()->create(array_merge($data, ['assigned_by_user_id' => $actor->id]));
            return $task;
        });
    }

    public function complete(User $actor, DepartmentTask $task, ?string $note = null): DepartmentTask
    {
        abort_unless($this->access->canAccessDepartment($actor, $task->department) || $task->assigned_to_user_id === $actor->id || $task->assignedToTeamMember?->user_id === $actor->id, 403);
        return DB::transaction(function () use ($actor, $task, $note): DepartmentTask {
            $task->update(['status' => 'COMPLETED', 'completed_at' => now(), 'completion_note' => $note]);
            return $task->fresh();
        });
    }

    public function comment(User $actor, DepartmentTask $task, string $comment): DepartmentTaskComment
    {
        abort_unless($this->access->canAccessDepartment($actor, $task->department) || $task->assigned_to_user_id === $actor->id || $task->assignedToTeamMember?->user_id === $actor->id, 403);
        return $task->comments()->create(['user_id' => $actor->id, 'comment' => $comment]);
    }
}
