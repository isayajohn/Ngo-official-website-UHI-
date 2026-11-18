<?php

namespace App\Services;

use App\Models\Department;
use App\Models\DepartmentLeadership;
use App\Models\TeamMember;
use App\Notifications\DepartmentWorkflowNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DepartmentLeadershipService
{
    public function appoint(Department $department, TeamMember $member, array $attributes): DepartmentLeadership
    {
        if ($member->department_id !== $department->id) {
            throw ValidationException::withMessages(['team_member_id' => 'A leader must be a team member of this department.']);
        }
        $role = $attributes['leadership_role'] ?? null;
        $primary = (bool) ($attributes['is_primary'] ?? false);

        return DB::transaction(function () use ($department, $member, $attributes, $role, $primary): DepartmentLeadership {
            if ($primary && $role === 'HEAD') {
                $existing = $department->leadership()->current()->where('leadership_role', 'HEAD')->where('is_primary', true)->exists();
                if ($existing) throw ValidationException::withMessages(['is_primary' => 'This department already has an active primary head. End that appointment before assigning another.']);
            }
            $appointment = $department->leadership()->create($attributes + ['team_member_id' => $member->id]);
            DepartmentActivity::record($department->id, auth()->id(), 'leadership.appointed', $appointment, ['role' => $role]);
            if ($member->user) $member->user->notify(new DepartmentWorkflowNotification('LEADERSHIP_APPOINTMENT', "You have been appointed {$role} for {$department->name}.", '/admin/departments'));
            return $appointment;
        });
    }
}
