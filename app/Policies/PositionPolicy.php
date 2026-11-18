<?php

namespace App\Policies;

use App\Models\Position;
use App\Models\User;

class PositionPolicy
{
    public function viewAny(User $user): bool { return $user->can('position.view'); }
    public function view(User $user, Position $position): bool { return $user->can('position.view'); }
    public function create(User $user): bool { return $user->can('position.create'); }
    public function update(User $user, Position $position): bool { return $user->can('position.update'); }
    public function delete(User $user, Position $position): bool { return $user->can('position.delete'); }
    public function restore(User $user, Position $position): bool { return $user->can('position.restore'); }
}
