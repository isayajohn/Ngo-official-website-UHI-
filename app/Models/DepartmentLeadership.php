<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Validation\ValidationException;

class DepartmentLeadership extends Model
{
    use SoftDeletes;

    protected $table = 'department_leadership';

    protected $fillable = ['department_id', 'team_member_id', 'leadership_role', 'is_primary', 'start_date', 'end_date', 'appointment_status', 'can_approve', 'can_assign_tasks', 'can_manage_staff', 'can_manage_projects', 'can_view_reports', 'active'];

    protected function casts(): array
    {
        return ['is_primary' => 'boolean', 'start_date' => 'date', 'end_date' => 'date', 'can_approve' => 'boolean', 'can_assign_tasks' => 'boolean', 'can_manage_staff' => 'boolean', 'can_manage_projects' => 'boolean', 'can_view_reports' => 'boolean', 'active' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::saving(function (DepartmentLeadership $appointment): void {
            if (auth()->check() && ! auth()->user()->can('organization.full_access')) {
                abort_unless(auth()->user()->can('department.manage_leadership') && app(\App\Services\DepartmentAccessService::class)->canAccessDepartment(auth()->user(), Department::findOrFail($appointment->department_id)), 403);
            }
            $member = TeamMember::find($appointment->team_member_id);
            if ($member && (int) $member->department_id !== (int) $appointment->department_id) {
                throw ValidationException::withMessages(['team_member_id' => 'A leader must be a team member of this department.']);
            }
            if ($appointment->active && $appointment->is_primary && $appointment->leadership_role === 'HEAD' && in_array($appointment->appointment_status, ['ACTIVE', 'ACTING'], true)) {
                $duplicate = static::query()->where('department_id', $appointment->department_id)->where('leadership_role', 'HEAD')->where('is_primary', true)->where('active', true)->whereIn('appointment_status', ['ACTIVE', 'ACTING'])->when($appointment->exists, fn ($q) => $q->whereKeyNot($appointment->getKey()))->exists();
                if ($duplicate) throw ValidationException::withMessages(['is_primary' => 'This department already has an active primary head.']);
            }
        });
        static::created(function (DepartmentLeadership $appointment): void {
            \App\Services\DepartmentActivity::record($appointment->department_id, auth()->id(), 'leadership.appointed', $appointment, ['role' => $appointment->leadership_role]);
        });
        static::deleted(function (DepartmentLeadership $appointment): void {
            \App\Services\DepartmentActivity::record($appointment->department_id, auth()->id(), 'leadership.removed', $appointment, ['role' => $appointment->leadership_role]);
        });
    }

    public function department(): BelongsTo { return $this->belongsTo(Department::class); }
    public function teamMember(): BelongsTo { return $this->belongsTo(TeamMember::class); }

    public function scopeCurrent($query)
    {
        return $query->where('active', true)->whereIn('appointment_status', ['ACTIVE', 'ACTING'])
            ->where(fn ($q) => $q->whereNull('start_date')->orWhereDate('start_date', '<=', today()))
            ->where(fn ($q) => $q->whereNull('end_date')->orWhereDate('end_date', '>=', today()));
    }
}
