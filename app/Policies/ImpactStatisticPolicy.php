<?php

namespace App\Policies;

use App\Models\ImpactStatistic;
use App\Models\User;

class ImpactStatisticPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('impact.view');
    }

    public function view(User $user, ImpactStatistic $record): bool
    {
        return $user->can('impact.view');
    }

    public function create(User $user): bool
    {
        return $user->can('impact.create');
    }

    public function update(User $user, ImpactStatistic $record): bool
    {
        return $user->can('impact.update');
    }

    public function delete(User $user, ImpactStatistic $record): bool
    {
        return $user->can('impact.delete');
    }

    public function restore(User $user, ImpactStatistic $record): bool
    {
        return $user->can('impact.restore');
    }

    public function forceDelete(User $user, ImpactStatistic $record): bool
    {
        return false;
    }

    public function verify(User $user, ImpactStatistic $record): bool
    {
        return $user->can('impact.verify') && $user->can('impact.update');
    }

    public function publish(User $user, ImpactStatistic $record): bool
    {
        return $user->can('impact.publish');
    }
}
