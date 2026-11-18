<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('user.view') || $user->can('user.manage');
    }

    public function view(User $user, User $record): bool
    {
        return $user->can('user.view') || $this->update($user, $record);
    }

    public function create(User $user): bool
    {
        return $user->can('user.create') || $user->can('user.manage');
    }

    public function update(User $user, User $record): bool
    {
        return ($user->can('user.update') || $user->can('user.manage')) && (! $record->hasRole('Super Admin') || $user->can('role.manage'));
    }

    public function delete(User $user, User $record): bool
    {
        return false;
    }
}
