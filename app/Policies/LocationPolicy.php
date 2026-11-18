<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class LocationPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('location.view');
    }

    public function view(User $user, Model $record): bool
    {
        return $user->can('location.view');
    }

    public function create(User $user): bool
    {
        return $user->can('location.create');
    }

    public function update(User $user, Model $record): bool
    {
        return $user->can('location.update');
    }

    public function delete(User $user, Model $record): bool
    {
        return $user->can('location.delete');
    }

    public function restore(User $user, Model $record): bool
    {
        return $user->can('location.restore');
    }

    public function forceDelete(User $user, Model $record): bool
    {
        return false;
    }
}
