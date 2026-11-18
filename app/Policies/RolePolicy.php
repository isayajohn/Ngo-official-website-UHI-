<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('role.manage');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('role.manage');
    }

    public function create(User $user): bool
    {
        return $user->can('role.manage');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('role.manage') && $role->name !== 'Super Admin';
    }

    public function delete(User $user, Role $role): bool
    {
        return $this->update($user, $role) && ! $role->users()->exists();
    }
}
