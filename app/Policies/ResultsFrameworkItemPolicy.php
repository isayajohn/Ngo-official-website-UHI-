<?php

namespace App\Policies;

use App\Models\ResultsFrameworkItem;
use App\Models\User;
use App\Services\PerformanceScopeService;

class ResultsFrameworkItemPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('organization.full_access') || $user->can('mel.results_framework.view') || $user->can('mel.results_framework.manage');
    }

    public function view(User $user, ResultsFrameworkItem $item): bool
    {
        if (! $this->viewAny($user)) return false;
        return $this->inScope($user, $item);
    }

    public function create(User $user): bool
    {
        return $user->can('organization.full_access') || $user->can('mel.results_framework.manage');
    }

    public function update(User $user, ResultsFrameworkItem $item): bool
    {
        return $this->create($user) && $this->inScope($user, $item);
    }

    public function delete(User $user, ResultsFrameworkItem $item): bool
    {
        return $this->update($user, $item) && ! $item->children()->exists() && ! $item->indicators()->exists();
    }

    private function inScope(User $user, ResultsFrameworkItem $item): bool
    {
        if ($user->can('organization.full_access')) return true;
        $scope = app(PerformanceScopeService::class);
        if ($item->project_id) return in_array($item->project_id, $scope->projectIds($user), true);
        if ($item->programme_id) return in_array($item->programme_id, $scope->programmeIds($user), true);
        return $user->can('mel.results_framework.view') || $user->can('mel.results_framework.manage');
    }
}
