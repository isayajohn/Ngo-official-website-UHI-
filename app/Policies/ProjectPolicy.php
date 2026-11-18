<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use App\Services\DepartmentAccessService;

class ProjectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('project.view') || $user->can('organization.full_access');
    }

    public function view(User $user, Project $record): bool
    {
        return ($user->can('project.view') || $user->can('organization.full_access')) && app(DepartmentAccessService::class)->canViewProject($user, $record);
    }

    public function create(User $user): bool
    {
        return $user->can('project.create');
    }

    public function update(User $user, Project $record): bool
    {
        return app(DepartmentAccessService::class)->canManageProject($user, $record);
    }

    public function delete(User $user, Project $record): bool
    {
        return $user->can('project.delete') && app(DepartmentAccessService::class)->canManageProject($user, $record);
    }

    public function restore(User $user, Project $record): bool
    {
        return $user->can('project.restore');
    }

    public function forceDelete(User $user, Project $record): bool
    {
        return false;
    }

    public function publish(User $user, Project $record): bool
    {
        return $user->can('project.publish');
    }
}
