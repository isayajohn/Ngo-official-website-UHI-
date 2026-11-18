<?php

namespace App\Policies;

use App\Models\Department;
use App\Models\User;
use App\Services\DepartmentAccessService;

class DepartmentPolicy
{
    public function viewAny(User $user): bool { return $user->can('department.view') || $user->can('organization.full_access'); }
    public function view(User $user, Department $department): bool { return ($user->can('department.view') || $user->can('organization.full_access')) && app(DepartmentAccessService::class)->canAccessDepartment($user, $department); }
    public function create(User $user): bool { return $user->can('department.create'); }
    public function update(User $user, Department $department): bool { return app(DepartmentAccessService::class)->canManageDepartment($user, $department); }
    public function delete(User $user, Department $department): bool { return $user->can('department.delete') && app(DepartmentAccessService::class)->canAccessDepartment($user, $department); }
    public function restore(User $user, Department $department): bool { return $user->can('department.restore'); }
}
