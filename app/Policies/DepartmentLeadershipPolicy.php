<?php

namespace App\Policies;

use App\Models\DepartmentLeadership;
use App\Models\User;
use App\Services\DepartmentAccessService;

class DepartmentLeadershipPolicy
{
    public function viewAny(User $user): bool { return $user->can('department.view') || $user->can('organization.full_access'); }
    public function view(User $user, DepartmentLeadership $record): bool { return app(DepartmentAccessService::class)->canAccessDepartment($user, $record->department); }
    public function create(User $user): bool { return $user->can('department.manage_leadership') || $user->can('organization.full_access'); }
    public function update(User $user, DepartmentLeadership $record): bool { return $user->can('department.manage_leadership') && app(DepartmentAccessService::class)->canAccessDepartment($user, $record->department) || $user->can('organization.full_access'); }
    public function delete(User $user, DepartmentLeadership $record): bool { return $this->update($user, $record); }
}
