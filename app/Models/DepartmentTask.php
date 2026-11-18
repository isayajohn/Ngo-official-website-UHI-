<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class DepartmentTask extends Model
{
    use SoftDeletes;

    protected $fillable = ['department_id', 'title', 'description', 'assigned_by_user_id', 'assigned_to_team_member_id', 'assigned_to_user_id', 'priority', 'status', 'start_date', 'due_date', 'completed_at', 'completion_note'];

    protected function casts(): array
    {
        return ['start_date' => 'date', 'due_date' => 'date', 'completed_at' => 'datetime'];
    }

    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function assignedBy(): BelongsTo { return $this->belongsTo(User::class, 'assigned_by_user_id'); }
    public function assignedToUser(): BelongsTo { return $this->belongsTo(User::class, 'assigned_to_user_id'); }
    public function assignedToTeamMember(): BelongsTo { return $this->belongsTo(TeamMember::class); }
    public function comments(): HasMany { return $this->hasMany(DepartmentTaskComment::class); }
    public function attachments(): HasMany { return $this->hasMany(DepartmentTaskAttachment::class); }

    protected static function booted(): void
    {
        static::saving(function (DepartmentTask $task): void {
            $member = $task->assigned_to_team_member_id ? TeamMember::find($task->assigned_to_team_member_id) : null;
            if ($member && (int) $member->department_id !== (int) $task->department_id) throw ValidationException::withMessages(['assigned_to_team_member_id' => 'The staff member must belong to the selected department.']);
            if ($task->assigned_to_user_id && ! auth()->user()?->can('organization.full_access') && ! $task->department?->users()->whereKey($task->assigned_to_user_id)->wherePivot('active', true)->exists()) {
                throw ValidationException::withMessages(['assigned_to_user_id' => 'The user must have active access to this department.']);
            }
            if ($task->due_date && $task->start_date && $task->due_date->lt($task->start_date)) throw ValidationException::withMessages(['due_date' => 'The due date must be on or after the start date.']);
        });
        static::creating(function (DepartmentTask $task): void {
            $department = Department::find($task->department_id);
            $member = $task->assigned_to_team_member_id ? TeamMember::find($task->assigned_to_team_member_id) : null;
            if ($member && (int) $member->department_id !== (int) $task->department_id) throw ValidationException::withMessages(['assigned_to_team_member_id' => 'The staff member must belong to the selected department.']);
            if (! $department) throw ValidationException::withMessages(['department_id' => 'Select an active department.']);
        });
        static::created(function (DepartmentTask $task): void {
            \App\Services\DepartmentActivity::record($task->department_id, $task->assigned_by_user_id, 'task.assigned', $task);
            $notification = new \App\Notifications\DepartmentWorkflowNotification('TASK_ASSIGNED', "A task was assigned to you: {$task->title}", '/admin/department-tasks');
            if ($task->assigned_to_user_id) $task->assignedToUser?->notify($notification);
            else $task->assignedToTeamMember?->user?->notify($notification);
        });
        static::updated(function (DepartmentTask $task): void {
            if ($task->wasChanged('status') && $task->status === 'COMPLETED') \App\Services\DepartmentActivity::record($task->department_id, auth()->id(), 'task.completed', $task);
        });
    }

    public function getIsOverdueAttribute(): bool
    {
        return $this->due_date?->lt(today()) && ! in_array($this->status, ['COMPLETED', 'CANCELLED'], true);
    }

    public function getEffectiveStatusAttribute(): string
    {
        return $this->is_overdue ? 'OVERDUE' : $this->status;
    }
}
