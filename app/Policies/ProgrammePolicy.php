<?php

namespace App\Policies;

use App\Models\Programme;
use App\Models\User;

class ProgrammePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('programme.view');
    }

    public function view(User $user, Programme $programme): bool
    {
        return $user->can('programme.view');
    }

    public function create(User $user): bool
    {
        return $user->can('programme.create');
    }

    public function update(User $user, Programme $programme): bool
    {
        return $user->can('programme.update');
    }

    public function delete(User $user, Programme $programme): bool
    {
        return $user->can('programme.delete');
    }

    public function restore(User $user, Programme $programme): bool
    {
        return $user->can('programme.restore');
    }

    public function forceDelete(User $user, Programme $programme): bool
    {
        return false;
    }
}
