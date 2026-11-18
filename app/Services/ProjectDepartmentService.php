<?php

namespace App\Services;

use App\Models\Department;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Collection;

class ProjectDepartmentService
{
    public function assignableDepartments(User $user): Collection
    {
        $query = Department::query()->where('active', true)->orderBy('name');
        if (! app(DepartmentAccessService::class)->hasOrganizationAccess($user)) {
            $query->where(function ($query) use ($user): void {
                $query->whereHas('users', fn ($users) => $users->where('users.id', $user->id)->where('user_departments.active', true))
                    ->orWhereHas('activeLeadership.teamMember', fn ($members) => $members->where('user_id', $user->id))
                    ->orWhereHas('head', fn ($members) => $members->where('user_id', $user->id));
            });
        }

        return $query->get();
    }

    /**
     * Synchronize the lead and supporting departments after checking scope on the server.
     * A user cannot silently remove assignments they cannot manage.
     */
    public function sync(Project $project, User $user, ?int $leadDepartmentId, array $supportingDepartmentIds): void
    {
        if (! $user->can('department.assign_project') && ! $user->can('organization.full_access')) {
            abort(403);
        }

        $supportingDepartmentIds = collect($supportingDepartmentIds)
            ->filter(fn ($id) => filled($id))
            ->map(fn ($id) => (int) $id)
            ->unique()
            ->reject(fn (int $id) => $id === (int) $leadDepartmentId)
            ->values();
        $requestedIds = collect([$leadDepartmentId])->filter()->map(fn ($id) => (int) $id)->merge($supportingDepartmentIds)->unique();
        $allowedIds = $this->assignableDepartments($user)->modelKeys();

        abort_unless($requestedIds->diff($allowedIds)->isEmpty(), 403, 'You cannot assign this project to one or more selected departments.');

        $sync = [];
        if ($leadDepartmentId) {
            $sync[(int) $leadDepartmentId] = ['is_lead' => true];
        }
        foreach ($supportingDepartmentIds as $id) {
            $sync[$id] = ['is_lead' => false];
        }

        $project->departments()->sync($sync);
    }
}
